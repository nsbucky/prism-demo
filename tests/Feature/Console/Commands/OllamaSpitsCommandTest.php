<?php

use Illuminate\Support\Facades\Artisan;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Usage;
use Prism\Prism\Testing\TextResponseFake;


test('only accepts strings as a prompt', function () {
    $this->artisan('ollama:spits',['feugo' => []])
         ->assertFailed();
});


it('returns some text', function () {
    $fakeResponse = TextResponseFake::make()
                                    ->withText('Spit noise')
                                    ->withUsage(new Usage(10, 20));

    // Set up the fake
    $fake = Prism::fake([$fakeResponse]);

    // Run your code
    $this->artisan('ollama:spits',['feugo' => 'Hello'])
        ->expectsOutput('Spit noise')
         ->assertSuccessful();

    // Make assertions
});

