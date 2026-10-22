<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{

    protected $fillable = [
        'user_id',
        'path',
        'disk',
        'original_name',
        'alt',
        'mime',
        'size_kb',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_media')
            ->withPivot(['sort_order', 'is_cover']);
    }
}
