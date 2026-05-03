<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasUnitScope
{
    public static function activeUnit(): string
    {
        return config('app.unit_sppg', 'SPPG');
    }

    public function scopeForActiveUnit($query)
    {
        return $query;
    }
}