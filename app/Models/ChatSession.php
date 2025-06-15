<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Prism\Prism\Contracts\Message;
use Prism\Prism\ValueObjects\ToolResult;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;
use Prism\Prism\ValueObjects\Messages\ToolResultMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

class ChatSession extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'title',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (ChatSession $session) {
            if (!$session->session_id) {
                $session->session_id = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Get messages formatted for Prism's withMessages() method
     *
     * @return array<Message>
     */
    public function getMessagesForPrism(): array
    {
        return $this->messages()
                    ->orderBy('created_at')
                    ->get()
                    ->map(function (ChatMessage $message) {
                        return match ($message->role) {
                            'user' => new UserMessage($message->content),
                            'assistant' => new AssistantMessage($message->content),
                            'system' => new SystemMessage($message->content),
                            'tool_result' => $this->createToolResultMessage($message),
                            default => null,
                        };
                    })
                    ->filter()
                    ->prepend(new SystemMessage('You are a helpful assistant that responds in 250 words or less. 
                    You are very cordial and should say something nice to start. You should ask them for their contact information 
                    like first name, last name, email, and phone number.'))
                    ->values()
                    ->toArray();
    }

    private function createToolResultMessage(ChatMessage $message): ?ToolResultMessage
    {
        if (!$message->tool_results) {
            return null;
        }

        // Convert stored tool results back to ToolResult objects
        $toolResults = collect($message->tool_results)->map(function ($result) {
            return new ToolResult(
                toolCallId: $result['toolCallId'] ?? '',
                toolName: $result['toolName'] ?? '',
                args: $result['args'] ?? [],
                result: $result['result'] ?? ''
            );
        })->toArray();

        return new ToolResultMessage($toolResults);
    }
}
