<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ChatSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatHistoryController
{
    /**
     * Get or create a chat session and return its history
     */
    public function show(Request $request, ?string $sessionId = null): JsonResponse
    {
        $validated = $request->validate([
            'create_new' => 'sometimes|boolean',
        ]);

        $userId = auth()->id();

        if ($sessionId && !$validated['create_new'] ?? false) {
            $session = ChatSession::where('session_id', $sessionId)
                                  ->when($userId, fn($q) => $q->where('user_id', $userId))
                                  ->first();
        }

        if (!isset($session) || !$session) {
            $session = ChatSession::create([
                'user_id' => $userId,
                'title'   => 'New Chat Session',
            ]);
        }

        /**
         * the response needs to look like this:
         *{"text": "Show me a modern city", "role": "user"},
         * {"files": [{"src": "path-to-file.jpeg", "type": "image"}], "role": "ai"},
         * {"text": "Whats on your mind?", "role": "user"},
         * {"text": "Peace and tranquility", "role": "ai"}
         */

        return response()->json($session->messages?->map->toDeepChatResponse()->values() ?: []);
    }

    /**
     * Save a message to the chat history
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id'   => 'required|uuid|exists:chat_sessions,session_id',
            'role'         => 'required|in:user,assistant,system,tool_result',
            'content'      => 'required|string',
            'tool_calls'   => 'sometimes|array',
            'tool_results' => 'sometimes|array',
            'metadata'     => 'sometimes|array',
        ]);

        $session = ChatSession::where('session_id', $validated['session_id'])->firstOrFail();

        // Ensure user owns the session if authenticated
        if (auth()->check() && $session->user_id && $session->user_id !== auth()->id()) {
            abort(403);
        }

        $message = $session->messages()->create([
            'role'         => $validated['role'],
            'content'      => $validated['content'],
            'tool_calls'   => $validated['tool_calls'] ?? null,
            'tool_results' => $validated['tool_results'] ?? null,
            'metadata'     => $validated['metadata'] ?? null,
        ]);

        return response()->json([
            'id'         => $message->id,
            'created_at' => $message->created_at,
        ]);
    }

    /**
     * List recent chat sessions for the current user
     */
    public function index(Request $request): JsonResponse
    {
        $session = ChatSession::query()
                              ->latest()
                              ->when(auth()->id(), fn($q) => $q->where('user_id', auth()->id()))
                              ->first();

        return response()->json($session?->messages->map->toDeepChatResponse()->values() ?: []);
    }
}
