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

            // Apply temperature or topP if provided
            if (null !== $temperature) {
                $prism = $prism->usingTemperature($temperature);
            } elseif (null !== $topP) {
                $prism = $prism->usingTopP($topP);
            }

            $response = $prism->asStream();

            $toolResults = [];

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
                    }
                }

                // Send both text and any custom data
                $data = ['text' => $chunk->text];
                
                // Add chunk metadata if needed
                if ($chunk->chunkType === ChunkType::ToolCall || $chunk->chunkType === ChunkType::ToolResult) {
                    $data['chunkType'] = $chunk->chunkType->value;
                }
                
                echo 'data: '.json_encode($data)."\n\n";

                ob_flush();
                flush();

                // Stop streaming if finished
                if ($chunk->finishReason) {
                    break;
                }
            }

            // Send final message with tool results if any
            if ($toolResults) {
                echo 'data: '.json_encode([
                    'text' => '',  // Empty text since this is just metadata
                    'toolResults' => $toolResults,
                    'done' => true
                ])."\n\n";
            } else {
                echo 'data: '.json_encode([
                    'text' => '',
                    'done' => true
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
                        return json_encode(['name' => $user->name, 'email' => $user->email]);
                    }

                    return '[]';
                }),
            Tool::as('count')
                ->for('Count the number of users')
                ->using(function (): string {
                    $userCount = User::count();

                    return "Total number of users: {$userCount}";
                }),
            Tool::as('export_users')
                ->for('Export users according to the given criteria')
                ->withStringParameter('date_start', 'the start date of the period you want to search for')
                ->withStringParameter('date_end', 'the end date of the period you want to search for')
                ->using(function (string $date_start, string $date_end): string {
                    try {
                        $dateStart = Carbon::parse($date_start);
                        $dateEnd   = Carbon::parse($date_end);
                    } catch (\Exception $e) {
                        return 'Date could not be figured out from input, sorry!';
                    }

                    $users = User::whereBetween('created_at', [
                        $dateStart->toDateTimeString(),
                        $dateEnd->toDateTimeString(),
                    ])->get();

                    if ($users->isEmpty()) {
                        return 'Users not found';
                    }

                    $fp = fopen('php://temp', 'r+');

                    fputcsv($fp, ['Name', 'Email', 'Created At']);

                    foreach ($users as $user) {
                        fputcsv($fp, [
                            $user->name,
                            $user->email,
                            $user->created_at,
                        ]);
                    }

                    rewind($fp);

                    return stream_get_contents($fp);
                }),
        ];
    }
}
