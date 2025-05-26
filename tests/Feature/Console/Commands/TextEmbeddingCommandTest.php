<?php

use App\Console\Commands\TextEmbeddingCommand;
use Prism\Prism\Prism;
use Prism\Prism\Testing\EmbeddingsResponseFake;
use Prism\Prism\ValueObjects\Embedding;

it('generates text embeddings with default input', function () {
    $fakeMatrix = array_fill(0, 1024, 0.1);
    $fakeEmbeddings = Embedding::fromArray($fakeMatrix);
    $fakeResponse = EmbeddingsResponseFake::make()
        ->withEmbeddings([$fakeEmbeddings]);

    $fake = Prism::fake([$fakeResponse]);

    $this->artisan(TextEmbeddingCommand::class)
        ->expectsOutputToContain('🔤 Text Embedding Analysis')
        ->expectsQuestion('Enter text to generate embeddings', 'Welcome to Spatula City! Where are we?')
        ->expectsOutputToContain('Generating embeddings')
        ->expectsOutputToContain('Embedding Values (first 10 of 1024 dimensions)')
        ->expectsTable(
            ['Index', 'Value'],
            array_map(fn ($i) => ['index' => $i, 'value' => $fakeMatrix[$i]], range(0, 9))
        )
        ->expectsOutputToContain('Full embedding vector (truncated):')
        ->expectsOutputToContain('0.1, 0.1, 0.1')
        ->assertSuccessful();

    $fake->assertCallCount(1);
});

it('generates embeddings with custom input', function () {
    // Create a fake embedding response with different values
    $fakeEmbeddings = array_map(fn ($i) => $i * 0.01, range(1, 1024));
    $fakeEmbeddings = Embedding::fromArray($fakeEmbeddings);
    $fakeResponse = EmbeddingsResponseFake::make()
        ->withEmbeddings([$fakeEmbeddings]);

    $fake = Prism::fake([$fakeResponse]);

    $this->artisan(TextEmbeddingCommand::class)
        ->expectsQuestion('Enter text to generate embeddings', 'The spatula is the ultimate kitchen tool')
        ->expectsTable(
            ['Index', 'Value'],
            array_map(fn ($i) => ['index' => $i - 1, 'value' => $i * 0.01], range(1, 10))
        )
        ->assertSuccessful();

    $fake->assertCallCount(1);
});
