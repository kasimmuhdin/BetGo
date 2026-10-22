<?php

namespace App\Notifications;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ListingSubmittedForReview extends Notification
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
            'message' => ($this->listing->owner->name ?? 'Someone') . " submitted \"{$this->listing->title}\" for review.",
            'url' => route('admin.listings.show', $this->listing),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }

    public function broadcastAs(): string
    {
        return 'listing.submitted';
    }
}
