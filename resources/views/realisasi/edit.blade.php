@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-20" x-data="editRealisasiForm()">
    <!-- Header Title -->
    <div>
        <div class="flex items-center gap-2.5">
            <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 tracking-tight">
                {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'Edit Realisasi Supply (Persediaan)' : 'Edit Realisasi Perbaikan' }}
            </h1>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-[#094886] border border-blue-200' }}">
                {{ $realisasi['jenis'] ?? 'Maintenance' }}
            </span>
        </div>
        <p class="text-sm text-slate-500 mt-1">
            {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'Ubah rincian pengadaan barang persediaan dan anggaran yang telah diselesaikan.' : 'Ubah rincian pekerjaan dan anggaran yang telah diselesaikan.' }}
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

    <form id="formRealisasiEdit" action="{{ route('realisasi.update', $realisasi['id'] ?? 1) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @if(($realisasi['jenis'] ?? '') === 'Supply')
            <!-- Hidden inputs untuk Supply -->
            <input type="hidden" name="supplyItems" :value="JSON.stringify(supplyItems)">
            <input type="hidden" name="asetList" :value="JSON.stringify([{ id: (asetList[0] && asetList[0].id) || null, nama: 'Barang Persediaan', items: supplyItems }])">
            <template x-for="(photo, pIdx) in existingSupplyPhotos" :key="'ex-input-' + pIdx">
                <input type="hidden" name="existing_photos[]" :value="photo.path">
            </template>
        @else
            <!-- Hidden input untuk Maintenance -->
            <input type="hidden" name="asetList" :value="JSON.stringify(asetList)">
        @endif

        <!-- Card 1: Informasi Umum Realisasi -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-6 shadow-xs space-y-5 mb-6">
            <h2 class="text-base font-bold text-[#094886]">Informasi Umum Realisasi</h2>

            <!-- Pekerjaan / Kegiatan (Editable) -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-lg p-4">
                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pekerjaan/Kegiatan</span>
                <input type="text" name="pekerjaan" x-model="pekerjaan"
                       class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-md px-3 py-2 text-base font-bold text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none" required>
            </div>

            <!-- Grid: Data Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- No. Dokumen FPA (Editable) -->
                <div class="bg-blue-50/50 border border-blue-200 rounded-lg p-3.5">
                    <label class="block text-[10px] font-bold text-blue-900 uppercase tracking-wider mb-1">
                        {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'No. Dokumen FPA / Pengadaan' : 'No. Dokumen FPA' }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="no_fpa" x-model="noFpa" required placeholder="FPA/BPS/..."
                           class="w-full bg-white border border-blue-300 hover:border-blue-400 rounded-md px-2.5 py-1.5 text-sm text-slate-800 font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none">
                </div>

                <!-- Vendor Pelaksana / Toko Penyedia (Editable) -->
                <div class="bg-blue-50/50 border border-blue-200 rounded-lg p-3.5">
                    <label class="block text-[10px] font-bold text-blue-900 uppercase tracking-wider mb-1">
                        {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'Toko / Penyedia Barang' : 'Vendor Pelaksana' }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="vendor" x-model="vendor" required placeholder="{{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'Contoh: Toko ATK Sejahtera / CV Sumber Kertas' : 'Nama vendor pelaksana' }}"
                           class="w-full bg-white border border-blue-300 hover:border-blue-400 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none font-medium">
                </div>

                <!-- Mulai Pengerjaan / Tanggal Pembelian (Editable) -->
                <div class="bg-blue-50/50 border border-blue-200 rounded-lg p-3.5">
                    <label class="block text-[10px] font-bold text-blue-900 uppercase tracking-wider mb-1">
                        {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'Tanggal Pembelian / Pemesanan' : 'Mulai Pengerjaan' }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_mulai" x-model="tanggalMulai" required
                           class="w-full bg-white border border-blue-300 hover:border-blue-400 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none font-medium">
                </div>

                <!-- Selesai Pengerjaan / Tanggal Penerimaan (Editable) -->
                <div class="bg-blue-50/50 border border-blue-200 rounded-lg p-3.5">
                    <label class="block text-[10px] font-bold text-blue-900 uppercase tracking-wider mb-1">
                        {{ ($realisasi['jenis'] ?? '') === 'Supply' ? 'Tanggal Penerimaan Barang' : 'Selesai Pengerjaan' }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_selesai" x-model="tanggalSelesai" :min="tanggalMulai" required
                           class="w-full bg-white border border-blue-300 hover:border-blue-400 rounded-md px-2.5 py-1.5 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none font-medium">
                </div>
            </div>

            <!-- Upload Dokumen Realisasi -->
            <div>
                <span class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5">Dokumen Realisasi</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Upload KAK -->
                    <div class="border-2 border-dashed border-slate-200 hover:border-blue-400 bg-slate-50/50 hover:bg-blue-50/30 rounded-xl p-6 flex flex-col items-center justify-center transition-all group">
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-[#094886] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-slate-800 tracking-wider">KAK</span>
                        @if(!empty($realisasi['kak_file']))
                            <a href="{{ $realisasi['kak_file'] }}" target="_blank" class="text-[11px] text-blue-600 hover:underline font-semibold mt-0.5">Lihat File Saat Ini ↗</a>
                        @else
                            <span class="text-[11px] text-slate-400 mt-0.5">PDF (Max 10MB)</span>
                        @endif
                        <label class="mt-2 px-3 py-1 text-xs font-semibold text-blue-600 bg-white border border-slate-200 hover:border-blue-300 rounded-lg cursor-pointer shadow-2xs transition-all">
                            Pilih / Ganti File
                            <input type="file" name="kak" accept=".pdf" class="hidden" @change="kakChanged = true">
                        </label>
                        <span x-show="kakChanged" class="text-xs text-emerald-600 font-semibold mt-2">✓ File KAK baru terpilih</span>
                    </div>

                    @if(($realisasi['jenis'] ?? '') === 'Supply')
                    <!-- Upload BAST (Khusus Supply) -->
                    <div class="border-2 border-dashed border-slate-200 hover:border-blue-400 bg-slate-50/50 hover:bg-blue-50/30 rounded-xl p-6 flex flex-col items-center justify-center transition-all group">
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-[#094886] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-slate-800 tracking-wider">BAST</span>
                        @if(!empty($realisasi['bast_file']) || !empty($realisasi['kuitansi_file']))
                            <a href="{{ $realisasi['bast_file'] ?? $realisasi['kuitansi_file'] }}" target="_blank" class="text-[11px] text-blue-600 hover:underline font-semibold mt-0.5">Lihat File BAST Saat Ini ↗</a>
                        @else
                            <span class="text-[11px] text-slate-400 mt-0.5">PDF/JPG (Max 10MB)</span>
                        @endif
                        <label class="mt-2 px-3 py-1 text-xs font-semibold text-blue-600 bg-white border border-slate-200 hover:border-blue-300 rounded-lg cursor-pointer shadow-2xs transition-all">
                            Pilih / Ganti File
                            <input type="file" name="bast" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="bastChanged = true">
                        </label>
                        <span x-show="bastChanged" class="text-xs text-emerald-600 font-semibold mt-2">✓ File BAST baru terpilih</span>
                    </div>
                    @else
                    <!-- Upload Kuitansi (Khusus Maintenance) -->
                    <div class="border-2 border-dashed border-slate-200 hover:border-blue-400 bg-slate-50/50 hover:bg-blue-50/30 rounded-xl p-6 flex flex-col items-center justify-center transition-all group">
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-[#094886] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                <line x1="2" y1="10" x2="22" y2="10"></line>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-slate-800 tracking-wider">KUITANSI</span>
                        @if(!empty($realisasi['kuitansi_file']) || !empty($realisasi['bast_file']))
                            <a href="{{ $realisasi['kuitansi_file'] ?? $realisasi['bast_file'] }}" target="_blank" class="text-[11px] text-blue-600 hover:underline font-semibold mt-0.5">Lihat File Kuitansi Saat Ini ↗</a>
                        @else
                            <span class="text-[11px] text-slate-400 mt-0.5">PDF/JPG (Max 10MB)</span>
                        @endif
                        <label class="mt-2 px-3 py-1 text-xs font-semibold text-blue-600 bg-white border border-slate-200 hover:border-blue-300 rounded-lg cursor-pointer shadow-2xs transition-all">
                            Pilih / Ganti File
                            <input type="file" name="kuitansi" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="bastChanged = true">
                        </label>
                        <span x-show="bastChanged" class="text-xs text-emerald-600 font-semibold mt-2">✓ File Kuitansi baru terpilih</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if(($realisasi['jenis'] ?? '') === 'Supply')
            <!-- ========================================================================= -->
            <!-- KHUSUS SUPPLY: DETAIL RAB PERMINTAAN SUPPLY                               -->
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
                                <th class="py-3 px-6 w-12 text-center">NO</th>
                                <th class="py-3 px-6">URAIAN BARANG / PERSEDIAAN</th>
                                <th class="py-3 px-6 w-24 text-center">VOL</th>
                                <th class="py-3 px-6 w-32 text-center">SATUAN</th>
                                <th class="py-3 px-6 w-40 text-right">HARGA SATUAN (RP)</th>
                                <th class="py-3 px-6 w-48 text-right pr-8">JUMLAH (RP)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            @forelse($realisasi['rabItemsPermintaan'] ?? [] as $idx => $rab)
                                @php
                                    $subtotal = ($rab['vol'] ?? 1) * ($rab['hargaSatuan'] ?? 0);
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-2.5 px-6 text-center text-xs text-slate-500 font-semibold">{{ $idx + 1 }}</td>
                                    <td class="py-2.5 px-6 font-medium text-slate-800">{{ $rab['uraian'] }}</td>
                                    <td class="py-2.5 px-6 text-center text-slate-600">{{ $rab['vol'] }}</td>
                                    <td class="py-2.5 px-6 text-center text-slate-600">{{ $rab['satuan'] }}</td>
                                    <td class="py-2.5 px-6 text-right text-slate-600">{{ number_format($rab['hargaSatuan'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-6 text-right font-semibold text-slate-900 pr-8">{{ number_format($subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-sm text-slate-400 italic">Tidak ada data rincian RAB permintaan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200/80 bg-white">
                                <td colspan="5" class="py-3.5 px-6 text-right font-bold text-slate-800 text-xs uppercase tracking-wider">
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
            <!-- KHUSUS SUPPLY: DETAIL RAB REALISASI (BARANG PERSEDIAAN)                   -->
            <!-- ========================================================================= -->
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden mb-6">
                <!-- Header Card -->
                <div class="bg-[#edf2f9] px-6 py-4 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            Detail RAB Realisasi (Barang Persediaan)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Rincian Barang Persediaan Realisasi: Ubah rincian barang persediaan yang dibeli / direalisasikan beserta harga satuannya.</p>
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
                                    <th class="py-3 px-4 w-12 text-center">NO</th>
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
                                        <!-- No -->
                                        <td class="py-2.5 px-4 text-center text-xs text-slate-500 font-semibold" x-text="itemIndex + 1"></td>

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
                                                 class="absolute z-30 left-4 right-4 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto divide-y divide-slate-100"
                                                 style="display: none;">
                                                <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                                    <span>Master Persediaan Tersedia</span>
                                                    <span class="text-emerald-700" x-text="filteredSupplies(item.uraian).length + ' ditemukan'"></span>
                                                </div>
                                                <template x-for="sup in filteredSupplies(item.uraian)" :key="sup.id">
                                                    <div @click="selectSupply(item, sup)" 
                                                         class="px-3 py-2 hover:bg-emerald-50 cursor-pointer text-left transition-colors flex items-center justify-between">
                                                        <div>
                                                            <div class="text-xs font-bold text-slate-800" x-text="sup.nama_barang"></div>
                                                            <div class="text-[10px] text-slate-500">
                                                                Kode: <span class="font-mono text-slate-700" x-text="sup.kode_barang"></span> • Satuan: <span x-text="sup.satuan"></span>
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="text-xs font-bold text-emerald-700" x-text="'Rp ' + formatRupiah(sup.harga_estimasi)"></span>
                                                            <div class="text-[10px] text-slate-500" x-text="'Stok: ' + sup.stok"></div>
                                                        </div>
                                                    </div>
                                                </template>
                                                <div x-show="filteredSupplies(item.uraian).length === 0" class="px-3 py-2 text-xs text-slate-400 italic">
                                                    Tidak ditemukan di master persediaan. Anda tetap bisa mengetikkan nama barang baru secara manual.
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Vol -->
                                        <td class="py-2.5 px-4 text-center">
                                            <input type="number" min="1" x-model.number="item.vol" 
                                                   class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-md px-2 py-1.5 text-center text-xs text-slate-800 focus:ring-1 focus:ring-emerald-500 focus:outline-none font-medium" 
                                                   required>
                                        </td>

                                        <!-- Satuan -->
                                        <td class="py-2.5 px-4">
                                            <div class="relative">
                                                <select x-model="item.satuan" 
                                                        class="w-full appearance-none bg-white border border-slate-300 hover:border-slate-400 rounded-md px-2.5 py-1.5 pr-6 text-xs text-slate-700 focus:ring-1 focus:ring-emerald-500 focus:outline-none cursor-pointer"
                                                        required>
                                                    <option value="buah">buah</option>
                                                    <option value="rim">rim</option>
                                                    <option value="kotak">kotak</option>
                                                    <option value="pak">pak</option>
                                                    <option value="dus">dus</option>
                                                    <option value="lembar">lembar</option>
                                                    <option value="lusin">lusin</option>
                                                    <option value="pcs">pcs</option>
                                                    <option value="unit">unit</option>
                                                    <option value="set">set</option>
                                                    <option value="botol">botol</option>
                                                    <option value="roll">roll</option>
                                                    <option value="paket">paket</option>
                                                    <option value="m">m (meter)</option>
                                                    <option value="kg">kg</option>
                                                    <option value="liter">liter</option>
                                                    <option value="ls">ls (lumpsum)</option>
                                                    <option :value="item.satuan" x-text="item.satuan" x-show="item.satuan && !['buah','rim','kotak','pak','dus','lembar','lusin','pcs','unit','set','botol','roll','paket','m','kg','liter','ls'].includes(item.satuan)"></option>
                                                </select>
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Harga Satuan -->
                                        <td class="py-2.5 px-4 text-right">
                                            <input type="number" min="0" step="any" x-model.number="item.hargaSatuan" 
                                                   class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-md px-2.5 py-1.5 text-right text-xs text-slate-800 focus:ring-1 focus:ring-emerald-500 focus:outline-none font-medium" 
                                                   required>
                                        </td>

                                        <!-- Jumlah (Rp) -->
                                        <td class="py-2.5 px-4 text-right font-medium text-slate-900 text-xs">
                                            <span x-text="formatRupiah((item.vol || 0) * (item.hargaSatuan || 0))"></span>
                                        </td>

                                        <!-- Aksi (Hapus) -->
                                        <td class="py-2.5 px-4 text-center">
                                            <button type="button" @click="hapusItemSupply(itemIndex)" 
                                                    class="p-1 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer" title="Hapus Barang">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-200/80 bg-white">
                                    <td colspan="5" class="py-3 px-4 text-right font-bold text-slate-700 text-xs">
                                        Total RAB Realisasi (Persediaan):
                                    </td>
                                    <td colspan="2" class="py-3 px-4 text-right font-bold text-emerald-800 text-sm pr-10">
                                        <span x-text="formatRupiah(totalBiayaRealisasi)"></span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- + Tambah Barang Persediaan -->
                    <div class="mt-2.5 flex items-center justify-between">
                        <button type="button" @click="tambahBarisSupply()" 
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3.5 py-2 rounded-lg transition-colors cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>+ Tambah Barang Persediaan</span>
                        </button>
                    </div>

                    <!-- Dokumentasi Foto Persediaan -->
                    <div class="border-t border-slate-100 pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-xs font-bold text-[#094886] uppercase tracking-wider">Dokumentasi Foto Pengadaan Persediaan</h4>
                            <span x-show="(existingSupplyPhotos.length + newSupplyPhotosCount) > 0" class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200" x-text="(existingSupplyPhotos.length + newSupplyPhotosCount) + ' foto'"></span>
                        </div>

                        <!-- Existing Photos from Server -->
                        <div x-show="existingSupplyPhotos.length > 0" class="mb-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-xs font-bold text-slate-700" x-text="'Foto Tersimpan (' + existingSupplyPhotos.length + '):'"></span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                                <template x-for="(exPhoto, exIdx) in existingSupplyPhotos" :key="'ex-' + exIdx">
                                    <div class="relative group rounded-lg overflow-hidden border border-slate-200 shadow-2xs bg-white aspect-[4/3]">
                                        <img :src="exPhoto.url" class="w-full h-full object-cover">
                                        <!-- Hover Overlay with Remove Button -->
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">
                                            <div class="flex justify-end">
                                                <button type="button" 
                                                        @click="removeExistingSupplyPhoto(exIdx)"
                                                        class="w-6 h-6 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center shadow-xs cursor-pointer transition-colors"
                                                        title="Hapus foto ini">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                                    </svg>
                                                </button>
                                            </div>
                                            <span class="text-[10px] text-white truncate font-medium drop-shadow-xs">Tersimpan</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
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
                                <span class="text-xs font-bold text-slate-800">Klik atau Tarik Foto Bukti Fisik Barang ke Sini</span>
                                <span class="text-[11px] text-slate-500 mt-0.5">Mendukung format JPG, PNG</span>
                                <span class="text-[10px] text-emerald-700 font-medium mt-1">Bisa pilih beberapa foto sekaligus (Ctrl + klik) atau tambah satu per satu</span>
                            </div>
                        </div>

                        <!-- Multi-Image Previews -->
                        <div x-show="newSupplyPhotoPreviews.length > 0" class="mt-4 p-4 bg-emerald-50/50 border border-emerald-200 rounded-xl">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-emerald-800" x-text="'Foto Baru (' + newSupplyPhotoPreviews.length + '):'"></span>
                                <button type="button" 
                                        @click="clearAllNewSupplyPhotos()"
                                        class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition-colors cursor-pointer">
                                    Hapus Semua Foto Baru
                                </button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                                <template x-for="(photo, pIdx) in newSupplyPhotoPreviews" :key="'new-sup-' + pIdx">
                                    <div class="relative group rounded-lg overflow-hidden border border-emerald-200 shadow-2xs bg-white aspect-[4/3]">
                                        <img :src="photo.url" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">
                                            <div class="flex justify-end">
                                                <button type="button" 
                                                        @click="removeNewSupplyFoto(pIdx)"
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
            <!-- KHUSUS MAINTENANCE: TETAP PER ASET (JANGAN DIUBAH APAPUN)                  -->
            <!-- ========================================================================= -->
            
            <!-- Detail RAB Permintaan (Accordion Reference) -->
            @if(!empty($realisasi['rabItemsPermintaan']) || !empty($realisasi['asetList']))
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
                            <span>Detail RAB Permintaan Supply</span>
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
            @endif

            <!-- Dynamic Realization Asset Cards -->
            <div class="space-y-6 mb-6">
                <template x-for="(aset, asetIndex) in asetList" :key="aset.id">
                    <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden transition-all" x-data="{ openAset: false }">
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
                                <h4 class="text-xs font-bold text-[#094886] uppercase tracking-wider mb-3">Detail Biaya Realisasi</h4>
                                <div class="border border-slate-200/90 rounded-lg overflow-hidden">
                                    <table class="w-full text-left border-collapse">
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
                                            <template x-for="(item, itemIndex) in aset.items" :key="item.id">
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
                                    <h4 class="text-xs font-bold text-[#094886] uppercase tracking-wider">Dokumentasi Foto</h4>
                                    <span x-show="getTotalPhotoCount(aset) > 0" class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200" x-text="getTotalPhotoCount(aset) + ' foto'"></span>
                                </div>

                                <!-- Existing Photos from Server -->
                                <div x-show="aset.existingPhotos && aset.existingPhotos.length > 0" class="mb-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                                    <div class="flex items-center justify-between mb-2.5">
                                        <span class="text-xs font-bold text-slate-700" x-text="'Foto Tersimpan (' + aset.existingPhotos.length + '):'"></span>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                                        <template x-for="(exPhoto, exIdx) in aset.existingPhotos" :key="'ex-' + exIdx">
                                            <div class="relative group rounded-lg overflow-hidden border border-slate-200 shadow-2xs bg-white aspect-[4/3]">
                                                <img :src="exPhoto.url" class="w-full h-full object-cover">
                                                <!-- Hover Overlay with Remove Button -->
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">
                                                    <div class="flex justify-end">
                                                        <button type="button" 
                                                                @click="removeExistingPhoto(aset, exIdx)"
                                                                class="w-6 h-6 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center shadow-xs cursor-pointer transition-colors"
                                                                title="Hapus foto ini">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <span class="text-[10px] text-white truncate font-medium drop-shadow-xs">Tersimpan</span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
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
                                <div x-show="aset.newPhotoPreviews && aset.newPhotoPreviews.length > 0" class="mt-4 p-4 bg-emerald-50/50 border border-emerald-200 rounded-xl">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-xs font-bold text-emerald-800" x-text="'Foto Baru (' + aset.newPhotoPreviews.length + '):'"></span>
                                        <button type="button" 
                                                @click="clearAllNewPhotos(aset)"
                                                class="text-xs font-semibold text-rose-600 hover:text-rose-800 transition-colors cursor-pointer">
                                            Hapus Semua Foto Baru
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                                        <template x-for="(photo, pIdx) in aset.newPhotoPreviews" :key="'new-' + pIdx">
                                            <div class="relative group rounded-lg overflow-hidden border border-emerald-200 shadow-2xs bg-white aspect-[4/3]">
                                                <img :src="photo.url" class="w-full h-full object-cover">
                                                <!-- Hover Overlay with Remove Button -->
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">
                                                    <div class="flex justify-end">
                                                        <button type="button" 
                                                                @click="removeNewFoto(aset, pIdx)"
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
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200/80">
            <!-- Batal -->
            <a href="{{ route('realisasi.index') }}" 
               class="px-6 py-2.5 bg-white border border-[#094886] text-[#094886] hover:bg-blue-50/80 text-sm font-bold rounded-lg shadow-xs transition-colors">
                Batal
            </a>

            <!-- Simpan Perubahan -->
            <button type="submit" 
                    class="px-7 py-2.5 bg-[#094886] hover:bg-[#073868] text-white text-sm font-bold rounded-lg shadow-sm transition-all duration-150 cursor-pointer">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
    function editRealisasiForm() {
        const serverData = @json($realisasi ?? []);
        const jenis = serverData.jenis || 'Maintenance';
        const serverAsets = serverData.asetList || [];
        const availableSupplies = @json($supplies ?? []);

        const normalizeSatuan = (s) => {
            if (!s) return 'buah';
            const val = String(s).trim().toLowerCase();
            if (val === 'bh') return 'buah';
            if (val === 'ltr') return 'liter';
            return val;
        };

        const dataTransfers = {};
        const supplyDataTransfer = new DataTransfer();

        // 1. Data untuk Maintenance (asetList)
        const asetData = serverAsets.length > 0
            ? serverAsets.map(a => {
                const existing = (a.photos || []).map((p) => {
                    if (typeof p === 'object' && p !== null) {
                        return { path: p.path, url: p.url };
                    }
                    return { path: p, url: p };
                });

                return {
                    id: a.id,
                    nama: a.nama || 'Aset',
                    existingPhotos: existing,
                    existing_photos: existing.map(e => e.path),
                    newPhotoPreviews: [],
                    newPhotosCount: 0,
                    items: a.items && a.items.length > 0 ? a.items.map(item => ({
                        id: item.id || Date.now() + Math.random(),
                        uraian: item.uraian || '',
                        vol: item.vol || 1,
                        satuan: normalizeSatuan(item.satuan || 'unit'),
                        hargaSatuan: item.hargaSatuan || 0
                    })) : [
                        { id: Date.now(), uraian: '', vol: 1, satuan: 'unit', hargaSatuan: 0 }
                    ]
                };
            })
            : [];

        // 2. Data untuk Supply
        let initialSupplyItems = [];
        if (serverData.supplyItems && serverData.supplyItems.length > 0) {
            initialSupplyItems = serverData.supplyItems.map(it => ({
                id: it.id || Date.now() + Math.random(),
                uraian: it.uraian || '',
                vol: it.vol || 1,
                satuan: normalizeSatuan(it.satuan || 'buah'),
                hargaSatuan: it.hargaSatuan || 0,
                dropdownOpen: false
            }));
        } else if (asetData.length > 0 && asetData[0].items && asetData[0].items.length > 0) {
            initialSupplyItems = asetData[0].items.map(it => ({
                id: it.id || Date.now() + Math.random(),
                uraian: it.uraian || '',
                vol: it.vol || 1,
                satuan: normalizeSatuan(it.satuan || 'buah'),
                hargaSatuan: it.hargaSatuan || 0,
                dropdownOpen: false
            }));
        } else if (serverData.rabItemsPermintaan && serverData.rabItemsPermintaan.length > 0) {
            initialSupplyItems = serverData.rabItemsPermintaan.map(r => ({
                id: Date.now() + Math.random(),
                uraian: r.uraian || '',
                vol: r.vol || 1,
                satuan: normalizeSatuan(r.satuan || 'buah'),
                hargaSatuan: r.hargaSatuan || 0,
                dropdownOpen: false
            }));
        } else {
            initialSupplyItems = [{
                id: Date.now(),
                uraian: '',
                vol: 1,
                satuan: 'buah',
                hargaSatuan: 0,
                dropdownOpen: false
            }];
        }

        // Supply existing photos
        let initialSupplyPhotos = [];
        if (serverData.supplyPhotos && serverData.supplyPhotos.length > 0) {
            initialSupplyPhotos = serverData.supplyPhotos.map(p => {
                if (typeof p === 'object' && p !== null) return { path: p.path, url: p.url };
                return { path: p, url: p };
            });
        } else if (asetData.length > 0 && asetData[0].existingPhotos) {
            initialSupplyPhotos = [...asetData[0].existingPhotos];
        }

        return {
            jenis: jenis,
            noFpa: serverData.no_fpa || '',
            pekerjaan: serverData.pekerjaan || '',
            vendor: serverData.vendor || '',
            tanggalMulai: serverData.tanggal_mulai || '',
            tanggalSelesai: serverData.tanggal_selesai || '',
            kakChanged: false,
            bastChanged: false,
            kuitansiChanged: false,
            rabItemsPermintaan: serverData.rabItemsPermintaan || [],
            asetList: asetData,

            // State Khusus Supply
            availableSupplies: availableSupplies,
            supplyItems: initialSupplyItems,
            existingSupplyPhotos: initialSupplyPhotos,
            newSupplyPhotoPreviews: [],
            newSupplyPhotosCount: 0,

            // === FUNGSI SUPPLY ===
            filteredSupplies(query) {
                if (!query || query.trim() === '') return this.availableSupplies.slice(0, 15);
                const q = query.toLowerCase();
                return this.availableSupplies.filter(s => 
                    (s.nama_barang && s.nama_barang.toLowerCase().includes(q)) ||
                    (s.kode_barang && s.kode_barang.toLowerCase().includes(q))
                ).slice(0, 15);
            },

            selectSupply(item, sup) {
                item.uraian = sup.nama_barang;
                if (sup.satuan) item.satuan = normalizeSatuan(sup.satuan);
                if (sup.harga_estimasi && (!item.hargaSatuan || item.hargaSatuan === 0)) {
                    item.hargaSatuan = parseFloat(sup.harga_estimasi);
                }
                item.dropdownOpen = false;
            },

            tambahBarisSupply() {
                this.supplyItems.push({
                    id: Date.now() + Math.random(),
                    uraian: '',
                    vol: 1,
                    satuan: 'buah',
                    hargaSatuan: 0,
                    dropdownOpen: false
                });
            },

            hapusItemSupply(index) {
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
                if (!this.rabItemsPermintaan || this.rabItemsPermintaan.length === 0) return;
                this.supplyItems = this.rabItemsPermintaan.map(r => ({
                    id: Date.now() + Math.random(),
                    uraian: r.uraian || '',
                    vol: r.vol || 1,
                    satuan: normalizeSatuan(r.satuan || 'buah'),
                    hargaSatuan: r.hargaSatuan || 0,
                    dropdownOpen: false
                }));
            },

            removeExistingSupplyPhoto(index) {
                this.existingSupplyPhotos.splice(index, 1);
            },

            handleSupplyFotoChange(event) {
                const files = event.target.files;
                if (!files || files.length === 0) return;

                Array.from(files).forEach(file => {
                    supplyDataTransfer.items.add(file);
                });

                event.target.files = supplyDataTransfer.files;
                this.newSupplyPhotosCount = supplyDataTransfer.files.length;
                this.refreshSupplyPreviews(supplyDataTransfer);
            },

            removeNewSupplyFoto(index) {
                const inputEl = document.getElementById('foto_supply_input');
                supplyDataTransfer.items.remove(index);
                if (inputEl) {
                    inputEl.files = supplyDataTransfer.files;
                }
                this.newSupplyPhotosCount = supplyDataTransfer.files.length;
                this.refreshSupplyPreviews(supplyDataTransfer);
            },

            clearAllNewSupplyPhotos() {
                const inputEl = document.getElementById('foto_supply_input');
                while (supplyDataTransfer.items.length > 0) {
                    supplyDataTransfer.items.remove(0);
                }
                if (inputEl) {
                    inputEl.files = supplyDataTransfer.files;
                    inputEl.value = '';
                }
                this.newSupplyPhotoPreviews = [];
                this.newSupplyPhotosCount = 0;
            },

            refreshSupplyPreviews(dt) {
                const files = Array.from(dt.files);
                if (files.length === 0) {
                    this.newSupplyPhotoPreviews = [];
                    this.newSupplyPhotosCount = 0;
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
                            this.newSupplyPhotoPreviews = [...newPreviews];
                        }
                    };
                    reader.readAsDataURL(file);
                });
            },

            // === FUNGSI MAINTENANCE (ASLI & TIDAK BERUBAH) ===
            getTotalPhotoCount(aset) {
                return (aset.existingPhotos ? aset.existingPhotos.length : 0) + (aset.newPhotosCount || 0);
            },

            removeExistingPhoto(aset, index) {
                aset.existingPhotos.splice(index, 1);
                aset.existing_photos = aset.existingPhotos.map(e => e.path);
            },

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
                aset.newPhotosCount = dataTransfers[assetKey].files.length;
                this.refreshPreviews(aset, dataTransfers[assetKey]);
            },

            removeNewFoto(aset, photoIndex) {
                const assetKey = aset.id || 0;
                const dt = dataTransfers[assetKey];
                const inputEl = document.getElementById('foto_input_' + (aset.id || 0));

                if (dt) {
                    dt.items.remove(photoIndex);
                    if (inputEl) {
                        inputEl.files = dt.files;
                    }
                    aset.newPhotosCount = dt.files.length;
                    this.refreshPreviews(aset, dt);
                } else {
                    aset.newPhotoPreviews.splice(photoIndex, 1);
                    aset.newPhotosCount = aset.newPhotoPreviews.length;
                }
            },

            clearAllNewPhotos(aset) {
                const assetKey = aset.id || 0;
                dataTransfers[assetKey] = new DataTransfer();
                const inputEl = document.getElementById('foto_input_' + (aset.id || 0));
                if (inputEl) {
                    inputEl.files = dataTransfers[assetKey].files;
                    inputEl.value = '';
                }
                aset.newPhotoPreviews = [];
                aset.newPhotosCount = 0;
            },

            refreshPreviews(aset, dt) {
                const files = Array.from(dt.files);
                if (files.length === 0) {
                    aset.newPhotoPreviews = [];
                    aset.newPhotosCount = 0;
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
                            aset.newPhotoPreviews = [...newPreviews];
                        }
                    };
                    reader.readAsDataURL(file);
                });
            },

            tambahBaris(asetIndex) {
                this.asetList[asetIndex].items.push({
                    id: Date.now() + Math.random(),
                    uraian: '',
                    vol: 1,
                    satuan: 'unit',
                    hargaSatuan: 0
                });
            },

            hapusItem(asetIndex, itemIndex) {
                if (this.asetList[asetIndex].items.length > 1) {
                    this.asetList[asetIndex].items.splice(itemIndex, 1);
                } else {
                    this.asetList[asetIndex].items[0].uraian = '';
                    this.asetList[asetIndex].items[0].vol = 1;
                    this.asetList[asetIndex].items[0].hargaSatuan = 0;
                }
            },

            hitungSubtotalAset(aset) {
                return (aset.items || []).reduce((sum, item) => {
                    return sum + ((item.vol || 0) * (item.hargaSatuan || 0));
                }, 0);
            },

            // === TOTAL BIAYA REALISASI ===
            get totalBiayaRealisasi() {
                if (this.jenis === 'Supply') {
                    return this.supplyItems.reduce((sum, item) => {
                        return sum + ((item.vol || 0) * (item.hargaSatuan || 0));
                    }, 0);
                }
                return this.asetList.reduce((sum, aset) => {
                    return sum + this.hitungSubtotalAset(aset);
                }, 0);
            },

            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number || 0);
            }
        };
    }
</script>
@endsection
