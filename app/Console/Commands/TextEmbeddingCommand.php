<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use function Laravel\Prompts\text;

class TextEmbeddingCommand extends Command
{
    protected $signature = 'ollama:text-embed';

    protected $description = 'Generate text embeddings using Ollama mxbai-embed-large model';

    private array $tokens = [];
    private array $embeddings = [];

    public function handle()
    {
        $this->newLine();
        $this->components->info('🔤 Text Embedding Analysis');
        $this->newLine();

        $sampleText = text(
            label: 'Enter text to generate embeddings',
            placeholder: 'e.g., Welcome to Spatula City! Where are we?',
            default: 'Welcome to Spatula City! Where are we?',
            required: true
        );

        $this->components->twoColumnDetail('Input Text', $sampleText);
        $this->newLine();

        // Embedding generation section
        $this->components->task('Generating embeddings', function () use ($sampleText) {
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

        $tableData = collect($this->embeddings)
            ->take(10)
            ->map(function ($value, $index) {
                return [
                    'index' => $index,
                    'value' => $value
                ];
            })
            ->all();

        $this->table(['Index', 'Value'], $tableData);

        $this->newLine();
        $this->components->info('Full embedding vector (truncated):');
        $this->line(Str::limit(implode(', ', $this->embeddings), 100) . '...');

        return self::SUCCESS;
    }
}
