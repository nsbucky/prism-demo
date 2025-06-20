<?php

declare(strict_types=1);

use App\Services\ConversationFlowService;
use App\Services\LLMConversationService;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextStepFake;
use Prism\Prism\Text\ResponseBuilder;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\ToolCall;
use Prism\Prism\ValueObjects\ToolResult;
use Prism\Prism\ValueObjects\Usage;

beforeEach(function () {
    $this->flowService = Mockery::mock(ConversationFlowService::class);
    $this->service     = new LLMConversationService($this->flowService);
});

test('handles no active question scenario', function () {
    $this->flowService->shouldReceive('getState')
                      ->once()
                      ->andReturn(['markdown' => '## Conversation']);

    $this->flowService->shouldReceive('getCurrentQuestion')
                      ->once()
                      ->andReturn(null);

    $result = $this->service->handleUserResponse('Some response');

    expect($result)
        ->toHaveKey('success', false)
        ->toHaveKey('message', 'No active question')
        ->toHaveKey('isComplete', true);
});

test('processes user response with successful name extraction', function () {
    $currentQuestion = [
        'id'                 => 'traveler_name',
        'text'               => 'What is your name?',
        'requiresExtraction' => true,
        'extractionTool'     => 'confirm_name_extraction',
    ];

    $this->flowService->shouldReceive('getState')
                      ->once()
                      ->andReturn(['markdown' => '## Conversation Progress']);

    $this->flowService->shouldReceive('getCurrentQuestion')
                      ->once()
                      ->andReturn($currentQuestion);

    // Set up Prism fake with tool call response
    $responses = [
        (new ResponseBuilder)
            ->addStep(
                TextStepFake::make()
                            ->withText('I understand your name is John Doe.')
                            ->withToolCalls([
                                new ToolCall(
                                    id: 'call_name_123',
                                    name: 'confirm_name_extraction',
                                    arguments: ['firstName' => 'John', 'lastName' => 'Doe']
                                ),
                            ])
                            ->withFinishReason(FinishReason::ToolCalls)
                            ->withUsage(new Usage(50, 100))
                            ->withMeta(new Meta('fake-1', 'llama3.2'))
            )
            ->addStep(
                TextStepFake::make()
                            ->withText('I understand your name is John Doe.')
                            ->withToolResults([
                                new ToolResult(
                                    toolCallId: 'call_name_123',
                                    toolName: 'confirm_name_extraction',
                                    args: ['firstName' => 'John', 'lastName' => 'Doe'],
                                    result: json_encode([
                                        'success' => true,
                                        'data'    => [
                                            'firstName' => 'John',
                                            'lastName'  => 'Doe',
                                            'fullName'  => 'John Doe',
                                        ],
                                    ])
                                ),
                            ])
                            ->withFinishReason(FinishReason::Stop)
                            ->withUsage(new Usage(20, 30))
                            ->withMeta(new Meta('fake-2', 'llama3.2')),
            )
            ->toResponse(),
    ];

    Prism::fake($responses);

    $this->flowService->shouldReceive('processAnswer')
                      ->once()
                      ->with('John Doe', [
                          'firstName' => 'John',
                          'lastName'  => 'Doe',
                          'fullName'  => 'John Doe',
                      ])
                      ->andReturn([
                          'success'      => true,
                          'nextQuestion' => null,
                          'isComplete'   => false,
                      ]);

    $this->flowService->shouldReceive('generateMarkdownSummary')
                      ->once()
                      ->andReturn('## Updated Conversation');

    $result = $this->service->handleUserResponse('John Doe');

    expect($result)
        ->toHaveKey('success', true)
        ->toHaveKey('aiResponse', 'I understand your name is John Doe.')
        ->toHaveKey('markdown', '## Updated Conversation');
});

