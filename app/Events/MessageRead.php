<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;

class MessageRead
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(public int $conversationId, public int $readerId, public string $readAt)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversationId),
        ];
    }
    public function broadcastAs()
    {
        return 'messages.read';
    }
}
