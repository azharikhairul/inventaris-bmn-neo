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
        Schema::create('realisasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_id')->nullable()->constrained('permintaans')->nullOnDelete();
            $table->string('no_fpa')->nullable();
            $table->string('vendor')->nullable();
            $table->text('pekerjaan');
            $table->enum('jenis', ['Maintenance', 'Supply'])->default('Maintenance');
            $table->date('tanggal_pengajuan')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->decimal('total_realisasi', 20, 2)->default(0);
            $table->enum('status', ['siap realisasi', 'Selesai'])->default('siap realisasi');
            $table->string('kak_file')->nullable();
            $table->string('kuitansi_file')->nullable();
            $table->timestamps();
        });

        Schema::create('realisasi_asets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_id')->constrained('realisasis')->cascadeOnDelete();
            $table->foreignId('aset_id')->nullable()->constrained('asets')->nullOnDelete();
            $table->string('nama_aset')->nullable();
            $table->string('kode_inventaris')->nullable();
            $table->text('deskripsi_kerusakan')->nullable();
            $table->string('foto_dokumentasi')->nullable();
            $table->decimal('subtotal', 20, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('realisasi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisasi_aset_id')->constrained('realisasi_asets')->cascadeOnDelete();
            $table->string('uraian');
            $table->integer('volume')->default(1);
            $table->string('satuan')->default('Unit');
            $table->decimal('harga_satuan', 20, 2)->default(0);
            $table->decimal('jumlah', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_items');
        Schema::dropIfExists('realisasi_asets');
        Schema::dropIfExists('realisasis');
    }
};
