<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Prism\Prism\Contracts\Message;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\ToolResultMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Prism\Prism\ValueObjects\ToolResult;

class ChatMessage extends Model
{
    protected $fillable = [
        'chat_session_id',
        'role',
        'content',
        'tool_calls',
        'tool_results',
        'metadata',
    ];

    protected $casts = [
        'tool_calls' => 'array',
        'tool_results' => 'array',
        'metadata' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    public function toDeepChatResponse()
    {
        return match($this->role) {
            'user' => [
                'text' => $this->content,
                'role' => 'user',
            ],
            'tool_result' => [
                'text' => $this->content,
                'role' => 'tool_result',
                'tool_calls' => $this->tool_calls ? array_map(fn($call) => ['name' => $call['name'], 'args' => $call['args']], $this->tool_calls) : [],
            ],
            'assistant' => [
                'text' => $this->content,
                'role' => 'ai',
            ],
            default => throw new \InvalidArgumentException("Unknown role: {$this->role}"),

        };
    }
}
