<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Prism\Prism\Enums\ChunkType;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Prism;

class ChatWithToolsController
{
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
            // Set proper headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');

            // Stream the AI response
            $prism = Prism::text()
                          ->using(Provider::Ollama, 'llama3.2')
                          ->withTools(self::getTools())
                          ->withMaxSteps(5)
                          ->withSystemPrompt('You are a helpful assistant that responds in 250 words or less.')
                          ->withPrompt($prompt['0']['text'] ?? '');

            if (null !== $temperature) {
                $prism = $prism->usingTemperature($temperature);
            } elseif (null !== $topP) {
                $prism = $prism->usingTopP($topP);
            }

            $response = $prism->asStream();

            $toolResults = [];
            $toolHtml    = [];

            foreach ($response as $chunk) {
                // Check for tool calls
                if ($chunk->chunkType === ChunkType::ToolCall) {
                    foreach ($chunk->toolCalls as $call) {
                        logger("Tool called: ".$call->name);
                    }
                }

                // Check for tool results
                if ($chunk->chunkType === ChunkType::ToolResult) {
                    foreach ($chunk->toolResults as $result) {
                        logger("Tool result: ".$result->result);

                        $toolResults[$result->toolName] = $result->result;

                        logger('Tool structure', [
                            'toolCallId' => $result->toolCallId,
                            'toolName'   => $result->toolName,
                            'args'       => $result->args,
                            'result'     => $result->result,
                        ]);

                        $toolResultHtml = is_string($result->result)
                            ? (json_decode($result->result, true)['html'] ?? '')
                            : ($result->result['html'] ?? '');

                        if (trim($toolResultHtml) !== '') {
                            $toolHtml[$result->toolName] = $toolResultHtml;
                        }
                    }
                }


                if (count(array_filter($toolHtml)) > 0) {
                    logger('toolHtml', $toolHtml);
                    echo 'data: '.json_encode([
                            'html' => implode('', $toolHtml),
                        ])."\n\n";
                    ob_flush();
                    flush();

                    break;
                }

                // Stop streaming if finished
                if ($chunk->finishReason) {
                    break;
                }

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

            // Send final message with tool results if any
            if ($toolResults) {
                logger("Final tool results: ".json_encode($toolResults));
                // Send tool results and HTML
                logger("Final tool HTML: ".json_encode($toolHtml));
                echo 'data: '.json_encode([
                        'text'        => '',  // Empty text since this is just metadata
                        'toolResults' => $toolResults,
                        'toolHtml'    => $toolHtml,
                        'role'        => 'ai',
                        //'overwrite'   => false
                    ])."\n\n";
            }

            ob_flush();
            flush();

        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type'  => 'text/event-stream',
        ]);
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
                            'html' => '<a href="/users/'.$user->id.'" class="btn btn-info">View User</a>',
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
                        'html'  => '<p>Total number of users: '.$userCount.'</p>',
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
                    } catch (\Exception $e) {
                    }

                    $limit = $limit > 30 ? 30 : $limit; // Limit to 30 users for performance

                    $users = User::query()
                                 ->select(['id', 'name', 'email', 'created_at'])
                                 ->when($dateStart, function ($query) use ($dateStart) {
                                     return $query->where('created_at', '>=', $dateStart);
                                 })
                                 ->when($dateEnd, function ($query) use ($dateEnd) {
                                     return $query->where('created_at', '<=', $dateEnd);
                                 })
                                 ->limit($limit) // Limit to 100 users for performance
                                 ->get();

                    return json_encode([
                        'data' => $users->toArray(),
                        'html' => view('tools_list_users', [
                            'users' => $users,
                            'date_start' => $dateStart ? $dateStart->format('Y-m-d') : null,
                            'date_end'   => $dateEnd ? $dateEnd->format('Y-m-d') : null,
                        ])->render(),
                    ]);
                }),
        ];
    }
}
