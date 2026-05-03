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
        Schema::create('harga_bahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_pangan_id')->constrained('bahan_pangans')->cascadeOnDelete();
            $table->decimal('harga_per_100g', 10, 2)->default(0); // harga per 100g (satuan TKPI)
            $table->date('berlaku_mulai');
            $table->date('berlaku_sampai')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->index(['berlaku_mulai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_bahans');
    }
};
