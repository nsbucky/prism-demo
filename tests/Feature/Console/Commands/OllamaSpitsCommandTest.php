<?php

test('requires a prompt', function () {
    Artisan::call('ollama:spits')
           ->assertExitCode(1);
});

test('only accepts strings as a prompt', function () {
    Artisan::call('ollama:spits', ['feugo' => []])
           ->assertExitCode(1);
});
