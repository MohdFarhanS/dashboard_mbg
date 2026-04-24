<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuHarian extends Model
{
    protected $fillable = [
        'tanggal', 'user_id', 'unit_sppg', 'nama_menu', 'status', 'catatan', 'anggaran_per_porsi', 'jumlah_porsi', 'catatan_anggaran',
    ];

    protected $casts = ['tanggal' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detailBahans(): HasMany
    {
        return $this->hasMany(MenuDetailBahan::class);
    }

    public function totalGizi(): array
    {
        $keys = ['energi','protein','lemak','karbohidrat','serat','kalsium','besi','vit_c'];
        $total = array_fill_keys($keys, 0);

        foreach ($this->detailBahans as $detail) {
            $b = $detail->bahanPangan;
            if (!$b) continue;
            $bdd = $b->bdd ?? 100;
            $faktor = ($detail->jumlah_gram * ($b->bdd / 100)) / 100;
            foreach ($keys as $k) {
                $total[$k] += round($faktor * ($b->$k ?? 0), 2);
            }
        }
        return $total;
    }

    /**
     * Hitung total biaya bahan baku per porsi untuk menu ini.
     * Harga diambil dari tabel harga_bahans sesuai tanggal menu.
     */
    public function totalBiaya(): array
    {
        $totalBiayaSeluruh = 0;
        $detail = [];

        foreach ($this->detailBahans as $d) {
            $b = $d->bahanPangan;
            if (!$b) continue;

            $hargaPer100g = \App\Models\HargaBahan::hargaAktif(
                $b->id,
                $this->unit_sppg,
                $this->tanggal->toDateString()
            );

            // biaya = (gram / 100) × harga per 100g
            $biaya = ($d->jumlah_gram / 100) * $hargaPer100g;
            $totalBiayaSeluruh += $biaya;

            $detail[] = [
                'nama'          => $b->nama_bahan,
                'gram'          => $d->jumlah_gram,
                'harga_per_100g'=> $hargaPer100g,
                'biaya'         => round($biaya, 0),
            ];
        }

        $jumlahPorsi = max($this->jumlah_porsi, 1);

        return [
            'total_seluruh'  => round($totalBiayaSeluruh, 0),
            'cost_per_porsi' => round($totalBiayaSeluruh / $jumlahPorsi, 0),
            'anggaran'       => (float) $this->anggaran_per_porsi,
            'selisih'        => round(($this->anggaran_per_porsi) - ($totalBiayaSeluruh / $jumlahPorsi), 0),
            'persen_anggaran'=> $this->anggaran_per_porsi > 0
                ? round(($totalBiayaSeluruh / $jumlahPorsi / $this->anggaran_per_porsi) * 100, 1)
                : 0,
            'detail'         => $detail,
            'jumlah_porsi'   => $jumlahPorsi,
        ];
    }

    public function scopeTanggal($query, $tanggal)
    {
        return $query->whereDate('tanggal', $tanggal);
    }

    public function scopeUnit($query, $unit)
    {
        return $query->where('unit_sppg', $unit);
    }
}