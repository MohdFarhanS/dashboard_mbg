<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'filename', 'inserted', 'updated', 'skipped', 'mode',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
