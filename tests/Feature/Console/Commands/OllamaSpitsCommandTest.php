<?php

use App\Console\Commands\OllamaSpitsCommand;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;

it('returns some text', function () {
    $fakeResponse = TextResponseFake::make()
                                    ->withText('Rawr!');

    $fake = Prism::fake([$fakeResponse]);

    $this->artisan(OllamaSpitsCommand::class, ['fuego' => 'Hello'])
         ->expectsOutput('Rawr!')
         ->assertSuccessful();

    $fake->assertPrompt('Hello');

});