test('handles failed name extraction with retry', function () {
    $currentQuestion = [
        'id'                 => 'traveler_name',
        'text'               => 'What is your name?',
        'requiresExtraction' => true,
        'extractionTool'     => 'confirm_name_extraction',
    ];

    $this->flowService->shouldReceive('getState')
                      ->once()
                      ->andReturn(['markdown' => '## Conversation Progress']);

    $this->flowService->shouldReceive('getCurrentQuestion')
                      ->once()
                      ->andReturn($currentQuestion);

    // Set up Prism fake with failed extraction
    $responses = [
        (new ResponseBuilder)
            ->addStep(
                TextStepFake::make()
                            ->withText('Please provide both first and last name.')
                            ->withToolCalls([
                                new ToolCall(
                                    id: 'call_name_456',
                                    name: 'confirm_name_extraction',
                                    arguments: ['firstName' => 'John', 'lastName' => null]
                                ),
                            ])
                            ->withFinishReason(FinishReason::ToolCalls)
                            ->withUsage(new Usage(50, 100))
                            ->withMeta(new Meta('fake-1', 'llama3.2'))
            )
            ->addStep(
                TextStepFake::make()
                            ->withText('Please provide both first and last name.')
                            ->withToolResults([
                                new ToolResult(
                                    toolCallId: 'call_name_456',
                                    toolName: 'confirm_name_extraction',
                                    args: ['firstName' => 'John', 'lastName' => null],
                                    result: json_encode([
                                        'success' => false,
                                        'message' => 'Both first and last name are required',
                                    ])
                                ),
                            ])
                            ->withFinishReason(FinishReason::Stop)
                            ->withUsage(new Usage(20, 30))
                            ->withMeta(new Meta('fake-2', 'llama3.2')),
            )
            ->toResponse(),
    ];

    Prism::fake($responses);

    $result = $this->service->handleUserResponse('John');

    expect($result)
        ->toHaveKey('success', false)
        ->toHaveKey('message', 'Both first and last name are required')
        ->toHaveKey('retry', true)
        ->toHaveKey('aiResponse', 'Please provide both first and last name.');
});

test('processes response without extraction requirement', function () {
    $currentQuestion = [
        'id'                 => 'special_requests',
        'text'               => 'Do you have any special requests?',
        'requiresExtraction' => false,
    ];

    $this->flowService->shouldReceive('getState')
                      ->once()
                      ->andReturn(['markdown' => '## Conversation Progress']);

    $this->flowService->shouldReceive('getCurrentQuestion')
                      ->once()
                      ->andReturn($currentQuestion);

    // Set up Prism fake without tool calls
    $responses = [
        (new ResponseBuilder)
            ->addStep(
                TextStepFake::make()
                            ->withText('I noted your special requests.')
                            ->withFinishReason(FinishReason::Stop)
                            ->withUsage(new Usage(50, 100))
                            ->withMeta(new Meta('fake-1', 'llama3.2'))
            )
            ->toResponse(),
    ];

    Prism::fake($responses);

    $this->flowService->shouldReceive('processAnswer')
                      ->once()
                      ->with('No special requests', null)
                      ->andReturn([
                          'success'      => true,
                          'nextQuestion' => null,
                          'isComplete'   => true,
                      ]);

    $this->flowService->shouldReceive('generateMarkdownSummary')
                      ->once()
                      ->andReturn('## Completed Conversation');

    $result = $this->service->handleUserResponse('No special requests');

    expect($result)
        ->toHaveKey('success', true)
        ->toHaveKey('isComplete', true)
        ->toHaveKey('aiResponse', 'I noted your special requests.')
        ->toHaveKey('markdown', '## Completed Conversation');
});

