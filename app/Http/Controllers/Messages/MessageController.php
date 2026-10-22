<?php

namespace App\Http\Controllers\Messages;

use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request, Conversation $conversation)
    {
        abort_unless(
            in_array($request->user()->id, [$conversation->initiator_id, $conversation->recipient_id]),
            403
        );

        $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:5120'],
            'reply_to_id' => ['nullable', 'exists:messages,id']
        ]);
        if (!$request->body && !$request->hasFile('image')) {
            return response()->json(['error' => 'Image or Message is required']);
        }
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('messages', 'public');
        }
        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body'      => $request->body,
            'image_path' => $imagePath,
            'reply_to_id' => $request->reply_to_id
        ]);

        $conversation->update(['last_message_at' => now()]);

        $message->load(['sender', 'replyTo']);
        broadcast(new MessageSent($message))->toOthers();

        return response()->json($this->formatMessage($message), 201);
    }
    public function markAsRead(Request $request, Conversation $conversation)
    {
        abort_unless(
            in_array($request->user()->id, [$conversation->initiator_id, $conversation->recipient_id]),
            403
        );

        $now = now();

        $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => $now]);
        broadcast(new MessageRead($conversation->id, $request->user()->id, now()->toIso8601String()))->toOthers();

        return response()->json(['status' => 'success']);
    }
    private function formatMessage($message)
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'image_url' => $message->image_path ? asset('storage/' . $message->image_path) : null,
            'sender_id' => $message->sender_id,
            'reply_to' => $message->replyTo ? [
                'body' => $message->replyTo->body,
                'sender_name' => $message->replyTo->sender->name
            ] : null,
            'created_at' => $message->created_at->toIso8601String(),
            'read_at' => $message->read_at ? $message->read_at->toIso8601String() : null,
        ];
    }
}
