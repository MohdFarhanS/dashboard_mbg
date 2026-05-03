<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $unitSppg = config('app.unit_sppg', 'SPPG');

        // Isi unit_sppg untuk semua user yang belum memiliki nilai
        DB::table('users')->whereNull('unit_sppg')->update(['unit_sppg' => $unitSppg]);
    }

    public function down(): void
    {
        // Tidak dapat di-rollback karena nilai lama tidak disimpan
    }
};
