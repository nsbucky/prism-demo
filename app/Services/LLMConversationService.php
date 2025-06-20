<?php

declare(strict_types=1);

namespace App\Services;

use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class LLMConversationService
{
    public function __construct(
        protected ConversationFlowService $flowService,
    ) {
    }

    public function handleUserResponse(string $userResponse): array
    {
        // Get current state and markdown
        $state           = $this->flowService->getState();
        $markdown        = $state['markdown'];
        $currentQuestion = $this->flowService->getCurrentQuestion();

        if (!$currentQuestion) {
            return [
                'success'    => false,
                'message'    => 'No active question',
                'isComplete' => true,
            ];
        }

        // Build system prompt with conversation state
        $systemPrompt = $this->buildSystemPrompt($markdown, $currentQuestion);

        // Process with LLM
        $result = Prism::text()
                       ->using(Provider::Ollama, 'llama3.2')
                       ->withSystemPrompt($systemPrompt)
                       ->withTools(ConversationToolService::getExtractionTools())
                       ->withPrompt("User response: {$userResponse}")
                       ->withMaxSteps(5)
                       ->asText();

        // Extract data from tool calls
        $extractedData = $this->extractDataFromResult($result);

        // Check if extraction was successful
        if ($currentQuestion['requiresExtraction'] ?? false) {
            if (!$extractedData || !($extractedData['success'] ?? false)) {
                return [
                    'success'    => false,
                    'message'    => $extractedData['message'] ?? 'Please provide a valid response with all required information.',
                    'retry'      => true,
                    'aiResponse' => $result->text,
                ];
            }
        }

        // Update conversation state
        $processResult = $this->flowService->processAnswer($userResponse, $extractedData['data'] ?? $extractedData);

        return array_merge($processResult, [
            'aiResponse' => $result->text,
            'markdown'   => $this->flowService->generateMarkdownSummary(),
        ]);
    }

    public function getConversationExample(): array
    {
        return [
            'questions' => [
                [
                    'id'                 => 'traveler_name',
                    'text'               => 'What is your name, or who will be travelling?',
                    'requiresExtraction' => true,
                    'extractionTool'     => 'confirm_name_extraction',
                ],
                [
                    'id'                 => 'shipping_address',
                    'text'               => 'Where should we ship your order?',
                    'requiresExtraction' => true,
                    'extractionTool'     => 'confirm_address_extraction',
                ],
                [
                    'id'                 => 'travel_dates',
                    'text'               => 'What are your preferred travel dates?',
                    'requiresExtraction' => true,
                    'extractionTool'     => 'confirm_date_extraction',
                ],
                [
                    'id'                 => 'special_requests',
                    'text'               => 'Do you have any special requests or requirements?',
                    'requiresExtraction' => false,
                ],
            ],
        ];
    }

    protected function buildSystemPrompt(string $markdown, ?array $currentQuestion): string
    {
        $prompt = <<<PROMPT
You are conducting a structured interview. Here is the current conversation state:

{$markdown}

Current Question: {$currentQuestion['text']}

Instructions:
1. Validate the user's response answers the current question
2. If the question requires extraction (like names, dates, or addresses), use the appropriate tool
3. Only accept valid answers before moving to the next question
4. For name questions: ensure you get both first and last name
5. For date questions: ensure you get valid dates in YYYY-MM-DD format
6. For address questions: ensure you get all required fields based on the country

If the response is invalid or missing information, politely ask the user to provide the missing information.

IMPORTANT: You must use the extraction tools when the question requires extraction. Do not proceed without calling the appropriate tool.
PROMPT;

        return $prompt;
    }

    protected function extractDataFromResult($result): ?array
    {
        /*
         * Illuminate\Support\Collection^ {#3121
  #items: array:2 [
    0 => Prism\Prism\Testing\TextStepFake^ {#3098
      +text: "I understand your name is John Doe."
      +finishReason: Prism\Prism\Enums\FinishReason^ {#2959
        +name: "ToolCalls"
      }
      +toolCalls: array:1 [
        0 => Prism\Prism\ValueObjects\ToolCall^ {#3124
          +id: "call_name_123"
          +name: "confirm_name_extraction"
          #arguments: array:2 [
            "firstName" => "John"
            "lastName" => "Doe"
          ]
          +resultId: null
          +reasoningId: null
          +reasoningSummary: null
        }
      ]
      +toolResults: []
      +usage: Prism\Prism\ValueObjects\Usage^ {#2958
        +promptTokens: 50
        +completionTokens: 100
        +cacheWriteInputTokens: null
        +cacheReadInputTokens: null
        +thoughtTokens: null
      }
      +meta: Prism\Prism\ValueObjects\Meta^ {#2957
        +id: "fake-1"
        +model: "llama3.2"
        +rateLimits: []
      }
      +messages: []
      +systemPrompts: []
      +additionalContent: []
    }
    1 => Prism\Prism\Testing\TextStepFake^ {#2960
      +text: "I understand your name is John Doe."
      +finishReason: Prism\Prism\Enums\FinishReason^ {#3099
        +name: "Stop"
      }
      +toolCalls: []
      +toolResults: array:1 [
        0 => Prism\Prism\ValueObjects\ToolResult^ {#2956
          +toolCallId: "call_name_123"
          +toolName: "confirm_name_extraction"
          +args: array:2 [
            "firstName" => "John"
            "lastName" => "Doe"
          ]
          +result: "{"success":true,"data":{"firstName":"John","lastName":"Doe","fullName":"John Doe"}}"
          +toolCallResultId: null
        }
      ]
      +usage: Prism\Prism\ValueObjects\Usage^ {#2953
        +promptTokens: 20
        +completionTokens: 30
        +cacheWriteInputTokens: null
        +cacheReadInputTokens: null
        +thoughtTokens: null
      }
      +meta: Prism\Prism\ValueObjects\Meta^ {#2954
        +id: "fake-2"
        +model: "llama3.2"
        +rateLimits: []
      }
      +messages: []
      +systemPrompts: []
      +additionalContent: []
    }
  ]
  #escapeWhenCastingToString: false
}

         */
        return collect($result->steps)
            ->filter(fn($step) => $step->toolResults && count($step->toolResults) > 0)
            ->flatMap(function ($step) {
                return collect($step->toolResults)
                    ->map(function ($toolResult) {
                        // Parse the JSON result from the tool
                        $resultData = is_string($toolResult->result)
                            ? json_decode($toolResult->result, true)
                            : $toolResult->result;

                        return [
                            'success' => $resultData['success'] ?? false,
                            'data'    => $resultData['data'] ?? null,
                            'message' => $resultData['message'] ?? null,
                        ];
                    });
            })
            ->first();

    }
}
