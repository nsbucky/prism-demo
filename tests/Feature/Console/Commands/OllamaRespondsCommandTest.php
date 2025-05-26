<?php

use App\Console\Commands\OllamaRespondsCommand;
use Illuminate\Support\Str;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;

it('returns some text', function () {

    $fakeResponse = TextResponseFake::make()
        ->withText('Hello, Ollama!');

    $fake = Prism::fake([$fakeResponse]);

    $this->artisan(OllamaRespondsCommand::class)
        ->expectsQuestion('You', 'Hello, Ollama!')
        ->expectsOutputToContain('🦙 Ollama Chat Interface')
        ->expectsQuestion('You', 'exit')
        ->assertSuccessful();

    $fake->assertCallCount(1);

});

it('exits when prompted', function () {
    $randomText = Str::random();

    $fakeResponse = TextResponseFake::make()
        ->withText($randomText);

    $fake = Prism::fake([$fakeResponse]);

    $this->artisan(OllamaRespondsCommand::class)
        ->expectsQuestion('You', 'exit')
        ->expectsOutputToContain('🦙 Ollama Chat Interface')
        ->doesntExpectOutputToContain('Ollama is thinking...')
        ->assertSuccessful();

    $fake->assertCallCount(0);

});
