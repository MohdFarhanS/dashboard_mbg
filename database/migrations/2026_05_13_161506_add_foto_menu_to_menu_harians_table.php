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
            $table->string('foto_menu')->nullable()->after('catatan_anggaran');
        });
    }

    public function down(): void
    {
        Schema::table('menu_harians', function (Blueprint $table) {
            $table->dropColumn('foto_menu');
        });
    }
};
