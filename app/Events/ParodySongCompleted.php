<?php

namespace App\Events;

use App\Models\Song;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParodySongCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Song $song,
        public ?int $userId = null
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channelName = $this->userId ? "user.{$this->userId}" : 'user.guest';
        
        return [
            new PrivateChannel($channelName),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'song' => [
                'id' => $this->song->id,
                'title' => $this->song->title,
                'lyrics' => $this->song->lyrics,
                'artist' => $this->song->artist ?? 'Weird Al Yankovic',
                'style' => $this->song->style ?? 'Parody',
                'prompt' => $this->song->prompt,
                'created_at' => $this->song->created_at,
            ],
        ];
    }
}
