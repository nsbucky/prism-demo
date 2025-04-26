<?php

use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\Usage;

test('only accepts strings as a prompt', function () {
    $this->artisan('ollama:streams',['prompt' => []])
         ->assertFailed();
});


it('stream is not supported by PrismFake', function () {
    $fakeResponse = TextResponseFake::make()
                                    ->withText('Spit noise');

    Prism::fake([$fakeResponse]);

    $this->artisan('ollama:streams',['prompt' => 'Hello'])
         ->assertFailed();

})->throws(PrismException::class);;
