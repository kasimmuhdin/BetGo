<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'sender_id', 'body', 'image_path', 'reply_to_id'];
    protected $casts = ['read_at' => 'datetime'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class);
    }
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }
}
