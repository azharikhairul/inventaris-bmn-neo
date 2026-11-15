@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="asetPage()">
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <h1 class="text-xl sm:text-2xl md:text-[28px] font-bold text-slate-900 tracking-tight">Daftar Aset & Inventaris BPS</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">Data master barang milik negara (BMN), status operasional, kondisi fisik, dan kelayakan pemeliharaan aset.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
            <!-- Export Button -->
            <a href="{{ route('aset.export', request()->query()) }}" 
               class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl shadow-2xs transition-all duration-150 cursor-pointer">
                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Export CSV</span>
            </a>

            @if(!\App\Services\SatkerService::isViewingOtherSatker())
            <!-- Import Button -->
            <button type="button" 
                    @click="showImportModal = true"
                    class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl shadow-2xs transition-all duration-150 cursor-pointer">
                <svg class="w-4 h-4 text-[#094886]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <span>Import Data</span>
            </button>

            <!-- Tambah Aset Manual Button -->
            <button type="button" 
                    @click="openCreateModal()"
                    class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-[#094886] hover:bg-[#0c3167] text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs hover:shadow transition-all duration-150 cursor-pointer">
                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Tambah Aset Manual</span>
            </button>
            @else
            <div class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-50 text-amber-800 text-xs font-semibold rounded-xl border border-amber-200">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Mode Lihat Saja (Penambahan Dinonaktifkan)</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Rollback Import Banner (Jika Tersedia Backup Sebelum Import) -->
    @if(session('can_rollback_import') && session('last_aset_backup'))
    <div class="bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 border border-amber-300 rounded-2xl p-4 sm:p-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-2xs mt-0.5">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                    <path d="M3 3v5h5"></path>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-amber-950">Snapshot Cadangan Otomatis Tersedia</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200/80 text-amber-900 uppercase">Bisa Di-Rollback</span>
                </div>
                <p class="text-xs text-amber-800/90 mt-1 leading-relaxed">
                    Jika file yang baru diimpor salah atau ada data survei fisik yang tidak sengaja tertimpa, Anda dapat langsung mengembalikan seluruh data aset ke kondisi persis sebelum import
                    <span class="font-bold">({{ session('last_aset_backup')['timestamp'] ?? '' }})</span>.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2.5 flex-shrink-0 self-end md:self-center">
            <form action="{{ route('aset.rollback-import') }}" method="POST" onsubmit="return confirm('PERINGATAN: Perubahan dari import terakhir akan dibatalkan dan seluruh aset dikembalikan ke kondisi cadangan sebelum import. Lanjutkan rollback?');" class="m-0">
                @csrf
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white text-xs font-bold rounded-xl transition-all shadow-xs cursor-pointer">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                        <path d="M3 3v5h5"></path>
                    </svg>
                    <span>Batalkan / Rollback Import Terakhir</span>
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Top Metric Summary Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Aset Terdaftar -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">TOTAL ASET TERDAFTAR</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#094886] flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($totalAset, 0, ',', '.') }}</p>
            <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-2">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span>Tersebar di {{ $totalRuangan }} Ruangan & Unit Kerja</span>
            </div>
        </div>

        <!-- 2. Kondisi Baik -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">KONDISI BAIK</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2.5">
                <p class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($totalBaik, 0, ',', '.') }}</p>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                    {{ $persenBaik }}%
                </span>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Siap operasional BMN</span>
            </div>
        </div>

        <!-- 3. Rusak Ringan -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">RUSAK RINGAN</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2.5">
                <p class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($totalRusakRingan, 0, ',', '.') }}</p>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                    {{ $persenRusakRingan }}%
                </span>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Layak diajukan pemeliharaan</span>
            </div>
        </div>

        <!-- 4. Rusak Berat / Non-Aktif -->
        <div class="bg-white rounded-xl border border-rose-200/70 shadow-2xs p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-rose-500 uppercase tracking-wider">RUSAK BERAT / NON-AKTIF</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2.5">
                <p class="text-3xl font-extrabold text-rose-900 tracking-tight">{{ number_format($totalRusakBerat, 0, ',', '.') }}</p>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/60">
                    {{ $persenRusakBerat }}%
                </span>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-rose-600 font-medium mt-2">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                <span>Tidak dimaintenance</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs">
        <form method="GET" action="{{ route('aset.index') }}" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                <!-- Search Box (Col 4) -->
                <div class="lg:col-span-4 relative" style="position: relative;">
                    <div style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #94a3b8; display: flex; align-items: center;">
                        <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           enterkeyhint="search"
                           onkeydown="if(event.key === 'Enter') { event.preventDefault(); this.form.submit(); }"
                           placeholder="Cari NUP, nama aset, tipe, atau pengguna..." 
                           class="w-full bg-white border border-slate-300 hover:border-slate-400 text-slate-800 text-sm rounded-lg pl-10 pr-28 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                           style="width: 100%; padding-left: 40px; padding-right: 90px; height: 42px; box-sizing: border-box;">
                    
                    <div style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); display: flex; align-items: center; gap: 4px; z-index: 10;">
                        @if(request('search'))
                        <a href="{{ route('aset.index', request()->except('search')) }}" 
                           style="display: inline-flex; align-items: center; justify-content: center; padding: 4px; color: #94a3b8; text-decoration: none;"
                           title="Hapus kata kunci pencarian">
                            <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </a>
                        @endif
                        <button type="submit" 
                                style="display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 6px 14px; background-color: #094886; color: #ffffff; font-size: 13px; font-weight: 600; border-radius: 6px; border: none; cursor: pointer; height: 30px; box-sizing: border-box; line-height: 1;"
                                title="Mulai Pencarian">
                            <span>Cari</span>
                        </button>
                    </div>
                </div>

                <!-- Dropdown Kategori (Col 3) -->
                <div class="lg:col-span-3 relative">
                    <select name="kategori" 
                            onchange="this.form.submit()"
                            class="w-full appearance-none bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg px-3.5 py-2.5 pr-9 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition-all">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>
                                {{ $kat }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                </div>

                <!-- Dropdown Ruangan (Col 3) -->
                <div class="lg:col-span-3 relative">
                    <select name="ruangan" 
                            onchange="this.form.submit()"
                            class="w-full appearance-none bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg px-3.5 py-2.5 pr-9 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition-all">
                        <option value="">Semua Ruangan</option>
                        @foreach($ruanganList as $ruang)
                            <option value="{{ $ruang['raw'] }}" {{ request('ruangan') == $ruang['raw'] ? 'selected' : '' }}>
                                {{ $ruang['cleaned'] }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                </div>

                <!-- Dropdown Kondisi (Col 2) -->
                <div class="lg:col-span-2 relative">
                    <select name="kondisi" 
                            onchange="this.form.submit()"
                            class="w-full appearance-none bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg px-3.5 py-2.5 pr-9 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer transition-all">
                        <option value="">Semua Kondisi</option>
                        @foreach($kondisiList as $knd)
                            <option value="{{ $knd }}" {{ request('kondisi') == $knd ? 'selected' : '' }}>
                                {{ $knd }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                </div>
            </div>

            <!-- Keep per_page param if exists -->
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <!-- Active Filters Row & Reset Button -->
            @if(request('search') || request('kategori') || request('ruangan') || request('kondisi'))
            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 text-xs">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-slate-400 font-medium">Filter Aktif:</span>

                    @if(request('search'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-[#094886] font-medium border border-blue-200/60">
                        Pencarian: "{{ request('search') }}"
                        <a href="{{ route('aset.index', request()->except('search')) }}" class="hover:text-blue-900">✕</a>
                    </span>
                    @endif

                    @if(request('kategori'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-[#094886] font-medium border border-blue-200/60">
                        Kategori: {{ request('kategori') }}
                        <a href="{{ route('aset.index', request()->except('kategori')) }}" class="hover:text-blue-900">✕</a>
                    </span>
                    @endif

                    @if(request('ruangan'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-[#094886] font-medium border border-blue-200/60">
                        Ruangan: {{ trim(str_replace('- - ', '', request('ruangan'))) }}
                        <a href="{{ route('aset.index', request()->except('ruangan')) }}" class="hover:text-blue-900">✕</a>
                    </span>
                    @endif

                    @if(request('kondisi'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md font-medium border {{ request('kondisi') === 'Rusak Berat' ? 'bg-rose-50 text-rose-700 border-rose-200' : (request('kondisi') === 'Rusak Ringan' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200') }}">
                        Kondisi: {{ request('kondisi') }}
                        <a href="{{ route('aset.index', request()->except('kondisi')) }}" class="hover:opacity-75">✕</a>
                    </span>
                    @endif

                    <a href="{{ route('aset.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold underline ml-1">
                        Hapus Semua
                    </a>
                </div>

                <div>
                    <a href="{{ route('aset.index') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                            <path d="M3 3v5h5"></path>
                        </svg>
                        <span>Reset Filter</span>
                    </a>
                </div>
            </div>
            @endif
        </form>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <!-- Table Sub-bar -->
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-2">
            <span class="text-xs font-semibold text-slate-600">Daftar Barang Milik Negara</span>
            <span class="text-xs text-slate-500">Total Data: <strong class="text-slate-800 font-bold">{{ number_format($asets->total(), 0, ',', '.') }}</strong> BMN</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px] text-left border-collapse">
                <thead>
                    <tr class="bg-[#edf2f9]/70 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-12 text-center">NO</th>
                        <th class="py-3.5 px-4 min-w-[150px]">KODE BARANG / NUP</th>
                        <th class="py-3.5 px-4 min-w-[240px]">NAMA ASET & MEREK</th>
                        <th class="py-3.5 px-4 min-w-[220px]">JENIS & LOKASI</th>
                        <th class="py-3.5 px-4 min-w-[130px]">TANGGAL PEROLEHAN</th>
                        <th class="py-3.5 px-4 min-w-[160px]">KONDISI & STATUS</th>
                        <th class="py-3.5 px-4 min-w-[90px] text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($asets as $item)
                        <tr class="hover:bg-blue-50/30 transition-colors {{ $item->is_rusak_berat ? 'bg-rose-50/20' : '' }}">
                            <!-- No -->
                            <td class="py-3 px-4 text-center text-slate-500 font-medium text-xs">
                                {{ $loop->iteration + ($asets->currentPage() - 1) * $asets->perPage() }}
                            </td>

                            <!-- Kode Barang / NUP -->
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block font-mono text-sm">{{ $item->clean_kode_barang }}</span>
                                <span class="text-xs text-slate-500 font-medium">NUP: {{ $item->clean_nup }}</span>
                            </td>

                            <!-- Nama Aset & Merek -->
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block text-sm">{{ $item->nama_barang ?? '-' }}</span>
                                <span class="text-xs text-slate-500 block truncate max-w-xs">
                                    {{ trim(($item->merk ?? '') . ' ' . ($item->tipe ?? '')) ?: '-' }}
                                </span>
                            </td>

                            <!-- Jenis & Lokasi -->
                            <td class="py-3 px-4">
                                <span class="text-xs font-bold text-slate-700 block tracking-tight uppercase">
                                    {{ $item->jenis_bmn ?? '-' }}
                                </span>
                                <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                    <span class="truncate max-w-[200px]" title="{{ $item->clean_lokasi_ruang }}">
                                        {{ $item->clean_lokasi_ruang }}
                                    </span>
                                </div>
                            </td>

                            <!-- Tanggal Perolehan -->
                            <td class="py-3 px-4 whitespace-nowrap text-slate-600 text-xs font-medium">
                                {{ $item->tanggal_perolehan ? $item->tanggal_perolehan->format('Y-m-d') : '-' }}
                            </td>

                            <!-- Kondisi & Kelayakan -->
                            <td class="py-3 px-4">
                                @if($item->is_rusak_berat)
                                    <div class="inline-flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                            Rusak Berat
                                        </span>
                                        <span class="text-[10px] font-semibold text-rose-600 flex items-center gap-1">
                                            <span>⛔</span> Tidak Dimaintenance
                                        </span>
                                    </div>
                                @elseif($item->is_rusak_ringan)
                                    <div class="inline-flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                            Rusak Ringan
                                        </span>
                                        <span class="text-[10px] font-semibold text-amber-700 flex items-center gap-1">
                                            <span>✓</span> Layak Dimaintenance
                                        </span>
                                    </div>
                                @else
                                    <div class="inline-flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Baik
                                        </span>
                                        <span class="text-[10px] font-semibold text-slate-400">
                                            Siap Operasional
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Eye Icon (View Detail Modal) -->
                                    <button type="button" 
                                            @click="viewDetail({{ $item->id }})" 
                                            class="p-1.5 text-slate-500 hover:text-[#094886] hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                            title="Lihat Detail Kondisi Aset">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>

                                    @if(!\App\Services\SatkerService::isViewingOtherSatker())
                                    <!-- Edit Icon (Open Edit Modal) -->
                                    <button type="button" 
                                            @click="openEditModal({{ $item->id }})" 
                                            class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                            title="Edit Data Aset">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>

                                    <!-- Quick Action to Permintaan: ONLY if NOT Rusak Berat -->
                                    @if($item->is_rusak_ringan)
                                    <a href="{{ route('permintaan.create', ['type' => 'maintenance', 'aset_id' => $item->id]) }}" 
                                       class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition-colors cursor-pointer"
                                       title="Ajukan Pemeliharaan untuk aset ini">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                                        </svg>
                                    </a>
                                    @elseif($item->is_rusak_berat)
                                    <span class="p-1.5 text-slate-300 cursor-not-allowed" title="Aset Rusak Berat tidak dapat diajukan pemeliharaan">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                                        </svg>
                                    </span>
                                    @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-3 text-slate-300">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </div>
                                <p class="text-base font-semibold text-slate-700">Tidak ada data aset yang ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter yang dipilih.</p>
                                <div class="mt-4">
                                    <a href="{{ route('aset.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-[#094886] bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                        Bersihkan Semua Filter
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Per Page Row -->
        <div class="px-6 py-4 border-t border-slate-100 bg-white flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Info text -->
            <div class="text-xs text-slate-600">
                Menampilkan <strong>{{ $asets->firstItem() ?? 0 }}</strong> - <strong>{{ $asets->lastItem() ?? 0 }}</strong> dari <strong>{{ number_format($asets->total(), 0, ',', '.') }}</strong> data BMN
            </div>

            <!-- Custom Clean Pagination Navigation & Per Page Selector -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Per Page selector -->
                <div class="flex items-center gap-2 text-xs text-slate-600 sm:border-r sm:border-slate-200 sm:pr-3">
                    <label for="per_page_select" class="text-slate-400 whitespace-nowrap">Tampilkan per halaman:</label>
                    <select id="per_page_select" 
                            onchange="window.location.href = updateQueryStringParameter(window.location.href, 'per_page', this.value)"
                            class="bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-xs rounded-md px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer">
                        <option value="10" {{ $asets->perPage() == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ $asets->perPage() == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $asets->perPage() == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $asets->perPage() == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="flex items-center gap-1">
                    @if ($asets->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center text-slate-300 bg-slate-50 rounded-lg cursor-not-allowed border border-slate-200/60" title="Sebelumnya">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </span>
                    @else
                        <a href="{{ $asets->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 rounded-lg border border-slate-300 transition-colors shadow-2xs" title="Sebelumnya">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </a>
                    @endif

                    <!-- Compact page numbers -->
                    @php
                        $currentPage = $asets->currentPage();
                        $lastPage = $asets->lastPage();
                        $start = max(1, $currentPage - 1);
                        $end = min($lastPage, $currentPage + 1);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $asets->url(1) }}" class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-300">1</a>
                        @if($start > 2)
                            <span class="px-1 text-slate-400">...</span>
                        @endif
                    @endif

                    @for ($p = $start; $p <= $end; $p++)
                        @if ($p == $currentPage)
                            <span class="w-8 h-8 flex items-center justify-center text-xs font-bold text-white bg-[#094886] rounded-lg shadow-2xs">
                                {{ $p }}
                            </span>
                        @else
                            <a href="{{ $asets->url($p) }}" class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-300 transition-colors">
                                {{ $p }}
                            </a>
                        @endif
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="px-1 text-slate-400">...</span>
                        @endif
                        <a href="{{ $asets->url($lastPage) }}" class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-300">{{ $lastPage }}</a>
                    @endif

                    @if ($asets->hasMorePages())
                        <a href="{{ $asets->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 rounded-lg border border-slate-300 transition-colors shadow-2xs" title="Selanjutnya">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    @else
                        <span class="w-8 h-8 flex items-center justify-center text-slate-300 bg-slate-50 rounded-lg cursor-not-allowed border border-slate-200/60" title="Selanjutnya">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Asset Modal (Popup) -->
    <div x-show="detailModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
             @click="closeDetailModal()"></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl border border-slate-200"
                 @click.stop>
                
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between bg-slate-50/70">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-[#094886] flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-slate-900" x-text="activeAset.nama_barang || 'Detail Aset BMN'"></h3>
                                <template x-if="activeAset.is_rusak_berat">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Rusak Berat
                                    </span>
                                </template>
                                <template x-if="activeAset.is_rusak_ringan">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Rusak Ringan
                                    </span>
                                </template>
                                <template x-if="activeAset.is_baik">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Baik
                                    </span>
                                </template>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5 font-mono">
                                Kode: <span x-text="activeAset.kode_barang"></span> | NUP: <span x-text="activeAset.nup"></span>
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="closeDetailModal()"
                            class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200/60 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                    <!-- Status Banner inside modal -->
                    <template x-if="activeAset.is_rusak_berat">
                        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                            </div>
                            <div class="text-xs text-rose-900 leading-relaxed">
                                <span class="font-bold block text-sm mb-0.5">Aset Rusak Berat — Tidak Memenuhi Syarat Pemeliharaan</span>
                                Aset ini tercatat dalam kondisi <strong>Rusak Berat</strong>. Berdasarkan ketentuan pengelolaan aset BMN, barang dengan kondisi ini <strong>tidak dialokasikan untuk pekerjaan pemeliharaan / perbaikan rutin</strong> dan diarahkan langsung untuk usulan penghapusan BMN.
                            </div>
                        </div>
                    </template>

                    <template x-if="activeAset.is_rusak_ringan">
                        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            </div>
                            <div class="text-xs text-amber-900 leading-relaxed">
                                <span class="font-bold block text-sm mb-0.5">Aset Rusak Ringan — Layak Diajukan Pemeliharaan</span>
                                Kondisi fisik aset memungkinkan untuk dilakukan perbaikan/servis berkala melalui formulir Permintaan Pemeliharaan.
                            </div>
                        </div>
                    </template>

                    <template x-if="activeAset.is_baik">
                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                            <div class="text-xs text-emerald-900 leading-relaxed">
                                <span class="font-bold block text-sm mb-0.5">Aset Kondisi Baik — Siap Operasional</span>
                                Aset dalam keadaan berfungsi prima untuk mendukung kegiatan operasional BPS.
                            </div>
                        </div>
                    </template>

                    <!-- Section 1: Spesifikasi & Lokasi -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Spesifikasi & Penempatan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs">
                            <div>
                                <span class="text-slate-400 block mb-0.5">Merek & Tipe</span>
                                <span class="font-semibold text-slate-800" x-text="(activeAset.merk || '-') + ' ' + (activeAset.tipe || '')"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block mb-0.5">Jenis BMN</span>
                                <span class="font-semibold text-slate-800" x-text="activeAset.jenis_bmn || '-'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block mb-0.5">Lokasi Ruangan</span>
                                <span class="font-semibold text-slate-800" x-text="activeAset.lokasi_ruang || '-'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block mb-0.5">Pengguna / Penghuni</span>
                                <span class="font-semibold text-slate-800" x-text="activeAset.pengguna || activeAset.penghuni || '-'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block mb-0.5">Tanggal Perolehan</span>
                                <span class="font-semibold text-slate-800" x-text="activeAset.tanggal_perolehan || '-'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block mb-0.5">Umur Aset</span>
                                <span class="font-semibold text-slate-800" x-text="activeAset.umur_aset || '-'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Nilai Finansial BMN -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Nilai Akuntansi & Buku</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs">
                            <div>
                                <span class="text-slate-400 block mb-0.5">Nilai Perolehan</span>
                                <span class="font-bold text-slate-900 text-sm" x-text="activeAset.nilai_perolehan || '-'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block mb-0.5">Nilai Penyusutan</span>
                                <span class="font-semibold text-slate-700" x-text="activeAset.nilai_penyusutan || '-'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block mb-0.5">Nilai Buku</span>
                                <span class="font-bold text-[#094886] text-sm" x-text="activeAset.nilai_buku || '-'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Legalitas & Riwayat Permintaan -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Riwayat Pemeliharaan Tercatat</h4>
                        <div class="border border-slate-200/90 rounded-xl overflow-hidden">
                            <template x-if="activeAset.riwayat_permintaan && activeAset.riwayat_permintaan.length > 0">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-50 border-b border-slate-200/80 font-bold text-slate-600">
                                        <tr>
                                            <th class="py-2.5 px-3">Tanggal</th>
                                            <th class="py-2.5 px-3">Pekerjaan</th>
                                            <th class="py-2.5 px-3">Deskripsi Kerusakan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="r in activeAset.riwayat_permintaan" :key="r.id">
                                            <tr>
                                                <td class="py-2 px-3 text-slate-500 whitespace-nowrap" x-text="r.tanggal"></td>
                                                <td class="py-2 px-3 font-semibold text-slate-800" x-text="r.pekerjaan"></td>
                                                <td class="py-2 px-3 text-slate-600" x-text="r.deskripsi_kerusakan"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </template>
                            <template x-if="!activeAset.riwayat_permintaan || activeAset.riwayat_permintaan.length === 0">
                                <div class="py-4 text-center text-xs text-slate-400 bg-slate-50/50">
                                    Belum ada catatan riwayat pemeliharaan untuk aset ini di sistem.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="closeDetailModal()"
                                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-200/60 rounded-lg transition-colors cursor-pointer">
                            Tutup
                        </button>
                        @if(!\App\Services\SatkerService::isViewingOtherSatker())
                        <button type="button" 
                                @click="openEditModal(activeAset.id); closeDetailModal();"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-[#094886] hover:text-[#0c3167] bg-blue-50 hover:bg-blue-100 border border-blue-200/80 rounded-lg transition-colors cursor-pointer"
                                title="Edit Data Aset">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            <span>Edit Aset Ini</span>
                        </button>
                        @endif
                    </div>

                    <div>
                        <template x-if="activeAset.is_rusak_ringan">
                            <a :href="'{{ route('permintaan.create', ['type' => 'maintenance']) }}&aset_id=' + activeAset.id"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-[#094886] hover:bg-[#073868] text-white text-xs font-bold rounded-lg shadow-xs transition-colors">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Ajukan Pemeliharaan Aset Ini</span>
                            </a>
                        </template>

                        <template x-if="activeAset.is_rusak_berat">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-700 text-xs font-bold rounded-lg border border-rose-200">
                                <span>⛔</span> Tidak Dapat Diajukan Pemeliharaan
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!\App\Services\SatkerService::isViewingOtherSatker())
    <!-- Modal Import Data Aset (Excel / CSV) -->
    <div x-show="showImportModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200/90 w-full max-w-xl overflow-hidden"
             @click.away="if (!isSubmitting) closeImportModal()">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-50/90 border-b border-slate-200/80 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#094886] flex items-center justify-center flex-shrink-0 shadow-2xs">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Import Data Aset BMN</h3>
                        <p class="text-xs text-slate-500">Unggah file format Excel (.xlsx, .xls) atau CSV (.csv)</p>
                    </div>
                </div>

                <button type="button" 
                        @click="closeImportModal()" 
                        :disabled="isSubmitting"
                        class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 rounded-xl transition cursor-pointer disabled:opacity-50">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('aset.import') }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  @submit="isSubmitting = true"
                  class="p-6 space-y-5">
                @csrf

                <!-- File Dropzone -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilih Berkas Aset <span class="text-rose-500">*</span>
                    </label>

                    <div class="relative border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer group"
                         :class="isDragging ? 'border-[#094886] bg-blue-50/50' : (fileName ? 'border-emerald-300 bg-emerald-50/30' : 'border-slate-300 hover:border-[#094886] bg-slate-50/50')"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleFileDrop($event)"
                         @click="$refs.fileInput.click()">
                        
                        <input type="file" 
                               name="file" 
                               x-ref="fileInput"
                               @change="handleFileSelect($event)" 
                               accept=".xlsx,.xls,.csv" 
                               required 
                               class="hidden">

                        <!-- State: No File Selected -->
                        <template x-if="!fileName">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <div class="w-12 h-12 rounded-full bg-blue-50 text-[#094886] flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="12" y1="18" x2="12" y2="12"></line>
                                        <line x1="9" y1="15" x2="15" y2="15"></line>
                                    </svg>
                                </div>
                                <div class="text-sm text-slate-600 font-medium">
                                    <span class="text-[#094886] font-bold">Klik untuk memilih</span> atau seret file ke sini
                                </div>
                                <p class="text-[11px] text-slate-400">
                                    Mendukung format: <span class="font-semibold text-slate-600">.XLSX, .XLS, .CSV</span> (Maksimal 50MB)
                                </p>
                            </div>
                        </template>

                        <!-- State: File Selected -->
                        <template x-if="fileName">
                            <div class="flex items-center justify-between p-2">
                                <div class="flex items-center gap-3 text-left">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs uppercase flex-shrink-0">
                                        <span x-text="fileExt"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-800 truncate" x-text="fileName"></p>
                                        <p class="text-xs text-slate-500" x-text="fileSize"></p>
                                    </div>
                                </div>
                                <button type="button" 
                                        @click.stop="clearFile()" 
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-white rounded-lg transition cursor-pointer"
                                        title="Hapus File">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Mode Import Selection Cards -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Pilihan Metode Import <span class="text-rose-500">*</span>
                    </label>

                    <div class="space-y-2">
                        <!-- Mode 1: Safe Update (Rekomendasi) -->
                        <label class="relative flex items-start gap-3 p-3.5 border rounded-2xl cursor-pointer transition-all"
                               :class="importMode === 'safe_update' ? 'border-[#094886] bg-blue-50/50 ring-2 ring-[#094886]/10 shadow-2xs' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" name="mode" value="safe_update" x-model="importMode" class="mt-0.5 text-[#094886] focus:ring-[#094886]">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-900">Lindungi Data Survei Fisik</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 uppercase">Rekomendasi</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    Memperbarui nilai buku & master SIMAN, namun <strong>mengunci dan melindungi</strong> kolom yang telah diinput manual: <span class="font-semibold text-slate-800">Kondisi, Lokasi Ruang, Pengguna, dan Penghuni</span> agar tidak tertimpa.
                                </p>
                            </div>
                        </label>

                        <!-- Mode 2: Skip Existing (Hanya Tambah Baru) -->
                        <label class="relative flex items-start gap-3 p-3.5 border rounded-2xl cursor-pointer transition-all"
                               :class="importMode === 'skip' ? 'border-[#094886] bg-blue-50/50 ring-2 ring-[#094886]/10 shadow-2xs' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" name="mode" value="skip" x-model="importMode" class="mt-0.5 text-[#094886] focus:ring-[#094886]">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-900">Hanya Tambah Aset Baru</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">Aman 100%</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    Hanya mendaftarkan aset baru. Jika Kode Barang & NUP sudah ada di database, data tersebut <strong>dilewati sama sekali (tidak disentuh)</strong>.
                                </p>
                            </div>
                        </label>

                        <!-- Mode 3: Overwrite All -->
                        <label class="relative flex items-start gap-3 p-3.5 border rounded-2xl cursor-pointer transition-all"
                               :class="importMode === 'overwrite_all' ? 'border-amber-400 bg-amber-50/40 ring-2 ring-amber-400/20 shadow-2xs' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" name="mode" value="overwrite_all" x-model="importMode" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-slate-900">Timpa Seluruh Kolom Data (Overwrite)</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase">Perhatian</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    Menimpa semua kolom dari file Excel. Gunakan ini jika file yang diunggah adalah hasil survei terbaru yang sudah lengkap.
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Proteksi Kolom Kosong -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="ignore_blank" value="1" checked class="mt-0.5 rounded text-[#094886] focus:ring-[#094886]">
                        <div class="text-xs text-slate-700">
                            <span class="font-bold text-slate-900">Abaikan Kolom Kosong (Proteksi Tambahan)</span>
                            <p class="text-slate-500 mt-0.5">Jika di file Excel ada kolom yang kosong/blank (misal: lokasi atau pengguna belum terisi), sistem <strong>tidak akan menghapus</strong> data yang sudah ada di database.</p>
                        </div>
                    </label>
                </div>

                <!-- Auto Snapshot Notice -->
                <div class="flex items-start gap-2.5 p-3 bg-blue-50/70 border border-blue-100 rounded-xl text-xs text-blue-900">
                    <svg class="w-4 h-4 text-[#094886] flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <p class="leading-relaxed">
                        <strong>Auto-Backup Aktif:</strong> Sistem otomatis membuat snapshot cadangan data aset sebelum proses import dijalankan. Jika terjadi kesalahan file, data dapat dipulihkan (*rollback*) dengan 1 klik.
                    </p>
                </div>

                <!-- Modal Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" 
                            @click="closeImportModal()" 
                            :disabled="isSubmitting"
                            class="px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer disabled:opacity-50">
                        Batal
                    </button>
                    <button type="submit" 
                            :disabled="!fileName || isSubmitting"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-[#094886] hover:bg-[#0c3167] rounded-xl transition cursor-pointer shadow-xs hover:shadow disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="isSubmitting">
                            <svg class="animate-spin w-4 h-4 text-white" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </template>
                        <span x-text="isSubmitting ? 'Memproses Import...' : 'Mulai Import Data'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Tambah / Edit Data Aset Manual -->
    <div x-show="formModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto" 
         x-cloak 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200/90 w-full max-w-3xl overflow-hidden my-auto"
             style="max-width: 48rem;"
             @click.away="if (!isSubmittingForm) closeFormModal()">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-50/90 border-b border-slate-200/80 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#094886] flex items-center justify-center flex-shrink-0 shadow-2xs">
                        <template x-if="!isEdit">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </template>
                        <template x-if="isEdit">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </template>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base" x-text="isEdit ? 'Edit Data Aset BMN' : 'Tambah Aset BMN Manual'"></h3>
                        <p class="text-xs text-slate-500" x-text="isEdit ? 'Perbarui informasi identitas, kondisi, dan penempatan aset.' : 'Daftarkan data aset baru beserta spesifikasi dan lokasi secara manual.'"></p>
                    </div>
                </div>

                <button type="button" 
                        @click="closeFormModal()" 
                        :disabled="isSubmittingForm"
                        class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 rounded-xl transition cursor-pointer disabled:opacity-50">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Tab Headers -->
            <div class="flex items-center border-b border-slate-200 bg-slate-50/50 px-6 text-xs font-semibold overflow-x-auto gap-2 pt-2">
                <button type="button" 
                        @click="activeFormTab = 'identitas'"
                        :class="activeFormTab === 'identitas' ? 'border-[#094886] text-[#094886] border-b-2 font-bold bg-white rounded-t-lg' : 'text-slate-500 hover:text-slate-700 border-b-2 border-transparent hover:bg-slate-100 rounded-t-lg'"
                        class="py-2.5 px-3.5 transition-colors flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                    <span>1. Data Pokok & Spesifikasi</span>
                </button>
                <button type="button" 
                        @click="activeFormTab = 'kondisi'"
                        :class="activeFormTab === 'kondisi' ? 'border-[#094886] text-[#094886] border-b-2 font-bold bg-white rounded-t-lg' : 'text-slate-500 hover:text-slate-700 border-b-2 border-transparent hover:bg-slate-100 rounded-t-lg'"
                        class="py-2.5 px-3.5 transition-colors flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                    <span>2. Kondisi & Lokasi Ruang</span>
                </button>
                <button type="button" 
                        @click="activeFormTab = 'finansial'"
                        :class="activeFormTab === 'finansial' ? 'border-[#094886] text-[#094886] border-b-2 font-bold bg-white rounded-t-lg' : 'text-slate-500 hover:text-slate-700 border-b-2 border-transparent hover:bg-slate-100 rounded-t-lg'"
                        class="py-2.5 px-3.5 transition-colors flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    <span>3. Nilai Finansial & Legalitas</span>
                </button>
            </div>

            <!-- Server Validation Error Alert -->
            @if($errors->any())
            <div class="p-3.5 mx-6 mt-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="font-bold mb-1 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>Terdapat kendala pada isian formulir:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Modal Form -->
            <form :action="isEdit ? '{{ url('/aset') }}/' + form.id : '{{ route('aset.store') }}'" 
                  method="POST" 
                  @submit="isSubmittingForm = true"
                  class="flex flex-col">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="_is_edit" :value="isEdit ? '1' : '0'">
                <input type="hidden" name="_aset_id" :value="form.id || ''">

                <!-- Scrollable Body Container -->
                <div class="p-6 max-h-[60vh] overflow-y-auto space-y-5" style="max-height: 60vh;">
                    
                    <!-- TAB 1: IDENTITAS POKOK & SPESIFIKASI -->
                    <div x-show="activeFormTab === 'identitas'" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Kode Barang -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Kode Barang <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="kode_barang" 
                                       x-model="form.kode_barang" 
                                       required 
                                       placeholder="Contoh: 3050104001" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-mono text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                                <p class="text-[11px] text-slate-400 mt-1">Kode klasifikasi BMN sesuai SIMAK/SAKTI.</p>
                            </div>

                            <!-- NUP -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Nomor Urut Pendaftaran (NUP) <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="nup" 
                                       x-model="form.nup" 
                                       required 
                                       placeholder="Contoh: 1" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-mono text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                                <p class="text-[11px] text-slate-400 mt-1">Nomor urut registrasi BMN.</p>
                            </div>
                        </div>

                        <!-- Nama Barang -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Barang / Uraian BMN <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nama_barang" 
                                   x-model="form.nama_barang" 
                                   required 
                                   placeholder="Contoh: Personal Computer / AC Split 1.5 PK / Kursi Kerja" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                        </div>

                        <!-- Merek & Tipe -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Merek / Pabrikan
                                </label>
                                <input type="text" 
                                       name="merk" 
                                       x-model="form.merk" 
                                       placeholder="Contoh: Dell / Lenovo / Daikin" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Tipe / Model Spesifik
                                </label>
                                <input type="text" 
                                       name="tipe" 
                                       x-model="form.tipe" 
                                       placeholder="Contoh: Optiplex 7070 / Inverter FTKQ35" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                        </div>

                        <!-- Jenis BMN / Kategori -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Jenis BMN / Kategori
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       name="jenis_bmn" 
                                       list="kategori_datalist" 
                                       x-model="form.jenis_bmn" 
                                       placeholder="Pilih dari daftar atau ketik baru..." 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Dapat memilih kategori terdaftar atau mengetik kategori baru.</p>
                        </div>
                    </div>

                    <!-- TAB 2: KONDISI & LOKASI RUANG -->
                    <div x-show="activeFormTab === 'kondisi'" class="space-y-4">
                        <!-- Kondisi Fisik Cards Selector -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Kondisi Fisik Aset BMN <span class="text-rose-500">*</span>
                            </label>
                            <input type="hidden" name="kondisi" :value="form.kondisi">
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <!-- Card: Baik -->
                                <div @click="form.kondisi = 'Baik'"
                                     :class="form.kondisi === 'Baik' ? 'border-emerald-500 bg-emerald-50/50 ring-2 ring-emerald-500/20 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'"
                                     class="p-3.5 rounded-xl border transition-all cursor-pointer flex flex-col justify-between">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                            <span class="text-sm font-bold text-slate-800">Baik</span>
                                        </div>
                                        <span x-show="form.kondisi === 'Baik'" class="text-emerald-600 font-bold text-xs">✓ Dipilih</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500">Berfungsi normal & siap operasional.</p>
                                </div>

                                <!-- Card: Rusak Ringan -->
                                <div @click="form.kondisi = 'Rusak Ringan'"
                                     :class="form.kondisi === 'Rusak Ringan' ? 'border-amber-500 bg-amber-50/50 ring-2 ring-amber-500/20 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'"
                                     class="p-3.5 rounded-xl border transition-all cursor-pointer flex flex-col justify-between">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                            <span class="text-sm font-bold text-slate-800">Rusak Ringan</span>
                                        </div>
                                        <span x-show="form.kondisi === 'Rusak Ringan'" class="text-amber-600 font-bold text-xs">✓ Dipilih</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500">Layak diajukan pemeliharaan / perbaikan.</p>
                                </div>

                                <!-- Card: Rusak Berat -->
                                <div @click="form.kondisi = 'Rusak Berat'"
                                     :class="form.kondisi === 'Rusak Berat' ? 'border-rose-500 bg-rose-50/50 ring-2 ring-rose-500/20 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'"
                                     class="p-3.5 rounded-xl border transition-all cursor-pointer flex flex-col justify-between">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                                            <span class="text-sm font-bold text-slate-800">Rusak Berat</span>
                                        </div>
                                        <span x-show="form.kondisi === 'Rusak Berat'" class="text-rose-600 font-bold text-xs">✓ Dipilih</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500">Tidak dimaintenance (usulan hapus).</p>
                                </div>
                            </div>
                        </div>

                        <!-- Lokasi Ruang -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Lokasi Penempatan Ruangan
                            </label>
                            <input type="text" 
                                   name="lokasi_ruang" 
                                   list="ruangan_datalist" 
                                   x-model="form.lokasi_ruang" 
                                   placeholder="Pilih ruangan atau ketik nama ruangan baru..." 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            <p class="text-[11px] text-slate-400 mt-1">Dapat memilih ruangan dari daftar atau mengetik ruangan baru.</p>
                        </div>

                        <!-- Pengguna & Penghuni -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Pengguna / Penanggung Jawab
                                </label>
                                <input type="text" 
                                       name="pengguna" 
                                       x-model="form.pengguna" 
                                       placeholder="Nama pegawai pemegang aset" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Penghuni (Khusus Rumah / Fasilitas)
                                </label>
                                <input type="text" 
                                       name="penghuni" 
                                       x-model="form.penghuni" 
                                       placeholder="Nama penghuni jika ada" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: NILAI FINANSIAL & LEGALITAS -->
                    <div x-show="activeFormTab === 'finansial'" class="space-y-4">
                        <!-- Tanggal Perolehan & Umur Aset -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Tanggal Perolehan
                                </label>
                                <input type="date" 
                                       name="tanggal_perolehan" 
                                       x-model="form.tanggal_perolehan" 
                                       @change="calculateUmurAset()" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Umur Aset (Tahun)
                                </label>
                                <input type="number" 
                                       name="umur_aset" 
                                       x-model="form.umur_aset" 
                                       min="0" 
                                       placeholder="Otomatis dihitung / isi manual" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                        </div>

                        <!-- Nilai Finansial (Perolehan, Buku, Penyusutan) -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Nilai Perolehan (Rp)
                                </label>
                                <input type="text" 
                                       name="nilai_perolehan" 
                                       x-model="form.nilai_perolehan" 
                                       placeholder="Contoh: 15000000" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Nilai Buku (Rp)
                                </label>
                                <input type="text" 
                                       name="nilai_buku" 
                                       x-model="form.nilai_buku" 
                                       placeholder="Contoh: 5000000" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Nilai Penyusutan (Rp)
                                </label>
                                <input type="text" 
                                       name="nilai_penyusutan" 
                                       x-model="form.nilai_penyusutan" 
                                       placeholder="Contoh: 10000000" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                        </div>

                        <!-- Kode Satker & Nama Satker -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kode Satker</label>
                                <input type="text" 
                                       name="kode_satker" 
                                       x-model="form.kode_satker" 
                                       placeholder="Contoh: 0540101" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Satker</label>
                                <input type="text" 
                                       name="nama_satker" 
                                       x-model="form.nama_satker" 
                                       placeholder="Contoh: BPS Provinsi Kalimantan Timur" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                        </div>

                        <!-- No PSP & Tanggal PSP -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. Surat Keputusan PSP</label>
                                <input type="text" 
                                       name="no_psp" 
                                       x-model="form.no_psp" 
                                       placeholder="Nomor SK PSP BMN" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal PSP</label>
                                <input type="date" 
                                       name="tanggal_psp" 
                                       x-model="form.tanggal_psp" 
                                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-hidden focus:border-[#094886] focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all">
                            </div>
                        </div>

                        <!-- Status SBSN, BMN Idle & Usul Hapus -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status SBSN</label>
                                <select name="status_sbsn" x-model="form.status_sbsn" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-hidden focus:border-[#094886] focus:bg-white cursor-pointer">
                                    <option value="">- Belum ditentukan -</option>
                                    <option value="Non SBSN">Non SBSN</option>
                                    <option value="SBSN">SBSN</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status BMN Idle</label>
                                <select name="status_bmn_idle" x-model="form.status_bmn_idle" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-hidden focus:border-[#094886] focus:bg-white cursor-pointer">
                                    <option value="">- Belum ditentukan -</option>
                                    <option value="Tidak">Tidak</option>
                                    <option value="Ya">Ya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Usul Hapus</label>
                                <select name="usul_hapus" x-model="form.usul_hapus" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm text-slate-800 focus:outline-hidden focus:border-[#094886] focus:bg-white cursor-pointer">
                                    <option value="">- Belum ditentukan -</option>
                                    <option value="Tidak">Tidak</option>
                                    <option value="Ya">Ya</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer Actions -->
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/80 flex items-center justify-between gap-3">
                    <div>
                        <button type="button" 
                                @click="closeFormModal()" 
                                :disabled="isSubmittingForm"
                                class="px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-200/60 rounded-xl transition cursor-pointer disabled:opacity-50">
                            Batal
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Navigation between tabs -->
                        {{-- <template x-if="activeFormTab !== 'identitas'">
                            <button type="button" 
                                    @click="prevTab()" 
                                    class="px-3.5 py-2 text-xs sm:text-sm font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl transition cursor-pointer">
                                Sebelumnya
                            </button>
                        </template>

                        <template x-if="activeFormTab !== 'finansial'">
                            <button type="button" 
                                    @click="nextTab()" 
                                    class="px-3.5 py-2 text-xs sm:text-sm font-semibold text-[#094886] bg-blue-50 border border-blue-200 hover:bg-blue-100 rounded-xl transition cursor-pointer">
                                Selanjutnya
                            </button>
                        </template> --}}

                        <!-- Submit Button -->
                        <button type="submit" 
                                :disabled="isSubmittingForm"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-[#094886] hover:bg-[#0c3167] rounded-xl transition cursor-pointer shadow-xs hover:shadow disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="isSubmittingForm">
                                <svg class="animate-spin w-4 h-4 text-white" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                            <span x-text="isSubmittingForm ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Simpan Aset Baru')"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Datalist for categories -->
    <datalist id="kategori_datalist">
        @foreach($kategoriList as $kat)
            <option value="{{ $kat }}"></option>
        @endforeach
    </datalist>

    <!-- Datalist for rooms -->
    <datalist id="ruangan_datalist">
        @foreach($ruanganList as $rg)
            @if(!empty($rg['cleaned']) && $rg['cleaned'] !== 'Belum berlokasi')
                <option value="{{ $rg['cleaned'] }}"></option>
            @endif
        @endforeach
    </datalist>
</div>

<script>
    function asetPage() {
        return {
            selectedRows: [],
            detailModalOpen: false,
            activeAset: {},
            showImportModal: false,
            importMode: 'safe_update',
            fileName: '',
            fileSize: '',
            fileExt: '',
            isDragging: false,
            isSubmitting: false,

            // Manual Add / Edit Form State
            formModalOpen: {{ $errors->any() ? 'true' : 'false' }},
            isEdit: {{ old('_is_edit') === '1' ? 'true' : 'false' }},
            activeFormTab: 'identitas',
            isSubmittingForm: false,
            form: {
                id: '{{ old('_aset_id', '') }}',
                kode_barang: '{{ old('kode_barang', '') }}',
                nup: '{{ old('nup', '') }}',
                nama_barang: '{{ old('nama_barang', '') }}',
                merk: '{{ old('merk', '') }}',
                tipe: '{{ old('tipe', '') }}',
                kondisi: '{{ old('kondisi', 'Baik') }}',
                jenis_bmn: '{{ old('jenis_bmn', '') }}',
                lokasi_ruang: '{{ old('lokasi_ruang', '') }}',
                pengguna: '{{ old('pengguna', '') }}',
                penghuni: '{{ old('penghuni', '') }}',
                tanggal_perolehan: '{{ old('tanggal_perolehan', '') }}',
                umur_aset: '{{ old('umur_aset', '') }}',
                nilai_perolehan: '{{ old('nilai_perolehan', '') }}',
                nilai_buku: '{{ old('nilai_buku', '') }}',
                nilai_penyusutan: '{{ old('nilai_penyusutan', '') }}',
                kode_satker: '{{ old('kode_satker', '') }}',
                nama_satker: '{{ old('nama_satker', '') }}',
                no_psp: '{{ old('no_psp', '') }}',
                tanggal_psp: '{{ old('tanggal_psp', '') }}',
                status_sbsn: '{{ old('status_sbsn', '') }}',
                status_bmn_idle: '{{ old('status_bmn_idle', '') }}',
                usul_hapus: '{{ old('usul_hapus', '') }}'
            },

            openCreateModal() {
                this.isEdit = false;
                this.activeFormTab = 'identitas';
                this.isSubmittingForm = false;
                this.form = {
                    id: '',
                    kode_barang: '',
                    nup: '',
                    nama_barang: '',
                    merk: '',
                    tipe: '',
                    kondisi: 'Baik',
                    jenis_bmn: '',
                    lokasi_ruang: '',
                    pengguna: '',
                    penghuni: '',
                    tanggal_perolehan: '',
                    umur_aset: '',
                    nilai_perolehan: '',
                    nilai_buku: '',
                    nilai_penyusutan: '',
                    kode_satker: '',
                    nama_satker: '',
                    no_psp: '',
                    tanggal_psp: '',
                    status_sbsn: '',
                    status_bmn_idle: '',
                    usul_hapus: ''
                };
                this.formModalOpen = true;
            },

            openEditModal(id) {
                fetch(`{{ url('/aset') }}/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        this.isEdit = true;
                        this.activeFormTab = 'identitas';
                        this.isSubmittingForm = false;
                        this.form = {
                            id: data.id,
                            kode_barang: data.raw_kode_barang || (data.kode_barang !== '-' ? data.kode_barang : '') || '',
                            nup: data.raw_nup || (data.nup !== '-' ? data.nup : '') || '',
                            nama_barang: data.raw_nama_barang || (data.nama_barang !== '-' ? data.nama_barang : '') || '',
                            merk: data.raw_merk || (data.merk !== '-' ? data.merk : '') || '',
                            tipe: data.raw_tipe || (data.tipe !== '-' ? data.tipe : '') || '',
                            kondisi: data.raw_kondisi || data.kondisi || 'Baik',
                            jenis_bmn: data.raw_jenis_bmn || (data.jenis_bmn !== '-' ? data.jenis_bmn : '') || '',
                            lokasi_ruang: data.raw_lokasi_ruang || (data.lokasi_ruang !== 'Belum berlokasi' ? data.lokasi_ruang : '') || '',
                            pengguna: data.raw_pengguna || (data.pengguna !== '-' ? data.pengguna : '') || '',
                            penghuni: data.raw_penghuni || (data.penghuni !== '-' ? data.penghuni : '') || '',
                            tanggal_perolehan: data.raw_tanggal_perolehan || '',
                            umur_aset: data.raw_umur_aset !== null && data.raw_umur_aset !== undefined ? data.raw_umur_aset : '',
                            nilai_perolehan: data.raw_nilai_perolehan !== '' && data.raw_nilai_perolehan !== null ? data.raw_nilai_perolehan : '',
                            nilai_buku: data.raw_nilai_buku !== '' && data.raw_nilai_buku !== null ? data.raw_nilai_buku : '',
                            nilai_penyusutan: data.raw_nilai_penyusutan !== '' && data.raw_nilai_penyusutan !== null ? data.raw_nilai_penyusutan : '',
                            kode_satker: data.raw_kode_satker || (data.kode_satker !== '-' ? data.kode_satker : '') || '',
                            nama_satker: data.raw_nama_satker || (data.nama_satker !== '-' ? data.nama_satker : '') || '',
                            no_psp: data.raw_no_psp || (data.no_psp !== '-' ? data.no_psp : '') || '',
                            tanggal_psp: data.raw_tanggal_psp || '',
                            status_sbsn: data.raw_status_sbsn || (data.status_sbsn !== '-' ? data.status_sbsn : '') || '',
                            status_bmn_idle: data.raw_status_bmn_idle || (data.status_bmn_idle !== '-' ? data.status_bmn_idle : '') || '',
                            usul_hapus: data.raw_usul_hapus || (data.usul_hapus !== '-' ? data.usul_hapus : '') || ''
                        };
                        this.formModalOpen = true;
                    })
                    .catch(err => {
                        console.error('Error fetching asset detail for edit:', err);
                        alert('Gagal mengambil data aset untuk diedit.');
                    });
            },

            closeFormModal() {
                this.formModalOpen = false;
                this.isSubmittingForm = false;
            },

            prevTab() {
                if (this.activeFormTab === 'finansial') {
                    this.activeFormTab = 'kondisi';
                } else if (this.activeFormTab === 'kondisi') {
                    this.activeFormTab = 'identitas';
                }
            },

            nextTab() {
                if (this.activeFormTab === 'identitas') {
                    this.activeFormTab = 'kondisi';
                } else if (this.activeFormTab === 'kondisi') {
                    this.activeFormTab = 'finansial';
                }
            },

            calculateUmurAset() {
                if (this.form.tanggal_perolehan) {
                    const dt = new Date(this.form.tanggal_perolehan);
                    const now = new Date();
                    let diffYears = now.getFullYear() - dt.getFullYear();
                    const m = now.getMonth() - dt.getMonth();
                    if (m < 0 || (m === 0 && now.getDate() < dt.getDate())) {
                        diffYears--;
                    }
                    if (diffYears >= 0) {
                        this.form.umur_aset = diffYears;
                    }
                }
            },

            toggleSelectAll(checked) {
                if (checked) {
                    const rowCheckboxes = document.querySelectorAll('tbody input[type="checkbox"]');
                    this.selectedRows = Array.from(rowCheckboxes).map(cb => parseInt(cb.value));
                } else {
                    this.selectedRows = [];
                }
            },

            viewDetail(id) {
                fetch(`{{ url('/aset') }}/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        this.activeAset = data;
                        this.detailModalOpen = true;
                    })
                    .catch(err => {
                        console.error('Error fetching asset detail:', err);
                    });
            },

            closeDetailModal() {
                this.detailModalOpen = false;
                this.activeAset = {};
            },

            handleFileSelect(event) {
                const files = event.target.files;
                if (files && files[0]) {
                    this.setFile(files[0]);
                }
            },

            handleFileDrop(event) {
                this.isDragging = false;
                const files = event.dataTransfer.files;
                if (files && files[0]) {
                    this.$refs.fileInput.files = files;
                    this.setFile(files[0]);
                }
            },

            setFile(file) {
                this.fileName = file.name;
                const ext = file.name.split('.').pop().toLowerCase();
                this.fileExt = ext;
                const sizeKb = (file.size / 1024).toFixed(1);
                this.fileSize = sizeKb > 1024 ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';
            },

            clearFile() {
                this.fileName = '';
                this.fileSize = '';
                this.fileExt = '';
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },

            closeImportModal() {
                this.showImportModal = false;
                this.isDragging = false;
                this.clearFile();
            }
        };
    }

    function updateQueryStringParameter(uri, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        }
        else {
            return uri + separator + key + "=" + value;
        }
    }
</script>
@endsection
