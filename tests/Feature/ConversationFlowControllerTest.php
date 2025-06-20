<?php

declare(strict_types=1);

use App\Http\Controllers\ConversationFlowController;
use App\Services\ConversationFlowService;
use App\Services\LLMConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

it('can start a conversation and return first question', function (): void {
    $mockFlowService = Mockery::mock(ConversationFlowService::class);
    $mockLlmService  = Mockery::mock(LLMConversationService::class);

    $expectedQuestion = [
        'id'                 => 'traveler_name',
        'text'               => 'What is your name, or who will be travelling?',
        'requiresExtraction' => true,
        'extractionPrompt'   => 'Extract the first name and last name from this response',
    ];

    $expectedMarkdown = "## Conversation Progress\n\n- [ ] **What is your name, or who will be travelling?**\n\n**Progress: 0%**\n";

    $mockFlowService->shouldReceive('initializeQuestions')
                    ->once()
                    ->with(Mockery::type('array'));

    $mockFlowService->shouldReceive('getCurrentQuestion')
                    ->once()
                    ->andReturn($expectedQuestion);

    $mockFlowService->shouldReceive('generateMarkdownSummary')
                    ->once()
                    ->andReturn($expectedMarkdown);

    $controller = new ConversationFlowController($mockFlowService, $mockLlmService);
    $request    = new Request();

    $response = $controller->startConversation($request);


    expect($response)->toBeInstanceOf(JsonResponse::class);

    $data = $response->getData(true);
    expect($data)->toHaveKeys(['question', 'markdown']);
    expect($data['question'])->toBe($expectedQuestion);
    expect($data['markdown'])->toBe($expectedMarkdown);
});

it('can process user response and return result', function (): void {
    $mockFlowService = Mockery::mock(ConversationFlowService::class);
    $mockLlmService  = Mockery::mock(LLMConversationService::class);

    $userResponse   = 'My name is John Doe';
    $expectedResult = [
        'success'      => true,
        'extracted'    => ['firstName' => 'John', 'lastName' => 'Doe'],
        'nextQuestion' => [
            'id'                 => 'destination',
            'text'               => 'Where would you like to travel to?',
            'requiresExtraction' => false,
        ],
        'isComplete'   => false,
        'aiResponse'   => 'I understand your name is John Doe.',
    ];

    $expectedState = [
        'questions'    => [],
        'answers'      => [],
        'currentIndex' => 1,
        'isComplete'   => false,
        'history'      => [],
        'markdown'     => '## Progress: 33%',
    ];

    $mockLlmService->shouldReceive('handleUserResponse')
                   ->once()
                   ->with($userResponse)
                   ->andReturn($expectedResult);

    $mockFlowService->shouldReceive('getState')
                    ->once()
                    ->andReturn($expectedState);

    $controller = new ConversationFlowController($mockFlowService, $mockLlmService);
    $request    = new Request(['response' => $userResponse]);

    $response = $controller->processResponse($request);


    expect($response)->toBeInstanceOf(JsonResponse::class);

    $data = $response->getData(true);
    expect($data)->toHaveKeys(['result', 'state']);
    expect($data['result'])->toBe($expectedResult);
    expect($data['state'])->toBe($expectedState);
});

it('required a response in the request', function (): void {
    $mockFlowService = Mockery::mock(ConversationFlowService::class);
    $mockLlmService  = Mockery::mock(LLMConversationService::class);

    $controller = new ConversationFlowController($mockFlowService, $mockLlmService);
    $request    = new Request();

    $this->expectException(ValidationException::class);
    $controller->processResponse($request);
});

it('handles conversation completion', function (): void {
    $mockFlowService = Mockery::mock(ConversationFlowService::class);
    $mockLlmService  = Mockery::mock(LLMConversationService::class);

    $userResponse   = 'No special requests';
    $expectedResult = [
        'success'      => true,
        'extracted'    => null,
        'nextQuestion' => null,
        'isComplete'   => true,
        'aiResponse'   => 'Thank you for completing the conversation.',
    ];

    $expectedState = [
        'questions'    => [],
        'answers'      => [],
        'currentIndex' => 3,
        'isComplete'   => true,
        'history'      => [],
        'markdown'     => '## Progress: 100%',
    ];

    $mockLlmService->shouldReceive('handleUserResponse')
                   ->once()
                   ->with($userResponse)
                   ->andReturn($expectedResult);

    $mockFlowService->shouldReceive('getState')
                    ->once()
                    ->andReturn($expectedState);

    $controller = new ConversationFlowController($mockFlowService, $mockLlmService);
    $request    = new Request(['response' => $userResponse]);

    $response = $controller->processResponse($request);


    expect($response)->toBeInstanceOf(JsonResponse::class);

    $data = $response->getData(true);
    expect($data['result']['isComplete'])->toBeTrue();
    expect($data['result']['nextQuestion'])->toBeNull();
    expect($data['state']['isComplete'])->toBeTrue();
});
