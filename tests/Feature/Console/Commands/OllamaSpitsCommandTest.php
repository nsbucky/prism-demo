<?php

use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;


test('only accepts strings as a prompt', function () {
    $this->artisan('ollama:spits', ['fuego' => []])
         ->assertFailed();
});


it('returns some text', function () {
    $fakeResponse = TextResponseFake::make()
                                    ->withText('Spit noise')
                                    ->withUsage(new Usage(10, 20));

    Prism::fake([$fakeResponse]);

    $this->artisan('ollama:spits', ['fuego' => 'Hello'])
         ->expectsOutput('Spit noise')
         ->assertSuccessful();

});

