<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggaranPorsi extends Model
{
    protected $fillable = [
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

    public static function aktif(?string $tanggal = null): float
    {
        $tgl = $tanggal ?? today()->toDateString();

        $anggaran = static::where('berlaku_mulai', '<=', $tgl)
            ->where(function ($q) use ($tgl) {
                $q->whereNull('berlaku_sampai')
                  ->orWhere('berlaku_sampai', '>=', $tgl);
            })
            ->orderByDesc('berlaku_mulai')
            ->value('anggaran_per_porsi');

        return (float) ($anggaran ?? 15000);
    }
}