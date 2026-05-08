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
        Schema::table('menu_harians', function (Blueprint $table) {
            $table->string('kelompok_sasaran')->default('SD_4_6')->after('jumlah_porsi');
        });
    }

    public function down(): void
    {
        Schema::table('menu_harians', function (Blueprint $table) {
            $table->dropColumn('kelompok_sasaran');
        });
    }
};
