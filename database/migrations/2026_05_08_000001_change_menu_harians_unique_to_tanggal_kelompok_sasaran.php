<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_harians', function (Blueprint $table) {
            // Ganti unique constraint: dari hanya tanggal → (tanggal + kelompok_sasaran)
            // Sehingga 1 hari bisa menyimpan banyak menu, 1 per kelompok_sasaran
            $table->dropUnique('menu_harians_tanggal_unique');
            $table->unique(['tanggal', 'kelompok_sasaran'], 'menu_harians_tanggal_kelompok_sasaran_unique');
        });
    }

    public function down(): void
    {
        Schema::table('menu_harians', function (Blueprint $table) {
            $table->dropUnique('menu_harians_tanggal_kelompok_sasaran_unique');
            $table->unique(['tanggal'], 'menu_harians_tanggal_unique');
        });
    }
};
