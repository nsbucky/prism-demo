<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class StreamController extends Controller
{
    public function __invoke(Request $request)
    {
        // Validate the request
        $request->validate([
            'prompt' => 'required|string|max:255',
        ]);

        $prompt = $request->input('prompt');

        return response()->stream(function () use ($prompt) {
            // Set proper headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');

            // Stream the AI response
            $response = Prism::text()
                             ->using(Provider::Ollama, 'llama3.2')
                             ->withPrompt($prompt)
                             ->asStream();

            foreach ($response as $chunk) {
                echo "data: " . json_encode(['chunk' => $chunk->text]) . "\n\n";
                ob_flush();
                flush();

                // Check if the chunk has a finish reason
                if ($chunk->finishReason) {
                    break; // Stop streaming if finished
                }
            }

            echo "data: " . json_encode(['done' => true]) . "\n\n";
            ob_flush();
            flush();

        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type'  => 'text/event-stream',
        ]);
    }
}
