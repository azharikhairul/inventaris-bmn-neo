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
        Schema::table('realisasi_asets', function (Blueprint $table) {
            $table->longText('foto_dokumentasi')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_asets', function (Blueprint $table) {
            $table->string('foto_dokumentasi')->nullable()->change();
        });
    }
};