test('processes address extraction with complete data', function () {
    $currentQuestion = [
        'id'                 => 'shipping_address',
        'text'               => 'Where should we ship your order?',
        'requiresExtraction' => true,
        'extractionTool'     => 'confirm_address_extraction',
    ];

    $this->flowService->shouldReceive('getState')
                      ->once()
                      ->andReturn(['markdown' => '## Conversation Progress']);

    $this->flowService->shouldReceive('getCurrentQuestion')
                      ->once()
                      ->andReturn($currentQuestion);

    $addressData = [
        'streetAddress'  => '123 Main St',
        'streetAddress2' => 'Apt 4B',
        'city'           => 'New York',
        'stateProvince'  => 'NY',
        'postalCode'     => '10001',
        'country'        => 'US',
        'fullAddress'    => '123 Main St, Apt 4B, New York, NY 10001, US',
    ];

    // Set up Prism fake with address extraction
    $responses = [
        (new ResponseBuilder)
            ->addStep(
                TextStepFake::make()
                            ->withText('I have your shipping address.')
                            ->withToolCalls([
                                new ToolCall(
                                    id: 'call_addr_789',
                                    name: 'confirm_address_extraction',
                                    arguments: [
                                        'streetAddress'  => '123 Main St',
                                        'streetAddress2' => 'Apt 4B',
                                        'city'           => 'New York',
                                        'stateProvince'  => 'NY',
                                        'postalCode'     => '10001',
                                        'country'        => 'US',
                                    ]
                                ),
                            ])
                            ->withFinishReason(FinishReason::ToolCalls)
                            ->withUsage(new Usage(100, 150))
                            ->withMeta(new Meta('fake-1', 'llama3.2'))
            )
            ->addStep(
                TextStepFake::make()
                            ->withText('I have your shipping address.')
                            ->withToolResults([
                                new ToolResult(
                                    toolCallId: 'call_addr_789',
                                    toolName: 'confirm_address_extraction',
                                    args: [
                                        'streetAddress'  => '123 Main St',
                                        'streetAddress2' => 'Apt 4B',
                                        'city'           => 'New York',
                                        'stateProvince'  => 'NY',
                                        'postalCode'     => '10001',
                                        'country'        => 'US',
                                    ],
                                    result: json_encode([
                                        'success' => true,
                                        'data'    => $addressData,
                                    ])
                                ),
                            ])
                            ->withFinishReason(FinishReason::Stop)
                            ->withUsage(new Usage(50, 75))
                            ->withMeta(new Meta('fake-2', 'llama3.2')),
            )
            ->toResponse(),
    ];

    Prism::fake($responses);

    $this->flowService->shouldReceive('processAnswer')
                      ->once()
                      ->with('123 Main St, Apt 4B, New York, NY 10001', $addressData)
                      ->andReturn([
                          'success'      => true,
                          'nextQuestion' => null,
                          'isComplete'   => false,
                      ]);

    $this->flowService->shouldReceive('generateMarkdownSummary')
                      ->once()
                      ->andReturn('## Updated with Address');

    $result = $this->service->handleUserResponse('123 Main St, Apt 4B, New York, NY 10001');

    expect($result)
        ->toHaveKey('success', true)
        ->toHaveKey('aiResponse', 'I have your shipping address.')
        ->toHaveKey('markdown', '## Updated with Address');
});

test('processes date extraction with duration calculation', function () {
    $currentQuestion = [
        'id'                 => 'travel_dates',
        'text'               => 'What are your preferred travel dates?',
        'requiresExtraction' => true,
        'extractionTool'     => 'confirm_date_extraction',
    ];

    $this->flowService->shouldReceive('getState')
                      ->once()
                      ->andReturn(['markdown' => '## Conversation Progress']);

    $this->flowService->shouldReceive('getCurrentQuestion')
                      ->once()
                      ->andReturn($currentQuestion);

    // Set up Prism fake with date extraction
    $responses = [
        (new ResponseBuilder)
            ->addStep(
                TextStepFake::make()
                            ->withText('I have your travel dates from January 1st to January 10th.')
                            ->withToolCalls([
                                new ToolCall(
                                    id: 'call_date_101',
                                    name: 'confirm_date_extraction',
                                    arguments: [
                                        'startDate' => '2024-01-01',
                                        'endDate'   => '2024-01-10',
                                    ]
                                ),
                            ])
                            ->withFinishReason(FinishReason::ToolCalls)
                            ->withUsage(new Usage(75, 125))
                            ->withMeta(new Meta('fake-1', 'llama3.2'))
            )
            ->addStep(
                TextStepFake::make()
                            ->withText('I have your travel dates from January 1st to January 10th.')
                            ->withToolResults([
                                new ToolResult(
                                    toolCallId: 'call_date_101',
                                    toolName: 'confirm_date_extraction',
                                    args: [
                                        'startDate' => '2024-01-01',
                                        'endDate'   => '2024-01-10',
                                    ],
                                    result: json_encode([
                                        'success' => true,
                                        'data'    => [
                                            'startDate' => '2024-01-01',
                                            'endDate'   => '2024-01-10',
                                            'duration'  => '9 days',
                                        ],
                                    ])
                                ),
                            ])
                            ->withFinishReason(FinishReason::Stop)
                            ->withUsage(new Usage(40, 60))
                            ->withMeta(new Meta('fake-2', 'llama3.2')),
            )
            ->toResponse(),
    ];

    Prism::fake($responses);

    $this->flowService->shouldReceive('processAnswer')
                      ->once()
                      ->with('January 1st to January 10th', [
                          'startDate' => '2024-01-01',
                          'endDate'   => '2024-01-10',
                          'duration'  => '9 days',
                      ])
                      ->andReturn([
                          'success'      => true,
                          'nextQuestion' => null,
                          'isComplete'   => false,
                      ]);

    $this->flowService->shouldReceive('generateMarkdownSummary')
                      ->once()
                      ->andReturn('## Updated with Dates');

    $result = $this->service->handleUserResponse('January 1st to January 10th');

    expect($result)
        ->toHaveKey('success', true)
        ->toHaveKey('aiResponse', 'I have your travel dates from January 1st to January 10th.')
        ->toHaveKey('markdown', '## Updated with Dates');
});

