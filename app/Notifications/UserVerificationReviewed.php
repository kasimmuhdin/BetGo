<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class UserVerificationReviewed extends Notification
{
    use Queueable;

    public function __construct(public User $reviewedUser) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        $approved = $this->reviewedUser->status === 'approved';

        return [
            'title' => $approved ? 'Verification approved' : 'Verification rejected',
            'message' => $approved
                ? "You're verified as " . ($this->reviewedUser->role) . '. You can now post listings.'
                : "Your {$this->reviewedUser->role} verification was rejected: {$this->reviewedUser->verification_rejection_reason}",
            'url' => route($this->reviewedUser->role . '.dashboard'),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastAs(): string
    {
        return 'user.verification.reviewed';
    }
}
