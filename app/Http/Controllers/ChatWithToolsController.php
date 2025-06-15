<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'messages'        => 'required|array',
            'messages.*'      => 'required|array',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.text' => 'required|string|max:1000',
            'temperature'     => 'nullable|numeric|min:0|max:1|prohibits:topP',
            'topP'            => 'nullable|numeric|min:0|max:1|prohibits:temperature',
        ]);

        $prompt      = $validated['messages'] ?? [];
        $temperature = $validated['temperature'] ?? null;
        $topP        = $validated['topP'] ?? null;

        return response()->stream(function () use ($prompt, $temperature, $topP): void {
            self::sendStreamHeaders();

            $response = $this->getPrismGenerator($prompt['0']['text'], $temperature, $topP);

            foreach ($response as $chunk) {
                // Log tool calls
                if (ChunkType::ToolCall === $chunk->chunkType) {
                    foreach ($chunk->toolCalls as $call) {
                        logger("Tool called: " . $call->name);
                    }
                }

                // Check for tool results
                if (ChunkType::ToolResult === $chunk->chunkType) {
                    $this->extractToolResults($chunk);
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

        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type'  => 'text/event-stream',
        ]);
    }

    /**
     * @param $text
     * @param  mixed  $temperature
     * @param  mixed  $topP
     * @return Generator
     */
    public function getPrismGenerator($text, mixed $temperature, mixed $topP): Generator
    {
        $prism = Prism::text()
            ->using(Provider::Ollama, 'llama3.2')
            ->withTools(self::getTools())
            ->withMaxSteps(5)
            ->withSystemPrompt('You are a helpful assistant that responds in 250 words or less.')
            ->withPrompt($text ?? '');

        if (null !== $temperature) {
            $prism = $prism->usingTemperature($temperature);
        } elseif (null !== $topP) {
            $prism = $prism->usingTopP($topP);
        }

        return $prism->asStream();
    }

    /**
     * @param  mixed  $chunk
     * @return void
     */
    public function extractToolResults(mixed $chunk): void
    {
        foreach ($chunk->toolResults as $result) {
            logger("Tool result: " . $result->result);

            $this->toolResults[$result->toolName] = $result->result;

            logger('Tool structure', [
                'toolCallId' => $result->toolCallId,
                'toolName'   => $result->toolName,
                'args'       => $result->args,
                'result'     => $result->result,
            ]);

            $toolResultHtml = is_string($result->result)
                ? (json_decode($result->result, true)['html'] ?? '')
                : ($result->result['html'] ?? '');

            if ('' !== mb_trim($toolResultHtml)) {
                $this->toolHtml[$result->toolName] = $toolResultHtml;
            }
        }
    }

    /**
     * @return void
     */
    public function flushToolHtmlResponse(): void
    {
        logger('toolHtml', $this->toolHtml);

        echo 'data: ' . json_encode([
            'html' => implode('', $this->toolHtml),
        ]) . "\n\n";

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

        echo 'data: ' . json_encode($data) . "\n\n";

        ob_flush();
        flush();
    }

    /**
     * @return void
     */
    public function flushFinalToolResultsPayload(): void
    {
        if ($this->toolResults) {
            logger("Final tool results: " . json_encode($this->toolResults));

            // Send tool results and HTML
            logger("Final tool HTML: " . json_encode($this->toolHtml));

            echo 'data: ' . json_encode([
                'text'        => '',  // Empty text since this is just metadata
                'toolResults' => $this->toolResults,
                'toolHtml'    => $this->toolHtml,
                'role'        => 'ai',
                //'overwrite'   => false
            ]) . "\n\n";
        }

        ob_flush();
        flush();
    }

    private static function getTools(): array
    {
        return [
            Tool::as('search')
                ->for('Search for user')
                ->withStringParameter('name', 'The name of the person you are searching for with this tool')
                ->using(function (string $name): string {
                    $user = User::where('name', 'like', "%{$name}%")->first();

                    if ($user) {
                        return json_encode([
                            'data' => [
                                'name'  => $user->name,
                                'email' => $user->email,
                                'id'    => $user->id,
                            ],
                            'html' => '<a href="/users/' . $user->id . '" class="btn btn-info">View User</a>',
                        ]);
                    }

                    return '[]';
                }),
            Tool::as('count_users')
                ->for('Count the number of users')
                ->withStringParameter('notUsed', 'This parameter is not used, but required for the tool to work')
                ->using(function (string $notUsed): string {

                    $userCount = User::count();

                    return json_encode([
                        'count' => $userCount,
                        'html'  => '<p>Total number of users: ' . $userCount . '</p>',
                    ]);
                }),
            Tool::as('list_users')
                ->for('List users in a period')
                ->withStringParameter('date_start', 'the start date of the period you want to search for')
                ->withStringParameter('date_end', 'the end date of the period you want to search for')
                ->withNumberParameter('limit', 'the maximum number of users to return')
                ->using(function (string $date_start, string $date_end, int $limit = 10): string {
                    $dateStart = $dateEnd = null;

                    try {
                        $dateStart = Carbon::parse($date_start);
                        $dateEnd   = Carbon::parse($date_end);
                    } catch (Exception $e) {
                    }

                    $limit = $limit > 30 ? 30 : $limit; // Limit to 30 users for performance

                    $users = User::query()
                        ->select(['id', 'name', 'email', 'created_at'])
                        ->when($dateStart, fn($query) => $query->where('created_at', '>=', $dateStart))
                        ->when($dateEnd, fn($query) => $query->where('created_at', '<=', $dateEnd))
                        ->limit($limit) // Limit to 100 users for performance
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
}
