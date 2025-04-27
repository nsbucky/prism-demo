<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class TextEmbeddingCommand extends Command
{
    protected $signature = 'ollama:text-embed';

    protected $description = 'Generate text embeddings using Ollama mxbai-embed-large model ';

    public function handle()
    {
        // https://prismphp.com/core-concepts/embeddings.html
        $response = Prism::embeddings()
                         ->using(Provider::Ollama, 'mxbai-embed-large')
                         ->fromInput('this is text input king - man = queen')
                         ->asEmbeddings();

        $embeddings = $response->embeddings[0]->embedding;

        $this->info('Embeddings: ' . implode(', ', $embeddings));

        return self::SUCCESS;
    }
}
