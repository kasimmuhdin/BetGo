<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;




    /**
     * Create a new event instance.
     */
    public function __construct(public Message $message)
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
        $recipientId = $this->message->conversation->otherUser($this->message->sender_id)->id;
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
            new PrivateChannel('App.Models.User.' . $recipientId)
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'body'            => $this->message->body,
            'image_url' => $this->message->image_path ? asset('storage/' . $this->message->image_path) : null,
            'sender_id'       => $this->message->sender_id,
            'sender_name'     => $this->message->sender->name,
            'reply_to'        => $this->message->replyTo ? [
                'body'        => $this->message->replyTo->body,
                'sender_name' => $this->message->replyTo->sender->name
            ] : null,
            'created_at'      => $this->message->created_at->toIso8601String(),
        ];
    }
}
