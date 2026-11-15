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
        Schema::table('supply_distribusis', function (Blueprint $table) {
            $table->foreignId('permintaan_id')->nullable()->after('supply_permintaan_id')->constrained('permintaans')->nullOnDelete();
            $table->foreignId('realisasi_id')->nullable()->after('permintaan_id')->constrained('realisasis')->nullOnDelete();
        });

        Schema::table('supply_distribusi_items', function (Blueprint $table) {
            $table->foreignId('supply_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supply_distribusis', function (Blueprint $table) {
            $table->dropForeign(['permintaan_id']);
            $table->dropForeign(['realisasi_id']);
            $table->dropColumn(['permintaan_id', 'realisasi_id']);
        });

        Schema::table('supply_distribusi_items', function (Blueprint $table) {
            $table->foreignId('supply_id')->nullable(false)->change();
        });
    }
};
