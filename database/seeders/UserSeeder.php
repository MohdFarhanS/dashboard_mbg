<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $unitSppg = config('app.unit_sppg', 'SPPG');

        User::create([
            'name'          => 'Administrator',
            'nama_lengkap'  => 'Administrator Sistem',
            'email'         => 'admin@mbg.id',
            'password'      => Hash::make('password123'),
            'role'          => 'admin',
            'unit_sppg'     => $unitSppg,
            'is_active'     => true,
        ]);

        User::create([
            'name'         => 'Pengelola SPPG',
            'nama_lengkap' => 'Pengelola',
            'email'        => 'pengelola@mbg.id',
            'password'     => Hash::make('password123'),
            'role'         => 'pengelola',
            'unit_sppg'    => $unitSppg,
            'is_active'    => true,
        ]);
    }
}
