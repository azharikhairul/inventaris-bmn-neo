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
        Schema::create('rab_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_aset_id')->constrained('permintaan_asets')->cascadeOnDelete();
            $table->string('uraian'); // deskripsi item RAB
            $table->integer('volume')->default(1);
            $table->string('satuan')->default('bh');
            $table->decimal('harga_satuan', 20, 2)->default(0);
            $table->decimal('jumlah', 20, 2)->default(0); // volume * harga_satuan, computed on save
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rab_items');
    }
};
