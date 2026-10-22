<?php

namespace App\Notifications;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ListingReviewed extends Notification
{
    use Queueable;

    public function __construct(public Property $listing) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'listing_id' => $this->listing->id,
            'title' => $this->listing->title,
            'status' => $this->listing->status,
            'rejection_reason' => $this->listing->rejection_reason,
            'message' => $this->listing->status === 'published'
                ? "Your listing \"{$this->listing->title}\" was approved and published."
                : "Your listing \"{$this->listing->title}\" was rejected: {$this->listing->rejection_reason}",
            'url' => route('owner.listings.show', $this->listing),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastAs(): string
    {
        return 'listing.reviewed';
    }
}
