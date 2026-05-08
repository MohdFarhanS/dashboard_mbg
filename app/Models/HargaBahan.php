<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HargaBahan extends Model
{
    protected $fillable = [
        'bahan_pangan_id', 'harga_per_100g', 'berlaku_mulai', 'berlaku_sampai', 'keterangan',
    ];

    protected $casts = [
        'berlaku_mulai'  => 'date:Y-m-d',
        'berlaku_sampai' => 'date:Y-m-d',
        'harga_per_100g' => 'decimal:2',
    ];

    public function bahanPangan()
    {
        return $this->belongsTo(BahanPangan::class);
    }

    /**
     * Ambil harga aktif untuk bahan + unit tertentu pada tanggal tertentu.
     */
    public static function hargaAktif(int $bahanId, ?string $tanggal = null): float
    {
        $tgl = $tanggal ?? today()->toDateString();

        $harga = static::where('bahan_pangan_id', $bahanId)
            ->where('berlaku_mulai', '<=', $tgl)
            ->where(function ($q) use ($tgl) {
                $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', $tgl);
            })
            ->orderByDesc('berlaku_mulai')
            ->value('harga_per_100g');

        return (float) ($harga ?? 0);
    }
}