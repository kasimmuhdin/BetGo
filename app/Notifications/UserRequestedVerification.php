<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class UserRequestedVerification extends Notification
{
    use Queueable;

    public function __construct(public User $applicant) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'New verification request',
            'message' => "{$this->applicant->name} requested to become " . ($this->applicant->role === 'owner' ? 'an Owner'  : ($this->applicant->role === 'agent' ? 'an Agent' : 'a User')) . '.',
            'url' => route('admin.users.show', $this->applicant),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastAs(): string
    {
        return 'user.verification.requested';
    }
}
