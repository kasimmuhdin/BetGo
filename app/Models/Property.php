<?php

namespace App\Models;

use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

// app/Models/Property.php
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'property_type',
        'listing_type',
        'price',
        'currency',
        'price_negotiable',
        'bedrooms',
        'bathrooms',
        'parking_spaces',
        'furnishing_status',
        'area',
        'area_unit',
        'city',
        'sub_city',
        'woreda',
        'neighborhood',
        'address',
        'latitude',
        'longitude',
        'details',
        'status',
        'is_verified',
        'published_at',
        'expires_at',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'submitted_at'
    ];

    protected $casts = [
        'details' => 'array',
        'is_verified' => 'boolean',
        'price_negotiable' => 'boolean',
        'price' => 'decimal:2',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',

    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function media()
    {
        return $this->belongsToMany(Media::class, 'property_media')
            ->withPivot(['sort_order', 'is_cover'])
            ->orderByPivot('sort_order');
    }
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")
                        ->orWhere('city', 'like', "%{$term}%")
                        ->orWhere('sub_city', 'like', "%{$term}%")
                        ->orWhere('neighborhood', 'like', "%{$term}%");
                });
            })
            ->when($filters['listing_type'] ?? null, fn($q, $v) => $q->where('listing_type', $v))
            ->when($filters['property_type'] ?? null, fn($q, $v) => $q->whereIn('property_type', (array) $v))
            ->when($filters['rent_period'] ?? null, fn($q, $v) => $q->where('rent_period', $v))
            ->when($filters['min_price'] ?? null, fn($q, $v) => $q->where('price', '>=', $v))
            ->when($filters['max_price'] ?? null, fn($q, $v) => $q->where('price', '<=', $v))
            ->when($filters['bedrooms'] ?? null, fn($q, $v) => $q->where('bedrooms', '>=', $v));
    }
    public function getCoverUrlAttribute(): ?string
    {
        $cover = $this->media->firstWhere('pivot.is_cover', true) ?? $this->media->first();

        return $cover?->url;
    }
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_listings')->withTimestamps();
    }
    public function isPublic(): bool
    {
        return $this->status === 'published'
            && (! $this->expires_at || $this->expires_at->isFuture());
    }
    public const TYPES = [
        'apartment' => 'Apartment',
        'house' => 'House',
        'villa' => 'Villa',
        'office' => 'Office',
        'land' => 'Land',
        'commercial' => 'Commercial',
        'warehouse' => 'Warehouse',
    ];
}
