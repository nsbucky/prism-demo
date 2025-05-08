<?php

use App\Http\Controllers\ToolController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\ValueObjects\ToolCall;
use Prism\Prism\ValueObjects\ToolResult;

beforeEach(function () {
    $this->controller = new ToolController();
});

it('returns a response when using a tool', function () {
    // Create a test user that can be found by the search tool
    $user = User::factory()->create([
        'name'  => 'Test User',
        'email' => 'test@example.com'
    ]);

    $expectedResponse = 'I found Test User';

    $fake = Prism::fake([
        TextResponseFake::make()
                        ->withToolCalls([
                            new ToolCall(
                                id: 'search',
                                name: 'search',
                                arguments: ['name' => 'Test User'],
                            )
                        ])
                        ->withFinishReason(FinishReason::ToolCalls),

        TextResponseFake::make()
                        ->withText($expectedResponse)
                        ->withToolResults([
                            new ToolResult(
                                toolCallId: 'search',
                                toolName: 'search',
                                args: ['name' => 'Test User'],
                                result: 'Found user: Test User with email:' . $user->email
                            )
                        ])
                        ->withFinishReason(FinishReason::Stop)
    ]);

    $request = Request::create('/tool', 'POST', [
        'prompt' => 'Find user Test User'
    ]);

    $response = $this->controller->__invoke($request);

    expect($response)->toBe($expectedResponse);

    $fake->assertPrompt('Find user Test User');
    $fake->assertCallCount(2);
})->skip(1);// waiting for testing stuff to work from prism

it('validates that prompt is required', function () {
    $request = Request::create('/tool', 'POST', [
        'prompt' => ''
    ]);

    $this->expectException(ValidationException::class);

    $this->controller->__invoke($request);
});

it('validates that prompt is a string', function () {
    $request = Request::create('/tool', 'POST', [
        'prompt' => 123
    ]);

    $this->expectException(ValidationException::class);

    $this->controller->__invoke($request);
});

it('validates that prompt is not too long', function () {
    $request = Request::create('/tool', 'POST', [
        'prompt' => Str::random(256)
    ]);

    $this->expectException(ValidationException::class);

    $this->controller->__invoke($request);
});

it('passes with valid prompt data', function () {
    $expectedResponse = 'Valid tool response';

    $fakeResponse = TextResponseFake::make()
                                    ->withText($expectedResponse);

    Prism::fake([$fakeResponse]);

    $request = Request::create('/tool', 'POST', [
        'prompt' => 'Valid prompt for tool'
    ]);

    $response = $this->controller->__invoke($request);

    expect($response)->toBe($expectedResponse);
});

