<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (!$conversation) return false;

    return in_array($user->id, [$conversation->initiator_id, $conversation->recipient_id]);
});
Broadcast::channel('app', function ($user) {
    if ($user) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    return false;
});
