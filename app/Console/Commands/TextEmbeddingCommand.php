<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use App\Services\GPT3Encoder;

class TextEmbeddingCommand extends Command
{
    protected $signature = 'ollama:text-embed';

    protected $description = 'Generate text embeddings using Ollama mxbai-embed-large model ';

    public function handle()
    {
        $sampleText = 'this is text input king - man = queen';

        $tokens = GPT3Encoder::encode($sampleText);

        $this->info('Tokens: ' . implode(', ', $tokens));

        // https://prismphp.com/core-concepts/embeddings.html
        $response = Prism::embeddings()
                         ->using(Provider::Ollama, 'mxbai-embed-large')
                         ->fromInput($sampleText)
                         ->asEmbeddings();

        $embeddings = $response->embeddings[0]->embedding;

        $this->info('Embeddings: ' . implode(', ', $embeddings));

        $embeddingArray = array_map('floatval', $response->embeddings[0]->embedding);
        $formattedEmbedding = '[' . implode(',', $embeddingArray) . ']';

        Document::create([
            'name'          => 'Document Name',
            'embedding'     => $formattedEmbedding,
            'original_text' => $sampleText
        ]);

        return self::SUCCESS;
    }
}
