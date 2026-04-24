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
        Schema::create('menu_detail_bahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_harian_id')->constrained('menu_harians')->onDelete('cascade');
            $table->foreignId('bahan_pangan_id')->constrained('bahan_pangans')->onDelete('cascade');
            $table->decimal('jumlah_gram', 8, 2);
            $table->integer('jumlah_porsi')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_detail_bahans');
    }
};
