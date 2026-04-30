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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nama_lengkap')) {
                $table->string('nama_lengkap')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'unit_sppg')) {
                $table->string('unit_sppg')->nullable()->after('nama_lengkap');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'pengelola'])->default('pengelola')->after('unit_sppg');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nama_lengkap', 'unit_sppg', 'role', 'is_active']);
        });
    }
};
