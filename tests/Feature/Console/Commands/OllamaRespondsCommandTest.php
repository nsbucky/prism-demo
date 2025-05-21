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

    $this->artisan(OllamaRespondsCommand::class)
        ->expectsQuestion('Prompt','Where is Uncle Nutzy\'s Clubhouse?')
        ->expectsOutputToContain('🦙 Ollama Response Generator')
        ->expectsOutputToContain('Response from Ollama (llama3.2 model)')
        ->expectsOutputToContain('Complete!')
        ->assertSuccessful();

    $fake->assertCallCount(1);

});

