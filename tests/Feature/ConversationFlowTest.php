<?php

declare(strict_types=1);

use App\Services\ConversationFlowService;
use App\Services\LLMConversationService;

beforeEach(function () {
    $this->conversationService = app(ConversationFlowService::class);
    $this->llmService          = app(LLMConversationService::class);
});

test('can start a conversation and get first question', function () {
    $response = $this->postJson('/conversation/start');

    $response->assertOk()
             ->assertJsonStructure([
                 'question' => [
                     'id',
                     'text',
                     'requiresExtraction'
                 ],
                 'markdown'
             ])
             ->assertJsonPath('question.id', 'traveler_name')
             ->assertJsonPath('question.text', 'What is your name, or who will be travelling?');

    expect($response->json('markdown'))->toContain('[ ] **What is your name');
});

test('conversation service initializes questions correctly', function () {
    $questions = [
        ['id' => 'q1', 'text' => 'Question 1'],
        ['id' => 'q2', 'text' => 'Question 2']
    ];

    $this->conversationService->initializeQuestions($questions);

    $currentQuestion = $this->conversationService->getCurrentQuestion();
    expect($currentQuestion)->toBe($questions[0]);
});

test('conversation service tracks answers and progresses', function () {
    $questions = [
        ['id' => 'name', 'text' => 'What is your name?'],
        ['id' => 'location', 'text' => 'Where are you from?']
    ];

    $this->conversationService->initializeQuestions($questions);

    // Answer first question
    $result = $this->conversationService->processAnswer('John Doe', ['firstName' => 'John', 'lastName' => 'Doe']);

    expect($result['success'])->toBeTrue();
    expect($result['nextQuestion']['id'])->toBe('location');
    expect($result['isComplete'])->toBeFalse();

    // Check markdown
    $markdown = $this->conversationService->generateMarkdownSummary();
    expect($markdown)->toContain('[x] **What is your name?**');
    expect($markdown)->toContain('[ ] **Where are you from?**');
    expect($markdown)->toContain('50%');
});

test('it completes three questions and returns completion status', function () {
    $questions = [
        ['id' => 'q1', 'text' => 'Question 1'],
        ['id' => 'q2', 'text' => 'Question 2'],
        ['id' => 'q3', 'text' => 'Question 3']
    ];

    $this->conversationService->initializeQuestions($questions);

    expect($this->conversationService->getCurrentQuestion())->toBe($questions[0]);
    expect($this->conversationService->getNextQuestion())->toBe($questions[1]);
    $markdown = $this->conversationService->generateMarkdownSummary();
    expect($markdown)->toContain('[ ] **Question 1**');
    expect($markdown)->toContain('[ ] **Question 2**');
    expect($markdown)->toContain('[ ] **Question 3**');

    $this->conversationService->processAnswer('Answer 1');
    $markdown = $this->conversationService->generateMarkdownSummary();
    expect($markdown)->toContain('[x] **Question 1**');
    expect($markdown)->toContain('[ ] **Question 2**');
    expect($markdown)->toContain('[ ] **Question 3**');

    expect($this->conversationService->getCurrentQuestion())->toBe($questions[1]);
    expect($this->conversationService->getNextQuestion())->toBe($questions[2]);

    $this->conversationService->processAnswer('Answer 2');
    $markdown = $this->conversationService->generateMarkdownSummary();
    expect($markdown)->toContain('[x] **Question 1**');
    expect($markdown)->toContain('[x] **Question 2**');
    expect($markdown)->toContain('[ ] **Question 3**');

    expect($this->conversationService->getCurrentQuestion())->toBe($questions[2]);
    expect($this->conversationService->getNextQuestion())->toBeNull();

    $result = $this->conversationService->processAnswer('Answer 3');

    $markdown = $this->conversationService->generateMarkdownSummary();
    expect($markdown)->toContain('[x] **Question 1**');
    expect($markdown)->toContain('[x] **Question 2**');
    expect($markdown)->toContain('[x] **Question 3**');
    expect($this->conversationService->getCurrentQuestion())->toBeNull();

    expect($result['success'])->toBeTrue();

    expect($result['isComplete'])->toBeTrue();
    expect($result['nextQuestion'])->toBeNull();

});


test('conversation completes when all questions answered', function () {
    $questions = [
        ['id' => 'q1', 'text' => 'Question 1']
    ];

    $this->conversationService->initializeQuestions($questions);
    $result = $this->conversationService->processAnswer('Answer 1');

    expect($result['isComplete'])->toBeTrue();
    expect($result['nextQuestion'])->toBeNull();
});

test('markdown summary shows extracted data', function () {
    $questions = [
        ['id' => 'name', 'text' => 'What is your name?', 'requiresExtraction' => true]
    ];

    $this->conversationService->initializeQuestions($questions);
    $this->conversationService->processAnswer('John Doe', [
        'firstName' => 'John',
        'lastName'  => 'Doe'
    ]);

    $markdown = $this->conversationService->generateMarkdownSummary();

    expect($markdown)->toContain('firstName: John');
    expect($markdown)->toContain('lastName: Doe');
});