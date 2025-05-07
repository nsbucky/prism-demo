<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\GPT3Encoder;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class TextEmbeddingCommand extends Command
{
    protected $signature = 'ollama:text-embed {text? : The text to embed}';

    protected $description = 'Generate text embeddings using Ollama mxbai-embed-large model';

    private array $tokens = [];
    private array $embeddings = [];

    public function handle()
    {
        $this->newLine();
        $this->components->info('Text Embedding Analysis');
        $this->newLine();

        $sampleText = $this->argument('text') ?? 'Welcome to Spatula City! Where are we?';

        $this->components->twoColumnDetail('Input Text', $sampleText);
        $this->newLine();

        // Tokenization section
        $this->components->task('Tokenizing text', function() use ($sampleText) {
            $this->tokens = GPT3Encoder::encode($sampleText);
            return true;
        });

        $this->components->bulletList([
            'Token count: ' . count($this->tokens),
            'Tokens: ' . Str::limit(implode(', ', $this->tokens), 60)
        ]);
        $this->newLine();

        // Embedding generation section
        $this->components->task('Generating embeddings', function() use ($sampleText) {
            $response = Prism::embeddings()
                ->withClientOptions(['timeout' => 60])
                ->using(Provider::Ollama, 'mxbai-embed-large')
                ->fromInput($sampleText)
                ->asEmbeddings();

            $this->embeddings = $response->embeddings[0]->embedding;
            return true;
        });

        // Display embeddings in a nice table format
        $this->components->info('Embedding Values (first 10 of ' . count($this->embeddings) . ' dimensions)');

        $tableData = [];
        for ($i = 0; $i < min(10, count($this->embeddings)); $i++) {
            $tableData[] = [
                'index' => $i,
                'value' => $this->embeddings[$i]
            ];
        }

        $this->table(['Index', 'Value'], $tableData);

        $this->newLine();
        $this->components->info('Full embedding vector (truncated):');
        $this->line(Str::limit(implode(', ', $this->embeddings), 100) . '...');

        return self::SUCCESS;
    }
}
