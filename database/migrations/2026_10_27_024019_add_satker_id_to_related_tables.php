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
            $table->foreignId('satker_id')->nullable()->after('id')->constrained('satkers')->nullOnDelete();
        });

        Schema::table('permintaans', function (Blueprint $table) {
            $table->foreignId('satker_id')->nullable()->after('id')->constrained('satkers')->nullOnDelete();
        });

        Schema::table('realisasis', function (Blueprint $table) {
            $table->foreignId('satker_id')->nullable()->after('id')->constrained('satkers')->nullOnDelete();
        });

        Schema::table('supplies', function (Blueprint $table) {
            $table->foreignId('satker_id')->nullable()->after('id')->constrained('satkers')->nullOnDelete();
        });

        Schema::table('supply_distribusis', function (Blueprint $table) {
            $table->foreignId('satker_id')->nullable()->after('id')->constrained('satkers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supply_distribusis', function (Blueprint $table) {
            $table->dropForeign(['satker_id']);
            $table->dropColumn('satker_id');
        });

        Schema::table('supplies', function (Blueprint $table) {
            $table->dropForeign(['satker_id']);
            $table->dropColumn('satker_id');
        });

        Schema::table('realisasis', function (Blueprint $table) {
            $table->dropForeign(['satker_id']);
            $table->dropColumn('satker_id');
        });

        Schema::table('permintaans', function (Blueprint $table) {
            $table->dropForeign(['satker_id']);
            $table->dropColumn('satker_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['satker_id']);
            $table->dropColumn('satker_id');
        });
    }
};
