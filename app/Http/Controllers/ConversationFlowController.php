<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ConversationFlowService;
use App\Services\LLMConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ConversationFlowController extends Controller
{
    public function __construct(
        protected ConversationFlowService $conversationService,
        protected LLMConversationService $llmService
    ) {
    }

    public function startConversation(Request $request): JsonResponse
    {
        $questions = [
            [
                'id'                 => 'traveler_name',
                'text'               => 'What is your name, or who will be travelling?',
                'requiresExtraction' => true,
                'extractionPrompt'   => 'Extract the first name and last name from this response'
            ],
            [
                'id'                 => 'destination',
                'text'               => 'Where would you like to travel to?',
                'requiresExtraction' => false
            ],
            [
                'id'                 => 'travel_dates',
                'text'               => 'What are your preferred travel dates?',
                'requiresExtraction' => true,
                'extractionPrompt'   => 'Extract the start date and end date from this response'
            ]
        ];

        $this->conversationService->initializeQuestions($questions);

        $firstQuestion = $this->conversationService->getCurrentQuestion();

        return response()->json([
            'question' => $firstQuestion,
            'markdown' => $this->conversationService->generateMarkdownSummary()
        ]);
    }

    public function processResponse(Request $request): JsonResponse
    {
        $request->validate([
            'response' => 'required|string|max:1000',
        ]);

        $userResponse = $request->input('response');

        // Use LLM service to handle the response
        $result = $this->llmService->handleUserResponse($userResponse);

        return response()->json([
            'result' => $result,
            'state'  => $this->conversationService->getState()
        ]);
    }

}