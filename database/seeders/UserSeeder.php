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
        User::create([
            'name'          => 'Administrator',
            'nama_lengkap'  => 'Administrator Sistem',
            'email'         => 'admin@mbg.id',
            'password'      => Hash::make('password123'),
            'unit_sppg'     => 'Pusat',
            'role'          => 'admin',
            'is_active'     => true,
        ]);

        User::create([
        'name'         => 'Pengelola SPPG',
        'nama_lengkap' => 'Pengelola Unit A',
        'email'        => 'pengelola@mbg.id',
        'password'     => Hash::make('password123'),
        'unit_sppg'    => 'SPPG Unit A Pekanbaru',
        'role'         => 'pengelola',
        'is_active'    => true, 
        ]);
    }
}
