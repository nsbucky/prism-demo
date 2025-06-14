<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class ChatWithToolsController
{
    public function __invoke(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'prompt'      => 'required|string|max:255',
            'temperature' => 'nullable|numeric|min:0|max:1|prohibits:topP',
            'topP'        => 'nullable|numeric|min:0|max:1|prohibits:temperature',
        ]);

        $prompt      = $validated['prompt'];
        $temperature = $validated['temperature'] ?? null;
        $topP        = $validated['topP'] ?? null;

        return response()->stream(function () use ($prompt, $temperature, $topP) {
            // Set proper headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');

            // Stream the AI response
            $prism = Prism::text()
                          ->using(Provider::Ollama, 'llama3.2')
                          ->withSystemPrompt('You are a helpful assistant that responds in 250 words or less.')
                          ->withPrompt($prompt);

            // Apply temperature or topP if provided
            if ($temperature !== null) {
                $prism = $prism->usingTemperature($temperature);
            } elseif ($topP !== null) {
                $prism = $prism->usingTopP($topP);
            }

            $response = $prism->asStream();

            foreach ($response as $chunk) {
                echo 'data: '.json_encode(['chunk' => $chunk->text])."\n\n";
                ob_flush();
                flush();

                // Check if the chunk has a finish reason
                if ($chunk->finishReason) {
                    break; // Stop streaming if finished
                }
            }

            echo 'data: '.json_encode(['done' => true])."\n\n";
            ob_flush();
            flush();

        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type'  => 'text/event-stream',
        ]);
    }
}
