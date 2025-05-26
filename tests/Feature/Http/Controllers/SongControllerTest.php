<?php

use Prism\Prism\Prism;
use Prism\Prism\Testing\EmbeddingsResponseFake;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Embedding;

it('validates the input', function () {
    $this->postJson('/song')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['prompt']);
});

it('calls the console command with the correct prompt', function () {

    $prompt = 'Test prompt';

    // create a 1024 dimensional embedding
    $embedding = [];

    for ($i = 0; $i < 1024; $i++) {
        $embedding[] = rand(0, 1);
    }

    $prismFake = Prism::fake([
        EmbeddingsResponseFake::make()
            ->withEmbeddings([Embedding::fromArray($embedding)]),
        TextResponseFake::make()
            ->withText('test, prompt'),
        TextResponseFake::make()
            ->withText('Twinkle Dinky, little star'),
    ]);

    $response = $this->post('/song', [
        'prompt' => $prompt,
    ]);

    $response->assertStatus(200)
        ->assertSee('Twinkle Dinky, little star');

})->skip(1);
