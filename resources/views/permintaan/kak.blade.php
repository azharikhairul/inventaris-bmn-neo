@extends('layouts.app')

@section('title', 'Dokumen Kerangka Acuan Kerja (KAK) - ' . ($permintaan->pekerjaan ?? 'Permintaan'))

@section('content')
<div class="space-y-6" x-data="kakGenerator()">

    <!-- Action Bar & Toolbar (Hidden on Print) -->
    <div class="no-print bg-white/95 backdrop-blur-sm rounded-2xl border border-slate-200/90 p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sticky top-[68px] sm:top-[84px] z-20">
        <div class="flex items-center gap-3">
            <a href="{{ route('permintaan.show', $permintaan->id) }}" 
               class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors"
               title="Kembali ke Detail Permintaan">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div>
                <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Dokumen Kerangka Acuan Kerja (KAK)</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if($permintaan->isSupply())
                        Format baku resmi Kerangka Acuan Kerja Pengadaan Persediaan BPS Provinsi Gorontalo
                    @else
                        Format baku resmi Kerangka Acuan Kerja Pemeliharaan BPS Provinsi Gorontalo
                    @endif
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Edit Parameters Button -->
            <button type="button" 
                    @click="showEditModal = true"
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-300 text-slate-700 text-xs font-bold rounded-xl shadow-2xs transition-all cursor-pointer">
                <svg class="w-4 h-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span>Sesuaikan Data</span>
            </button>

            <!-- Unduh Dokumen PDF Button with Processing Lock & Auto-Reset -->
            <button type="button" 
                    @click="downloadPdf(true)" 
                    :disabled="isDownloading"
                    :class="{
                        'bg-emerald-600 text-white cursor-default shadow-xs': isSuccess,
                        'bg-slate-400 text-white cursor-not-allowed': isDownloading,
                        'bg-[#094886] hover:bg-[#073868] text-white cursor-pointer shadow-xs hover:shadow': !isDownloading && !isSuccess
                    }"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold rounded-xl transition-all"
                    :title="isDownloading ? 'Sedang memproses berkas PDF, mohon tunggu...' : 'Unduh dokumen PDF KAK'">
                
                <!-- Spinner saat proses pengunduhan -->
                <template x-if="isDownloading">
                    <svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>

                <!-- Checkmark saat selesai diunduh -->
                <template x-if="isSuccess && !isDownloading">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </template>

                <!-- Icon unduh standar -->
                <template x-if="!isDownloading && !isSuccess">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </template>

                <span x-text="downloadButtonText"></span>
            </button>
        </div>
    </div>

    <!-- Document Paper Preview Container -->
    <div class="overflow-x-auto pb-12">
        <div class="printable-area bg-white border border-slate-300/80 rounded-2xl shadow-md p-8 sm:p-14 mx-auto max-w-[820px] text-black font-sans leading-relaxed text-[13px]">
            
            @if($permintaan->isSupply())
                {{-- ========================================================= --}}
                {{-- TEMPLATE KAK PERSESUAIAN RESMI SUPPLY / PERSEDIAAN (PDF) --}}
                {{-- ========================================================= --}}

                <!-- ================= PAGE 1 (SUPPLY) ================= -->
                <div class="kak-page kak-page-1 space-y-4">
                    <!-- Header Logo KAK -->
                    <div class="text-center pt-2">
                        <img src="{{ asset(file_exists(public_path('logo_KAK_opt.png')) ? 'logo_KAK_opt.png' : (file_exists(public_path('logo_KAK.png')) ? 'logo_KAK.png' : 'logo_bps.png')) }}" 
                             alt="Logo KAK" 
                             class="h-16 w-auto mx-auto object-contain">
                    </div>

                    <!-- Document Title -->
                    <div class="text-center my-4 space-y-1">
                        <p class="text-[13.5px] font-bold text-black">Analisis Kebutuhan Anggaran</p>
                        <h1 class="text-[14px] font-extrabold uppercase tracking-wide text-black leading-snug" x-text="form.judul_dokumen || '{{ $permintaan->isSupply() ? 'BELANJA KEPERLUAN SEHARI-HARI PERKANTORAN (PERSEDIAAN)' : 'KERANGKA ACUAN KERJA' }}'"></h1>
                        <p class="text-[13.5px] font-bold text-black uppercase">
                            <span x-text="form.instansi"></span> TA <span x-text="form.tahun_anggaran"></span>
                        </p>
                    </div>

                    <!-- Section A: Latar Belakang -->
                    <div class="space-y-2 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">A. LATAR BELAKANG</h2>
                        <p class="indent-8">
                            Badan Pusat Statistik adalah Lembaga Pemerintah Non Kementerian yang bertanggung jawab langsung kepada Presiden. BPS dibentuk berdasarkan UU Nomor 6 Tahun 1960 tentang Sensus dan UU Nomer 7 Tahun 1960 tentang Statistik. Sebagai pengganti kedua UU tersebut ditetapkan UU Nomor 16 Tahun 1997 tentang Statistik. Tugas, fungsi dan kewenangan BPS telah ditetapkan berdasarkan Peraturan Presiden Nomor 86 Tahun 2007 tentang Badan Pusat Statistik dan Peraturan Kepala Badan Pusat Statistik Nomor 7 Tahun 2008 tentang Organisasi dan Tata Kerja Badan Pusat Statistik. Tugas BPS adalah untuk melaksanakan tugas pemerintahan dibidang statistik sesuai peraturan perundang-undangan.
                        </p>
                        <p class="indent-8">
                            Dalam rangka mendukung kelancaran pelaksanaan tugas pemerintahan di kantor Badan Pusat Statistik Provinsi Gorontalo diperlukan belanja kebutuhan dasar dan penunjang untuk operasional perkantoran berupa ATK dan Komputer Supplies. Kebutuhan tersebut merupakan komponen penting dalam menjalankan operasional sehari-hari kantor dan penunjang produktivitas/kinerja pegawai dan pelayanan publik yang prima kepada pihak eksternal.
                        </p>
                    </div>

                    <!-- Section B: Maksud dan Tujuan -->
                    <div class="space-y-1.5 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">B. MAKSUD DAN TUJUAN</h2>
                        <p class="indent-8">
                            Maksud dan tujuan dari pelaksanaan kegiatan pemenuhan belanja barang keperluan operasional perkantoran di BPS Provinsi Gorontalo adalah agar tersedianya ATK dan komputer supplies untuk memperlancar kegiatan operasional untuk mendukung kegiatan survei dan sensus dan optimalisasi pelayanan publik dilingkungan kantor BPS Provinsi Gorontalo.
                        </p>
                    </div>

                    <!-- Section C: Ruang Lingkup -->
                    <div class="space-y-1.5 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">C. RUANG LINGKUP</h2>
                        <p class="indent-8">
                            Kegiatan meliputi pengadaan dan pembelian barang persediaan berupa ATK (alat Tulis Kantor) dan komputer (komputer supplies) yang merupakan elemen penting dalam penyelenggaraan operasional pendukung survei dan sensus.
                        </p>
                    </div>

                    <!-- Section D: Produk Yang Dihasilkan -->
                    <div class="space-y-1.5 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">D. PRODUK YANG DIHASILKAN</h2>
                        <p class="indent-8">
                            Barang persediaan berupa ATK dan Komputer Supplies sesuai dengan spesfikasi kebutuhan.
                        </p>
                    </div>

                    <!-- Section E: Sumber Pembiayaan -->
                    <div class="space-y-2 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">E. SUMBER PEMBIAYAAN</h2>
                        <p class="indent-8">
                            Sumber pembiayaan adalah dari DIPA <span x-text="form.instansi"></span> dengan rincian anggaran biaya sebagai berikut:
                        </p>
                        <div class="grid grid-cols-[140px_10px_1fr] text-[13px] gap-y-1 pl-4 pt-1">
                            <span>Program</span>
                            <span>:</span>
                            <span class="font-medium" x-text="form.program"></span>

                            <span>Kegiatan</span>
                            <span>:</span>
                            <span class="font-medium" x-text="form.kegiatan"></span>

                            <span>Output</span>
                            <span>:</span>
                            <span class="font-medium" x-text="form.output"></span>

                            <span>Komponen</span>
                            <span>:</span>
                            <span class="font-medium" x-text="form.komponen"></span>

                            <span>Item Kegiatan</span>
                            <span>:</span>
                            <span class="font-medium" x-text="form.item_kegiatan"></span>

                            <span>Total Pagu</span>
                            <span>:</span>
                            <span class="font-medium">Rp <span x-text="formatRupiah(form.total_pagu)"></span>,-</span>
                        </div>

                        <p class="pt-2">
                            Estimasi biaya yang dibutuhkan pada kegiatan ini selama 1 tahun anggaran adalah sebagai berikut:
                        </p>

                        <!-- Tabel Estimasi Biaya Supply (Header Kuning Emas khas PDF) -->
                        <div class="overflow-x-auto my-3">
                            <table class="w-full border-collapse text-[12px] border border-black">
                                <thead>
                                    <tr class="bg-[#ffc000] text-black font-bold text-center border border-black">
                                        <th class="py-2.5 px-2 w-10 border border-black">No</th>
                                        <th class="py-2.5 px-4 text-center border border-black">Uraian</th>
                                        <th class="py-2.5 px-3 w-24 text-center border border-black">Unit/<br>Satuan</th>
                                        <th class="py-2.5 px-3 w-16 text-center border border-black">Vol</th>
                                        <th class="py-2.5 px-3 w-28 text-center border border-black">Harga<br>Satuan (Rp)</th>
                                        <th class="py-2.5 px-3 w-32 text-center border border-black">Jumlah (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black text-slate-900">
                                    @forelse($rabItems as $idx => $r)
                                    <tr class="bg-white">
                                        <td class="py-2.5 px-2 text-center border border-black">{{ $idx + 1 }}</td>
                                        <td class="py-2.5 px-4 font-medium border border-black">{{ $r['kegiatan'] }}</td>
                                        <td class="py-2.5 px-3 text-center border border-black">{{ $r['satuan'] }}</td>
                                        <td class="py-2.5 px-3 text-center border border-black">{{ $r['volume'] }}</td>
                                        <td class="py-2.5 px-3 text-right border border-black">{{ number_format($r['harga_satuan'], 0, ',', '.') }}</td>
                                        <td class="py-2.5 px-3 text-right font-medium border border-black">{{ number_format($r['total'], 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr class="bg-white">
                                        <td class="py-2.5 px-2 text-center border border-black">1</td>
                                        <td class="py-2.5 px-4 font-medium border border-black" x-text="form.pekerjaan"></td>
                                        <td class="py-2.5 px-3 text-center border border-black">Paket</td>
                                        <td class="py-2.5 px-3 text-center border border-black">1</td>
                                        <td class="py-2.5 px-3 text-right border border-black" x-text="formatRupiah(form.total_biaya)"></td>
                                        <td class="py-2.5 px-3 text-right font-medium border border-black" x-text="formatRupiah(form.total_biaya)"></td>
                                    </tr>
                                    @endforelse
                                    <!-- Jumlah Row -->
                                    <tr class="bg-white font-bold text-black border-t-2 border-black">
                                        <td colspan="5" class="py-2.5 px-4 text-center border border-black tracking-wider">Jumlah</td>
                                        <td class="py-2.5 px-3 text-right border border-black text-[13px]">
                                            <span x-text="formatRupiah(form.total_biaya)"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ================= PAGE 2 (SUPPLY) ================= -->
                <div class="kak-page kak-page-2 space-y-6 pt-10 page-break border-t-2 border-dashed border-slate-200 mt-10">
                    <!-- Section A: Waktu Pelaksanaan -->
                    <div class="space-y-2 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">A. WAKTU PELAKSANAAN</h2>
                        <div class="grid grid-cols-[200px_10px_1fr] text-[13px] gap-y-1 pl-4 pt-1">
                            <span>Pelaksanaan Pengadaan</span>
                            <span>:</span>
                            <span class="font-medium" x-text="form.waktu_pengadaan || form.waktu_pelaksanaan"></span>

                            <span>Pelaksanaan Kontrak</span>
                            <span>:</span>
                            <span class="font-medium" x-text="form.waktu_kontrak || form.waktu_pelaksanaan"></span>

                            <span>Pemanfaatan hasil</span>
                            <span>:</span>
                            <span class="font-medium" x-text="form.pemanfaatan_hasil || form.waktu_pelaksanaan"></span>
                        </div>
                    </div>

                    <!-- Section B: Penutup -->
                    <div class="space-y-2 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">B. PENUTUP</h2>
                        <ol class="list-decimal pl-6 space-y-1.5 text-[13px]">
                            <li>Apabila terdapat hal-hal yang bertentangan dengan ketentuan, peraturan, pedoman, dan kebijaksanaan pemerintah yang berlaku, maka segala yang termaktub di dalam Kerangka Acuan Kegiatan (KAK) akan diteliti kembali.</li>
                            <li>Hal-hal yang belum diatur dalam KAK akan ditetapkan lebih lanjut.</li>
                            <li>Demikian KAK ini dibuat untuk dipergunakan semestinya.</li>
                        </ol>
                    </div>

                    <!-- Signatures Section (Tanda Tangan Supply) -->
                    <div class="pt-8 text-[12.5px]">
                        <div class="grid grid-cols-2 gap-8 text-center">
                            <!-- Left: Penanggung Jawab Kegiatan -->
                            <div class="flex flex-col items-center justify-between min-h-[140px]">
                                <p class="font-medium">Penanggung jawab kegiatan</p>
                                <div class="mt-16">
                                    <p class="font-bold leading-tight" x-text="form.penanggung_jawab_nama"></p>
                                    <p class="text-xs mt-0.5" x-text="'NIP. ' + form.penanggung_jawab_nip"></p>
                                </div>
                            </div>

                            <!-- Right: Tanggal & Pejabat Pembuat Komitmen -->
                            <div class="flex flex-col items-center justify-between min-h-[140px]">
                                <div>
                                    <p class="font-medium mb-1"><span x-text="form.kota"></span>, <span x-text="form.tanggal"></span></p>
                                    <p class="font-medium">Pejabat Pembuat Komitmen</p>
                                </div>
                                <div class="mt-16">
                                    <p class="font-bold leading-tight" x-text="form.ppk_nama"></p>
                                    <p class="text-xs mt-0.5" x-text="'NIP. ' + form.ppk_nip"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @else
                {{-- ========================================================= --}}
                {{-- TEMPLATE KAK PEMELIHARAAN (MAINTENANCE) ASLI TIDAK DIUBAH --}}
                {{-- ========================================================= --}}

                <!-- ================= PAGE 1 ================= -->
                <div class="kak-page kak-page-1 space-y-5">
                    <!-- Header Logo KAK -->
                    <div class="text-center pt-2">
                        <img src="{{ asset(file_exists(public_path('logo_KAK_opt.png')) ? 'logo_KAK_opt.png' : (file_exists(public_path('logo_KAK.png')) ? 'logo_KAK.png' : 'logo_bps.png')) }}" 
                             alt="Logo KAK" 
                             class="h-16 w-auto mx-auto object-contain">
                    </div>

                    <!-- Document Title -->
                    <div class="text-center my-4">
                        <h1 class="text-[14.5px] font-extrabold uppercase tracking-wide text-black leading-snug">
                            Analisis Kebutuhan Anggaran <span x-text="form.pekerjaan"></span>
                        </h1>
                        <p class="text-[13.5px] font-bold text-black mt-0.5">
                            <span x-text="form.instansi"></span> TA <span x-text="form.tahun_anggaran"></span>
                        </p>
                    </div>

                    <!-- Section A: Latar Belakang -->
                    <div class="space-y-2 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">A. LATAR BELAKANG</h2>
                        <p class="indent-8">
                            Ketentuan dalam Peraturan Pemerintah Nomor 27 Tahun 2014 tentang Pengelolaan Barang Milik Negara / Daerah (Lembaga Negara Republik Indonesia Tahun 2014 Nomor 92, Tambahan Lembaran Negara Republik Indonesia Nomor 5533) Pasal 6 ayat (2) huruf (f) yang berbunyi "Pengguna Barang Milik Negara berwenang dan bertanggung jawab mengamankan dan memelihara Barang Milik Negara yang berada dalam penguasaannya".
                        </p>
                        <p class="indent-8">
                            Berdasarkan Ketentuan Peraturan Pemerintah tersebut, maka perlu dilaksanakan kegiatan pengurusan dan pemeliharaan Barang Milik Negara berupa pemeliharaan peralatan dan mesin di lingkungan Kantor <span x-text="form.instansi"></span>.
                        </p>
                        <p class="indent-8">
                            Pemeliharaan peralatan dan mesin berupa pemeliharaan mesin <span x-text="form.pekerjaan"></span> sangat penting serta memastikan aset tersebut tetap berfungsi dengan baik dan memiliki umur pakai yang optimal untuk mendukung operasional kantor dalam rangka meningkatkan produktivitas dan kinerja pegawai, dan optimalisasi pelayanan publik.
                        </p>
                    </div>

                    <!-- Section B: Maksud dan Tujuan -->
                    <div class="space-y-1.5 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">B. MAKSUD DAN TUJUAN</h2>
                        <p class="indent-8">
                            Dalam rangka memberikan kepastian berfungsinya mesin <span x-text="form.pekerjaan"></span> secara optimal serta mewujudkan ruang kerja yang nyaman untuk aktivitas kerja dalam kantor di lingkungan Badan Pusat Statistik Provinsi Gorontalo.
                        </p>
                    </div>

                    <!-- Section C: Target -->
                    <div class="space-y-1.5 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">C. TARGET</h2>
                        <p class="indent-8">
                            Target kegiatan adalah berfungsinya seluruh peralatan dan mesin berupa mesin <span x-text="form.pekerjaan"></span> agar dapat digunakan oleh pegawai untuk kelancaran kinerja dan operasional perkantoran lainnya serta mengoptimalisasi pelayanan publik.
                        </p>
                    </div>

                    <!-- Section D: Ruang Lingkup -->
                    <div class="space-y-1.5 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">D. RUANG LINGKUP</h2>
                        <p class="indent-8">
                            Ruang lingkup kegiatan pemeliharaan <span x-text="form.pekerjaan"></span> dilingkungan Kantor BPS yaitu untuk tersedianya penyedia untuk melakukan pemeliharaan rutin, berkala. Pemeliharaan peralatan dan mesin yaitu perawatan (cuci dan perbaikan) serta penggantian <em>sparepart</em>.
                        </p>
                    </div>

                    <!-- Section E: Produk Yang Dihasilkan -->
                    <div class="space-y-1.5 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">E. PRODUK YANG DIHASILKAN</h2>
                        <p class="indent-8">
                            Terlaksananya pemeliharaan peralatan dan mesin berupa pemeliharaan <span x-text="form.pekerjaan"></span> dilingkungan Kantor BPS secara rutin, berkala.
                        </p>
                    </div>

                    <!-- Section F: Waktu Pelaksanaan -->
                    <div class="space-y-2 text-justify">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">F. WAKTU PELAKSANAAN</h2>
                        <p class="indent-8">
                            Pelaksanaan kegiatan dilaksanakan secara rutin dan secara berkala dengan tujuan tertentu misalnya terdapat indikasi penurunan kinerja/kerusakan ruangan kemudian diperbaiki.
                        </p>
                        <div class="grid grid-cols-[180px_10px_1fr] text-[13px] gap-y-1 pl-4 pt-1">
                            <span>Pelaksanaan Pengadaan</span>
                            <span>:</span>
                            <span class="font-medium" x-text="form.waktu_pelaksanaan"></span>

                            <span>Pelaksanaan Kontrak</span>
                            <span>:</span>
                            <span class="font-medium" x-text="form.waktu_pelaksanaan"></span>

                            <span>Pemanfaatan Hasil</span>
                            <span>:</span>
                            <span class="font-medium" x-text="form.waktu_pelaksanaan"></span>
                        </div>
                    </div>
                </div>

                <!-- ================= PAGE 2 ================= -->
                <div class="kak-page kak-page-2 space-y-6 pt-10 page-break">
                    
                    <!-- Section G: Spesifikasi Teknis -->
                    <div class="space-y-3">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">G. SPESIFIKASI TEKNIS</h2>
                        <div class="pl-4 space-y-3">
                            <div>
                                <p class="font-medium">1. Teknis pekerjaan / Jenis Pekerjaan :</p>
                                <div class="pl-5 space-y-2 pt-1 text-[12.5px]">
                                    <div>
                                        <p class="font-medium">a. Pemasangan:</p>
                                        <ul class="list-disc pl-5 text-slate-800">
                                            <li>Bongkar Pasang unit, menyambung pipa refrigeran, serta instalasi jalur kelistrikan AC</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <p class="font-medium">b. Perawatan Rutin:</p>
                                        <ul class="list-disc pl-5 text-slate-800">
                                            <li>Mencuci filter dan evaporator, mengecek tekanan freon, serta memastikan aliran air pembuangan lancar.</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <p class="font-medium">c. Perbaikan:</p>
                                        <ul class="list-disc pl-5 text-slate-800">
                                            <li>Menganalisis kerusakan, mengganti sparepart seperti kapasitor, modul PCB, kipas blower, atau memperbaiki kebocoran.</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <p class="font-medium">d. Pengisian Freon:</p>
                                        <ul class="list-disc pl-5 text-slate-800">
                                            <li>Menambah atau mengisi ulang cairan pendingin jika tekanan atau ampere menunjukan angka di bawah normal.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <p class="font-medium">2. Jenis Kualitas Sparepart / Barang :</p>
                                <div class="pl-5 pt-1 text-[12.5px]">
                                    <p>a. Original</p>
                                    <p>b. Non Pabrikan</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section H: Biaya -->
                    <div class="space-y-3">
                        <h2 class="font-bold uppercase tracking-wider text-black text-[13px]">H. BIAYA</h2>
                        <p class="text-justify leading-relaxed">
                            Perkiraan total biaya untuk kegiatan <span class="font-medium" x-text="form.pekerjaan"></span> sebanyak 
                            <span class="font-bold" x-text="form.total_unit"></span> (<span x-text="form.total_unit_terbilang"></span>) unit sebesar 
                            <span class="font-bold">Rp<span x-text="formatRupiah(form.total_biaya)"></span>.- (<span x-text="form.total_biaya_terbilang"></span>)</span>. Rincian biaya pelaksanaan kegiatan tersebut adalah sebagai berikut:
                        </p>

                        <!-- RAB Table (Header Cyan matching official sample) -->
                        <div class="overflow-x-auto my-3">
                            <table class="w-full border-collapse text-[12px] border border-slate-300">
                                <thead>
                                    <tr class="bg-[#4697cf] text-white font-bold uppercase tracking-wider text-[11px] border border-[#3b87bc]">
                                        <th class="py-2.5 px-3 w-10 text-center border border-[#3b87bc]">NO</th>
                                        <th class="py-2.5 px-4 text-left border border-[#3b87bc]">KEGIATAN</th>
                                        <th class="py-2.5 px-3 w-14 text-center border border-[#3b87bc]">VOL</th>
                                        <th class="py-2.5 px-3 w-16 text-center border border-[#3b87bc]">SATUAN</th>
                                        <th class="py-2.5 px-4 w-32 text-right border border-[#3b87bc]">HARGA SATUAN</th>
                                        <th class="py-2.5 px-4 w-32 text-right border border-[#3b87bc]">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 text-slate-900">
                                    @forelse($rabItems as $idx => $r)
                                    <tr class="{{ $idx % 2 == 1 ? 'bg-slate-50/50' : 'bg-white' }}">
                                        <td class="py-2 px-3 text-center border border-slate-200">{{ $idx + 1 }}.</td>
                                        <td class="py-2 px-4 font-medium border border-slate-200">{{ $r['kegiatan'] }}</td>
                                        <td class="py-2 px-3 text-center border border-slate-200">{{ $r['volume'] }}</td>
                                        <td class="py-2 px-3 text-center border border-slate-200">{{ $r['satuan'] }}</td>
                                        <td class="py-2 px-4 text-right border border-slate-200">{{ number_format($r['harga_satuan'], 0, ',', '.') }}</td>
                                        <td class="py-2 px-4 text-right font-medium border border-slate-200">{{ number_format($r['total'], 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-center text-slate-400 italic border border-slate-200">
                                            Tidak ada rincian item anggaran RAB.
                                        </td>
                                    </tr>
                                    @endforelse
                                    <!-- Sub Total Row -->
                                    <tr class="bg-slate-100/80 font-bold text-black border-t-2 border-slate-300">
                                        <td colspan="5" class="py-2.5 px-4 text-left border border-slate-300 tracking-wider">SUB TOTAL</td>
                                        <td class="py-2.5 px-4 text-right border border-slate-300 text-[13px] underline">
                                            Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Signatures Section (Tanda Tangan) -->
                    <div class="pt-6 text-[12.5px]">
                        <!-- Tanggal Kota -->
                        <div class="text-center font-medium mb-6">
                            <span x-text="form.kota"></span>, <span x-text="form.tanggal"></span>
                        </div>

                        <!-- 2 Columns: Penanggung Jawab & Pejabat Pembuat Komitmen -->
                        <div class="grid grid-cols-2 gap-8 text-center">
                            <!-- Left: Penanggung Jawab Kegiatan -->
                            <div class="flex flex-col items-center justify-between min-h-[140px]">
                                <p class="font-medium">Penanggung Jawab Kegiatan</p>
                                <div class="mt-16">
                                    <p class="font-bold underline leading-tight" x-text="form.penanggung_jawab_nama"></p>
                                    <p class="text-xs mt-0.5" x-text="'NIP. ' + form.penanggung_jawab_nip"></p>
                                </div>
                            </div>

                            <!-- Right: Pejabat Pembuat Komitmen -->
                            <div class="flex flex-col items-center justify-between min-h-[140px]">
                                <p class="font-medium">Pejabat Pembuat Komitmen</p>
                                <div class="mt-16">
                                    <p class="font-bold underline leading-tight" x-text="form.ppk_nama"></p>
                                    <p class="text-xs mt-0.5" x-text="'NIP. ' + form.ppk_nip"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @endif

        </div>
    </div>

    <!-- Parameter Adjustment Modal (Sesuaikan Data) -->
    <div x-show="showEditModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200/90 w-full max-w-xl overflow-hidden" 
             @click.away="showEditModal = false">
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-[#094886] flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    </div>
                    <h3 class="font-extrabold text-slate-800 text-sm">Sesuaikan Parameter Dokumen KAK</h3>
                </div>
                <button type="button" @click="showEditModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <!-- Modal Body (Form Fields) -->
            <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                @if($permintaan->isSupply())
                {{-- FORM PARAMETER SUPPLY --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Judul Dokumen Pengadaan</label>
                    <input type="text" x-model="form.judul_dokumen" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Instansi Satker</label>
                        <input type="text" x-model="form.instansi" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tahun Anggaran</label>
                        <input type="text" x-model="form.tahun_anggaran" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-center focus:outline-hidden focus:border-[#094886]">
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-[#0c2340] uppercase tracking-wider mb-3">Rincian Anggaran DIPA</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Program</label>
                            <input type="text" x-model="form.program" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">Kegiatan</label>
                                <input type="text" x-model="form.kegiatan" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">Output</label>
                                <input type="text" x-model="form.output" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">Komponen</label>
                                <input type="text" x-model="form.komponen" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                            </div>
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">Item Kegiatan (Akun)</label>
                                <input type="text" x-model="form.item_kegiatan" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Total Pagu (Rp)</label>
                            <input type="number" x-model="form.total_pagu" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-[#0c2340] uppercase tracking-wider mb-3">Waktu Pelaksanaan</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Pelaksanaan Pengadaan</label>
                            <input type="text" x-model="form.waktu_pengadaan" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Pelaksanaan Kontrak</label>
                            <input type="text" x-model="form.waktu_kontrak" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Pemanfaatan Hasil</label>
                            <input type="text" x-model="form.pemanfaatan_hasil" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kota Dokumen</label>
                        <input type="text" x-model="form.kota" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tanggal Dokumen KAK</label>
                        <input type="text" x-model="form.tanggal" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-[#0c2340] uppercase tracking-wider mb-3">Penanggung Jawab Kegiatan</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Nama Lengkap</label>
                            <input type="text" x-model="form.penanggung_jawab_nama" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">NIP</label>
                            <input type="text" x-model="form.penanggung_jawab_nip" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-[#0c2340] uppercase tracking-wider mb-3">Pejabat Pembuat Komitmen (PPK)</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Nama Lengkap</label>
                            <input type="text" x-model="form.ppk_nama" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">NIP</label>
                            <input type="text" x-model="form.ppk_nip" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                    </div>
                </div>

                @else
                {{-- FORM PARAMETER PEMELIHARAAN (ASLI TIDAK DIUBAH) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Uraian / Nama Pekerjaan</label>
                        <input type="text" x-model="form.pekerjaan" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Instansi & Tahun Anggaran</label>
                        <div class="flex gap-2">
                            <input type="text" x-model="form.instansi" class="flex-1 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                            <input type="text" x-model="form.tahun_anggaran" class="w-24 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-center focus:outline-hidden focus:border-[#094886]">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Kota Dokumen</label>
                        <input type="text" x-model="form.kota" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Tanggal Dokumen KAK</label>
                        <input type="text" x-model="form.tanggal" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Waktu Pelaksanaan (Pengadaan / Kontrak / Pemanfaatan)</label>
                    <input type="text" x-model="form.waktu_pelaksanaan" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-[#0c2340] uppercase tracking-wider mb-3">Penanggung Jawab Kegiatan</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Nama Lengkap & Gelar</label>
                            <input type="text" x-model="form.penanggung_jawab_nama" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">NIP</label>
                            <input type="text" x-model="form.penanggung_jawab_nip" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-[#0c2340] uppercase tracking-wider mb-3">Pejabat Pembuat Komitmen (PPK)</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Nama Lengkap & Gelar</label>
                            <input type="text" x-model="form.ppk_nama" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">NIP</label>
                            <input type="text" x-model="form.ppk_nip" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:border-[#094886]">
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-end gap-2.5">
                <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200 rounded-xl transition cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="showEditModal = false; resetDownloadState()" class="px-4 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl transition cursor-pointer shadow-2xs">
                    Terapkan di Pratinjau
                </button>
                <button type="button" @click="showEditModal = false; resetDownloadState(); downloadPdf(true)" class="px-4 py-2 text-xs font-bold text-white bg-[#094886] hover:bg-[#073868] rounded-xl transition cursor-pointer shadow-xs">
                    Terapkan & Unduh PDF
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Stylesheet & Print Rules -->
<style>
@media print {
    /* Sembunyikan layout navigation, sidebar, header, dan action buttons */
    nav, aside, header, #app-sidebar, #app-header, .no-print, [role="navigation"] {
        display: none !important;
    }

    html, body {
        background-color: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        min-height: 0 !important;
        overflow: visible !important;
        display: block !important;
        font-family: Arial, Calibri, "Segoe UI", sans-serif !important;
    }

    #app-main-content, main, .overflow-x-auto, .overflow-y-auto {
        display: block !important;
        position: static !important;
        overflow: visible !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        height: auto !important;
    }

    .printable-area {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 auto !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .page-break {
        page-break-before: always !important;
        break-before: page !important;
        padding-top: 15mm !important;
    }

    table {
        border-collapse: collapse !important;
        width: 100% !important;
    }

    th {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    @page {
        size: A4 portrait;
        margin: 15mm 20mm;
    }
}
</style>

<script>
function kakGenerator() {
    return {
        showEditModal: false,
        isDownloading: false,
        isSuccess: false,
        downloadButtonText: 'Unduh PDF',
        form: {
            instansi: '{{ addslashes($defaultData['instansi']) }}',
            tahun_anggaran: '{{ addslashes($defaultData['tahun_anggaran']) }}',
            judul_dokumen: '{{ addslashes($defaultData['judul_dokumen'] ?? ($permintaan->isSupply() ? 'BELANJA KEPERLUAN SEHARI-HARI PERKANTORAN (PERSEDIAAN)' : 'KERANGKA ACUAN KERJA')) }}',
            pekerjaan: '{{ addslashes($defaultData['pekerjaan']) }}',
            jenis: '{{ addslashes($defaultData['jenis']) }}',
            program: '{{ addslashes($defaultData['program'] ?? '054.01.GG. Program Penyediaan & Pelayanan Informasi Statistik') }}',
            kegiatan: '{{ addslashes($defaultData['kegiatan'] ?? '2902 BMA Publikasi/Laporan Sensus Ekonomi') }}',
            output: '{{ addslashes($defaultData['output'] ?? '006. Publikasi/Laporan Sensus Ekonomi') }}',
            komponen: '{{ addslashes($defaultData['komponen'] ?? '530 Pelaksanaan SE2026') }}',
            item_kegiatan: '{{ addslashes($defaultData['item_kegiatan'] ?? '521811/Belanja Barang Persediaan Barang Konsumsi') }}',
            total_pagu: {{ (float)($defaultData['total_pagu'] ?? $totalBiaya) }},
            waktu_pengadaan: '{{ addslashes($defaultData['waktu_pengadaan'] ?? 'Maret – Desember 2026') }}',
            waktu_kontrak: '{{ addslashes($defaultData['waktu_kontrak'] ?? 'Maret – Desember 2026') }}',
            pemanfaatan_hasil: '{{ addslashes($defaultData['pemanfaatan_hasil'] ?? 'Maret – Desember 2026') }}',
            kota: '{{ addslashes($defaultData['kota']) }}',
            tanggal: '{{ addslashes($defaultData['tanggal']) }}',
            waktu_pelaksanaan: '{{ addslashes($defaultData['waktu_pelaksanaan']) }}',
            total_unit: {{ (int)$totalUnit }},
            total_unit_terbilang: '{{ addslashes($totalUnitTerbilang) }}',
            total_biaya: {{ (float)$totalBiaya }},
            total_biaya_terbilang: '{{ addslashes($totalBiayaTerbilang) }}',
            penanggung_jawab_nama: '{{ addslashes($defaultData['penanggung_jawab_nama']) }}',
            penanggung_jawab_nip: '{{ addslashes($defaultData['penanggung_jawab_nip']) }}',
            ppk_nama: '{{ addslashes($defaultData['ppk_nama']) }}',
            ppk_nip: '{{ addslashes($defaultData['ppk_nip']) }}',
        },
        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number || 0);
        },
        resetDownloadState() {
            this.isDownloading = false;
            this.isSuccess = false;
            this.downloadButtonText = 'Unduh PDF';
        },
        downloadPdf(isDownload = true) {
            if (!isDownload) {
                // Tampilkan pratinjau PDF di tab browser baru
                const formEl = document.createElement('form');
                formEl.method = 'POST';
                formEl.action = '{{ route('permintaan.kak.pdf', $permintaan->id) }}';
                formEl.target = '_blank';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                formEl.appendChild(csrfInput);

                for (const [key, val] of Object.entries(this.form)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = val;
                    formEl.appendChild(input);
                }

                document.body.appendChild(formEl);
                formEl.submit();
                document.body.removeChild(formEl);
                return;
            }

            // Kunci tombol saat sedang proses unduh agar tidak ditekan berulang kali
            if (this.isDownloading) {
                return;
            }

            this.isDownloading = true;
            this.isSuccess = false;
            this.downloadButtonText = 'Menyiapkan PDF...';

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('download', '1');
            for (const [key, val] of Object.entries(this.form)) {
                formData.append(key, val);
            }

            fetch('{{ route('permintaan.kak.pdf', $permintaan->id) }}', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal memproses berkas PDF.');
                return res.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                const safeName = (this.form.pekerjaan || 'Dokumen_KAK').replace(/[^a-zA-Z0-9_-]/g, '_');
                a.download = `KAK-${safeName}-{{ $permintaan->id }}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                // Berikan jeda feedback sukses, lalu pulihkan tombol agar bisa diunduh kembali
                this.isDownloading = false;
                this.isSuccess = true;
                this.downloadButtonText = '✓ Selesai Terunduh';

                setTimeout(() => {
                    this.isSuccess = false;
                    this.downloadButtonText = 'Unduh PDF';
                }, 2000);
            })
            .catch(err => {
                console.error(err);
                this.isDownloading = false;
                this.isSuccess = false;
                this.downloadButtonText = 'Unduh PDF';
                alert('Terjadi kesalahan saat mengunduh dokumen KAK. Silakan coba kembali.');
            });
        }
    };
}
</script>
@endsection
