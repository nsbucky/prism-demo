<?php

use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\Tool;
use Prism\Prism\ValueObjects\Usage;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\ValueObjects\ToolCall;
use Prism\Prism\ValueObjects\ToolResult;

test('it can stalk users', function () {
    $responses = [
        // First response: AI decides to use the weather tool
        TextResponseFake::make()
                        ->withToolCalls([
                            new ToolCall(
                                id: 'call_123',
                                name: 'search',
                                arguments: ['name' => 'Ollama'],
                            )
                        ])
                        ->withFinishReason(FinishReason::ToolCalls)
                        ->withUsage(new Usage(15, 25))
                        ->withMeta(new Meta('fake-1', 'fake-model')),

        // Second response: AI uses the tool result to form a response
        TextResponseFake::make()
                        ->withText('The user "Ollama" with email "llama2@example.com" was found.')
                        ->withToolResults([
                            new ToolResult(
                                toolCallId: 'call_123',
                                toolName: 'search',
                                args: ['name' => 'Ollama'],
                                result: 'Found user: Ollama with email: llama2@example.com',
                            )
                        ])
                        ->withFinishReason(FinishReason::Stop)
                        ->withUsage(new Usage(20, 30))
                        ->withMeta(new Meta('fake-2', 'fake-model')),
    ];

    $fake = Prism::fake($responses);

    $this->artisan('ollama:tool', ['name' => 'Ollama'])
         ->expectsOutput('Searching for Ollama');

    $fake->assertCallCount(1);
});
