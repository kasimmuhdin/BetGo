<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['property_id', 'initiator_id', 'recipient_id', 'last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->oldest();
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    // the "other person" relative to whoever is logged in
    public function otherUser($currentUserId)
    {
        return $this->initiator_id === $currentUserId ? $this->recipient : $this->initiator;
    }

    public function unreadCountFor($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($query) use ($userId) {
            $query->where('initiator_id', $userId)
                ->orWhere('recipient_id', $userId);
        });
    }
}
