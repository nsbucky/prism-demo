<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Generator;
use Illuminate\Http\Request;
use Prism\Prism\Enums\ChunkType;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Prism;

class ChatWithToolsController
{
    private array $toolResults = [];
    private array $toolHtml = [];

    private ChatSession $session;

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'messages'        => 'required|array',
            'messages.*'      => 'required|array',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.text' => 'required|string|max:1000',
            'temperature'     => 'nullable|numeric|min:0|max:1|prohibits:topP',
            'topP'            => 'nullable|numeric|min:0|max:1|prohibits:temperature',
            'session_id'      => 'nullable|uuid|exists:chat_sessions,session_id',
        ]);

        $prompt      = $validated['messages'] ?? [];
        $temperature = $validated['temperature'] ?? null;
        $topP        = $validated['topP'] ?? null;
        $sessionId   = $validated['session_id'] ?? null;

        return response()->stream(function () use ($prompt, $temperature, $topP, $sessionId): void {
            self::sendStreamHeaders();

            // Get or create session
            $this->session = $this->getOrCreateSession($sessionId);

            // Save user message to history
            if (!empty($prompt[0]['text'])) {
                $this->session->messages()->create([
                    'role'    => 'user',
                    'content' => $prompt[0]['text'],
                ]);
            }

            // Build full message history for Prism
            $messages = $this->session->getMessagesForPrism();

            $response = $this->getPrismGenerator($prompt[0]['text'], $temperature, $topP, $messages);

            $assistantContent   = '';
            $toolCallsCollected = [];

            foreach ($response as $chunk) {
                // Collect assistant content
                if ($chunk->text) {
                    $assistantContent .= $chunk->text;
                }

                // Log tool calls
                if (ChunkType::ToolCall === $chunk->chunkType) {
                    foreach ($chunk->toolCalls as $call) {
                        logger("Tool called: ".$call->name);
                    }
                }

                // Check for tool results
                if (ChunkType::ToolResult === $chunk->chunkType) {
                    $toolCallsCollected [] = $this->extractToolResults($chunk);
                }

                // If tool HTML is available, flush it immediately, you can't mix text and html in DeepChat response
                if ($this->hasToolHtml()) {
                    $this->flushToolHtmlResponse();
                    break;
                }

                // stream is over!
                if ($chunk->finishReason) {
                    break;
                }

                $this->flushTextChunk($chunk);
            }

            $this->flushFinalToolResultsPayload();

            // Save assistant message to history
            if ($assistantContent || $toolCallsCollected || $this->toolResults) {
                $this->saveSessionHistory($assistantContent, $toolCallsCollected);
            }

        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type'  => 'text/event-stream',
        ]);
    }

    /**
     * @param $text
     * @param  mixed  $temperature
     * @param  mixed  $topP
     * @param  array  $messages
     * @return Generator
     */
    public function getPrismGenerator($text, mixed $temperature, mixed $topP, array $messages = []): Generator
    {
        $prism = Prism::text()
                      ->using(Provider::Ollama, 'llama3.2')
                      ->withTools(self::getTools())
                      ->withMaxSteps(5);

        // Use message history if available, otherwise use prompt
        if (!empty($messages)) {
            $prism = $prism->withMessages($messages);
        } else {
            $prism = $prism->withPrompt($text ?? '');
        }

        if (null !== $temperature) {
            $prism = $prism->usingTemperature($temperature);
        } elseif (null !== $topP) {
            $prism = $prism->usingTopP($topP);
        }

        return $prism->asStream();
    }

    /**
     * @param  mixed  $chunk
     * @return array
     */
    public function extractToolResults(mixed $chunk): array
    {
        $results = [];

        foreach ($chunk->toolResults as $result) {
            logger("Tool result: ".$result->result);

            $this->toolResults[$result->toolName] = $result->result;

            logger('Tool structure', [
                'toolCallId' => $result->toolCallId,
                'toolName'   => $result->toolName,
                'arguments'  => $result->args,
                'result'     => $result->result,
            ]);

            $results[] = [
                'id'        => $result->toolCallId,
                'name'      => $result->toolName,
                'arguments' => $result->args,
            ];

            $toolResultHtml = is_string($result->result)
                ? (json_decode($result->result, true)['html'] ?? '')
                : ($result->result['html'] ?? '');

            if ('' !== mb_trim($toolResultHtml)) {
                $this->toolHtml[$result->toolName] = $toolResultHtml;
            }
        }

        return $results;
    }

    /**
     * @return void
     */
    public function flushToolHtmlResponse(): void
    {
        logger('toolHtml', $this->toolHtml);

        echo 'data: '.json_encode([
                'html' => implode('', $this->toolHtml),
            ])."\n\n";

        ob_flush();

        flush();
    }

    /**
     * @param  mixed  $chunk
     * @return void
     */
    public function flushTextChunk(mixed $chunk): void
    {
        // Send both text and any custom data
        $data = ['text' => $chunk->text];

        // Add chunk metadata if needed
        /*if ($chunk->chunkType === ChunkType::ToolCall || $chunk->chunkType === ChunkType::ToolResult) {
            $data['chunkType'] = $chunk->chunkType->value;
        }*/

        echo 'data: '.json_encode($data)."\n\n";

        ob_flush();
        flush();
    }

    /**
     * @return void
     */
    public function flushFinalToolResultsPayload(): void
    {
        if ($this->toolResults) {
            logger("Final tool results: ".json_encode($this->toolResults));

            // Send tool results and HTML
            logger("Final tool HTML: ".json_encode($this->toolHtml));

            echo 'data: '.json_encode([
                    'text'        => '',  // Empty text since this is just metadata
                    'toolResults' => $this->toolResults,
                    'toolHtml'    => $this->toolHtml,
                    'role'        => 'ai',
                    'session_id'  => $this->session->session_id,
                    //'overwrite'   => false
                ])."\n\n";
        }

        ob_flush();
        flush();
    }

    private static function getTools(): array
    {
        return [
            Tool::as('extract_contact_information')
                ->withStringParameter('first_name', 'The current user\'s first name')
                ->withStringParameter('last_name', 'The current user\'s last name')
                ->withStringParameter('email', 'The current user\'s email address')
                ->withStringParameter('phone', 'The current user\'s phone number')
                ->for('Extract contact information from a text')
                ->using(function (
                    string $first_name = null,
                    string $last_name = null,
                    string $email = null,
                    string $phone = null
                ): string {
                    // Simulate extracting contact information
                    $contactInfo = [
                        'first_name' => $first_name,
                        'last_name'  => $last_name,
                        'email'      => $email,
                        'phone'      => $phone,
                    ];

                    return json_encode([
                        'data' => $contactInfo,
                    ]);
                }),
            Tool::as('search')
                ->for('Search for user')
                ->withStringParameter('name', 'The name of the person you are searching for with this tool')
                ->using(function (string $name=null): string {
                    $user = User::where('name', 'like', "%{$name}%")->first();

                    if ($user) {
                        return json_encode([
                            'data' => [
                                'name'  => $user->name,
                                'email' => $user->email,
                                'id'    => $user->id,
                            ],
                            'html' => '<a href="/users/'.$user->id.'" class="btn btn-info">View User</a>',
                        ]);
                    }

                    return '[]';
                }),
            Tool::as('count_users')
                ->for('Count the number of users')
                ->withStringParameter('notUsed', 'This parameter is not used, but required for the tool to work')
                ->using(function (string $notUsed = null): string {

                    $userCount = User::count();

                    return json_encode([
                        'count' => $userCount,
                        'html'  => '<p>Total number of users: '.$userCount.'</p>',
                    ]);
                }),
            Tool::as('list_users')
                ->for('List users in a period')
                ->withStringParameter('date_start', 'the start date of the period you want to search for')
                ->withStringParameter('date_end', 'the end date of the period you want to search for')
                ->withNumberParameter('limit', 'the maximum number of users to return')
                ->using(function (string $date_start = null, string $date_end = null, $limit = 10): string {
                    $dateStart = $dateEnd = null;

                    try {
                        $dateStart = Carbon::parse($date_start);
                        $dateEnd   = Carbon::parse($date_end);
                    } catch (Exception $e) {
                    }

                    $limit = $limit > 30 ? 30 : $limit; // Limit to 30 users for performance

                    $users = User::query()
                                 ->select(['id', 'name', 'email', 'created_at'])
                                 ->when($dateStart,
                                     fn($query) => $query->where('created_at', '>=', $dateStart),
                                     fn($query) => $query->where('created_at', '>=', Carbon::now()->subDays(3))
                                 )
                                 ->when($dateEnd,
                                     fn($query) => $query->where('created_at', '<=', $dateEnd),
                                     fn($query) => $query->where('created_at', '<=', Carbon::now())
                                 )
                                 ->limit($limit) //
                        // tap the query to log it for debugging
                                 ->tap(function ($query) use ($dateStart, $dateEnd, $limit) {
                            logger('List users query', [
                                'query'      => $query->toSql(),
                                'bindings'   => $query->getBindings(),
                                'date_start' => $dateStart ? $dateStart->format('Y-m-d') : null,
                                'date_end'   => $dateEnd ? $dateEnd->format('Y-m-d') : null,
                                'limit'      => $limit,
                            ]);
                        })
                                 ->orderByDesc('created_at')
                                 ->get();

                    return json_encode([
                        'data' => $users->toArray(),
                        'html' => view('tools_list_users', [
                            'users'      => $users,
                            'date_start' => $dateStart ? $dateStart->format('Y-m-d') : null,
                            'date_end'   => $dateEnd ? $dateEnd->format('Y-m-d') : null,
                        ])->render(),
                    ]);
                }),
        ];
    }

    /**
     * @return void
     */
    private static function sendStreamHeaders(): void
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
    }

    private function hasToolHtml(): bool
    {
        return count(array_filter($this->toolHtml)) > 0;
    }

    private function getOrCreateSession(?string $sessionId): ChatSession
    {
        if ($sessionId) {
            $session = ChatSession::where('session_id', $sessionId)
                                  ->when(auth()->id(), fn($q) => $q->where('user_id', auth()->id()))
                                  ->first();

            if ($session) {
                return $session;
            }
        }

        return ChatSession::create([
            'user_id' => auth()->id(),
            'title'   => 'New Chat Session',
        ]);
    }

    /**
     * @param  string  $assistantContent
     * @param  array  $toolCallsCollected
     * @return void
     */
    function saveSessionHistory(string $assistantContent, array $toolCallsCollected): void
    {
        $this->session->messages()->create([
            'role'       => 'assistant',
            'content'    => $assistantContent ?: '',
            'tool_calls' => $toolCallsCollected ?: null,
        ]);

        // Save tool results as separate message if any
        if ($this->toolResults) {
            $this->session->messages()->create([
                'role'         => 'tool_result',
                'content'      => json_encode($this->toolResults),
                'tool_results' => array_map(function ($toolName, $result) {
                    return [
                        'toolCallId' => '',  // We'd need to track this from tool calls
                        'toolName'   => $toolName,
                        'args'       => [],  // We'd need to track this from tool calls
                        'result'     => $result,
                    ];
                }, array_keys($this->toolResults), $this->toolResults),
            ]);
        }
    }
}
