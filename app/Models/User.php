<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'password',
    'phone',
    'national_id',
    'tin',
    'role',
    'status',
    'email_verified_at',
    'verification_rejection_reason',
    'verified_by',
    'verified_at',
    'profile_picture',
    'is_active',
])]
#[Hidden(['password', 'remember_token', 'google_id'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, Billable, HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'verified_at' => 'datetime',
        ];
    }
    public function properties()
    {
        return $this->hasMany(Property::class);
    }
    public function media()
    {
        return $this->hasMany(Media::class);
    }
    public function conversationsInitiated()
    {
        return $this->hasMany(Conversation::class, 'initiator_id');
    }

    public function conversationsReceived()
    {
        return $this->hasMany(Conversation::class, 'recipient_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    public function dashboardUrl(): string
    {
        $name = match ($this->role) {
            'admin' => 'admin.dashboard',
            'owner' => 'owner.dashboard',
            'agent' => 'agent.dashboard',
            default => 'user.dashboard',
        };

        return Route::has($name) ? route($name) : url('/');
    }
    public function scopePendingVerification($query)
    {
        return $query->where('status', 'pending')->whereIn('role', ['owner', 'agent']);
    }
    public function listingGrants()
    {
        return $this->hasMany(ListingGrant::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function activeListingGrants()
    {
        return $this->listingGrants()
            ->with('plan')
            ->where('status', 'active')
            ->get()
            ->filter(fn($s) => $s->isActive())
            ->values();
    }
    public function hasListingCredit(): bool
    {
        return $this->activeListingGrants()
            ->contains(fn($s) => $s->plan->isUnlimited() || $s->remaining() > 0);
    }
    public function consumeListingCredit(): bool
    {
        $subs = $this->activeListingGrants();

        if ($subs->contains(fn($s) => $s->plan->isUnlimited())) {
            return true;
        }

        $sub = $subs->filter(fn($s) => $s->remaining() > 0)
            ->sortBy(fn($s) => $s->expires_at?->timestamp ?? PHP_INT_MAX)
            ->first();

        if (! $sub) {
            return false;
        }

        $sub->increment('listings_used');

        return true;
    }

    public function savedProperties()
    {
        return $this->belongsToMany(Property::class, 'saved_listings')->withTimestamps();
    }
}
