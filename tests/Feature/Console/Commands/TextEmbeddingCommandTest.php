<?php

use App\Console\Commands\TextEmbeddingCommand;
use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Prism\Prism\Prism;
use Prism\Prism\Testing\EmbeddingResponseFake;

beforeEach(function () {
    // Mock the text prompt to avoid interactive input during tests
    Prompt::fake([
        'Welcome to Spatula City! Where are we?',
        Key::ENTER,
    ]);
});

it('generates text embeddings with default input', function () {
    // Create a fake embedding response
    $fakeEmbeddings = array_fill(0, 1024, 0.1); // mxbai-embed-large uses 1024 dimensions
    
    $fakeResponse = EmbeddingResponseFake::make()
        ->withEmbedding($fakeEmbeddings);
    
    $fake = Prism::fake([$fakeResponse]);

    $this->artisan(TextEmbeddingCommand::class)
        ->expectsOutputToContain('🔤 Text Embedding Analysis')
        ->expectsOutputToContain('Input Text')
        ->expectsOutputToContain('Welcome to Spatula City! Where are we?')
        ->expectsOutputToContain('Generating embeddings')
        ->expectsOutputToContain('Embedding Values (first 10 of 1024 dimensions)')
        ->expectsTable(
            ['Index', 'Value'],
            array_map(fn($i) => ['index' => $i, 'value' => 0.1], range(0, 9))
        )
        ->expectsOutputToContain('Full embedding vector (truncated):')
        ->expectsOutputToContain('0.1, 0.1, 0.1')
        ->assertSuccessful();

    $fake->assertCallCount(1);
});

it('generates embeddings with custom input', function () {
    // Mock custom input
    Prompt::fake([
        'The spatula is the ultimate kitchen tool',
        Key::ENTER,
    ]);

    // Create a fake embedding response with different values
    $fakeEmbeddings = array_map(fn($i) => $i * 0.01, range(1, 1024));
    
    $fakeResponse = EmbeddingResponseFake::make()
        ->withEmbedding($fakeEmbeddings);
    
    $fake = Prism::fake([$fakeResponse]);

    $this->artisan(TextEmbeddingCommand::class)
        ->expectsOutputToContain('The spatula is the ultimate kitchen tool')
        ->expectsOutputToContain('Generating embeddings')
        ->expectsTable(
            ['Index', 'Value'],
            array_map(fn($i) => ['index' => $i - 1, 'value' => $i * 0.01], range(1, 10))
        )
        ->assertSuccessful();

    $fake->assertCallCount(1);
});

it('handles embeddings of different sizes', function () {
    // Test with a smaller embedding size
    $fakeEmbeddings = array_fill(0, 512, 0.5);
    
    $fakeResponse = EmbeddingResponseFake::make()
        ->withEmbedding($fakeEmbeddings);
    
    Prism::fake([$fakeResponse]);

    $this->artisan(TextEmbeddingCommand::class)
        ->expectsOutputToContain('Embedding Values (first 10 of 512 dimensions)')
        ->expectsTable(
            ['Index', 'Value'],
            array_map(fn($i) => ['index' => $i, 'value' => 0.5], range(0, 9))
        )
        ->assertSuccessful();
});

it('truncates long embedding vectors correctly', function () {
    $fakeEmbeddings = array_fill(0, 1024, 0.123456789);
    
    $fakeResponse = EmbeddingResponseFake::make()
        ->withEmbedding($fakeEmbeddings);
    
    Prism::fake([$fakeResponse]);

    $this->artisan(TextEmbeddingCommand::class)
        ->expectsOutputToContain('Full embedding vector (truncated):')
        ->expectsOutputToContain('0.123456789')
        ->expectsOutputToContain('...')
        ->assertSuccessful();
});