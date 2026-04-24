<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuSesi extends Model
{
    protected $fillable = ['menu_harian_id', 'sesi', 'nama_menu'];

    public function menuHarian(): BelongsTo
    {
        return $this->belongsTo(MenuHarian::class);
    }

    public function detailBahans(): HasMany
    {
        return $this->hasMany(MenuDetailBahan::class);
    }

    // Hitung total gizi sesi ini berdasarkan bahan + gram
    public function totalGizi(): array
    {
        $keys = ['energi','protein','lemak','karbohidrat','serat','kalsium','besi','vit_c'];
        $total = array_fill_keys($keys, 0);

        foreach ($this->detailBahans as $detail) {
            $b = $detail->bahanPangan;
            if (!$b) continue;
            // Nilai nutrisi TKPI = per 100g BDD
            // Rumus: (gram * BDD/100) / 100 * nilai_nutrisi
            $faktor = ($detail->jumlah_gram * ($b->bdd / 100)) / 100;
            foreach ($keys as $k) {
                $total[$k] += round($faktor * ($b->$k ?? 0), 2);
            }
        }
        return $total;
    }

    public function getLabelAttribute(): string
    {
        return ucfirst($this->sesi);
    }
}