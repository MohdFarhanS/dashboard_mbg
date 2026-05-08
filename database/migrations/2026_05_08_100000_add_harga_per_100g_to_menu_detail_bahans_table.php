<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_detail_bahans', function (Blueprint $table) {
            // Snapshot harga yang berlaku saat menu di-finalize.
            // Null = menu belum final atau dibuat sebelum fitur ini ditambahkan.
            $table->decimal('harga_per_100g', 10, 2)->nullable()->after('jumlah_porsi');
        });
    }

    public function down(): void
    {
        Schema::table('menu_detail_bahans', function (Blueprint $table) {
            $table->dropColumn('harga_per_100g');
        });
    }
};
