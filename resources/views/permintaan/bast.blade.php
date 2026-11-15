@extends('layouts.app')

@section('title', 'Dokumen Berita Acara Serah Terima (BAST) - ' . ($permintaan->pekerjaan ?? 'Permintaan'))

@section('content')
<div class="space-y-6" x-data="bastGenerator()">

    <!-- Action Bar & Toolbar (Hidden on Print) -->
    <div class="no-print bg-white/95 backdrop-blur-sm rounded-2xl border border-slate-200/90 p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sticky top-[68px] sm:top-[84px] z-20">
        <div class="flex items-center gap-3">
            <a href="{{ isset($realisasi) ? route('realisasi.show', $realisasi->id) : route('permintaan.show', $permintaan->id) }}" 
               class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors"
               title="Kembali">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div>
                <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Dokumen Berita Acara Serah Terima (BAST)</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Format baku resmi Berita Acara Serah Terima Hasil Pekerjaan / Pengadaan BPS Provinsi Gorontalo
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

            <!-- Unduh Dokumen PDF Button -->
            <button type="button" 
                    @click="downloadPdf()" 
                    :disabled="isDownloading"
                    :class="{
                        'bg-emerald-600 text-white cursor-default shadow-xs': isSuccess,
                        'bg-slate-400 text-white cursor-not-allowed': isDownloading,
                        'bg-[#094886] hover:bg-[#073868] text-white cursor-pointer shadow-xs hover:shadow': !isDownloading && !isSuccess
                    }"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold rounded-xl transition-all"
                    :title="isDownloading ? 'Sedang memproses berkas PDF, mohon tunggu...' : 'Unduh dokumen PDF BAST'">
                
                <template x-if="isDownloading">
                    <svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>

                <template x-if="isSuccess && !isDownloading">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </template>

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
            
            <!-- Header KOP BPS -->
            <table class="w-full border-b-2 border-black pb-3 mb-6">
                <tr>
                    <td class="w-16 align-middle pr-3">
                        <img src="{{ asset('logo_bps.png') }}" alt="Logo BPS" class="h-14 w-auto object-contain">
                    </td>
                    <td class="text-center align-middle">
                        <div class="text-[11px] font-semibold tracking-wider text-slate-700 uppercase">BADAN PUSAT STATISTIK</div>
                        <div class="text-[15px] font-bold tracking-wide text-black uppercase" x-text="formData.instansi"></div>
                        <div class="text-[10px] text-slate-600 mt-0.5">Jl. Dr. H. Farouk D. Isa No. 25, Kota Gorontalo 96115 • Telp: (0435) 822870</div>
                    </td>
                </tr>
            </table>

            <!-- Judul Dokumen -->
            <div class="text-center mb-6">
                <h1 class="text-base font-bold uppercase tracking-wide border-b border-black inline-block pb-0.5">
                    BERITA ACARA SERAH TERIMA HASIL PEKERJAAN
                </h1>
                <div class="text-xs font-mono font-bold mt-1.5 text-slate-800">
                    Nomor: <span x-text="formData.nomor_bast"></span>
                </div>
            </div>

            <!-- Paragraf Pembuka -->
            <p class="text-justify mb-4">
                <span x-text="formData.hari_tanggal"></span>, bertempat di Kantor <span class="font-bold" x-text="formData.instansi"></span>, kami yang bertanda tangan di bawah ini:
            </p>

            <!-- Pihak Pertama -->
            <table class="w-full mb-3 ml-2 text-xs">
                <tr>
                    <td class="w-6 align-top font-bold">1.</td>
                    <td class="w-32 align-top font-semibold">Nama</td>
                    <td class="w-4 align-top">:</td>
                    <td class="align-top font-bold" x-text="formData.pihak_pertama_nama"></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="align-top font-semibold">Jabatan</td>
                    <td class="align-top">:</td>
                    <td class="align-top" x-text="formData.pihak_pertama_jabatan"></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="align-top font-semibold">Instansi / Rekanan</td>
                    <td class="align-top">:</td>
                    <td class="align-top" x-text="formData.pihak_pertama_perusahaan"></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3" class="text-slate-500 italic pt-0.5">
                        Selanjutnya disebut sebagai <strong>PIHAK PERTAMA (Yang Menyerahkan)</strong>.
                    </td>
                </tr>
            </table>

            <!-- Pihak Kedua -->
            <table class="w-full mb-4 ml-2 text-xs">
                <tr>
                    <td class="w-6 align-top font-bold">2.</td>
                    <td class="w-32 align-top font-semibold">Nama</td>
                    <td class="w-4 align-top">:</td>
                    <td class="align-top font-bold" x-text="formData.pihak_kedua_nama"></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="align-top font-semibold">NIP</td>
                    <td class="align-top">:</td>
                    <td class="align-top font-mono" x-text="formData.pihak_kedua_nip"></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="align-top font-semibold">Jabatan</td>
                    <td class="align-top">:</td>
                    <td class="align-top" x-text="formData.pihak_kedua_jabatan"></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="3" class="text-slate-500 italic pt-0.5">
                        Selanjutnya disebut sebagai <strong>PIHAK KEDUA (Yang Menerima)</strong>.
                    </td>
                </tr>
            </table>

            <!-- Pernyataan Serah Terima -->
            <p class="text-justify mb-3">
                Kedua belah pihak telah bersepakat mengadakan serah terima hasil pekerjaan untuk kegiatan pengadaan/pemeliharaan berikut:
            </p>

            <table class="w-full mb-4 bg-slate-50/70 p-3 rounded-lg border border-slate-200 text-xs">
                <tr>
                    <td class="w-36 font-semibold text-slate-700">Nama Pekerjaan</td>
                    <td class="w-4">:</td>
                    <td class="font-bold text-slate-900" x-text="formData.pekerjaan"></td>
                </tr>
                <tr>
                    <td class="font-semibold text-slate-700">Jenis Pekerjaan</td>
                    <td>:</td>
                    <td class="font-semibold text-slate-800" x-text="formData.jenis"></td>
                </tr>
                <tr>
                    <td class="font-semibold text-slate-700">Lokasi Penyerahan</td>
                    <td>:</td>
                    <td class="font-semibold text-slate-800">Kantor BPS Provinsi Gorontalo</td>
                </tr>
            </table>

            <!-- Tabel Rincian Barang / Hasil Pekerjaan -->
            <div class="mb-5">
                <div class="text-xs font-bold text-slate-800 uppercase mb-2">Daftar Hasil Pekerjaan / Rincian Barang:</div>
                <table class="w-full border-collapse border border-black text-xs text-left">
                    <thead>
                        <tr class="bg-slate-100 font-bold text-center">
                            <th class="border border-black py-2 px-2 w-10">No</th>
                            <th class="border border-black py-2 px-3">Uraian Barang / Pekerjaan</th>
                            <th class="border border-black py-2 px-2 w-20 text-center">Volume</th>
                            <th class="border border-black py-2 px-2 w-20 text-center">Satuan</th>
                            <th class="border border-black py-2 px-3 w-40">Keterangan Hasil</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, idx) in rabItems" :key="idx">
                            <tr>
                                <td class="border border-black py-1.5 px-2 text-center" x-text="idx + 1"></td>
                                <td class="border border-black py-1.5 px-3 font-semibold" x-text="item.uraian"></td>
                                <td class="border border-black py-1.5 px-2 text-center font-bold" x-text="item.volume"></td>
                                <td class="border border-black py-1.5 px-2 text-center" x-text="item.satuan"></td>
                                <td class="border border-black py-1.5 px-3 text-emerald-700 font-medium" x-text="item.keterangan || 'Kondisi Baik & Lengkap'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pernyataan Penutup -->
            <p class="text-justify mb-8">
                PIHAK KEDUA telah memeriksa dengan seksama dan menerima hasil pekerjaan/barang yang diserahkan oleh PIHAK PERTAMA dalam keadaan baik, cukup, dan berfungsi sebagaimana mestinya sesuai ketentuan spesifikasi teknis yang dipersyaratkan.
            </p>

            <!-- Tanda Tangan Para Pihak -->
            <div class="mt-8">
                <div class="text-right text-xs mb-4">
                    <span x-text="formData.kota"></span>, <span x-text="formData.tanggal"></span>
                </div>

                <table class="w-full text-center text-xs">
                    <tr>
                        <td class="w-1/2 align-top pb-24">
                            <div class="font-bold">PIHAK PERTAMA</div>
                            <div class="text-slate-600">Yang Menyerahkan,</div>
                        </td>
                        <td class="w-1/2 align-top pb-24">
                            <div class="font-bold">PIHAK KEDUA</div>
                            <div class="text-slate-600">Yang Menerima,</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="align-bottom">
                            <div class="font-bold underline uppercase" x-text="formData.pihak_pertama_nama"></div>
                            <div class="text-slate-600" x-text="formData.pihak_pertama_jabatan"></div>
                        </td>
                        <td class="align-bottom">
                            <div class="font-bold underline uppercase" x-text="formData.pihak_kedua_nama"></div>
                            <div class="text-slate-600 font-mono">NIP. <span x-text="formData.pihak_kedua_nip"></span></div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="pt-8 pb-20 text-center">
                            <div class="font-bold">Mengetahui,</div>
                            <div class="text-slate-600" x-text="formData.kpa_jabatan"></div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-center">
                            <div class="font-bold underline uppercase" x-text="formData.kpa_nama"></div>
                            <div class="text-slate-600 font-mono">NIP. <span x-text="formData.kpa_nip"></span></div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Sesuaikan Data BAST -->
    <div x-show="showEditModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
             @click="showEditModal = false"></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">Sesuaikan Data Dokumen BAST</h3>
                    <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Nomor BAST</label>
                            <input type="text" x-model="formData.nomor_bast" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 font-mono text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal BAST</label>
                            <input type="text" x-model="formData.tanggal" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Hari & Tanggal Terbilang</label>
                        <input type="text" x-model="formData.hari_tanggal" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-xs">
                    </div>

                    <div class="border-t border-slate-200 pt-3">
                        <h4 class="font-bold text-slate-800 uppercase tracking-wider mb-2">Pihak Pertama (Yang Menyerahkan / Rekanan)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-slate-600 mb-1">Nama Pihak Pertama</label>
                                <input type="text" x-model="formData.pihak_pertama_nama" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-slate-600 mb-1">Jabatan Pihak Pertama</label>
                                <input type="text" x-model="formData.pihak_pertama_jabatan" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-slate-600 mb-1">Instansi / Perusahaan Rekanan</label>
                                <input type="text" x-model="formData.pihak_pertama_perusahaan" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-3">
                        <h4 class="font-bold text-slate-800 uppercase tracking-wider mb-2">Pihak Kedua (Yang Menerima / PPK)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-slate-600 mb-1">Nama PPK</label>
                                <input type="text" x-model="formData.pihak_kedua_nama" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-slate-600 mb-1">NIP PPK</label>
                                <input type="text" x-model="formData.pihak_kedua_nip" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 font-mono text-xs">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-3">
                        <h4 class="font-bold text-slate-800 uppercase tracking-wider mb-2">Kuasa Pengguna Anggaran (KPA)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-slate-600 mb-1">Nama KPA</label>
                                <input type="text" x-model="formData.kpa_nama" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-slate-600 mb-1">NIP KPA</label>
                                <input type="text" x-model="formData.kpa_nip" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 font-mono text-xs">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex justify-end">
                    <button type="button" @click="showEditModal = false" class="px-5 py-2 bg-[#094886] text-white font-bold rounded-xl text-xs hover:bg-[#073868]">
                        Terapkan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form for PDF Download -->
    <form id="bastPdfForm" method="POST" action="{{ isset($realisasi) ? route('realisasi.bast.pdf', $realisasi->id) : route('permintaan.bast.pdf', $permintaan->id) }}" class="hidden">
        @csrf
        <input type="hidden" name="nomor_bast" :value="formData.nomor_bast">
        <input type="hidden" name="tanggal" :value="formData.tanggal">
        <input type="hidden" name="hari_tanggal" :value="formData.hari_tanggal">
        <input type="hidden" name="kota" :value="formData.kota">
        <input type="hidden" name="instansi" :value="formData.instansi">
        <input type="hidden" name="pekerjaan" :value="formData.pekerjaan">
        <input type="hidden" name="pihak_pertama_nama" :value="formData.pihak_pertama_nama">
        <input type="hidden" name="pihak_pertama_jabatan" :value="formData.pihak_pertama_jabatan">
        <input type="hidden" name="pihak_pertama_perusahaan" :value="formData.pihak_pertama_perusahaan">
        <input type="hidden" name="pihak_kedua_nama" :value="formData.pihak_kedua_nama">
        <input type="hidden" name="pihak_kedua_nip" :value="formData.pihak_kedua_nip">
        <input type="hidden" name="pihak_kedua_jabatan" :value="formData.pihak_kedua_jabatan">
        <input type="hidden" name="kpa_nama" :value="formData.kpa_nama">
        <input type="hidden" name="kpa_nip" :value="formData.kpa_nip">
        <input type="hidden" name="kpa_jabatan" :value="formData.kpa_jabatan">
        <input type="hidden" name="rabItems" :value="JSON.stringify(rabItems)">
        <input type="hidden" name="download" value="1">
    </form>
</div>

<script>
function bastGenerator() {
    return {
        showEditModal: false,
        isDownloading: false,
        isSuccess: false,
        downloadButtonText: 'Unduh Dokumen BAST (PDF)',
        formData: {!! json_encode($defaultData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!},
        rabItems: {!! json_encode($rabItems, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!},

        downloadPdf() {
            this.isDownloading = true;
            this.downloadButtonText = 'Menyiapkan PDF...';

            const form = document.getElementById('bastPdfForm');
            if (form) {
                form.submit();
            }

            setTimeout(() => {
                this.isDownloading = false;
                this.isSuccess = true;
                this.downloadButtonText = '✓ Berhasil Diunduh';

                setTimeout(() => {
                    this.isSuccess = false;
                    this.downloadButtonText = 'Unduh Dokumen BAST (PDF)';
                }, 4000);
            }, 2500);
        }
    };
}
</script>
@endsection

