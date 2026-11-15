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
        Schema::table('realisasis', function (Blueprint $table) {
            $table->boolean('stok_masuk_processed')->default(false)->after('status');
        });

        Schema::table('supply_mutasis', function (Blueprint $table) {
            $table->foreignId('realisasi_id')->nullable()->after('supply_distribusi_id')->constrained('realisasis')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supply_mutasis', function (Blueprint $table) {
            $table->dropForeign(['realisasi_id']);
            $table->dropColumn(['realisasi_id']);
        });

        Schema::table('realisasis', function (Blueprint $table) {
            $table->dropColumn(['stok_masuk_processed']);
        });
    }
};
