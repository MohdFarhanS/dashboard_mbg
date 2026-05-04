<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggaranPorsi extends Model
{
    const KELOMPOK_LABELS = [
        'balita_sd3'       => 'Balita s/d Kelas 3 SD',
        'sd4_ibu_menyusui' => 'Kelas 4 SD s/d Ibu Menyusui',
    ];

    protected $fillable = [
        'kelompok',
        'anggaran_per_porsi',
        'berlaku_mulai', 'berlaku_sampai',
        'keterangan', 'created_by',
    ];

    protected $casts = [
        'berlaku_mulai'  => 'date',
        'berlaku_sampai' => 'date',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getLabelKelompokAttribute(): string
    {
        return self::KELOMPOK_LABELS[$this->kelompok] ?? '-';
    }

    public static function aktif(?string $tanggal = null, ?string $kelompok = null): float
    {
        $tgl = $tanggal ?? today()->toDateString();

        $query = static::where('berlaku_mulai', '<=', $tgl)
            ->where(function ($q) use ($tgl) {
                $q->whereNull('berlaku_sampai')
                  ->orWhere('berlaku_sampai', '>=', $tgl);
            });

        if ($kelompok !== null) {
            $query->where('kelompok', $kelompok);
        }

        $anggaran = $query->orderByDesc('berlaku_mulai')
            ->value('anggaran_per_porsi');

        return (float) ($anggaran ?? 15000);
    }
}
