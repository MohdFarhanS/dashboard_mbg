<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bahan_pangans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();         // AR001, AP001, dll
            $table->string('kode_lama', 10)->nullable();  // IDA001, dll
            $table->string('nama_bahan');
            $table->string('kategori');                   // Serealia, Umbi, dll
            $table->string('sub_kategori')->nullable();   // TUNGGAL/SINGLE atau OLAHAN
            $table->string('sumber')->nullable();         // KZGPI-1990, dll

            // Komposisi per 100g BDD
            $table->decimal('bdd', 5, 1)->nullable();          // % Bagian Dapat Dimakan
            $table->decimal('air', 6, 2)->nullable();          // g
            $table->decimal('energi', 7, 2)->nullable();       // Kal
            $table->decimal('protein', 6, 2)->nullable();      // g
            $table->decimal('lemak', 6, 2)->nullable();        // g
            $table->decimal('karbohidrat', 6, 2)->nullable();  // g
            $table->decimal('serat', 6, 2)->nullable();        // g
            $table->decimal('abu', 6, 2)->nullable();          // g
            $table->decimal('kalsium', 8, 2)->nullable();      // mg
            $table->decimal('fosfor', 8, 2)->nullable();       // mg
            $table->decimal('besi', 7, 2)->nullable();         // mg
            $table->decimal('natrium', 8, 2)->nullable();      // mg
            $table->decimal('kalium', 8, 2)->nullable();       // mg
            $table->decimal('tembaga', 7, 2)->nullable();      // mg
            $table->decimal('seng', 7, 2)->nullable();         // mg
            $table->decimal('retinol', 8, 2)->nullable();      // mcg
            $table->decimal('b_karoten', 9, 2)->nullable();    // mcg
            $table->decimal('kar_total', 9, 2)->nullable();    // mcg
            $table->decimal('thiamin', 7, 2)->nullable();      // mg
            $table->decimal('riboflavin', 7, 2)->nullable();   // mg
            $table->decimal('niasin', 7, 2)->nullable();       // mg
            $table->decimal('vit_c', 7, 2)->nullable();        // mg

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('kode');
            $table->index('kategori');
            $table->index('nama_bahan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_pangans');
    }
};
