<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class EmbeddingController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:500',
        ]);

        $response = Prism::embeddings()
            ->withClientOptions(['timeout' => 60])
            ->using(Provider::Ollama, 'mxbai-embed-large')
            ->fromInput($validated['text'])
            ->asEmbeddings();

        $embeddings = $response->embeddings[0]->embedding;

        $embeddingData = [
            'text' => $validated['text'],
            'embeddings' => array_slice($embeddings, 0, 50), // Return first 50 values for display
            'total_dimensions' => count($embeddings),
        ];

        return back()->with('payload', $embeddingData);
    }
}
