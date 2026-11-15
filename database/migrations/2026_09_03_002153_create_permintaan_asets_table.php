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
        Schema::create('permintaan_asets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_id')->constrained('permintaans')->cascadeOnDelete();
            $table->foreignId('aset_id')->nullable()->constrained('asets')->nullOnDelete();
            $table->text('deskripsi_kerusakan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_asets');
    }
};
