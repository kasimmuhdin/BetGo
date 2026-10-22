<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'listing_grants_id',
        'provider',
        'tx_ref',
        'amount',
        'currency',
        'status',
        'chapa_reference',
        'meta',
        'paid_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function listingGrant()
    {
        return $this->belongsTo(ListingGrant::class, 'listing_grants_id');
    }
}
