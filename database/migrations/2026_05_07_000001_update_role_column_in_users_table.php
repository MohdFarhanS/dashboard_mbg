<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah kolom role dari enum ke string agar mendukung 4 role baru
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->default('ahli_gizi')->change();
        });

        // Migrasi data lama: admin → ketua_sppg, pengelola → ahli_gizi
        DB::table('users')->where('role', 'admin')->update(['role' => 'ketua_sppg']);
        DB::table('users')->where('role', 'pengelola')->update(['role' => 'ahli_gizi']);
    }

    public function down(): void
    {
        // Kembalikan data ke role lama
        DB::table('users')->where('role', 'ketua_sppg')->update(['role' => 'admin']);
        DB::table('users')->whereIn('role', ['ahli_gizi', 'akuntan', 'superadmin'])
            ->update(['role' => 'pengelola']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'pengelola'])->default('pengelola')->change();
        });
    }
};
