@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-16" x-data="permintaanForm()">
    <!-- Header Page Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 tracking-tight" 
                    x-text="jenis === 'Supply' ? 'Permintaan Supply (Persediaan)' : 'Permintaan Maintenance (Pemeliharaan)'"></h1>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold"
                      :class="jenis === 'Supply' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-[#094886] border border-blue-200'"
                      x-text="jenis"></span>
            </div>
            <p class="text-sm text-slate-500 mt-1" 
               x-text="jenis === 'Supply' ? 'Ajukan permintaan barang persediaan habis pakai / ATK / perlengkapan dan rincian kebutuhan biaya (RAB).' : 'Pilih daftar aset BMN yang ada dan buat rincian perbaikan serta rencana anggaran biaya (RAB).'"></p>
        </div>

        <!-- Tombol Kembali ke Daftar -->
        <a href="{{ route('permintaan.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 hover:bg-slate-50 px-3.5 py-2 rounded-xl transition-colors self-start sm:self-auto shadow-2xs">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <span>Kembali ke Daftar</span>
        </a>
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

    <form id="permintaanCreateForm" action="{{ route('permintaan.store') }}" method="POST" @submit.prevent="submitForm">
        @csrf
        <input type="hidden" name="jenis" :value="jenis">

        <!-- ========================================================================= -->
        <!-- 1. KHUSUS MAINTENANCE: CARD INFORMASI + DYNAMIC ASSET CARDS               -->
        <!-- ========================================================================= -->
        <template x-if="jenis === 'Maintenance'">
            <div class="space-y-6">
                <!-- Card 1: Informasi Permintaan (Pekerjaan & Tanggal) -->
                <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-6 shadow-xs space-y-4">
                    <!-- Tanggal Permintaan -->
                    <div style="max-width: 280px;">
                        <label class="block text-sm font-bold text-slate-800 mb-2">
                            Tanggal Permintaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal" x-model="tanggal" required
                               class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium cursor-pointer">
                    </div>

                    <!-- Pekerjaan -->
                    <div>
                        <label class="block text-sm font-bold text-slate-800 mb-2">
                            Pekerjaan / Rencana Pemeliharaan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="pekerjaan" x-model="pekerjaan" rows="3" required
                                  placeholder="Contoh: Pemeliharaan rutin AC ruang pimpinan dan service berkala laptop operasional..." 
                                  class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-lg p-3 sm:p-3.5 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"></textarea>
                    </div>
                </div>

                <!-- Dynamic Asset Cards List -->
                <div class="space-y-6">
                    <template x-for="(aset, asetIndex) in asetList" :key="aset.id">
                        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs transition-all"
                             :class="aset.dropdownOpen ? 'relative z-30' : 'relative z-10'">
                            <!-- Header Card Aset -->
                            <div class="px-4 sm:px-6 py-3.5 sm:py-4 flex items-center justify-between border-b border-slate-100 rounded-t-xl bg-[#edf2f9]">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold bg-[#094886] text-white"
                                          x-text="asetIndex + 1"></span>
                                    <h2 class="text-base sm:text-lg font-bold text-slate-900" 
                                        x-text="'Aset ' + (asetIndex + 1)"></h2>
                                </div>
                                
                                <!-- Tombol Hapus Aset -->
                                <button type="button" @click="hapusAset(asetIndex)" 
                                        x-show="asetList.length > 1"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-2 sm:px-2.5 py-1.5 rounded-md transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                    <span>Hapus Aset</span>
                                </button>
                            </div>

                            <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                                <!-- Pilih Aset BMN Combobox -->
                                <div class="relative" @click.outside="aset.dropdownOpen = false">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        PILIH ASET BMN <span class="text-rose-500">*</span>
                                    </label>
                                    
                                    <button type="button" 
                                            @click="aset.dropdownOpen = !aset.dropdownOpen; if(aset.dropdownOpen) { $nextTick(() => { $refs['searchInput_' + asetIndex]?.focus() }); }"
                                            class="w-full bg-white border border-slate-300 hover:border-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 rounded-lg px-3.5 py-2.5 text-left text-sm text-slate-800 transition-all flex items-center justify-between cursor-pointer shadow-2xs">
                                        
                                        <template x-if="aset.selectedAsset && getAsetDetail(aset.selectedAsset)">
                                            <div class="flex items-center gap-2 truncate">
                                                <span class="font-mono font-bold text-[#094886]" x-text="'[' + getAsetDetail(aset.selectedAsset).clean_kode_barang + '] NUP: ' + getAsetDetail(aset.selectedAsset).clean_nup"></span>
                                                <span class="text-slate-300">•</span>
                                                <span class="font-bold text-slate-800 truncate" x-text="getAsetDetail(aset.selectedAsset).nama_barang"></span>
                                                <template x-if="getAsetDetail(aset.selectedAsset).merk || getAsetDetail(aset.selectedAsset).tipe">
                                                    <span class="text-slate-500 text-xs truncate" x-text="'(' + (getAsetDetail(aset.selectedAsset).merk || '') + (getAsetDetail(aset.selectedAsset).tipe ? ' ' + getAsetDetail(aset.selectedAsset).tipe : '') + ')'"></span>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!aset.selectedAsset || !getAsetDetail(aset.selectedAsset)">
                                            <span class="text-slate-400">Ketik nama barang, kode aset, NUP, atau merk...</span>
                                        </template>
                                        
                                        <div class="flex items-center gap-1.5 flex-shrink-0 text-slate-400">
                                            <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': aset.dropdownOpen}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                        </div>
                                    </button>

                                    <!-- Dropdown List Aset -->
                                    <div x-show="aset.dropdownOpen" 
                                         x-cloak
                                         class="absolute z-50 mt-1 w-full bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden">
                                        <div class="p-2.5 border-b border-slate-100 bg-slate-50/80">
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                                </div>
                                                <input type="text" 
                                                       :x-ref="'searchInput_' + asetIndex"
                                                       x-model="aset.search"
                                                       placeholder="Cari berdasarkan nama, kode, NUP, atau merk..."
                                                       class="w-full pl-9 pr-8 py-2 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium">
                                                <button type="button" 
                                                        x-show="aset.search" 
                                                        @click="aset.search = ''; $refs['searchInput_' + asetIndex]?.focus()"
                                                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                </button>
                                            </div>
                                            <div class="flex items-center justify-between text-[11px] text-slate-400 mt-1.5 px-1">
                                                <span>Pilih aset yang memenuhi syarat pemeliharaan</span>
                                                <span x-text="aset.search ? ('Ditemukan ' + getFilteredAsets(aset.search).length + ' aset') : ('Menampilkan ' + getFilteredAsets('').length + ' dari ' + availableAsets.length + ' aset')"></span>
                                            </div>
                                        </div>

                                        <div class="max-h-[260px] overflow-y-auto divide-y divide-slate-100 text-sm">
                                            <template x-for="item in getFilteredAsets(aset.search)" :key="item.id">
                                                <div @click="selectAsset(aset, item)"
                                                     class="px-3.5 py-2 transition-colors cursor-pointer flex items-center justify-between gap-3"
                                                     :class="{
                                                         'bg-slate-100/70 cursor-not-allowed opacity-60': item.is_rusak_berat,
                                                         'hover:bg-blue-50/70': !item.is_rusak_berat,
                                                         'bg-blue-50/40 font-semibold': String(aset.selectedAsset) === String(item.id)
                                                     }">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <span class="font-mono text-xs font-bold text-[#094886]" x-text="'[' + item.clean_kode_barang + '] NUP: ' + item.clean_nup"></span>
                                                            <span class="text-xs text-slate-300">•</span>
                                                            <span class="font-bold text-slate-800 truncate" x-text="item.nama_barang"></span>
                                                            <template x-if="item.merk || item.tipe">
                                                                <span class="text-xs text-slate-500 truncate" x-text="'(' + (item.merk || '') + (item.tipe ? ' ' + item.tipe : '') + ')'"></span>
                                                            </template>
                                                        </div>
                                                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-0.5">
                                                            <svg class="w-3 h-3 text-slate-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                                            <span class="truncate" x-text="item.clean_lokasi_ruang || 'Belum berlokasi'"></span>
                                                        </div>
                                                    </div>

                                                    <div class="flex-shrink-0">
                                                        <template x-if="item.is_rusak_berat">
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                                                <span>⛔</span> Rusak Berat
                                                            </span>
                                                        </template>
                                                        <template x-if="item.is_rusak_ringan">
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> Rusak Ringan
                                                            </span>
                                                        </template>
                                                        <template x-if="item.is_baik">
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Baik
                                                            </span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="getFilteredAsets(aset.search).length === 0">
                                                <div class="py-8 text-center text-xs text-slate-400">
                                                    Tidak ditemukan aset dengan kata kunci "<span class="font-semibold text-slate-600" x-text="aset.search"></span>"
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kondisi Aset Read-Only -->
                                <div x-show="aset.selectedAsset" x-transition.opacity.duration.200ms>
                                    <div class="rounded-xl border p-3.5 transition-all flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                                         :class="{
                                             'bg-emerald-50/70 border-emerald-200': getAsetKondisi(aset.selectedAsset) === 'Baik',
                                             'bg-amber-50/70 border-amber-200': getAsetKondisi(aset.selectedAsset) === 'Rusak Ringan',
                                             'bg-rose-50/70 border-rose-200': getAsetKondisi(aset.selectedAsset) === 'Rusak Berat',
                                             'bg-slate-50 border-slate-200': !['Baik', 'Rusak Ringan', 'Rusak Berat'].includes(getAsetKondisi(aset.selectedAsset))
                                         }">
                                        <div class="flex items-center gap-3">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-slate-500 uppercase">Kondisi Aset:</span>
                                                    <span class="px-2 py-0.5 rounded text-xs font-bold"
                                                          :class="{
                                                              'bg-emerald-100 text-emerald-800 border border-emerald-200': getAsetKondisi(aset.selectedAsset) === 'Baik',
                                                              'bg-amber-100 text-amber-800 border border-amber-200': getAsetKondisi(aset.selectedAsset) === 'Rusak Ringan',
                                                              'bg-rose-100 text-rose-800 border border-rose-200': getAsetKondisi(aset.selectedAsset) === 'Rusak Berat',
                                                              'bg-slate-100 text-slate-700': !['Baik', 'Rusak Ringan', 'Rusak Berat'].includes(getAsetKondisi(aset.selectedAsset))
                                                          }"
                                                          x-text="getAsetKondisi(aset.selectedAsset)"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <template x-if="getAsetLokasi(aset.selectedAsset)">
                                            <div class="text-xs text-slate-500">
                                                <span class="text-slate-400">Lokasi:</span>
                                                <span class="font-semibold text-slate-700" x-text="getAsetLokasi(aset.selectedAsset)"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Deskripsi Perbaikan / Kerusakan -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi Perbaikan / Kerusakan</label>
                                    <textarea x-model="aset.deskripsi_kerusakan" rows="2"
                                              placeholder="Jelaskan detail kerusakan atau rencana perbaikan pada aset ini..."
                                              class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-lg px-3.5 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"></textarea>
                                </div>

                                <!-- Tabel RAB (Maintenance) -->
                                <div class="border border-slate-200/90 rounded-lg overflow-x-auto shadow-2xs mt-4">
                                    <table class="w-full min-w-[560px] text-left border-collapse">
                                        <thead>
                                            <tr class="border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider bg-[#edf2f9]/70 text-slate-600">
                                                <th class="py-3 px-4">Rincian / Uraian Kebutuhan <span class="text-rose-500 font-bold">*</span></th>
                                                <th class="py-3 px-4 w-28 text-center">Volume <span class="text-rose-500 font-bold">*</span></th>
                                                <th class="py-3 px-4 w-36">Satuan <span class="text-rose-500 font-bold">*</span></th>
                                                <th class="py-3 px-4 w-44 text-right">Estimasi Harga (Rp) <span class="text-rose-500 font-bold">*</span></th>
                                                <th class="py-3 px-4 w-40 text-right">Jumlah (Rp)</th>
                                                <th class="py-3 px-4 w-16 text-center">Hapus</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                                            <template x-for="(item, itemIndex) in aset.rabItems" :key="item.id">
                                                <tr class="hover:bg-slate-50 transition-colors">
                                                    <td class="py-2.5 px-4 relative z-10">
                                                        <input type="text" x-model="item.uraian" 
                                                               placeholder="Nama barang/jasa perbaikan..."
                                                               class="w-full bg-transparent border-0 border-b border-transparent focus:border-blue-500 rounded-none px-1 py-1 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-0" 
                                                               required>
                                                    </td>
                                                    <td class="py-2.5 px-4">
                                                        <input type="number" min="1" x-model.number="item.volume"
                                                               @wheel="$event.target.blur()"
                                                               class="w-full bg-transparent border-0 text-center text-sm text-slate-800 focus:outline-none focus:ring-0 font-medium" required>
                                                    </td>
                                                    <td class="py-2.5 px-4">
                                                        <div class="relative">
                                                            <select x-model="item.satuan" 
                                                                    class="w-full appearance-none bg-transparent border-0 text-sm text-slate-700 pr-6 focus:outline-none focus:ring-0 cursor-pointer" required>
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
                                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center text-slate-400">
                                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-2.5 px-4 text-right">
                                                        <input type="number" min="1" step="100" x-model.number="item.hargaSatuan" placeholder="0"
                                                               @wheel="$event.target.blur()"
                                                               class="w-full bg-transparent border-0 text-right text-sm text-slate-800 focus:outline-none focus:ring-0 font-medium" required>
                                                    </td>
                                                    <td class="py-2.5 px-4 text-right font-bold text-slate-800">
                                                        <span x-text="formatRupiah((item.volume || 0) * (item.hargaSatuan || 0))"></span>
                                                    </td>
                                                    <td class="py-2.5 px-4 text-center">
                                                        <button type="button" @click="hapusRabItem(asetIndex, itemIndex)" 
                                                                class="p-1.5 bg-rose-50 text-rose-500 hover:bg-rose-100 hover:text-rose-700 rounded-md transition-colors cursor-pointer" title="Hapus Item">
                                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Button Tambah Item RAB -->
                                <div>
                                    <button type="button" @click="tambahRabItem(asetIndex)" 
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#1b7a37] hover:bg-[#15632c] text-white text-xs font-bold rounded-lg shadow-xs transition-colors cursor-pointer">
                                        <span>+</span>
                                        <span>Item RAB</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- ========================================================================= -->
        <!-- 2. KHUSUS SUPPLY: HANYA SATU CARD GABUNGAN                                -->
        <!-- ========================================================================= -->
        <template x-if="jenis === 'Supply'">
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs p-4 sm:p-6 space-y-5">
                <!-- Tanggal Permintaan -->
                <div style="max-width: 280px;">
                    <label class="block text-sm font-bold text-slate-800 mb-2">
                        Tanggal Permintaan <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal" x-model="tanggal" required
                           class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-medium cursor-pointer">
                </div>

                <!-- Pekerjaan / Keperluan Permintaan Supply -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-2">
                        Pekerjaan / Keperluan Permintaan Supply <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="pekerjaan" x-model="pekerjaan" rows="3" required
                              placeholder="Contoh: Pengadaan persediaan ATK dan toner printer untuk operasional kantor..." 
                              class="w-full bg-white border border-slate-300 hover:border-slate-400 rounded-lg p-3 sm:p-3.5 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all"></textarea>
                </div>

                <!-- Tabel Nama Barang / Persediaan -->
                <div class="border border-slate-200/90 rounded-lg sm:overflow-visible overflow-x-auto shadow-2xs mt-4">
                    <table class="w-full min-w-[560px] text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider bg-emerald-50/50 text-emerald-800">
                                <th class="py-3 px-4">Nama Barang / Persediaan <span class="text-rose-500 font-bold">*</span></th>
                                <th class="py-3 px-4 w-28 text-center">Volume <span class="text-rose-500 font-bold">*</span></th>
                                <th class="py-3 px-4 w-36">Satuan <span class="text-rose-500 font-bold">*</span></th>
                                <th class="py-3 px-4 w-44 text-right">Estimasi Harga (Rp) <span class="text-rose-500 font-bold">*</span></th>
                                <th class="py-3 px-4 w-40 text-right">Jumlah (Rp)</th>
                                <th class="py-3 px-4 w-16 text-center">Hapus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                            <template x-for="(item, itemIndex) in asetList[0]?.rabItems || []" :key="item.id">
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <!-- Kolom Nama Barang / Persediaan: Pilih dari Master/Riwayat atau Ketik Bebas -->
                                    <td class="py-2.5 px-4" :class="item.dropdownOpen ? 'relative z-40' : 'relative z-10'" @click.outside="item.dropdownOpen = false">
                                        <div>
                                            <div class="relative flex items-center">
                                                <input type="text" 
                                                       x-model="item.uraian" 
                                                       @focus="openRabItemDropdown(item, asetList[0])"
                                                       @input="onRabItemInput(item, asetList[0])"
                                                       placeholder="Ketik atau pilih nama barang persediaan..."
                                                       class="w-full bg-transparent border-0 border-b border-transparent focus:border-emerald-500 rounded-none px-1 py-1 pr-6 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-0 font-medium" 
                                                       required>
                                                <button type="button" 
                                                        @click="toggleRabItemDropdown(item, asetList[0])"
                                                        class="absolute right-0 text-slate-400 hover:text-slate-600 p-1 cursor-pointer"
                                                        tabindex="-1"
                                                        title="Pilih dari daftar barang / riwayat pengajuan">
                                                    <svg class="w-3.5 h-3.5 transition-transform" :class="{'rotate-180': item.dropdownOpen}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="6 9 12 15 18 9"></polyline>
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Dropdown Saran Barang Riwayat & Master -->
                                            <div x-show="item.dropdownOpen" 
                                                 x-cloak
                                                 class="absolute z-50 left-2 top-full mt-1 w-80 sm:w-96 bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden max-h-56 overflow-y-auto divide-y divide-slate-100 text-xs">
                                                <div class="p-2 bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                                    <span>Pilih Barang / Riwayat</span>
                                                    <span class="text-[10px] text-slate-400 font-normal">Bisa pilih atau ketik</span>
                                                </div>
                                                <template x-for="(sug, sIdx) in getFilteredSupplyOptions(item.uraian)" :key="sIdx">
                                                    <div @click="selectRabItemOption(item, sug)"
                                                         class="p-2.5 hover:bg-emerald-50/70 cursor-pointer flex items-center justify-between gap-2 transition-colors">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="font-bold text-slate-800 truncate" x-text="sug.nama_barang"></div>
                                                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-400">
                                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold"
                                                                      :class="sug.sumber === 'Master Persediaan' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-[#094886] border border-blue-200'"
                                                                      x-text="sug.sumber"></span>
                                                                <template x-if="sug.stok !== null && sug.stok !== undefined">
                                                                    <span class="font-mono text-emerald-700 font-semibold text-[10px]" x-text="'Stok: ' + sug.stok + ' ' + sug.satuan"></span>
                                                                </template>
                                                                <template x-if="sug.harga_estimasi">
                                                                    <span class="text-slate-500 text-[10px]" x-text="'• Rp ' + formatRupiah(sug.harga_estimasi)"></span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <span class="text-xs text-emerald-600 font-semibold flex-shrink-0">Pilih →</span>
                                                    </div>
                                                </template>
                                                <template x-if="getFilteredSupplyOptions(item.uraian).length === 0">
                                                    <div class="p-3 text-center text-slate-400 text-xs">
                                                        Barang belum ada di riwayat, silakan ketik langsung nama barang baru.
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Volume -->
                                    <td class="py-2.5 px-4">
                                        <input type="number" min="1" x-model.number="item.volume"
                                               @wheel="$event.target.blur()"
                                               class="w-full bg-transparent border-0 text-center text-sm text-slate-800 focus:outline-none focus:ring-0 font-medium" required>
                                    </td>

                                    <!-- Satuan -->
                                    <td class="py-2.5 px-4">
                                        <div class="relative">
                                            <select x-model="item.satuan" 
                                                    class="w-full appearance-none bg-transparent border-0 text-sm text-slate-700 pr-6 focus:outline-none focus:ring-0 cursor-pointer" required>
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
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center text-slate-400">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Estimasi Harga -->
                                    <td class="py-2.5 px-4 text-right">
                                        <input type="number" min="1" step="100" x-model.number="item.hargaSatuan" placeholder="0"
                                               @wheel="$event.target.blur()"
                                               class="w-full bg-transparent border-0 text-right text-sm text-slate-800 focus:outline-none focus:ring-0 font-medium" required>
                                    </td>

                                    <!-- Jumlah (Rp) -->
                                    <td class="py-2.5 px-4 text-right font-bold text-slate-800">
                                        <span x-text="formatRupiah((item.volume || 0) * (item.hargaSatuan || 0))"></span>
                                    </td>

                                    <!-- Hapus Item -->
                                    <td class="py-2.5 px-4 text-center">
                                        <button type="button" @click="hapusRabItem(0, itemIndex)" 
                                                class="p-1.5 bg-rose-50 text-rose-500 hover:bg-rose-100 hover:text-rose-700 rounded-md transition-colors cursor-pointer" title="Hapus Item">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Tombol Tambah Item RAB di Bawah Tabel -->
                <div>
                    <button type="button" @click="tambahRabItem(0)" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#1b7a37] hover:bg-[#15632c] text-white text-xs font-bold rounded-lg shadow-xs transition-colors cursor-pointer">
                        <span>+</span>
                        <span>Item RAB</span>
                    </button>
                </div>
            </div>
        </template>

        <!-- Total Perkiraan Biaya & Actions Footer -->
        <div class="mt-8 space-y-6">
            <!-- Summary Perkiraan Biaya -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between sm:justify-end gap-2 sm:gap-4 bg-white sm:bg-transparent p-4 sm:p-0 rounded-xl border sm:border-0 border-slate-200/80">
                <span class="text-sm font-bold text-slate-800">Total Perkiraan Anggaran (RAB):</span>
                <div class="bg-[#edf4fb] border border-blue-200/80 rounded-lg px-5 sm:px-6 py-2 sm:py-2.5 w-full sm:w-auto sm:min-w-[220px] text-right">
                    <span class="text-xl font-extrabold text-[#0c4a82]" x-text="'Rp ' + formatRupiah(totalPerkiraanBiaya)"></span>
                </div>
            </div>

            <!-- Footer Buttons Row -->
            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 pt-4 border-t border-slate-200/80">
                <!-- Batal -->
                <a href="{{ route('permintaan.index') }}" 
                   class="px-5 py-2.5 text-sm font-semibold text-[#094886] hover:bg-slate-100 rounded-lg transition-colors text-center">
                    Batal
                </a>

                <!-- + Tambah Aset (HANYA DITAMPILKAN PADA MAINTENANCE) -->
                <button type="button" @click="tambahAset" 
                        x-show="jenis === 'Maintenance'"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-[#094886] text-[#094886] hover:bg-blue-50/80 text-sm font-bold rounded-lg shadow-xs transition-colors cursor-pointer">
                    <span class="text-base font-bold">+</span>
                    <span>Tambah Aset</span>
                </button>

                <!-- Simpan Draft -->
                <button type="button" @click="openConfirmModal" 
                        :disabled="isSaving"
                        :class="isSaving ? 'opacity-60 cursor-not-allowed bg-slate-100 text-slate-400 border-slate-300' : 'bg-white border-[#094886] text-[#094886] hover:bg-blue-50/80 cursor-pointer'"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 border text-sm font-bold rounded-lg shadow-xs transition-all">
                    <template x-if="isSaving">
                        <svg class="animate-spin h-4 w-4 text-[#094886]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Draft Permintaan'"></span>
                </button>
            </div>
        </div>

        <!-- Modal Konfirmasi Simpan Draft -->
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
                 @click.away="if (!isSaving) confirmModalOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="text-center">
                    <div class="w-14 h-14 bg-blue-50 text-[#094886] rounded-2xl mx-auto flex items-center justify-center shadow-xs border border-blue-100 mb-3.5">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900">Simpan Draft Permintaan?</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Data permohonan akan disimpan ke dalam sistem untuk dapat dilanjutkan ke penyusunan KAK dan proses realisasi.
                    </p>
                </div>

                <!-- Preview Info -->
                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-2 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">Jenis Permintaan:</span>
                        <span class="font-bold" :class="jenis === 'Supply' ? 'text-emerald-700' : 'text-[#094886]'" x-text="jenis"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">Tanggal:</span>
                        <span class="text-slate-800 font-semibold" x-text="formatTanggalIndo(tanggal)"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium" x-text="jenis === 'Supply' ? 'Jumlah Barang:' : 'Jumlah Aset:'"></span>
                        <span class="text-slate-800 font-bold" x-text="jenis === 'Supply' ? ((asetList[0]?.rabItems?.length || 0) + ' Barang') : (asetList.length + ' Item')"></span>
                    </div>
                    <div class="flex justify-between items-center pt-2.5 border-t border-slate-200/80">
                        <span class="text-slate-700 font-bold">Total Estimasi RAB:</span>
                        <span class="text-[#094886] font-extrabold text-sm" x-text="'Rp ' + formatRupiah(totalPerkiraanBiaya)"></span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" 
                            @click="confirmModalOpen = false" 
                            :disabled="isSaving"
                            class="px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer disabled:opacity-50">
                        Periksa Kembali
                    </button>
                    <button type="button" 
                            @click="executeSimpanDraft()" 
                            :disabled="isSaving"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#094886] hover:bg-[#073868] text-white rounded-xl text-xs font-bold shadow-sm transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                        <template x-if="isSaving">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="isSaving ? 'Menyimpan...' : 'Ya, Simpan Draft'"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function permintaanForm() {
        const rawAsets = @json($asets ?? []);
        const formattedAsets = rawAsets.map(item => {
            const cleanKode = (item.kode_barang || '').replace(/\.0+$/, '');
            const cleanNup = (item.nup !== undefined && item.nup !== null ? String(item.nup) : '').replace(/\.0+$/, '');
            const knd = (item.kondisi || '').trim().toLowerCase();
            const cleanLokasi = (item.lokasi_ruang || '').replace(/^[-–—\s]+/, '').trim();

            return {
                id: String(item.id),
                kode_barang: item.kode_barang || '',
                clean_kode_barang: cleanKode || '-',
                nup: item.nup || '',
                clean_nup: cleanNup || '-',
                nama_barang: item.nama_barang || '-',
                merk: item.merk || '',
                tipe: item.tipe || '',
                clean_lokasi_ruang: cleanLokasi || 'Belum berlokasi',
                kondisi: item.kondisi || 'Belum Ditentukan',
                is_rusak_berat: knd === 'rusak berat',
                is_rusak_ringan: knd === 'rusak ringan',
                is_baik: knd === 'baik',
            };
        });

        const rawSupplies = @json($supplies ?? []);
        const rawRiwayatBarang = @json($riwayatBarang ?? []);

        const initialJenis = '{{ $jenis ?? "Maintenance" }}';
        const preselectedAsetId = '{{ request("aset_id", "") }}';
        const initialItem = {
            id: 1,
            search: '',
            dropdownOpen: false,
            selectedAsset: preselectedAsetId,
            nama_barang: '',
            deskripsi_kerusakan: '',
            kondisi_aset: '',
            rabItems: [
                {
                    id: 1,
                    uraian: '',
                    volume: 1,
                    satuan: initialJenis === 'Supply' ? 'buah' : 'unit',
                    hargaSatuan: '',
                    dropdownOpen: false,
                }
            ]
        };

        if (preselectedAsetId) {
            const matched = formattedAsets.find(a => String(a.id) === String(preselectedAsetId));
            if (matched) {
                initialItem.kondisi_aset = matched.kondisi;
                initialItem.nama_barang = matched.nama_barang;
            }
        }

        return {
            jenis: initialJenis,
            pekerjaan: '',
            tanggal: '{{ date("Y-m-d") }}',
            confirmModalOpen: false,
            availableAsets: formattedAsets,
            availableSupplies: rawSupplies,
            riwayatBarang: rawRiwayatBarang,
            getFilteredSupplyOptions(query) {
                const suppliesMapped = (this.availableSupplies || []).map(s => ({
                    nama_barang: s.nama_barang,
                    kategori: s.kategori || '',
                    stok: s.stok,
                    satuan: (s.satuan || 'buah').toLowerCase(),
                    harga_estimasi: s.harga_estimasi ? Math.round(Number(s.harga_estimasi)) : null,
                    sumber: 'Master Persediaan',
                }));

                const riwayatMapped = (this.riwayatBarang || []).filter(r => {
                    const rNama = (r.uraian || '').toLowerCase().trim();
                    return !suppliesMapped.some(s => (s.nama_barang || '').toLowerCase().trim() === rNama);
                }).map(r => ({
                    nama_barang: r.uraian,
                    kategori: '',
                    stok: null,
                    satuan: (r.satuan || 'buah').toLowerCase(),
                    harga_estimasi: r.harga_satuan ? Math.round(Number(r.harga_satuan)) : null,
                    sumber: r.sumber || 'Riwayat Pengajuan',
                }));

                const allOptions = suppliesMapped.concat(riwayatMapped);

                if (!query || !query.trim()) {
                    return allOptions.slice(0, 20);
                }

                const q = query.toLowerCase().trim();
                return allOptions.filter(item => {
                    return (item.nama_barang || '').toLowerCase().includes(q) ||
                           (item.kategori || '').toLowerCase().includes(q);
                }).slice(0, 20);
            },
            toggleRabItemDropdown(item, aset) {
                if (this.jenis !== 'Supply') return;
                const isCurrentlyOpen = item.dropdownOpen;
                if (aset && aset.rabItems) {
                    aset.rabItems.forEach(r => r.dropdownOpen = false);
                }
                item.dropdownOpen = !isCurrentlyOpen;
            },
            openRabItemDropdown(item, aset) {
                if (this.jenis !== 'Supply') return;
                if (aset && aset.rabItems) {
                    aset.rabItems.forEach(r => r.dropdownOpen = false);
                }
                item.dropdownOpen = true;
            },
            onRabItemInput(item, aset) {
                if (this.jenis !== 'Supply') return;
                item.dropdownOpen = true;
                if (!item.uraian) return;
                const match = this.getFilteredSupplyOptions('').find(opt => 
                    (opt.nama_barang || '').toLowerCase().trim() === (item.uraian || '').toLowerCase().trim()
                );
                if (match) {
                    if (match.satuan && (!item.satuan || item.satuan === 'buah' || item.satuan === 'unit')) {
                        item.satuan = match.satuan.toLowerCase();
                    }
                    if (match.harga_estimasi && !item.hargaSatuan) {
                        item.hargaSatuan = Math.round(Number(match.harga_estimasi));
                    }
                }
            },
            selectRabItemOption(item, sug) {
                item.uraian = sug.nama_barang;
                if (sug.satuan) {
                    item.satuan = sug.satuan.toLowerCase();
                }
                if (sug.harga_estimasi) {
                    item.hargaSatuan = Math.round(Number(sug.harga_estimasi));
                }
                item.dropdownOpen = false;
            },
            setJenis(newJenis) {
                this.jenis = newJenis;
            },
            getAsetDetail(id) {
                if (!id) return null;
                return this.availableAsets.find(item => String(item.id) === String(id)) || null;
            },
            getAsetKondisi(id) {
                const aset = this.getAsetDetail(id);
                return aset ? aset.kondisi : '-';
            },
            getAsetLokasi(id) {
                const aset = this.getAsetDetail(id);
                return aset ? aset.clean_lokasi_ruang : null;
            },
            getFilteredAsets(search) {
                if (!search || !search.trim()) {
                    return this.availableAsets.slice(0, 30);
                }
                const q = search.toLowerCase().trim();
                return this.availableAsets.filter(item => {
                    return (item.nama_barang || '').toLowerCase().includes(q) ||
                           (item.clean_kode_barang || '').toLowerCase().includes(q) ||
                           (item.clean_nup || '').toLowerCase().includes(q) ||
                           (item.merk || '').toLowerCase().includes(q) ||
                           (item.tipe || '').toLowerCase().includes(q);
                }).slice(0, 50);
            },
            selectAsset(aset, item) {
                if (item.is_rusak_berat) {
                    window.showToast({
                        type: 'warning',
                        title: 'Aset Rusak Berat',
                        message: 'Aset ini berstatus Rusak Berat dan tidak dapat diajukan pemeliharaan!'
                    });
                    return;
                }
                aset.selectedAsset = String(item.id);
                aset.nama_barang = item.nama_barang;
                aset.kondisi_aset = item.kondisi;
                aset.dropdownOpen = false;
                aset.search = '';
            },
            asetList: [initialItem],
            tambahAset() {
                const newId = this.asetList.length > 0 ? Math.max(...this.asetList.map(a => a.id)) + 1 : 1;
                this.asetList.push({
                    id: newId,
                    search: '',
                    dropdownOpen: false,
                    selectedAsset: '',
                    nama_barang: '',
                    deskripsi_kerusakan: '',
                    kondisi_aset: '',
                    rabItems: [
                        {
                            id: 1,
                            uraian: '',
                            volume: 1,
                            satuan: this.jenis === 'Supply' ? 'buah' : 'unit',
                            hargaSatuan: '',
                            dropdownOpen: false,
                        }
                    ]
                });
            },
            hapusAset(index) {
                if (this.asetList.length > 1) {
                    this.asetList.splice(index, 1);
                }
            },
            tambahRabItem(asetIndex) {
                if (!this.asetList[asetIndex]) return;
                const rabList = this.asetList[asetIndex].rabItems;
                const newId = rabList.length > 0 ? Math.max(...rabList.map(r => r.id)) + 1 : 1;
                rabList.push({
                    id: newId,
                    uraian: '',
                    volume: 1,
                    satuan: this.jenis === 'Supply' ? 'buah' : 'unit',
                    hargaSatuan: '',
                    dropdownOpen: false,
                });
            },
            hapusRabItem(asetIndex, itemIndex) {
                if (!this.asetList[asetIndex]) return;
                if (this.asetList[asetIndex].rabItems.length > 1) {
                    this.asetList[asetIndex].rabItems.splice(itemIndex, 1);
                } else {
                    this.asetList[asetIndex].rabItems[0].uraian = '';
                    this.asetList[asetIndex].rabItems[0].volume = 1;
                    this.asetList[asetIndex].rabItems[0].hargaSatuan = '';
                    this.asetList[asetIndex].rabItems[0].dropdownOpen = false;
                }
            },
            get totalPerkiraanBiaya() {
                return this.asetList.reduce((sumAset, aset) => {
                    const sumRab = (aset.rabItems || []).reduce((sumItem, item) => {
                        return sumItem + ((item.volume || 0) * (item.hargaSatuan || 0));
                    }, 0);
                    return sumAset + sumRab;
                }, 0);
            },
            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number || 0);
            },
            formatTanggalIndo(dateStr) {
                if (!dateStr) return '-';
                try {
                    const [y, m, d] = dateStr.split('-');
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    return `${parseInt(d)} ${months[parseInt(m) - 1]} ${y}`;
                } catch(e) {
                    return dateStr;
                }
            },
            validateForm() {
                if (!this.pekerjaan || !this.pekerjaan.trim()) {
                    window.showToast({
                        type: 'warning',
                        title: 'Data Belum Lengkap',
                        message: this.jenis === 'Supply' ? 'Mohon isi pekerjaan/keperluan permintaan supply terlebih dahulu.' : 'Mohon isi uraian rencana pekerjaan pemeliharaan terlebih dahulu.'
                    });
                    return false;
                }

                if (!this.tanggal) {
                    window.showToast({
                        type: 'warning',
                        title: 'Tanggal Belum Diisi',
                        message: 'Mohon isi tanggal permintaan terlebih dahulu.'
                    });
                    return false;
                }

                if (!this.asetList || this.asetList.length === 0) {
                    window.showToast({
                        type: 'warning',
                        title: 'Item Permintaan Kosong',
                        message: 'Mohon tambahkan minimal 1 item permintaan.'
                    });
                    return false;
                }

                if (this.jenis === 'Supply') {
                    // Pastikan hanya 1 entri pada asetList untuk Supply
                    if (this.asetList.length > 1) {
                        this.asetList = [this.asetList[0]];
                    }
                    const targetAset = this.asetList[0];
                    if (!targetAset.rabItems || targetAset.rabItems.length === 0) {
                        window.showToast({
                            type: 'warning',
                            title: 'Daftar Barang Kosong',
                            message: 'Mohon tambahkan minimal 1 nama barang persediaan.'
                        });
                        return false;
                    }

                    for (let r = 0; r < targetAset.rabItems.length; r++) {
                        const rab = targetAset.rabItems[r];
                        const num = r + 1;
                        if (!rab.uraian || !rab.uraian.trim()) {
                            window.showToast({
                                type: 'warning',
                                title: 'Nama Barang Belum Diisi',
                                message: 'Nama barang persediaan pada baris ke-' + num + ' belum diisi.'
                            });
                            return false;
                        }
                        if (rab.volume === undefined || rab.volume === null || rab.volume === '' || Number(rab.volume) <= 0) {
                            window.showToast({
                                type: 'warning',
                                title: 'Volume Tidak Valid',
                                message: 'Volume pada baris ke-' + num + ' harus berupa angka lebih dari 0.'
                            });
                            return false;
                        }
                        if (!rab.satuan || !rab.satuan.trim()) {
                            window.showToast({
                                type: 'warning',
                                title: 'Satuan Belum Dipilih',
                                message: 'Satuan pada baris ke-' + num + ' wajib dipilih.'
                            });
                            return false;
                        }
                        if (rab.hargaSatuan === undefined || rab.hargaSatuan === null || rab.hargaSatuan === '' || Number(rab.hargaSatuan) <= 0) {
                            window.showToast({
                                type: 'warning',
                                title: 'Harga Belum Diisi',
                                message: 'Estimasi harga satuan pada baris ke-' + num + ' belum diisi atau masih 0.'
                            });
                            return false;
                        }
                    }

                    // Sinkronkan nama_barang utama dengan item baris pertama
                    targetAset.nama_barang = targetAset.rabItems[0].uraian;
                    return true;
                }

                // Untuk Maintenance
                for (let i = 0; i < this.asetList.length; i++) {
                    const a = this.asetList[i];
                    const numAset = i + 1;
                    const itemLabel = 'Aset ke-' + numAset;

                    if (!a.selectedAsset) {
                        window.showToast({
                            type: 'warning',
                            title: 'Pilih Aset BMN',
                            message: 'Mohon pilih aset BMN yang akan dipelihara pada ' + itemLabel + '.'
                        });
                        return false;
                    }

                    const item = this.getAsetDetail(a.selectedAsset);
                    if (item && item.is_rusak_berat) {
                        window.showToast({
                            type: 'warning',
                            title: 'Aset Rusak Berat',
                            message: itemLabel + ' (' + item.nama_barang + ') berkondisi Rusak Berat. Tidak dapat diajukan pemeliharaan!'
                        });
                        return false;
                    }

                    if (!a.rabItems || a.rabItems.length === 0) {
                        window.showToast({
                            type: 'warning',
                            title: 'Item RAB Kosong',
                            message: 'Item atau uraian RAB pada ' + itemLabel + ' wajib diisi. Mohon tambahkan minimal 1 item RAB.'
                        });
                        return false;
                    }

                    for (let r = 0; r < a.rabItems.length; r++) {
                        const rab = a.rabItems[r];
                        const numRab = r + 1;
                        if (!rab.uraian || !rab.uraian.trim()) {
                            window.showToast({
                                type: 'warning',
                                title: 'Uraian RAB Kosong',
                                message: 'Item atau rincian RAB ke-' + numRab + ' pada ' + itemLabel + ' belum diisi.'
                            });
                            return false;
                        }
                        if (rab.volume === undefined || rab.volume === null || rab.volume === '' || Number(rab.volume) <= 0) {
                            window.showToast({
                                type: 'warning',
                                title: 'Volume Tidak Valid',
                                message: 'Volume pada item ke-' + numRab + ' (' + itemLabel + ') harus berupa angka lebih dari 0.'
                            });
                            return false;
                        }
                        if (!rab.satuan || !rab.satuan.trim()) {
                            window.showToast({
                                type: 'warning',
                                title: 'Satuan Belum Dipilih',
                                message: 'Satuan pada item ke-' + numRab + ' (' + itemLabel + ') wajib dipilih.'
                            });
                            return false;
                        }
                        if (rab.hargaSatuan === undefined || rab.hargaSatuan === null || rab.hargaSatuan === '' || Number(rab.hargaSatuan) <= 0) {
                            window.showToast({
                                type: 'warning',
                                title: 'Harga Belum Diisi',
                                message: 'Estimasi harga satuan pada item ke-' + numRab + ' (' + itemLabel + ') belum diisi atau masih 0.'
                            });
                            return false;
                        }
                    }
                }

                return true;
            },
            isSaving: false,
            openConfirmModal() {
                if (this.isSaving) return;
                if (!this.validateForm()) {
                    return;
                }
                this.confirmModalOpen = true;
            },
            async executeSimpanDraft() {
                if (this.isSaving) return;
                this.isSaving = true;

                const tokenInput = document.querySelector('input[name="_token"]');
                const token = tokenInput ? tokenInput.value : '{{ csrf_token() }}';
                const targetUrl = "{{ route('permintaan.store', [], false) }}";

                if (this.jenis === 'Supply') {
                    if (this.asetList.length > 1) {
                        this.asetList = [this.asetList[0]];
                    }
                    if (this.asetList[0]?.rabItems?.[0]) {
                        this.asetList[0].nama_barang = this.asetList[0].rabItems[0].uraian || 'Barang Persediaan';
                    }
                }

                try {
                    const response = await fetch(targetUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            pekerjaan: this.pekerjaan,
                            tanggal: this.tanggal,
                            jenis: this.jenis,
                            asetList: this.asetList
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        sessionStorage.setItem('pendingToast', JSON.stringify({
                            type: 'success',
                            title: 'Data Berhasil Ditambahkan',
                            message: data.message || 'Data permintaan baru berhasil ditambahkan.'
                        }));
                        this.confirmModalOpen = false;
                        window.location.href = data.redirect || "{{ route('permintaan.index', [], false) }}";
                    } else if (response.status === 419) {
                        window.showToast({
                            type: 'error',
                            title: 'Sesi Berakhir',
                            message: 'Sesi telah kedaluwarsa (CSRF token expired). Silakan refresh halaman dan simpan kembali.'
                        });
                        this.isSaving = false;
                    } else {
                        const data = await response.json().catch(() => ({}));
                        window.showToast({
                            type: 'error',
                            title: 'Gagal Menyimpan',
                            message: data.message || 'Periksa kembali kelengkapan data yang diinput.'
                        });
                        this.isSaving = false;
                    }
                } catch (e) {
                    console.error('Fetch error:', e);
                    window.showToast({
                        type: 'error',
                        title: 'Terjadi Gangguan Jaringan',
                        message: 'Gagal menghubungi server. Periksa koneksi atau refresh halaman.'
                    });
                    this.isSaving = false;
                }
            },
            submitForm() {
                this.openConfirmModal();
            }
        };
    }
</script>
@endsection