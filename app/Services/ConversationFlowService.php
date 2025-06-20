<?php

declare(strict_types=1);

namespace App\Services;

class ConversationFlowService
{
    protected array $questions = [];
    protected array $answers = [];
    protected int $currentQuestionIndex = 0;
    protected array $conversationHistory = [];

    public function initializeQuestions(array $questions): void
    {
        $this->questions             = $questions;
        $this->conversationHistory[] = [
            'role'    => 'system',
            'content' => 'You are conducting a structured interview. Ask questions one at a time and validate responses.'
        ];
    }

    public function getCurrentQuestion(): ?array
    {
        if ($this->currentQuestionIndex >= count($this->questions)) {
            return null;
        }

        return $this->questions[$this->currentQuestionIndex];
    }

    public function getNextQuestion()
    {
        if ($this->currentQuestionIndex + 1 >= count($this->questions)) {
            return null;
        }

        return $this->questions[$this->currentQuestionIndex + 1];
    }

    public function addToHistory(string $role, string $content): void
    {
        $this->conversationHistory[] = [
            'role'      => $role,
            'content'   => $content,
            'timestamp' => now()->toISOString()
        ];
    }

    public function processAnswer(string $userResponse, ?array $extractedData = null): array
    {
        $currentQuestion = $this->getCurrentQuestion();

        if (!$currentQuestion) {
            return ['success' => false, 'message' => 'No active question'];
        }

        // Store answer with extracted data
        $this->answers[$currentQuestion['id']] = [
            'question'  => $currentQuestion['text'],
            'answer'    => $userResponse,
            'extracted' => $extractedData,
            'timestamp' => now()
        ];

        // Add to conversation history
        $this->addToHistory('user', $userResponse);

        // Move to next question
        $this->currentQuestionIndex++;

        return [
            'success'      => true,
            'extracted'    => $extractedData,
            'nextQuestion' => $this->getCurrentQuestion(),
            'isComplete'   => $this->currentQuestionIndex >= count($this->questions)
        ];
    }

    public function getConversationHistory(): array
    {
        return $this->conversationHistory;
    }

    public function generateMarkdownSummary(): string
    {
        $markdown = "## Conversation Progress\n\n";

        foreach ($this->questions as $index => $question) {
            $isAnswered = isset($this->answers[$question['id']]);
            $checkbox   = $isAnswered ? '[x]' : '[ ]';

            $markdown .= "- $checkbox **{$question['text']}**\n";

            if ($isAnswered) {
                $answer   = $this->answers[$question['id']];
                $markdown .= "  - Answer: {$answer['answer']}\n";

                if ($answer['extracted']) {
                    $markdown .= "  - Extracted Data:\n";
                    foreach ($answer['extracted'] as $key => $value) {
                        $markdown .= "    - {$key}: {$value}\n";
                    }
                }
            }
        }

        $progress = round(($this->currentQuestionIndex / count($this->questions)) * 100);
        $markdown .= "\n**Progress: {$progress}%**\n";

        return $markdown;
    }

    public function getState(): array
    {
        return [
            'questions'    => $this->questions,
            'answers'      => $this->answers,
            'currentIndex' => $this->currentQuestionIndex,
            'isComplete'   => $this->currentQuestionIndex >= count($this->questions),
            'history'      => $this->conversationHistory,
            'markdown'     => $this->generateMarkdownSummary()
        ];
    }
}