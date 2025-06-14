<?php

use Prism\Prism\Prism;
use Prism\Prism\Testing\EmbeddingsResponseFake;

it('can generate embeddings', function () {
    $fakeEmbedding = array_fill(0, 1024, 0.123456); // Create fake embedding array
    
    $fakeResponse = EmbeddingsResponseFake::make()
        ->withEmbeddings([
            [
                'embedding' => $fakeEmbedding,
                'index' => 0,
            ]
        ]);
    
    $prismFake = Prism::fake([$fakeResponse]);
    
    $response = $this->withoutMiddleware()
        ->post('/embedding', [
            'text' => 'Welcome to Spatula City!'
        ]);
    
    $response->assertStatus(302); // Redirect back
    $response->assertSessionHas('embedding');
    
    $embeddingData = session('embedding');
    expect($embeddingData['text'])->toBe('Welcome to Spatula City!');
    expect($embeddingData['total_dimensions'])->toBe(1024);
    expect($embeddingData['embeddings'])->toHaveCount(50);
    
    $prismFake->assertCallCount(1);
})->skip(1); // waiting for the fix of the Prism package

it('validates required text input', function () {
    $response = $this->withoutMiddleware()
        ->post('/embedding', []);
    
    $response->assertStatus(302); // Redirect back on validation failure
    $response->assertSessionHasErrors('text');
});

it('validates max length of text input', function () {
    $longText = str_repeat('a', 501);
    
    $response = $this->withoutMiddleware()
        ->post('/embedding', [
            'text' => $longText
        ]);
    
    $response->assertStatus(302); // Redirect back on validation failure
    $response->assertSessionHasErrors('text');
});
