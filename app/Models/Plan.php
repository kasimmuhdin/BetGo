<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Plan extends Model
{
    protected $fillable = [
        'key',
        'name',
        'price',
        'price_usd',
        'stripe_price_id',
        'listings_limit',
        'duration_days',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_usd' => 'decimal:2',
        'is_active' => 'boolean',
    ];


    public function listingGrants()
    {
        return $this->hasMany(ListingGrant::class);
    }

    public function isUnlimited(): bool
    {
        return is_null($this->listings_limit);
    }

    public function isRecurring(): bool
    {
        return !is_null($this->duration_days);
    }
    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('plans.public'));
        static::deleted(fn() => Cache::forget('plans.public'));
    }

    public static function publicLists()
    {
        $plans = Cache::remember(
            'plans.public',
            600,
            fn() => static::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->toArray()
        );

        return collect($plans)->map(
            fn($plan) => (new static())->newFromBuilder($plan)
        );
    }
    public function pricePerListing(): ?float
    {
        return $this->listings_limit ? (float) $this->price / (float) $this->listings_limit : null;
    }
}
