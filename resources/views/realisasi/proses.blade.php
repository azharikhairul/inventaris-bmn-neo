@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-20" x-data="inputRealisasiForm()">
    <!-- Header Title -->
    <div>
        <div class="flex items-center gap-2.5">
            <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 tracking-tight">
                {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'Input Realisasi Supply (Persediaan)' : 'Input Realisasi Perbaikan' }}
            </h1>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-[#094886] border border-blue-200' }}">
                {{ $realisasi['jenis'] ?? 'Maintenance' }}
            </span>
        </div>
        <p class="text-sm text-slate-500 mt-1">
            {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'Input rincian pengadaan barang persediaan dan anggaran yang telah diselesaikan.' : 'Input rincian pekerjaan dan anggaran yang telah diselesaikan.' }}
        </p>
    </div>

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl shadow-xs">
        <div class="font-bold text-sm mb-1">Terdapat kesalahan input:</div>
        <ul class="list-disc list-inside text-xs space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form id="formRealisasiSelesai" action="{{ route('realisasi.selesai', $realisasi['id'] ?? 1) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="asetList" :value="JSON.stringify(jenis === 'Supply' ? [{ id: (asetList[0] && asetList[0].id) || null, nama: 'Barang Persediaan', items: supplyItems }] : asetList)">
        <input type="hidden" name="supplyItems" :value="jenis === 'Supply' ? JSON.stringify(supplyItems) : ''">

        <!-- Card 1: Informasi Umum Realisasi -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-6 shadow-xs space-y-5 mb-6">
            <h2 class="text-base font-bold text-[#094886]">Informasi Umum Realisasi</h2>

            <!-- Pekerjaan / Kegiatan -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-lg p-4">
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pekerjaan/Kegiatan</span>
                <span class="block text-base font-bold text-slate-900 mt-0.5">{{ $realisasi['pekerjaan'] ?? 'Perbaikan & Maintenance Aset TI' }}</span>
            </div>

            @if(($realisasi['jenis'] ?? '') === 'Supply')
                <!-- Grid: 4 Input Realisasi Supply (3 Terbawa dari Modal + Penerimaan Barang) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- No. Dokumen FPA -->
                    <div class="bg-blue-50/50 border border-blue-200 rounded-lg p-3.5 flex flex-col justify-between">
                        <div>
                            <label class="block text-[10px] font-bold text-blue-900 uppercase tracking-wider mb-1">
                                No. Dokumen FPA / Pengadaan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="no_fpa" x-model="no_fpa" required placeholder="FPA/BPS/..."
                                   class="w-full bg-white border border-blue-300 hover:border-blue-400 rounded-md px-2.5 py-1.5 text-xs text-slate-800 font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none font-medium">
                        </div>
                        <span class="block text-[10px] text-emerald-600 font-medium mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Terbawa dari modal proses
                        </span>
                    </div>

                    <!-- Toko / Penyedia Barang -->
                    <div class="bg-blue-50/50 border border-blue-200 rounded-lg p-3.5 flex flex-col justify-between">
                        <div>
                            <label class="block text-[10px] font-bold text-blue-900 uppercase tracking-wider mb-1">
                                Toko / Penyedia Barang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="vendor" x-model="vendor" required placeholder="Contoh: Toko ATK Sejahtera / CV Sumber Kertas"
                                   class="w-full bg-white border border-blue-300 hover:border-blue-400 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none font-medium">
                        </div>
                        <span class="block text-[10px] text-emerald-600 font-medium mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Terbawa dari modal proses
                        </span>
                    </div>

                    <!-- Tanggal Pembelian / Pemesanan -->
                    <div class="bg-blue-50/50 border border-blue-200 rounded-lg p-3.5 flex flex-col justify-between">
                        <div>
                            <label class="block text-[10px] font-bold text-blue-900 uppercase tracking-wider mb-1">
                                Tanggal Pembelian / Pemesanan <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal_mulai" x-model="tanggal_mulai" required
                                   class="w-full bg-white border border-blue-300 hover:border-blue-400 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none font-medium cursor-pointer">
                        </div>
                        <span class="block text-[10px] text-emerald-600 font-medium mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Terbawa dari modal proses
                        </span>
                    </div>

                    <!-- Tanggal Penerimaan Barang -->
                    <div class="bg-blue-50/50 border border-blue-200 rounded-lg p-3.5 flex flex-col justify-between">
                        <div>
                            <label class="block text-[10px] font-bold text-blue-900 uppercase tracking-wider mb-1">
                                Tanggal Penerimaan Barang <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="tanggal_selesai" x-model="tanggal_selesai" :min="tanggal_mulai" required
                                   class="w-full bg-white border border-blue-300 hover:border-blue-400 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none font-medium cursor-pointer">
                        </div>
                        <span class="block text-[10px] text-slate-400 mt-1.5">
                            Tanggal barang fisik tiba di gudang
                        </span>
                    </div>
                </div>
            @else
                <!-- Grid: Data dari modal permintaan & Input Selesai Pengerjaan (Khusus Maintenance - Tetap Sesuai Asli) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- No. Dokumen FPA (Read-only) -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-lg p-3.5">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">No. Dokumen FPA</span>
                        <span class="block text-sm font-bold text-slate-800 mt-1 font-mono">{{ $realisasi['no_fpa'] ?? '-' }}</span>
                        <span class="block text-[10px] text-slate-400 mt-0.5">Dari FPA awal (Read-only)</span>
                    </div>

                    <!-- Vendor Pelaksana (Read-only) -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-lg p-3.5">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Vendor Pelaksana</span>
                        <div class="flex items-center gap-1.5 mt-1">
                            <svg class="w-3.5 h-3.5 text-slate-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14M21 7v14M6 11h2M6 15h2M16 11h2M16 15h2M9 21v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4M9 3h6v4H9z"></path></svg>
                            <span class="text-sm font-bold text-slate-800 truncate">{{ $realisasi['vendor'] ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- Mulai Pengerjaan (Read-only dari modal permintaan) -->
                    <div class="bg-slate-50 border border-slate-200/80 rounded-lg p-3.5">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mulai Pengerjaan</span>
                        <div class="flex items-center gap-1.5 mt-1">
                            <svg class="w-3.5 h-3.5 text-blue-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <span class="text-sm font-bold text-slate-800">{{ $realisasi['tanggal_mulai_formatted'] ?? '-' }}</span>
                        </div>
                        <span class="block text-[10px] text-emerald-600 font-medium mt-0.5">✓ Dari modal proses</span>
                    </div>

                    <!-- Selesai Pengerjaan (Input Baru) -->
                    <div class="bg-blue-50/50 border border-blue-200 rounded-lg p-3.5 flex flex-col justify-center">
                        <label class="block text-[10px] font-bold text-blue-900 uppercase tracking-wider mb-1">
                            Selesai Pengerjaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal_selesai" x-model="tanggal_selesai" min="{{ $realisasi['tanggal_mulai'] }}" required
                               class="w-full bg-white border border-blue-300 hover:border-blue-400 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none font-medium cursor-pointer">
                    </div>
                </div>
            @endif

            <!-- Upload Dokumen Realisasi -->
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <span class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Dokumen Realisasi <span class="text-rose-500">*</span></span>
                    <span class="text-[11px] text-slate-500 font-medium">
                        @if(($realisasi['jenis'] ?? '') === 'Supply')
                            KAK dan BAST bertanda tangan fisik wajib diunggah
                        @else
                            KAK dan Kuitansi wajib diunggah
                        @endif
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Upload KAK -->
                    <label class="border-2 border-dashed border-slate-200 hover:border-blue-400 bg-slate-50/50 hover:bg-blue-50/30 rounded-xl p-6 flex flex-col items-center justify-center cursor-pointer transition-all group"
                           :class="{ 'border-emerald-400 bg-emerald-50/30': kakUploaded }">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 group-hover:scale-110 transition-transform"
                             :class="kakUploaded ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-50 text-[#094886]'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-slate-800 tracking-wider">KAK <span class="text-rose-500">*</span></span>
                        <span class="text-[11px] text-slate-400 mt-0.5">PDF (Max 10MB)</span>
                        <input type="file" name="kak" accept=".pdf" class="hidden" @change="if ($event.target.files.length) { kakUploaded = true; kakFileName = $event.target.files[0].name; }">
                        <template x-if="kakUploaded">
                            <span class="text-xs text-emerald-600 font-semibold mt-2 flex items-center gap-1 text-center">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span class="truncate max-w-[200px]" x-text="kakFileName || 'File KAK terpilih'"></span>
                            </span>
                        </template>
                    </label>

                    @if(($realisasi['jenis'] ?? '') === 'Supply')
                    <!-- Upload BAST (Khusus Supply) -->
                    <label class="border-2 border-dashed border-slate-200 hover:border-blue-400 bg-slate-50/50 hover:bg-blue-50/30 rounded-xl p-6 flex flex-col items-center justify-center cursor-pointer transition-all group"
                           :class="{ 'border-emerald-400 bg-emerald-50/30': bastUploaded }">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 group-hover:scale-110 transition-transform"
                             :class="bastUploaded ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-50 text-[#094886]'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-slate-800 tracking-wider">BAST <span class="text-rose-500">*</span></span>
                        <span class="text-[11px] text-slate-400 mt-0.5">PDF/JPG (Max 10MB)</span>
                        <input type="file" name="bast" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="if ($event.target.files.length) { bastUploaded = true; bastFileName = $event.target.files[0].name; }">
                        <template x-if="bastUploaded">
                            <span class="text-xs text-emerald-600 font-semibold mt-2 flex items-center gap-1 text-center">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span class="truncate max-w-[200px]" x-text="bastFileName || 'File BAST terpilih'"></span>
                            </span>
                        </template>
                    </label>
                    @else
                    <!-- Upload Kuitansi (Khusus Maintenance) -->
                    <label class="border-2 border-dashed border-slate-200 hover:border-blue-400 bg-slate-50/50 hover:bg-blue-50/30 rounded-xl p-6 flex flex-col items-center justify-center cursor-pointer transition-all group"
                           :class="{ 'border-emerald-400 bg-emerald-50/30': bastUploaded }">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 group-hover:scale-110 transition-transform"
                             :class="bastUploaded ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-50 text-[#094886]'">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                <line x1="2" y1="10" x2="22" y2="10"></line>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-slate-800 tracking-wider">KUITANSI <span class="text-rose-500">*</span></span>
                        <span class="text-[11px] text-slate-400 mt-0.5">PDF/JPG (Max 10MB)</span>
                        <input type="file" name="kuitansi" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="if ($event.target.files.length) { bastUploaded = true; bastFileName = $event.target.files[0].name; }">
                        <template x-if="bastUploaded">
                            <span class="text-xs text-emerald-600 font-semibold mt-2 flex items-center gap-1 text-center">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span class="truncate max-w-[200px]" x-text="bastFileName || 'File Kuitansi terpilih'"></span>
                            </span>
                        </template>
                    </label>
                    @endif
                </div>

                @if(($realisasi['jenis'] ?? '') === 'Supply')
                <!-- Shortcut BAST Generator (Hanya untuk Supply) -->
                <div class="mt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-600 bg-blue-50/60 px-3.5 py-2.5 rounded-xl border border-blue-200/80">
                    <div class="flex items-center gap-1.5 font-medium">
                        <svg class="w-4 h-4 text-[#094886]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <span>Belum mencetak formulir Berita Acara Serah Terima (BAST)?</span>
                    </div>
                    <a href="{{ route('realisasi.bast', $realisasi['id']) }}" target="_blank" 
                       class="font-bold text-[#094886] hover:text-[#073868] hover:underline inline-flex items-center gap-1">
                        <span>Sesuaikan & Unduh PDF BAST ↗</span>
                    </a>
                </div>
                @endif
            </div>
        </div>

        @if(($realisasi['jenis'] ?? '') === 'Supply')
            <!-- ========================================================================= -->
            <!-- CARD 2 KHUSUS SUPPLY: DETAIL RAB PERMINTAAN SUPPLY                        -->
            <!-- ========================================================================= -->
            @php
                $totalRabAwalSupply = 0;
                foreach($realisasi['rabItemsPermintaan'] ?? [] as $rab) {
                    $totalRabAwalSupply += ($rab['vol'] ?? 1) * ($rab['hargaSatuan'] ?? 0);
                }
            @endphp
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden mb-6 transition-all" x-data="{ openRabSupply: true }">
                <button type="button" 
                        @click="openRabSupply = !openRabSupply" 
                        class="w-full bg-[#edf2f9] hover:bg-[#e4ebf6] px-6 py-3.5 border-b border-slate-200/80 flex items-center justify-between transition-colors text-left cursor-pointer select-none">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-slate-500 transition-transform duration-200" 
                             :class="openRabSupply ? '' : '-rotate-90'" 
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                        <h3 class="text-xs font-bold text-[#094886] uppercase tracking-wider flex items-center gap-2">
                            <span>Detail RAB Permintaan Supply</span>
                            <span class="text-[10px] font-semibold text-slate-500 lowercase font-normal">(klik untuk buka / tutup)</span>
                        </h3>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-semibold text-emerald-700 bg-white px-2.5 py-0.5 rounded-full border border-emerald-200 shadow-2xs">
                            {{ count($realisasi['rabItemsPermintaan'] ?? []) }} Barang Permintaan
                        </span>
                        <span class="text-xs font-bold text-slate-700 bg-white px-3 py-0.5 rounded-full border border-slate-200/80 shadow-2xs">
                            Total RAB: Rp {{ number_format($totalRabAwalSupply, 0, ',', '.') }}
                        </span>
                    </div>
                </button>
                <div x-show="openRabSupply" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#edf2f9]/70 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-6">URAIAN BARANG / PERSEDIAAN</th>
                                <th class="py-3 px-6 w-24 text-center">VOL</th>
                                <th class="py-3 px-6 w-32 text-center">SATUAN</th>
                                <th class="py-3 px-6 w-40 text-right">HARGA SATUAN (RP)</th>
                                <th class="py-3 px-6 w-48 text-right pr-8">JUMLAH (RP)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @forelse($realisasi['rabItemsPermintaan'] ?? [] as $rab)
                                @php
                                    $subtotal = ($rab['vol'] ?? 1) * ($rab['hargaSatuan'] ?? 0);
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-2.5 px-6 font-medium text-slate-800">{{ $rab['uraian'] }}</td>
                                    <td class="py-2.5 px-6 text-center text-slate-600">{{ $rab['vol'] }}</td>
                                    <td class="py-2.5 px-6 text-center text-slate-600">{{ $rab['satuan'] }}</td>
                                    <td class="py-2.5 px-6 text-right text-slate-600">{{ number_format($rab['hargaSatuan'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-6 text-right font-semibold text-slate-900 pr-8">{{ number_format($subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-sm text-slate-400 italic">Tidak ada data rincian RAB permintaan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200/80 bg-white">
                                <td colspan="4" class="py-3.5 px-6 text-right font-bold text-slate-800 text-xs uppercase tracking-wider">
                                    TOTAL RAB PERMINTAAN
                                </td>
                                <td class="py-3.5 px-6 text-right font-bold text-slate-900 text-base pr-8">
                                    {{ number_format($totalRabAwalSupply, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- CARD 3 KHUSUS SUPPLY: DETAIL RAB REALISASI (BARANG PERSEDIAAN)            -->
            <!-- ========================================================================= -->
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden mb-6">
                <!-- Header Card -->
                <div class="bg-[#edf2f9] px-6 py-4 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            Detail RAB Realisasi
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Rincian Barang Persediaan Realisasi: Input daftar barang persediaan yang dibeli / direalisasikan beserta harga satuannya.</p>
                    </div>

                    <button type="button" @click="copyFromRABSupply()" 
                            x-show="rabItemsPermintaan && rabItemsPermintaan.length > 0" 
                            class="text-xs font-semibold text-[#094886] bg-white border border-blue-200 px-3.5 py-1.5 rounded-lg hover:bg-blue-50 transition-colors shadow-2xs inline-flex items-center gap-1.5 cursor-pointer self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        <span>Salin dari RAB Permintaan</span>
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Tabel Barang Persediaan -->
                    <div class="border border-slate-200/90 rounded-lg overflow-x-auto shadow-2xs">
                        <table class="w-full min-w-[620px] text-left border-collapse">
                            <thead>
                                <tr class="bg-emerald-50/60 border-b border-slate-200/80 text-[11px] font-bold text-emerald-900 uppercase tracking-wider">
                                    <th class="py-3 px-4">Nama Barang / Persediaan <span class="text-rose-500 font-bold">*</span></th>
                                    <th class="py-3 px-4 w-24 text-center">Vol <span class="text-rose-500 font-bold">*</span></th>
                                    <th class="py-3 px-4 w-36 text-center">Satuan <span class="text-rose-500 font-bold">*</span></th>
                                    <th class="py-3 px-4 w-44 text-right">Harga Satuan (Rp) <span class="text-rose-500 font-bold">*</span></th>
                                    <th class="py-3 px-4 w-44 text-right">Jumlah (Rp)</th>
                                    <th class="py-3 px-4 w-14 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                <template x-for="(item, itemIndex) in supplyItems" :key="item.id || itemIndex">
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <!-- Nama Barang -->
                                        <td class="py-2.5 px-4 relative" @click.outside="item.dropdownOpen = false">
                                            <div class="relative flex items-center">
                                                <input type="text" 
                                                       x-model="item.uraian" 
                                                       @focus="item.dropdownOpen = true"
                                                       @input="item.dropdownOpen = true"
                                                       placeholder="Ketik atau pilih nama barang..."
                                                       class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-md px-3 py-1.5 pr-7 text-xs text-slate-800 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none font-medium" 
                                                       required>
                                                <button type="button" 
                                                        @click="item.dropdownOpen = !item.dropdownOpen" 
                                                        class="absolute right-1 text-slate-400 hover:text-slate-600 p-1 cursor-pointer"
                                                        tabindex="-1">
                                                    <svg class="w-3.5 h-3.5 transition-transform" :class="{'rotate-180': item.dropdownOpen}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="6 9 12 15 18 9"></polyline>
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Dropdown Autocomplete Master Persediaan -->
                                            <div x-show="item.dropdownOpen" 
                                                 x-cloak
                                                 class="absolute z-50 left-4 top-full mt-1 w-80 sm:w-96 bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden max-h-56 overflow-y-auto divide-y divide-slate-100 text-xs">
                                                <div class="p-2 bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                                    <span>Master Persediaan</span>
                                                    <span class="text-[10px] text-slate-400 font-normal">Bisa pilih atau ketik</span>
                                                </div>
                                                <template x-for="(sug, sIdx) in getFilteredSupplies(item.uraian)" :key="sIdx">
                                                    <div @click="selectSupplyOption(item, sug)"
                                                         class="p-2.5 hover:bg-emerald-50/70 cursor-pointer flex items-center justify-between gap-2 transition-colors">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="font-bold text-slate-800 truncate" x-text="sug.nama_barang"></div>
                                                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-400">
                                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200" x-text="sug.kategori || 'Persediaan'"></span>
                                                                <span class="font-mono text-emerald-700 font-semibold text-[10px]" x-text="'Stok: ' + (sug.stok || 0) + ' ' + (sug.satuan || 'unit')"></span>
                                                                <template x-if="sug.harga_estimasi">
                                                                    <span class="text-slate-500 text-[10px]" x-text="'• Rp ' + formatRupiah(sug.harga_estimasi)"></span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <span class="text-xs text-emerald-600 font-semibold flex-shrink-0">Pilih →</span>
                                                    </div>
                                                </template>
                                                <template x-if="getFilteredSupplies(item.uraian).length === 0">
                                                    <div class="p-3 text-center text-slate-400 text-xs">
                                                        Barang belum ada di master, silakan ketik langsung nama barang.
                                                    </div>
                                                </template>
                                            </div>
                                        </td>

                                        <!-- Volume -->
                                        <td class="py-2.5 px-4 text-center">
                                            <input type="number" min="1" x-model.number="item.vol"
                                                   class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-md px-2 py-1.5 text-center text-xs text-slate-800 focus:ring-1 focus:ring-emerald-500 focus:outline-none font-medium" required>
                                        </td>

                                        <!-- Satuan -->
                                        <td class="py-2.5 px-4">
                                            <div class="relative">
                                                <select x-model="item.satuan" 
                                                        class="w-full appearance-none bg-white border border-slate-300 hover:border-slate-400 rounded-md px-2.5 py-1.5 pr-6 text-xs text-slate-700 focus:ring-1 focus:ring-emerald-500 focus:outline-none cursor-pointer" required>
                                                    <option value="buah">buah</option>
                                                    <option value="unit">unit</option>
                                                    <option value="pcs">pcs</option>
                                                    <option value="rim">rim</option>
                                                    <option value="kotak">kotak</option>
                                                    <option value="box">box</option>
                                                    <option value="pak">pak</option>
                                                    <option value="set">set</option>
                                                    <option value="roll">roll</option>
                                                    <option value="lusin">lusin</option>
                                                    <option value="paket">paket</option>
                                                    <option value="lembar">lembar</option>
                                                    <option value="batang">batang</option>
                                                    <option value="botol">botol</option>
                                                    <option value="tube">tube</option>
                                                    <option value="kaleng">kaleng</option>
                                                    <option value="liter">liter</option>
                                                    <option value="kg">kg</option>
                                                    <option value="meter">meter</option>
                                                    <option value="hari">hari</option>
                                                    <option value="ls">ls (lumpsum)</option>
                                                    <option :value="item.satuan" x-text="item.satuan" x-show="item.satuan && !['buah','unit','pcs','rim','kotak','box','pak','set','roll','lusin','paket','lembar','batang','botol','tube','kaleng','liter','kg','meter','hari','ls'].includes(item.satuan)"></option>
                                                </select>
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Harga Satuan -->
                                        <td class="py-2.5 px-4 text-right">
                                            <input type="number" min="0" step="any" x-model.number="item.hargaSatuan"
                                                   class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-md px-2.5 py-1.5 text-right text-xs text-slate-800 focus:ring-1 focus:ring-emerald-500 focus:outline-none font-medium" required>
                                        </td>

                                        <!-- Jumlah (Rp) -->
                                        <td class="py-2.5 px-4 text-right font-medium text-slate-900 text-xs">
                                            <span x-text="formatRupiah((item.vol || 0) * (item.hargaSatuan || 0))"></span>
                                        </td>

                                        <!-- Hapus Baris -->
                                        <td class="py-2.5 px-4 text-center">
                                            <button type="button" @click="hapusSupplyItem(itemIndex)" 
                                                    class="p-1 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer" title="Hapus Barang">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <tr x-show="supplyItems.length === 0">
                                    <td colspan="6" class="py-5 px-4 text-center text-sm text-slate-500">
                                        Belum ada barang persediaan. Klik tombol <strong>"+ Tambah Barang Persediaan"</strong> di bawah atau <strong>"Salin dari RAB Permintaan"</strong>.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-200/80 bg-white">
                                    <td colspan="4" class="py-3 px-4 text-right font-bold text-slate-700 text-xs uppercase tracking-wider">
                                        Total RAB Realisasi (Persediaan)
                                    </td>
                                    <td colspan="2" class="py-3 px-4 text-right font-extrabold text-emerald-800 text-base pr-8">
                                        <span x-text="'Rp ' + formatRupiah(totalBiayaSupply)"></span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Tombol Tambah Barang Persediaan -->
                    <div>
                        <button type="button" @click="tambahSupplyItem()" 
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-xs transition-colors cursor-pointer">
                            <span class="text-sm font-bold">+</span>
                            <span>Tambah Barang Persediaan</span>
                        </button>
                    </div>

                    <!-- Dokumentasi Foto Pengadaan (Opsional) -->
                    <div class="pt-4 border-t border-slate-200/80">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <h4 class="text-xs font-bold text-[#094886] uppercase tracking-wider">Dokumentasi Foto Pengadaan Barang</h4>
                                <span class="text-[10px] text-slate-400 font-semibold bg-slate-100 px-2 py-0.5 rounded-full">Opsional</span>
                            </div>
                            <span x-show="supplyPhotosCount > 0" class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200" x-text="supplyPhotosCount + ' foto dipilih'"></span>
                        </div>
                        
                        <!-- Interactive Dropzone -->
                        <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 bg-slate-50/70 hover:bg-emerald-50/30 rounded-xl p-6 text-center transition-all group relative cursor-pointer">
                            <input type="file" 
                                   multiple="multiple" 
                                   id="foto_supply_input"
                                   name="foto_supply[]" 
                                   accept="image/*" 
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                   @change="handleSupplyFotoChange($event)">
                            
                            <div class="flex flex-col items-center justify-center pointer-events-none">
                                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-800">Klik atau Tarik Foto Barang ke Sini</span>
                                <span class="text-[11px] text-slate-500 mt-0.5">Mendukung format JPG, PNG</span>
                                <span class="text-[10px] text-emerald-600 font-medium mt-1">Bisa pilih beberapa foto sekaligus (Ctrl + klik) atau tambah satu per satu</span>
                            </div>
                        </div>

                        <!-- Previews -->
                        <div x-show="supplyPhotoPreviews && supplyPhotoPreviews.length > 0" class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-slate-700" x-text="'Foto Terpilih (' + supplyPhotoPreviews.length + '):'"></span>
                                <button type="button" 
                                        @click="clearAllSupplyPhotos()"
                                        class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition-colors cursor-pointer">
                                    Hapus Semua Foto
                                </button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                                <template x-for="(photo, pIdx) in supplyPhotoPreviews" :key="pIdx">
                                    <div class="relative group rounded-lg overflow-hidden border border-slate-200 shadow-2xs bg-white aspect-[4/3]">
                                        <img :src="photo.url" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">
                                            <div class="flex justify-end">
                                                <button type="button" 
                                                        @click="removeSupplyFoto(pIdx)"
                                                        class="w-6 h-6 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center shadow-xs cursor-pointer transition-colors"
                                                        title="Hapus foto ini">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                                    </svg>
                                                </button>
                                            </div>
                                            <span class="text-[10px] text-white truncate font-medium drop-shadow-xs" x-text="photo.name"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- ========================================================================= -->
            <!-- CARD 2 KHUSUS MAINTENANCE: DETAIL RAB PERMINTAAN                          -->
            <!-- ========================================================================= -->
            @php
                $totalRabAwal = 0;
                foreach($realisasi['asetList'] as $asetItem) {
                    foreach($asetItem['rabItems'] ?? [] as $rab) {
                        $totalRabAwal += (($rab['vol'] ?? 0) * ($rab['hargaSatuan'] ?? 0));
                    }
                }
            @endphp
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden mb-6 transition-all" 
                 x-data="{ 
                     openRab: true, 
                     expandedRABs: { 0: true, 1: true, 2: true, 3: true, 4: true, 5: true, 6: true, 7: true, 8: true, 9: true },
                     toggleAset(idx) {
                         this.expandedRABs[idx] = !this.expandedRABs[idx];
                     }
                 }">
                <!-- Collapsible Header -->
                <button type="button" 
                        @click="openRab = !openRab" 
                        class="w-full bg-[#edf2f9] hover:bg-[#e4ebf6] px-6 py-3.5 border-b border-slate-200/80 flex items-center justify-between transition-colors text-left cursor-pointer select-none">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-slate-500 transition-transform duration-200" 
                             :class="openRab ? '' : '-rotate-90'" 
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                        <h3 class="text-xs font-bold text-[#094886] uppercase tracking-wider flex items-center gap-2">
                            <span>Detail RAB Permintaan</span>
                            <span class="text-[10px] font-semibold text-slate-500 lowercase font-normal">(klik untuk buka / tutup)</span>
                        </h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-slate-700 bg-white px-3 py-1 rounded-full border border-slate-200/80 shadow-2xs">
                            Total RAB: Rp {{ number_format($totalRabAwal, 0, ',', '.') }}
                        </span>
                    </div>
                </button>

                <!-- Collapsible Body -->
                <div x-show="openRab" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#edf2f9]/70 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-6">URAIAN ASET / PEKERJAAN</th>
                                <th class="py-3 px-6 w-24 text-center">VOL</th>
                                <th class="py-3 px-6 w-32 text-center">SATUAN</th>
                                <th class="py-3 px-6 w-40 text-right">HARGA SATUAN (RP)</th>
                                <th class="py-3 px-6 w-48 text-right pr-8">JUMLAH (RP)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @forelse($realisasi['asetList'] as $index => $aset)
                                @php 
                                    $subtotalRab = collect($aset['rabItems'] ?? [])->sum(function($item) { 
                                        return ($item['vol'] ?? 0) * ($item['hargaSatuan'] ?? 0); 
                                    }); 
                                @endphp
                                
                                <!-- Aset Header -->
                                <tr class="hover:bg-blue-50/40 transition-colors cursor-pointer bg-slate-50/50" 
                                    @click="toggleAset({{ $index }})">
                                    <td class="py-3 px-6 font-bold text-[#094886]">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" 
                                                 :class="expandedRABs[{{ $index }}] ? 'rotate-90' : ''" 
                                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="9 18 15 12 9 6"></polyline>
                                            </svg>
                                            <span>{{ $aset['nama'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-6 text-center"></td>
                                    <td class="py-3 px-6 text-center"></td>
                                    <td class="py-3 px-6 text-right"></td>
                                    <td class="py-3 px-6 text-right font-bold text-[#094886] pr-8">{{ number_format($subtotalRab, 0, ',', '.') }}</td>
                                </tr>

                                <!-- Uraian Items -->
                                @forelse($aset['rabItems'] ?? [] as $rab)
                                <tr x-show="expandedRABs[{{ $index }}]" class="hover:bg-slate-50/40 transition-colors">
                                    <td class="py-2.5 px-6 pl-12 text-sm font-medium text-slate-700">{{ $rab['uraian'] }}</td>
                                    <td class="py-2.5 px-6 text-center text-sm text-slate-600">{{ $rab['vol'] }}</td>
                                    <td class="py-2.5 px-6 text-center text-sm text-slate-600">{{ $rab['satuan'] }}</td>
                                    <td class="py-2.5 px-6 text-right text-sm text-slate-600">{{ number_format($rab['hargaSatuan'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-6 text-right text-sm font-medium text-slate-800 pr-8">{{ number_format((($rab['vol'] ?? 0) * ($rab['hargaSatuan'] ?? 0)), 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr x-show="expandedRABs[{{ $index }}]">
                                    <td colspan="5" class="py-3 px-6 pl-12 text-sm text-slate-400 italic">Tidak ada rincian RAB untuk aset ini.</td>
                                </tr>
                                @endforelse
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-sm text-slate-400 italic">Tidak ada data RAB permintaan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200/80 bg-white">
                                <td colspan="4" class="py-3.5 px-6 text-right font-bold text-slate-800 text-xs uppercase tracking-wider">
                                    TOTAL RAB KESELURUHAN
                                </td>
                                <td class="py-3.5 px-6 text-right font-bold text-slate-900 text-base pr-8">
                                    {{ number_format($totalRabAwal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Dynamic Realization Asset Cards (Khusus Maintenance: Biarkan Tetap Begitu) -->
            <div class="space-y-6 mb-6">
                <template x-for="(aset, asetIndex) in asetList" :key="aset.id">
                    <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden transition-all" x-data="{ openAset: true }">
                        <!-- Asset Header -->
                        <button type="button" @click="openAset = !openAset" class="w-full bg-[#edf2f9] hover:bg-[#e4ebf6] px-6 py-4 border-b border-slate-200/80 flex items-center justify-between transition-colors text-left cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-white text-slate-600 flex items-center justify-center shadow-2xs transition-transform duration-200" :class="{ 'rotate-180': openAset }">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900" x-text="'Aset ' + (asetIndex + 1) + ': ' + aset.nama"></h3>
                            </div>
                            <span class="text-xs font-semibold text-[#0c4a82]" x-text="'Subtotal: ' + formatRupiah(hitungSubtotalAset(aset))"></span>
                        </button>

                        <div class="p-6 space-y-6" x-show="openAset" style="display: none;" x-transition>
                            <!-- Detail Biaya Realisasi Table -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-xs font-bold text-[#094886] uppercase tracking-wider">Detail Biaya Realisasi</h4>
                                    <button type="button" @click="copyFromRAB(asetIndex)" x-show="aset.rabItems && aset.rabItems.length > 0" class="text-xs font-semibold text-[#094886] bg-blue-50 border border-blue-200 px-3 py-1 rounded-md hover:bg-blue-100 transition-colors">
                                        <svg class="w-3 h-3 inline-block mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                        Copy dari RAB Permintaan
                                    </button>
                                </div>
                                <div class="border border-slate-200/90 rounded-lg overflow-x-auto shadow-2xs">
                                    <table class="w-full min-w-[580px] text-left border-collapse">
                                        <thead>
                                            <tr class="bg-[#edf2f9]/70 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                                <th class="py-3 px-4">URAIAN PEKERJAAN / SPAREPART</th>
                                                <th class="py-3 px-4 w-20 text-center">VOL</th>
                                                <th class="py-3 px-4 w-36 text-center">SATUAN</th>
                                                <th class="py-3 px-4 w-36 text-right">HARGA SATUAN (RP)</th>
                                                <th class="py-3 px-4 w-36 text-right">JUMLAH (RP)</th>
                                                <th class="py-3 px-4 w-14 text-center">ACT</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                            <template x-for="(item, itemIndex) in aset.items" :key="item.id || itemIndex">
                                                <tr class="hover:bg-blue-50/30 transition-colors">
                                                    <!-- Uraian -->
                                                    <td class="py-2.5 px-4">
                                                        <input type="text" x-model="item.uraian" placeholder="Nama sparepart/jasa..."
                                                               class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-md px-3 py-1.5 text-xs text-slate-800 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                                    </td>

                                                    <!-- Vol -->
                                                    <td class="py-2.5 px-4 text-center">
                                                        <input type="number" min="1" x-model.number="item.vol"
                                                               class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-md px-2 py-1.5 text-center text-xs text-slate-800 focus:ring-1 focus:ring-blue-500 focus:outline-none font-medium">
                                                    </td>

                                                    <!-- Satuan -->
                                                    <td class="py-2.5 px-4">
                                                        <div class="relative">
                                                            <select x-model="item.satuan" 
                                                                    class="w-full appearance-none bg-white border border-slate-300 hover:border-slate-400 rounded-md px-2.5 py-1.5 pr-6 text-xs text-slate-700 focus:ring-1 focus:ring-blue-500 focus:outline-none cursor-pointer">
                                                                <option value="buah">buah</option>
                                                                <option value="unit">unit</option>
                                                                <option value="set">set</option>
                                                                <option value="pcs">pcs</option>
                                                                <option value="m">m (meter)</option>
                                                                <option value="m2">m²</option>
                                                                <option value="m3">m³</option>
                                                                <option value="cm">cm</option>
                                                                <option value="mm">mm</option>
                                                                <option value="kg">kg</option>
                                                                <option value="g">g</option>
                                                                <option value="ton">ton</option>
                                                                <option value="liter">liter</option>
                                                                <option value="ml">ml</option>
                                                                <option value="hari">hari</option>
                                                                <option value="jam">jam</option>
                                                                <option value="menit">menit</option>
                                                                <option value="paket">paket</option>
                                                                <option value="roll">roll</option>
                                                                <option value="rim">rim</option>
                                                                <option value="kaleng">kaleng</option>
                                                                <option value="kantong">kantong</option>
                                                                <option value="karung">karung</option>
                                                                <option value="kotak">kotak</option>
                                                                <option value="lembar">lembar</option>
                                                                <option value="lusin">lusin</option>
                                                                <option value="ls">ls (lumpsum)</option>
                                                                <option :value="item.satuan" x-text="item.satuan" x-show="item.satuan && !['buah','unit','set','pcs','m','m2','m3','cm','mm','kg','g','ton','liter','ml','hari','jam','menit','paket','roll','rim','kaleng','kantong','karung','kotak','lembar','lusin','ls'].includes(item.satuan)"></option>
                                                            </select>
                                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <!-- Harga Satuan -->
                                                    <td class="py-2.5 px-4 text-right">
                                                        <input type="number" min="0" step="any" x-model.number="item.hargaSatuan"
                                                               class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-md px-2.5 py-1.5 text-right text-xs text-slate-800 focus:ring-1 focus:ring-blue-500 focus:outline-none font-medium">
                                                    </td>

                                                    <!-- Jumlah (Rp) -->
                                                    <td class="py-2.5 px-4 text-right font-medium text-slate-900 text-xs">
                                                        <span x-text="formatRupiah((item.vol || 0) * (item.hargaSatuan || 0))"></span>
                                                    </td>

                                                    <!-- Act (Delete Icon) -->
                                                    <td class="py-2.5 px-4 text-center">
                                                        <button type="button" @click="hapusItem(asetIndex, itemIndex)" 
                                                                class="p-1 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer" title="Hapus Baris">
                                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr x-show="aset.items.length === 0">
                                                <td colspan="6" class="py-4 px-4 text-center text-sm text-slate-500">
                                                    Belum ada detail biaya. Silakan input manual atau copy dari RAB permintaan.
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="border-t border-slate-200/80 bg-white">
                                                <td colspan="4" class="py-3 px-4 text-right font-bold text-slate-700 text-xs">
                                                    <span x-text="'Total Realisasi Aset ' + (asetIndex + 1)"></span>
                                                </td>
                                                <td colspan="2" class="py-3 px-4 text-right font-bold text-[#0c4a82] text-sm pr-10">
                                                    <span x-text="formatRupiah(hitungSubtotalAset(aset))"></span>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- + Tambah Baris -->
                                <div class="mt-2.5">
                                    <button type="button" @click="tambahBaris(asetIndex)" 
                                            class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors cursor-pointer">
                                        + Tambah Baris
                                    </button>
                                </div>
                            </div>

                            <!-- Dokumentasi Upload Box (Multi-Image Interactive Accumulator) -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs font-bold text-[#094886] uppercase tracking-wider">Dokumentasi Foto</h4>
                                        <span class="text-[10px] text-slate-400 font-semibold bg-slate-100 px-2 py-0.5 rounded-full">Opsional</span>
                                    </div>
                                    <span x-show="aset.photosCount > 0" class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200" x-text="aset.photosCount + ' foto dipilih'"></span>
                                </div>
                                
                                <!-- Interactive Dropzone -->
                                <div class="border-2 border-dashed border-slate-300 hover:border-blue-500 bg-slate-50/70 hover:bg-blue-50/30 rounded-xl p-6 text-center transition-all group relative cursor-pointer">
                                    <input type="file" 
                                           multiple="multiple" 
                                           :id="'foto_input_' + (aset.id || asetIndex)"
                                           :name="'foto_aset_' + (aset.id || asetIndex) + '[]'" 
                                           accept="image/*" 
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                           @change="handleFotoChange($event, aset)">
                                    
                                    <div class="flex flex-col items-center justify-center pointer-events-none">
                                        <div class="w-10 h-10 rounded-full bg-blue-50 text-[#094886] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                                <circle cx="12" cy="13" r="4"></circle>
                                            </svg>
                                        </div>
                                        <span class="text-xs font-bold text-slate-800">Klik atau Tarik Foto ke Sini</span>
                                        <span class="text-[11px] text-slate-500 mt-0.5">Mendukung format JPG, PNG</span>
                                        <span class="text-[10px] text-blue-600 font-medium mt-1">Bisa pilih beberapa foto sekaligus (Ctrl + klik) atau tambah satu per satu</span>
                                    </div>
                                </div>

                                <!-- Multi-Image Thumbnail Previews with individual remove button -->
                                <div x-show="aset.photoPreviews && aset.photoPreviews.length > 0" class="mt-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-xs font-bold text-slate-700" x-text="'Foto Terpilih (' + aset.photoPreviews.length + '):'"></span>
                                        <button type="button" 
                                                @click="clearAllPhotos(aset)"
                                                class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition-colors cursor-pointer">
                                            Hapus Semua Foto
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                                        <template x-for="(photo, pIdx) in aset.photoPreviews" :key="pIdx">
                                            <div class="relative group rounded-lg overflow-hidden border border-slate-200 shadow-2xs bg-white aspect-[4/3]">
                                                <img :src="photo.url" class="w-full h-full object-cover">
                                                <!-- Hover Overlay with Remove Button -->
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">
                                                    <div class="flex justify-end">
                                                        <button type="button" 
                                                                @click="removeFoto(aset, pIdx)"
                                                                class="w-6 h-6 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center shadow-xs cursor-pointer transition-colors"
                                                                title="Hapus foto ini">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <span class="text-[10px] text-white truncate font-medium drop-shadow-xs" x-text="photo.name"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        @endif

        <!-- Card 5: Total Biaya Realisasi Bar -->
        <div class="bg-[#dce9fa] border border-blue-200/80 rounded-xl p-5 shadow-xs flex items-center justify-between mb-8">
            <span class="text-base sm:text-lg font-bold text-slate-900">Total Biaya Realisasi</span>
            <span class="text-xl sm:text-2xl font-extrabold text-[#0c3968]" x-text="'Rp ' + formatRupiah(totalBiayaRealisasi)"></span>
        </div>

        <!-- Footer Actions -->
        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 pt-4 border-t border-slate-200/80">
            <!-- Batal -->
            <a href="{{ route('realisasi.index') }}" 
               class="px-6 py-2.5 bg-white border border-[#094886] text-[#094886] hover:bg-blue-50/80 text-sm font-bold rounded-lg shadow-xs transition-colors text-center">
                Batal
            </a>

            <!-- Selesai Realisasi -->
            <button type="button" 
                    @click="openConfirmModal()"
                    class="px-7 py-2.5 bg-[#094886] hover:bg-[#073868] text-white text-sm font-bold rounded-lg shadow-sm transition-all duration-150 cursor-pointer text-center">
                Selesai Realisasi
            </button>
        </div>

        <!-- Modal Konfirmasi Selesai Realisasi -->
        <div x-show="confirmModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 transition-all"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-5 transition-all"
                 @click.away="if (!isSubmitting) confirmModalOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="text-center">
                    <!-- Icon Header -->
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl mx-auto flex items-center justify-center shadow-xs border border-emerald-100 mb-3.5">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900">Konfirmasi Selesai Realisasi</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Pastikan seluruh rincian barang/pengerjaan, rincian biaya realisasi, dan dokumen pendukung telah lengkap sebelum menyelesaikan realisasi.
                    </p>
                </div>

                <!-- Ringkasan Info Card -->
                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-2.5 text-xs">
                    <template x-if="jenis === 'Supply'">
                        <div class="space-y-2 pb-2.5 border-b border-slate-200/80">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-medium">Nomor FPA:</span>
                                <span class="text-slate-800 font-semibold font-mono" x-text="no_fpa || '-'"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-medium">Toko / Rekanan:</span>
                                <span class="text-slate-800 font-semibold" x-text="vendor || '-'"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-medium">Periode Pengadaan:</span>
                                <span class="text-slate-800 font-semibold" x-text="(tanggal_mulai || '-') + ' s/d ' + (tanggal_selesai || '-')"></span>
                            </div>
                        </div>
                    </template>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium" x-text="jenis === 'Supply' ? 'Jumlah Barang:' : 'Jumlah Aset:'"></span>
                        <span class="text-slate-800 font-semibold" x-text="jenis === 'Supply' ? (supplyItems.length + ' Barang') : (asetList.length + ' Aset')"></span>
                    </div>
                    <div class="flex justify-between items-center pt-2.5 border-t border-slate-200/80">
                        <span class="text-slate-700 font-bold">Total Biaya Realisasi:</span>
                        <span class="text-emerald-700 font-extrabold text-sm" x-text="'Rp ' + formatRupiah(totalBiayaRealisasi)"></span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" 
                            @click="confirmModalOpen = false" 
                            :disabled="isSubmitting"
                            class="px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer disabled:opacity-50">
                        Periksa Kembali
                    </button>
                    <button type="button" 
                            @click="isSubmitting = true; document.getElementById('formRealisasiSelesai').submit();" 
                            :disabled="isSubmitting"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#094886] hover:bg-[#073868] text-white rounded-xl text-xs font-bold shadow-sm transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                        <template x-if="isSubmitting">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Ya, Selesaikan Realisasi'"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function inputRealisasiForm() {
        const jenis = '{{ $realisasi['jenis'] ?? 'Maintenance' }}';
        const serverAsets = @json($realisasi['asetList'] ?? []);
        const serverSupplyItems = @json($realisasi['supplyItems'] ?? []);
        const serverRabItems = @json($realisasi['rabItemsPermintaan'] ?? []);
        const rawSupplies = @json($supplies ?? []);

        const normalizeSatuan = (s) => {
            if (!s) return 'unit';
            const val = String(s).trim().toLowerCase();
            if (val === 'bh') return 'buah';
            if (val === 'ltr') return 'liter';
            return val;
        };

        @if(($realisasi['jenis'] ?? '') !== 'Supply')
        const defaultAsets = [
            {
                id: 1,
                nama: 'Laptop Dell Latitude 5420',
                photosCount: 0,
                photoPreviews: [],
                items: [
                    {
                        id: 1,
                        uraian: 'Penggantian Baterai Original',
                        vol: 1,
                        satuan: 'unit',
                        hargaSatuan: 1250000
                    },
                    {
                        id: 2,
                        uraian: 'Jasa Pemasangan',
                        vol: 1,
                        satuan: 'ls',
                        hargaSatuan: 150000
                    }
                ]
            },
            {
                id: 2,
                nama: 'Printer Epson L31110',
                photosCount: 0,
                photoPreviews: [],
                items: [
                    {
                        id: 3,
                        uraian: 'Penggantian Baterai Original',
                        vol: 1,
                        satuan: 'unit',
                        hargaSatuan: 1250000
                    },
                    {
                        id: 4,
                        uraian: 'Jasa Pemasangan',
                        vol: 1,
                        satuan: 'ls',
                        hargaSatuan: 150000
                    }
                ]
            }
        ];
        @else
        const defaultAsets = [];
        @endif

        let initialSupplyItems = [];
        if (serverSupplyItems && serverSupplyItems.length > 0) {
            initialSupplyItems = serverSupplyItems.map(item => ({
                id: item.id || Date.now() + Math.random(),
                uraian: item.uraian || '',
                vol: item.vol || 1,
                satuan: normalizeSatuan(item.satuan || 'buah'),
                hargaSatuan: item.hargaSatuan || 0,
                dropdownOpen: false
            }));
        } else if (serverRabItems && serverRabItems.length > 0) {
            initialSupplyItems = serverRabItems.map(rab => ({
                id: Date.now() + Math.random(),
                uraian: rab.uraian || '',
                vol: rab.vol || 1,
                satuan: normalizeSatuan(rab.satuan || 'buah'),
                hargaSatuan: rab.hargaSatuan || 0,
                dropdownOpen: false
            }));
        } else {
            initialSupplyItems = [
                {
                    id: Date.now(),
                    uraian: '',
                    vol: 1,
                    satuan: 'buah',
                    hargaSatuan: 0,
                    dropdownOpen: false
                }
            ];
        }

        const asetData = serverAsets && serverAsets.length > 0
            ? serverAsets.map(a => ({
                id: a.id,
                nama: a.nama || 'Aset',
                photosCount: (a.photos && a.photos.length) || 0,
                photoPreviews: (a.photos || []).map((url, idx) => ({
                    name: 'Foto ' + (idx + 1),
                    url: url
                })),
                rabItems: (a.rabItems || []).map(rab => ({
                    ...rab,
                    satuan: normalizeSatuan(rab.satuan)
                })),
                items: a.items && a.items.length > 0 ? a.items.map(item => ({
                    id: item.id || Date.now() + Math.random(),
                    uraian: item.uraian || '',
                    vol: item.vol || 1,
                    satuan: normalizeSatuan(item.satuan || 'unit'),
                    hargaSatuan: item.hargaSatuan || 0
                })) : []
            }))
            : (jenis === 'Supply' ? [] : defaultAsets);

        // Internal store for DataTransfer per asset and supply photos
        const dataTransfers = {};
        let supplyDataTransfer = new DataTransfer();

        return {
            jenis: jenis,
            confirmModalOpen: false,
            isSubmitting: false,
            no_fpa: @json($realisasi['no_fpa'] ?? ''),
            vendor: @json($realisasi['vendor'] ?? ''),
            tanggal_mulai: @json($realisasi['tanggal_mulai'] ?? ''),
            tanggal_selesai: '{{ $realisasi['tanggal_selesai'] ?? '' }}',
            kakUploaded: {{ !empty($realisasi['kak_file']) ? 'true' : 'false' }},
            kakFileName: '{{ !empty($realisasi['kak_file']) ? basename($realisasi['kak_file']) : '' }}',
            bastUploaded: {{ (!empty($realisasi['bast_file']) || !empty($realisasi['kuitansi_file'])) ? 'true' : 'false' }},
            bastFileName: '{{ (!empty($realisasi['bast_file']) ? basename($realisasi['bast_file']) : (!empty($realisasi['kuitansi_file']) ? basename($realisasi['kuitansi_file']) : '')) }}',
            asetList: asetData,
            supplyItems: initialSupplyItems,
            rabItemsPermintaan: serverRabItems || [],
            availableSupplies: rawSupplies || [],
            supplyPhotosCount: 0,
            supplyPhotoPreviews: [],

            // Filter supply master options
            getFilteredSupplies(query) {
                if (!query) return (this.availableSupplies || []).slice(0, 15);
                const q = query.toLowerCase().trim();
                return (this.availableSupplies || []).filter(s => 
                    (s.nama_barang || '').toLowerCase().includes(q) ||
                    (s.kategori || '').toLowerCase().includes(q)
                ).slice(0, 15);
            },

            selectSupplyOption(item, sug) {
                item.uraian = sug.nama_barang;
                if (sug.satuan) {
                    item.satuan = normalizeSatuan(sug.satuan);
                }
                if (sug.harga_estimasi && (!item.hargaSatuan || item.hargaSatuan === 0)) {
                    item.hargaSatuan = Number(sug.harga_estimasi);
                }
                item.dropdownOpen = false;
            },

            tambahSupplyItem() {
                this.supplyItems.push({
                    id: Date.now() + Math.random(),
                    uraian: '',
                    vol: 1,
                    satuan: 'buah',
                    hargaSatuan: 0,
                    dropdownOpen: false
                });
            },

            hapusSupplyItem(index) {
                if (this.supplyItems.length > 1) {
                    this.supplyItems.splice(index, 1);
                } else {
                    this.supplyItems[0].uraian = '';
                    this.supplyItems[0].vol = 1;
                    this.supplyItems[0].satuan = 'buah';
                    this.supplyItems[0].hargaSatuan = 0;
                }
            },

            copyFromRABSupply() {
                if (this.rabItemsPermintaan && this.rabItemsPermintaan.length > 0) {
                    this.supplyItems = this.rabItemsPermintaan.map(rab => ({
                        id: Date.now() + Math.random(),
                        uraian: rab.uraian,
                        vol: rab.vol,
                        satuan: normalizeSatuan(rab.satuan || 'buah'),
                        hargaSatuan: rab.hargaSatuan,
                        dropdownOpen: false
                    }));
                }
            },

            get totalBiayaSupply() {
                return (this.supplyItems || []).reduce((sum, item) => {
                    return sum + ((item.vol || 0) * (item.hargaSatuan || 0));
                }, 0);
            },

            // Supply Photo upload handling
            handleSupplyFotoChange(event) {
                const files = event.target.files;
                if (!files || files.length === 0) return;

                Array.from(files).forEach(file => {
                    supplyDataTransfer.items.add(file);
                });

                event.target.files = supplyDataTransfer.files;
                this.supplyPhotosCount = supplyDataTransfer.files.length;
                this.refreshSupplyPreviews(event.target);
            },

            removeSupplyFoto(index) {
                supplyDataTransfer.items.remove(index);
                const inputEl = document.getElementById('foto_supply_input');
                if (inputEl) {
                    inputEl.files = supplyDataTransfer.files;
                }
                this.supplyPhotosCount = supplyDataTransfer.files.length;
                this.refreshSupplyPreviews(inputEl);
            },

            clearAllSupplyPhotos() {
                supplyDataTransfer = new DataTransfer();
                const inputEl = document.getElementById('foto_supply_input');
                if (inputEl) {
                    inputEl.files = supplyDataTransfer.files;
                    inputEl.value = '';
                }
                this.supplyPhotoPreviews = [];
                this.supplyPhotosCount = 0;
            },

            refreshSupplyPreviews(inputEl) {
                const files = Array.from(supplyDataTransfer.files);
                if (files.length === 0) {
                    this.supplyPhotoPreviews = [];
                    this.supplyPhotosCount = 0;
                    return;
                }

                const newPreviews = [];
                let loadedCount = 0;

                files.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        newPreviews[index] = {
                            name: file.name,
                            url: e.target.result
                        };
                        loadedCount++;
                        if (loadedCount === files.length) {
                            this.supplyPhotoPreviews = [...newPreviews];
                        }
                    };
                    reader.readAsDataURL(file);
                });
            },

            // ==================== MAINTENANCE SPECIFIC HANDLERS ====================
            handleFotoChange(event, aset) {
                const files = event.target.files;
                if (!files || files.length === 0) return;

                const assetKey = aset.id || 0;
                if (!dataTransfers[assetKey]) {
                    dataTransfers[assetKey] = new DataTransfer();
                }

                Array.from(files).forEach(file => {
                    dataTransfers[assetKey].items.add(file);
                });

                event.target.files = dataTransfers[assetKey].files;
                aset.photosCount = dataTransfers[assetKey].files.length;
                this.refreshPreviews(aset, dataTransfers[assetKey], event.target);
            },

            removeFoto(aset, photoIndex) {
                const assetKey = aset.id || 0;
                const dt = dataTransfers[assetKey];
                const inputEl = document.getElementById('foto_input_' + (aset.id || 0));

                if (dt) {
                    dt.items.remove(photoIndex);
                    if (inputEl) {
                        inputEl.files = dt.files;
                    }
                    aset.photosCount = dt.files.length;
                    this.refreshPreviews(aset, dt, inputEl);
                } else {
                    aset.photoPreviews.splice(photoIndex, 1);
                    aset.photosCount = aset.photoPreviews.length;
                }
            },

            clearAllPhotos(aset) {
                const assetKey = aset.id || 0;
                dataTransfers[assetKey] = new DataTransfer();
                const inputEl = document.getElementById('foto_input_' + (aset.id || 0));
                if (inputEl) {
                    inputEl.files = dataTransfers[assetKey].files;
                    inputEl.value = '';
                }
                aset.photoPreviews = [];
                aset.photosCount = 0;
            },

            refreshPreviews(aset, dt, inputEl) {
                const files = Array.from(dt.files);
                if (files.length === 0) {
                    aset.photoPreviews = [];
                    aset.photosCount = 0;
                    return;
                }

                const newPreviews = [];
                let loadedCount = 0;

                files.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        newPreviews[index] = {
                            name: file.name,
                            url: e.target.result
                        };
                        loadedCount++;
                        if (loadedCount === files.length) {
                            aset.photoPreviews = [...newPreviews];
                        }
                    };
                    reader.readAsDataURL(file);
                });
            },

            copyFromRAB(asetIndex) {
                const aset = this.asetList[asetIndex];
                if (aset.rabItems && aset.rabItems.length > 0) {
                    aset.items = aset.rabItems.map(rab => ({
                        id: Date.now() + Math.random(),
                        uraian: rab.uraian,
                        vol: rab.vol,
                        satuan: normalizeSatuan(rab.satuan),
                        hargaSatuan: rab.hargaSatuan
                    }));
                }
            },

            tambahBaris(asetIndex) {
                this.asetList[asetIndex].items.push({
                    id: Date.now(),
                    uraian: '',
                    vol: 1,
                    satuan: 'unit',
                    hargaSatuan: 0
                });
            },

            hapusItem(asetIndex, itemIndex) {
                this.asetList[asetIndex].items.splice(itemIndex, 1);
            },

            hitungSubtotalAset(aset) {
                return (aset.items || []).reduce((sum, item) => {
                    return sum + ((item.vol || 0) * (item.hargaSatuan || 0));
                }, 0);
            },

            get totalBiayaRealisasi() {
                if (this.jenis === 'Supply') {
                    return this.totalBiayaSupply;
                }
                return this.asetList.reduce((sum, aset) => {
                    return sum + this.hitungSubtotalAset(aset);
                }, 0);
            },

            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number || 0);
            },

            validateRealisasi() {
                // Validasi data umum khusus Supply (3 input yang terbawa dari modal)
                if (this.jenis === 'Supply') {
                    if (!this.no_fpa || !this.no_fpa.trim()) {
                        window.showToast({
                            type: 'warning',
                            title: 'Nomor FPA Belum Diisi',
                            message: 'Nomor dokumen FPA / Pengadaan wajib diisi.'
                        });
                        const el = document.querySelector('input[name="no_fpa"]');
                        if (el) el.focus();
                        return false;
                    }
                    if (!this.vendor || !this.vendor.trim()) {
                        window.showToast({
                            type: 'warning',
                            title: 'Toko / Penyedia Belum Diisi',
                            message: 'Nama toko atau rekanan penyedia barang wajib diisi.'
                        });
                        const el = document.querySelector('input[name="vendor"]');
                        if (el) el.focus();
                        return false;
                    }
                    if (!this.tanggal_mulai) {
                        window.showToast({
                            type: 'warning',
                            title: 'Tanggal Pembelian Belum Diisi',
                            message: 'Tanggal pembelian / pemesanan barang wajib diisi.'
                        });
                        const el = document.querySelector('input[name="tanggal_mulai"]');
                        if (el) el.focus();
                        return false;
                    }
                }

                // 1. Validasi Tanggal Selesai
                if (!this.tanggal_selesai) {
                    window.showToast({
                        type: 'warning',
                        title: 'Tanggal Selesai Belum Diisi',
                        message: 'Mohon isi tanggal selesai pengerjaan terlebih dahulu.'
                    });
                    const tglInput = document.querySelector('input[name="tanggal_selesai"]');
                    if (tglInput) tglInput.focus();
                    return false;
                }

                if (this.tanggal_mulai && this.tanggal_selesai && this.tanggal_selesai < this.tanggal_mulai) {
                    window.showToast({
                        type: 'warning',
                        title: 'Tanggal Tidak Valid',
                        message: 'Tanggal selesai pengerjaan tidak boleh sebelum tanggal mulai pengerjaan.'
                    });
                    return false;
                }

                // 2. Validasi Dokumen KAK
                if (!this.kakUploaded) {
                    window.showToast({
                        type: 'warning',
                        title: 'Dokumen KAK Belum Diunggah',
                        message: 'Dokumen Kerangka Acuan Kerja (KAK) format PDF wajib diunggah.'
                    });
                    return false;
                }

                // 3. Validasi Dokumen Realisasi (BAST untuk Supply, Kuitansi untuk Maintenance)
                if (!this.bastUploaded) {
                    const isSupply = this.jenis === 'Supply';
                    window.showToast({
                        type: 'warning',
                        title: isSupply ? 'Dokumen BAST Belum Diunggah' : 'Dokumen Kuitansi Belum Diunggah',
                        message: isSupply 
                            ? 'Dokumen BAST (Berita Acara Serah Terima) bertanda tangan fisik wajib diunggah.'
                            : 'Dokumen Kuitansi / bukti pembayaran wajib diunggah.'
                    });
                    return false;
                }

                // 4. Validasi Items
                if (this.jenis === 'Supply') {
                    if (!this.supplyItems || this.supplyItems.length === 0) {
                        window.showToast({
                            type: 'warning',
                            title: 'Daftar Barang Kosong',
                            message: 'Rincian barang persediaan wajib diisi minimal 1 item.'
                        });
                        return false;
                    }

                    for (let i = 0; i < this.supplyItems.length; i++) {
                        const item = this.supplyItems[i];
                        const num = i + 1;

                        if (!item.uraian || !item.uraian.trim()) {
                            window.showToast({
                                type: 'warning',
                                title: 'Nama Barang Kosong',
                                message: 'Nama barang persediaan pada baris ke-' + num + ' wajib diisi.'
                            });
                            return false;
                        }

                        if (item.vol === undefined || item.vol === null || item.vol === '' || Number(item.vol) <= 0) {
                            window.showToast({
                                type: 'warning',
                                title: 'Volume Tidak Valid',
                                message: 'Volume barang pada baris ke-' + num + ' harus berupa angka lebih dari 0.'
                            });
                            return false;
                        }

                        if (!item.satuan || !item.satuan.trim()) {
                            window.showToast({
                                type: 'warning',
                                title: 'Satuan Belum Diisi',
                                message: 'Satuan barang pada baris ke-' + num + ' wajib diisi.'
                            });
                            return false;
                        }

                        if (item.hargaSatuan === undefined || item.hargaSatuan === null || item.hargaSatuan === '' || Number(item.hargaSatuan) <= 0) {
                            window.showToast({
                                type: 'warning',
                                title: 'Harga Satuan Kosong',
                                message: 'Harga satuan pada baris ke-' + num + ' belum diisi atau masih 0.'
                            });
                            return false;
                        }
                    }
                    return true;
                }

                // Validasi Maintenance per Aset
                if (!this.asetList || this.asetList.length === 0) {
                    window.showToast({
                        type: 'warning',
                        title: 'Daftar Aset Kosong',
                        message: 'Tidak ditemukan aset pada realisasi ini.'
                    });
                    return false;
                }

                for (let i = 0; i < this.asetList.length; i++) {
                    const aset = this.asetList[i];
                    const numAset = i + 1;
                    const namaAset = aset.nama || ('Aset ' + numAset);

                    if (!aset.items || aset.items.length === 0) {
                        window.showToast({
                            type: 'warning',
                            title: 'Rincian Realisasi Kosong',
                            message: 'Rincian hasil pengerjaan pada ' + namaAset + ' belum diisi. Tambahkan item atau klik tombol "Salin dari RAB".'
                        });
                        return false;
                    }

                    for (let j = 0; j < aset.items.length; j++) {
                        const item = aset.items[j];
                        const numItem = j + 1;

                        if (!item.uraian || !item.uraian.trim()) {
                            window.showToast({
                                type: 'warning',
                                title: 'Uraian Pengerjaan Kosong',
                                message: 'Uraian pengerjaan pada baris ke-' + numItem + ' (' + namaAset + ') belum diisi.'
                            });
                            return false;
                        }

                        if (item.vol === undefined || item.vol === null || item.vol === '' || Number(item.vol) <= 0) {
                            window.showToast({
                                type: 'warning',
                                title: 'Volume Tidak Valid',
                                message: 'Volume pengerjaan pada baris ke-' + numItem + ' (' + namaAset + ') harus berupa angka lebih dari 0.'
                            });
                            return false;
                        }

                        if (!item.satuan || !item.satuan.trim()) {
                            window.showToast({
                                type: 'warning',
                                title: 'Satuan Belum Diisi',
                                message: 'Satuan pengerjaan pada baris ke-' + numItem + ' (' + namaAset + ') wajib diisi.'
                            });
                            return false;
                        }

                        if (item.hargaSatuan === undefined || item.hargaSatuan === null || item.hargaSatuan === '' || Number(item.hargaSatuan) <= 0) {
                            window.showToast({
                                type: 'warning',
                                title: 'Harga Satuan Kosong',
                                message: 'Harga satuan pada baris ke-' + numItem + ' (' + namaAset + ') belum diisi atau masih 0.'
                            });
                            return false;
                        }
                    }
                }

                return true;
            },

            openConfirmModal() {
                if (this.isSubmitting) return;
                if (!this.validateRealisasi()) {
                    return;
                }
                this.confirmModalOpen = true;
            }
        }
    }
</script>
@endsection
