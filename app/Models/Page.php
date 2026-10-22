<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['slug', 'title', 'meta_description', 'content', 'updated_by'];

    /** Pages are found by slug (/about), in public URLs and in the admin. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
