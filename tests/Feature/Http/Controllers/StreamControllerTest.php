<?php

use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;

it('can stream a response', function () {

    $fakeResponse = TextResponseFake::make()
        ->withText('Sup Al!')
        ->withFinishReason(FinishReason::Stop);

    $prismFake = Prism::fake([$fakeResponse]);

    $response = $this->get('/stream?prompt=Hello');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');

    $content = $response->streamedContent();
    $this->assertStringContainsString('data: {"done":true}', $content);
    $this->assertStringContainsString('data: {"chunk":"Sup Al!"}', $content);

    $prismFake->assertPrompt('Hello');
    $prismFake->assertCallCount(1);
})->skip(1); // waiting for the fix of the Prism package
