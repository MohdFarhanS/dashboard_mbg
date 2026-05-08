<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuHarian extends Model
{
    protected $fillable = [
        'tanggal', 'user_id', 'nama_menu', 'status', 'kelompok',
        'catatan', 'anggaran_per_porsi', 'jumlah_porsi', 'catatan_anggaran',
        'kelompok_sasaran',
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
            // jumlah_porsi per bahan = total sajian batch (= menu.jumlah_porsi untuk bahan yg disajikan ke semua orang)
            $faktor = ($detail->jumlah_gram * (($b->bdd ?? 100) / 100)) / 100 * $detail->jumlah_porsi;
            foreach ($keys as $k) {
                $total[$k] += $faktor * ($b->$k ?? 0);
            }
        }

        // Bagi dengan jumlah_porsi untuk mendapatkan gizi PER ORANG (per porsi)
        $jumlahPorsi = max((int) $this->jumlah_porsi, 1);
        return array_map(fn($v) => round($v / $jumlahPorsi, 2), $total);
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

            // Menu final: pakai snapshot harga yang dikunci saat finalisasi.
            // Menu draft atau snapshot belum ada: hitung ulang dari HargaBahan.
            $hargaPer100g = ($this->status === 'final' && $d->harga_per_100g !== null)
                ? (float) $d->harga_per_100g
                : \App\Models\HargaBahan::hargaAktif($b->id, $this->tanggal->toDateString());

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

        // Menu final: pakai snapshot anggaran yang dikunci saat finalisasi.
        // Menu draft: hitung ulang dari AnggaranPorsi agar selalu pakai tarif terkini.
        $anggaran = ($this->status === 'final' && $this->anggaran_per_porsi > 0)
            ? (float) $this->anggaran_per_porsi
            : \App\Models\AnggaranPorsi::aktif($this->tanggal->toDateString(), $this->kelompok);

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

    /**
     * Kembalikan nilai anggaran aktif per porsi berdasarkan tanggal menu.
     */
    public function anggaranAktif(): float
    {
        if ($this->status === 'final' && $this->anggaran_per_porsi > 0) {
            return (float) $this->anggaran_per_porsi;
        }
        return (float) \App\Models\AnggaranPorsi::aktif($this->tanggal->toDateString(), $this->kelompok);
    }

    public function getLabelKelompokAttribute(): string
    {
        return \App\Models\AnggaranPorsi::KELOMPOK_LABELS[$this->kelompok] ?? '-';
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

    public function akgTarget(string $mealType = 'siang'): array
    {
        return \App\Constants\AKG::targetSajian(
            $this->kelompok_sasaran ?? 'SD_4_6',
            $mealType
        );
    }

    public function evaluasiGizi(string $mealType = 'siang'): array
    {
        $gizi   = $this->totalGizi();
        $target = $this->akgTarget($mealType);
        $result = [];
        foreach (['energi', 'protein', 'lemak', 'karbohidrat'] as $k) {
            $pct = $target[$k] > 0
                ? round(($gizi[$k] ?? 0) / $target[$k] * 100, 1)
                : 0;
            $result[$k] = [
                'pct'    => $pct,
                'aktual' => $gizi[$k] ?? 0,
                'target' => $target[$k],
                'status' => $pct < 80 ? 'kurang' : ($pct > 120 ? 'lebih' : 'cukup'),
            ];
        }
        return $result;
    }
}