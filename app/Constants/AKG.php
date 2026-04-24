<?php
namespace App\Constants;

class AKG
{
    // AKG Anak Sekolah (7-12 tahun) — MBG target utama
    public const HARIAN = [
        'energi'      => 1850,  // kkal
        'protein'     => 49,    // gram
        'lemak'       => 62,    // gram
        'karbohidrat' => 254,   // gram
        'serat'       => 22,    // gram
        'kalsium'     => 1000,  // mg
        'besi'        => 10,    // mg
        'vit_c'       => 45,    // mg
    ];

    // Kontribusi makan siang ~35% dari total harian
    public const MAKAN_SIANG = [
        'energi'      => 578,  // 35% × 1.650 kkal
        'protein'     => 14,   // 35% × 40g
        'lemak'       => 19,   // 35% × 55g
        'karbohidrat' => 88,   // 35% × 250g
        'serat'       => 8,    // 35% × 22g
        'kalsium'     => 350,  // 35% × 1000mg
        'besi'        => 4,    // 35% × 10mg
        'vit_c'       => 16,   // 35% × 45mg
    ];

    public const LABEL = [
        'energi'      => ['label' => 'Energi',      'satuan' => 'kkal', 'icon' => 'fa-fire'],
        'protein'     => ['label' => 'Protein',     'satuan' => 'g',    'icon' => 'fa-drumstick-bite'],
        'lemak'       => ['label' => 'Lemak',        'satuan' => 'g',    'icon' => 'fa-droplet'],
        'karbohidrat' => ['label' => 'Karbohidrat',  'satuan' => 'g',    'icon' => 'fa-wheat-awn'],
        'serat'       => ['label' => 'Serat',        'satuan' => 'g',    'icon' => 'fa-leaf'],
        'kalsium'     => ['label' => 'Kalsium',      'satuan' => 'mg',   'icon' => 'fa-bone'],
        'besi'        => ['label' => 'Zat Besi',     'satuan' => 'mg',   'icon' => 'fa-circle-dot'],
        'vit_c'       => ['label' => 'Vitamin C',    'satuan' => 'mg',   'icon' => 'fa-lemon'],
    ];
}