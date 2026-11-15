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
        Schema::create('satkers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_satker', 50)->unique();
            $table->string('nama_satker');
            $table->string('singkatan', 100)->nullable();
            $table->string('tingkat', 50)->default('Kabupaten/Kota');
            $table->string('kabupaten_kota', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satkers');
    }
};