test('handles missing extraction data gracefully', function () {
    $currentQuestion = [
        'id'                 => 'traveler_name',
        'text'               => 'What is your name?',
        'requiresExtraction' => true,
    ];

    $this->flowService->shouldReceive('getState')
                      ->once()
                      ->andReturn(['markdown' => '## Conversation']);

    $this->flowService->shouldReceive('getCurrentQuestion')
                      ->once()
                      ->andReturn($currentQuestion);

    // Set up Prism fake with no tool calls (LLM didn't extract)
    $responses = [
        (new ResponseBuilder)
            ->addStep(
                TextStepFake::make()
                            ->withText('Could not extract information.')
                            ->withFinishReason(FinishReason::Stop)
                            ->withUsage(new Usage(50, 100))
                            ->withMeta(new Meta('fake-1', 'llama3.2'))
            )
            ->toResponse(),
    ];

    Prism::fake($responses);

    $result = $this->service->handleUserResponse('invalid input');

    expect($result)
        ->toHaveKey('success', false)
        ->toHaveKey('message', 'Please provide a valid response with all required information.')
        ->toHaveKey('retry', true)
        ->toHaveKey('aiResponse', 'Could not extract information.');
});

test('builds correct system prompt', function () {
    $currentQuestion = [
        'id'   => 'shipping_address',
        'text' => 'Where should we ship your order?',
    ];

    $markdown = '## Current State';

    $reflection = new ReflectionClass($this->service);
    $method     = $reflection->getMethod('buildSystemPrompt');
    $method->setAccessible(true);

    $result = $method->invoke($this->service, $markdown, $currentQuestion);

    expect($result)
        ->toContain('## Current State')
        ->toContain('Where should we ship your order?')
        ->toContain('You are conducting a structured interview')
        ->toContain('If the question requires extraction');
});

test('provides correct conversation example structure', function () {
    $example = $this->service->getConversationExample();

    expect($example)
        ->toHaveKey('questions')
        ->and($example['questions'])->toHaveCount(4);

    $firstQuestion = $example['questions'][0];
    expect($firstQuestion)
        ->toHaveKey('id', 'traveler_name')
        ->toHaveKey('text')
        ->toHaveKey('requiresExtraction', true)
        ->toHaveKey('extractionTool', 'confirm_name_extraction');

    $lastQuestion = $example['questions'][3];
    expect($lastQuestion)
        ->toHaveKey('id', 'special_requests')
        ->toHaveKey('requiresExtraction', false);
});

test('correctly extracts data from tool results', function () {
    $extractedData = [
        'success' => true,
        'data'    => ['test' => 'value'],
        'message' => null
    ];

    // Create a response with tool results
    $response = (new ResponseBuilder)
        ->addStep(
            TextStepFake::make()
                        ->withToolCalls([
                            new ToolCall(
                                id: 'test_call',
                                name: 'test_tool',
                                arguments: []
                            ),
                        ])
                        ->withFinishReason(FinishReason::ToolCalls)
        )
        ->addStep(
            TextStepFake::make()
                        ->withToolResults([
                            new ToolResult(
                                toolCallId: 'test_call',
                                toolName: 'test_tool',
                                args: [],
                                result: json_encode($extractedData)
                            ),
                        ])
                        ->withFinishReason(FinishReason::Stop)
        )
        ->toResponse();

    $reflection = new ReflectionClass($this->service);
    $method     = $reflection->getMethod('extractDataFromResult');
    $method->setAccessible(true);

    $result = $method->invoke($this->service, $response);

    expect($result)->toBe($extractedData);
});

test('returns null when no tool calls found in response', function () {
    // Create a response without tool calls
    $response = (new ResponseBuilder)
        ->addStep(
            TextStepFake::make()
                        ->withText('No tools used')
                        ->withFinishReason(FinishReason::Stop)
        )
        ->toResponse();

    $reflection = new ReflectionClass($this->service);
    $method     = $reflection->getMethod('extractDataFromResult');
    $method->setAccessible(true);

    $result = $method->invoke($this->service, $response);

    expect($result)->toBeNull();
});

afterEach(function () {
    Mockery::close();
});