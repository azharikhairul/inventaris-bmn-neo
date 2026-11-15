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
        // 1. Master Barang Persediaan (Inventory Supplies)
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique()->index();
            $table->string('nama_barang')->index();
            $table->string('kategori')->default('Alat Tulis Kantor (ATK)')->index();
            $table->string('satuan')->default('Pcs');
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(5);
            $table->text('spesifikasi')->nullable();
            $table->decimal('harga_estimasi', 15, 2)->nullable();
            $table->timestamps();
        });

        // 2. Permintaan Supply oleh Unit Kerja / Pegawai
        Schema::create('supply_permintaans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_permintaan')->unique()->index();
            $table->date('tanggal_permintaan');
            $table->string('unit_kerja');
            $table->string('nama_pemohon');
            $table->string('nip_pemohon')->nullable();
            $table->string('jabatan_pemohon')->nullable();
            $table->text('keperluan');
            $table->enum('status', ['Menunggu', 'Didistribusikan', 'Ditolak'])->default('Menunggu');
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Item Rincian Permintaan Supply
        Schema::create('supply_permintaan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_permintaan_id')->constrained('supply_permintaans')->cascadeOnDelete();
            $table->foreignId('supply_id')->constrained('supplies')->cascadeOnDelete();
            $table->integer('jumlah_diminta');
            $table->integer('jumlah_disetujui')->nullable();
            $table->string('satuan');
            $table->string('catatan')->nullable();
            $table->timestamps();
        });

        // 4. Distribusi & Berita Acara Serah Terima (BAST)
        Schema::create('supply_distribusis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_permintaan_id')->nullable()->constrained('supply_permintaans')->nullOnDelete();
            $table->string('nomor_bast')->unique()->index();
            $table->date('tanggal_bast');
            $table->string('pihak_pertama_nama'); // Yang Menyerahkan (Petugas Pengelola Persediaan)
            $table->string('pihak_pertama_nip')->nullable();
            $table->string('pihak_pertama_jabatan')->default('Pengelola BMN & Persediaan');
            $table->string('pihak_kedua_nama'); // Yang Menerima (Pemohon)
            $table->string('pihak_kedua_nip')->nullable();
            $table->string('pihak_kedua_jabatan')->nullable();
            $table->string('pihak_kedua_unit_kerja');
            $table->text('kegiatan_keperluan')->nullable();
            $table->enum('status', ['Menunggu TTD', 'Selesai'])->default('Menunggu TTD');
            $table->string('file_bast_ttd')->nullable(); // Path scan BAST bertanda tangan manual
            $table->timestamp('uploaded_at')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Item Rincian BAST Distribusi
        Schema::create('supply_distribusi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_distribusi_id')->constrained('supply_distribusis')->cascadeOnDelete();
            $table->foreignId('supply_id')->constrained('supplies')->cascadeOnDelete();
            $table->string('nama_barang');
            $table->string('spesifikasi')->nullable();
            $table->integer('jumlah');
            $table->string('satuan');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        // 6. Mutasi Riwayat Stok Persediaan (Masuk / Keluar)
        Schema::create('supply_mutasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_id')->constrained('supplies')->cascadeOnDelete();
            $table->foreignId('supply_distribusi_id')->nullable()->constrained('supply_distribusis')->nullOnDelete();
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->integer('jumlah');
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supply_mutasis');
        Schema::dropIfExists('supply_distribusi_items');
        Schema::dropIfExists('supply_distribusis');
        Schema::dropIfExists('supply_permintaan_items');
        Schema::dropIfExists('supply_permintaans');
        Schema::dropIfExists('supplies');
    }
};
