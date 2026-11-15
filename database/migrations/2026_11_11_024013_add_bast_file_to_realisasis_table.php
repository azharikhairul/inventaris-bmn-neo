<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('realisasis', function (Blueprint $table) {
            $table->string('bast_file')->nullable()->after('kak_file');
        });

        // Salin data kuitansi_file yang sudah ada ke bast_file
        DB::statement('UPDATE realisasis SET bast_file = kuitansi_file WHERE kuitansi_file IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasis', function (Blueprint $table) {
            $table->dropColumn('bast_file');
        });
    }
};
