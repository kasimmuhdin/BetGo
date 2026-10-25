<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingGrant extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'listings_used',
        'start_at',
        'expire_at',
        'status',
        'stripe_subscription_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'listing_grants_id');
    }
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if (! $this->plan->isUnlimited() && $this->listings_used >= $this->plan->listings_limit) {
            return false;
        }

        return true;
    }


    public function remaining(): ?int
    {
        return $this->plan->isUnlimited()
            ? null
            : max(0, $this->plan->listings_limit - $this->listings_used);
    }
}
