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
        Schema::table('asets', function (Blueprint $table) {
            $table->text('nama')->nullable()->change();
            $table->text('uraian_kpknl')->nullable()->change();
            $table->text('uraian_kanwil')->nullable()->change();
            $table->text('no_dokumen')->nullable()->change();
            $table->text('no_sertifikat')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            $table->string('nama')->nullable()->change();
            $table->string('uraian_kpknl')->nullable()->change();
            $table->string('uraian_kanwil')->nullable()->change();
            $table->string('no_dokumen')->nullable()->change();
            $table->string('no_sertifikat')->nullable()->change();
        });
    }
};
