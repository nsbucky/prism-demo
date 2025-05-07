<?php

use App\Console\Commands\OllamaRespondsCommand;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Illuminate\Support\Str;

it('returns some text', function () {
    $randomText = Str::random();

    $fakeResponse = TextResponseFake::make()
                                    ->withText($randomText);

    $fake = Prism::fake([$fakeResponse]);

    $this->artisan(OllamaRespondsCommand::class, ['prompt' => 'Hello'])
        ->expectsOutputToContain('🦙 Ollama Response Generator')
        ->expectsOutputToContain('Response from Ollama (llama3.2 model)')
        ->expectsOutputToContain($randomText)
        ->expectsOutputToContain('Response complete!')
        ->assertSuccessful();

    $fake->assertPrompt('Hello');

    $fake->assertCallCount(1);

});

