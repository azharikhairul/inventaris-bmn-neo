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
        Schema::create('asets', function (Blueprint $table) {
            $table->id();
            $table->string('no')->nullable();
            $table->string('jenis_bmn')->nullable();
            $table->string('kode_satker')->nullable();
            $table->string('nama_satker')->nullable();
            $table->string('kode_barang')->nullable()->index();
            $table->string('nup')->nullable()->index();
            $table->string('nama_barang')->nullable()->index();
            $table->string('merk')->nullable();
            $table->string('tipe')->nullable();
            $table->string('kondisi')->nullable();
            $table->integer('umur_aset')->nullable();
            $table->string('intra_extra')->nullable();
            $table->string('henti_guna')->nullable();
            $table->string('status_sbsn')->nullable();
            $table->string('status_bmn_idle')->nullable();
            $table->string('status_kemitraan')->nullable();
            $table->string('bpybds')->nullable();
            $table->string('usulan_barang_hilang')->nullable();
            $table->string('usulan_barang_rb')->nullable();
            $table->string('usul_hapus')->nullable();
            $table->string('hibah_dktp')->nullable();
            $table->string('konsensi_jasa')->nullable();
            $table->string('properti_investasi')->nullable();
            $table->string('jenis_dokumen')->nullable();
            $table->string('no_dokumen')->nullable();
            $table->string('no_bpkp')->nullable();
            $table->string('no_polisi')->nullable();
            $table->string('status_sertifikasi')->nullable();
            $table->string('jenis_sertipikat')->nullable();
            $table->string('no_sertifikat')->nullable();
            $table->string('nama')->nullable();
            $table->date('tanggal_buku_pertama')->nullable();
            $table->date('tanggal_perolehan')->nullable();
            $table->date('tanggal_pengapusan')->nullable();
            $table->decimal('nilai_perolehan_pertama', 20, 2)->nullable();
            $table->decimal('nilai_mutasi', 20, 2)->nullable();
            $table->decimal('nilai_perolehan', 20, 2)->nullable();
            $table->decimal('nilai_penyusutan', 20, 2)->nullable();
            $table->decimal('nilai_buku', 20, 2)->nullable();
            $table->decimal('luas_tanah_seluruhnya', 15, 2)->nullable();
            $table->decimal('luas_tanah_untuk_bangunan', 15, 2)->nullable();
            $table->decimal('luas_tanah_untuk_sarana', 15, 2)->nullable();
            $table->decimal('luas_lahan_kosong', 15, 2)->nullable();
            $table->decimal('luas_bangunan', 15, 2)->nullable();
            $table->decimal('luas_tapak_bangunan', 15, 2)->nullable();
            $table->decimal('luas_pemanfaatan', 15, 2)->nullable();
            $table->integer('jumlah_lantai')->nullable();
            $table->integer('jumlah_foto')->nullable();
            $table->string('status_penggunaan')->nullable();
            $table->string('no_psp')->nullable();
            $table->date('tanggal_psp')->nullable();
            $table->text('alamat')->nullable();
            $table->string('rt_rw')->nullable();
            $table->string('kelurahan_desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kab_kota')->nullable();
            $table->string('kode_kab_kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_provinsi')->nullable();
            $table->string('kode_pos')->nullable();
            $table->decimal('sbsk', 20, 2)->nullable();
            $table->decimal('optimalisasi', 20, 2)->nullable();
            $table->string('penghuni')->nullable();
            $table->string('pengguna')->nullable();
            $table->string('kode_kpknl')->nullable();
            $table->string('uraian_kpknl')->nullable();
            $table->string('uraian_kanwil')->nullable();
            $table->string('nama_kl')->nullable();
            $table->string('nama_e1')->nullable();
            $table->string('nama_korwil')->nullable();
            $table->string('kode_register')->nullable();
            $table->string('lokasi_ruang')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asets');
    }
};
