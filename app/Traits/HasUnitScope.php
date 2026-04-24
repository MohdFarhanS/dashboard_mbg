<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasUnitScope
{
    /**
     * Kembalikan unit_sppg yang sedang aktif.
     * - Admin  → null  (semua unit)
     * - Pengelola → unit_sppg miliknya
     */
    public static function activeUnit(): ?string
    {
        $user = Auth::user();
        if (!$user) return null;

        return $user->role === 'admin' ? null : $user->unit_sppg;
    }

    /**
     * Terapkan filter unit ke query builder.
     * Jika unit null (admin), tidak ada filter → tampilkan semua.
     */
    public static function scopeForActiveUnit($query)
    {
        $unit = static::activeUnit();
        if ($unit) {
            $query->where('unit_sppg', $unit);
        }
        return $query;
    }
}