<?php

use App\Http\Controllers\RespondsController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;

beforeEach(function () {
    $this->controller = new RespondsController();
});

it('returns a response from Ollama', function () {
    $expectedResponse = 'This is a test response.';

    $fakeResponse = TextResponseFake::make()
        ->withText($expectedResponse);

    $fake = Prism::fake([$fakeResponse]);

    $request = Request::create('/responds', 'POST', [
        'prompt' => 'Hello world'
    ]);

    $response = $this->controller->__invoke($request);

    expect($response)->toBe($expectedResponse);

    $fake->assertPrompt('Hello world');
    $fake->assertCallCount(1);
});

it('validates that prompt is required', function () {
    $request = Request::create('/responds', 'POST', [
        'prompt' => ''
    ]);

    $this->expectException(ValidationException::class);

    $this->controller->__invoke($request);
});

it('validates that prompt is a string', function () {
    $request = Request::create('/responds', 'POST', [
        'prompt' => 123
    ]);

    $this->expectException(ValidationException::class);

    $this->controller->__invoke($request);
});

it('validates that prompt is not too long', function () {
    $request = Request::create('/responds', 'POST', [
        'prompt' => Str::random(256)
    ]);

    $this->expectException(ValidationException::class);

    $this->controller->__invoke($request);
});

it('passes with valid prompt data', function () {
    $expectedResponse = 'Valid response';

    $fakeResponse = TextResponseFake::make()
        ->withText($expectedResponse);

    Prism::fake([$fakeResponse]);

    $request = Request::create('/responds', 'POST', [
        'prompt' => 'Valid prompt'
    ]);

    $response = $this->controller->__invoke($request);

    expect($response)->toBe($expectedResponse);
});
