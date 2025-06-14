<?php

use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;

it('can stream a response', function () {

    $fakeResponse = TextResponseFake::make()
                                    ->withText('Sup Al!')
                                    ->withFinishReason(FinishReason::Stop);

    $prismFake = Prism::fake([$fakeResponse]);

    $response = $this->post('/chat', [
        'messages' => [
            ['role' => 'user', 'text' => 'Hello'],
        ],
    ]);

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');

    $content = $response->streamedContent();
    $this->assertStringContainsString('data: {"text":"Sup Al!"}', $content);

    $prismFake->assertPrompt('Hello');
    $prismFake->assertCallCount(1);
})->skip(1); // waiting for the fix of the Prism package

it('can stream a response with temperature', function () {

    $fakeResponse = TextResponseFake::make()
                                    ->withText('Creative response!')
                                    ->withFinishReason(FinishReason::Stop);

    $prismFake = Prism::fake([$fakeResponse]);

    $response = $this->post('/chat', [
        'messages'    => [
            ['role' => 'user', 'text' => 'Hello'],
        ],
        'temperature' => 0.8,
    ]);

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');

    $content = $response->streamedContent();
    $this->assertStringContainsString('data: {"done":true}', $content);
    $this->assertStringContainsString('data: {"chunk":"Creative response!"}', $content);

    $prismFake->assertPrompt('Hello');
    $prismFake->assertCallCount(1);
})->skip(1); // waiting for the fix of the Prism package

it('can stream a response with topP', function () {

    $fakeResponse = TextResponseFake::make()
                                    ->withText('Focused response!')
                                    ->withFinishReason(FinishReason::Stop);

    $prismFake = Prism::fake([$fakeResponse]);

    $response = $this->post('/chat', [
        'messages' => [
            ['role' => 'user', 'text' => 'Hello'],
        ],
        'topP'     => 0.3,
    ]);

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');

    $content = $response->streamedContent();
    $this->assertStringContainsString('data: {"done":true}', $content);
    $this->assertStringContainsString('data: {"chunk":"Focused response!"}', $content);

    $prismFake->assertPrompt('Hello');
    $prismFake->assertCallCount(1);
})->skip(1); // waiting for the fix of the Prism package

it('validates temperature range', function () {
    $response = $this->post('/chat', [
        'messages'    => [
            ['role' => 'user', 'text' => 'Hello'],
        ],
        'temperature' => 1.5,
    ]);

    $response->assertStatus(302)
             ->assertSessionHasErrors('temperature'); // Laravel returns 422 for validation errors
});

it('validates topP range', function () {
    $response = $this->post('/chat', [
        'messages' => [
            ['role' => 'user', 'text' => 'Hello'],
        ],
        'topP'     => -0.1,
    ]);

    $response->assertStatus(302)
             ->assertSessionHasErrors(['topP']); // Laravel returns 422 for validation errors
});

it('rejects using both temperature and topP', function () {
    $response = $this->post('/chat', [
        'messages'    => [
            ['role' => 'user', 'text' => 'Hello'],
        ],
        'temperature' => 0.5,
        'topP'        => 0.5,
    ]);
    $response->assertStatus(302)
             ->assertSessionHasErrors(['temperature', 'topP']);
});
