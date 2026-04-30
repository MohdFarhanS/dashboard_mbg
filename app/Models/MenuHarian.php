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
            $faktor = ($detail->jumlah_gram * (($b->bdd ?? 100) / 100)) / 100 * $detail->jumlah_porsi; // ← tambah × jumlah_porsi
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

            $biaya = ($d->jumlah_gram / 100) * $hargaPer100g * $d->jumlah_porsi; // ← tambah × jumlah_porsi
            $totalBiayaSeluruh += $biaya;

            $detail[] = [
                'nama'           => $b->nama_bahan,
                'gram'           => $d->jumlah_gram,
                'harga_per_100g' => $hargaPer100g,
                'biaya'          => round($biaya, 0),
            ];
        }

        $jumlahPorsi = max($this->jumlah_porsi, 1);

        $anggaran = \App\Models\AnggaranPorsi::aktif($this->tanggal->toDateString());

        return [
            'total_seluruh'   => round($totalBiayaSeluruh, 0),
            'cost_per_porsi'  => round($totalBiayaSeluruh / $jumlahPorsi, 0),
            'anggaran'        => $anggaran,
            'selisih'         => round($anggaran - ($totalBiayaSeluruh / $jumlahPorsi), 0),
            'persen_anggaran' => $anggaran > 0
                ? round(($totalBiayaSeluruh / $jumlahPorsi / $anggaran) * 100, 1)
                : 0,
            'detail'          => $detail,
            'jumlah_porsi'    => $jumlahPorsi,
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

    /**
     * Kembalikan nilai anggaran aktif per porsi berdasarkan tanggal menu.
     */
    public function anggaranAktif(): float
    {
        return (float) \App\Models\AnggaranPorsi::aktif($this->tanggal->toDateString());
    }

    /**
     * Status anggaran: 'over' | 'warning' | 'aman' | 'belum_ada_data'
     * Threshold warning di ≥85% dari anggaran.
     */
    public function statusAnggaran(): string
    {
        $biaya = $this->totalBiaya();

        if ($biaya['cost_per_porsi'] === 0) return 'belum_ada_data';

        $persen = $biaya['persen_anggaran'];

        if ($persen > 100) return 'over';
        if ($persen >= 85) return 'warning';
        return 'aman';
    }

    /**
     * Persentase biaya per porsi terhadap anggaran (0–100+).
     */
    public function persenAnggaran(): float
    {
        return (float) $this->totalBiaya()['persen_anggaran'];
    }
}