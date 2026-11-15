@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 tracking-tight">Daftar Permintaan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola dan pantau seluruh permohonan pemeliharaan aset (Maintenance) dan persediaan barang (Supply).</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
            @if(!\App\Services\SatkerService::isViewingOtherSatker())
                <!-- Tambah Maintenance Button -->
                <a href="{{ route('permintaan.create', ['type' => 'maintenance']) }}" 
                   class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2.5 bg-[#094886] hover:bg-[#073868] text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs hover:shadow-sm transition-all duration-150 cursor-pointer text-center"
                   title="Buat Permintaan Pemeliharaan Aset BMN">
                    <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                    </svg>
                    <span class="truncate">Permintaan Maintenance</span>
                </a>

                <!-- Tambah Supply Button -->
                <a href="{{ route('permintaan.create', ['type' => 'supply']) }}" 
                   class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs hover:shadow-sm transition-all duration-150 cursor-pointer text-center"
                   title="Buat Permintaan Persediaan Barang Habis Pakai">
                    <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span class="truncate">Permintaan Supply</span>
                </a>
            @else
                <div class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-50 text-amber-800 text-xs font-semibold rounded-xl border border-amber-200">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>Mode Lihat Saja (Penambahan Dinonaktifkan)</span>
                </div>
            @endif
        </div>
    </div>


    <!-- Filter Card with Search Bar -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
        <form method="GET" action="{{ route('permintaan.index') }}">
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-0 md:min-w-[240px]">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari pekerjaan, permohonan, atau deskripsi..."
                           class="w-full bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg pl-10 pr-9 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    @if($search)
                    <a href="{{ route('permintaan.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:flex items-center gap-3">
                    <!-- Jenis Dropdown -->
                    <div class="relative w-full sm:w-auto min-w-0 sm:min-w-[170px]">
                        <select name="jenis" onchange="this.form.submit()" class="w-full appearance-none bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg px-3.5 py-2.5 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                            <option value="">Semua Jenis</option>
                            <option value="Maintenance" {{ $jenis === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="Supply" {{ $jenis === 'Supply' ? 'selected' : '' }}>Supply</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>

                    <!-- Date Filter Input -->
                    <div class="relative w-full sm:w-auto min-w-0 sm:min-w-[170px]">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <input type="date" 
                               name="date"
                               value="{{ $date }}"
                               onchange="this.form.submit()"
                               class="w-full bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg pl-10 pr-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                    </div>

                    <button type="submit" class="px-4 py-2.5 bg-[#094886] hover:bg-[#073868] text-white text-sm font-semibold rounded-lg transition-colors cursor-pointer">
                        Filter
                    </button>
                </div>
            </div>

            <!-- Active Filters -->
            @if($search || $jenis || $date)
            <div class="flex flex-wrap items-center gap-2 pt-3 mt-3 border-t border-slate-100 text-xs">
                <span class="text-slate-400 font-medium">Filter Aktif:</span>
                @if($search)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-[#094886] font-medium border border-blue-200/60">
                        Pencarian: "{{ $search }}"
                        <a href="{{ route('permintaan.index', request()->except('search')) }}" class="hover:text-blue-900">✕</a>
                    </span>
                @endif
                @if($jenis)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-[#094886] font-medium border border-blue-200/60">
                        Jenis: {{ $jenis }}
                        <a href="{{ route('permintaan.index', request()->except('jenis')) }}" class="hover:text-blue-900">✕</a>
                    </span>
                @endif
                @if($date)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-[#094886] font-medium border border-blue-200/60">
                        Tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                        <a href="{{ route('permintaan.index', request()->except('date')) }}" class="hover:text-blue-900">✕</a>
                    </span>
                @endif
                <a href="{{ route('permintaan.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold underline ml-1">
                    Reset Filter
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left border-collapse">
                <thead>
                    <tr class="bg-[#edf2f9]/70 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-6 text-[11px] font-bold text-slate-600 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-3.5 px-6 text-[11px] font-bold text-slate-600 uppercase tracking-wider">PEKERJAAN / PERMOHONAN</th>
                        <th class="py-3.5 px-6 text-[11px] font-bold text-slate-600 uppercase tracking-wider text-center">JENIS</th>
                        <th class="py-3.5 px-6 text-[11px] font-bold text-slate-600 uppercase tracking-wider">TANGGAL PERMINTAAn</th>
                        <th class="py-3.5 px-6 text-[11px] font-bold text-slate-600 uppercase tracking-wider text-center">TOTAL RAB</th>
                        <th class="py-3.5 px-6 text-[11px] font-bold text-slate-600 uppercase tracking-wider text-right pr-8">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($permintaans as $index => $item)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <!-- No -->
                        <td class="py-4 px-6 text-center text-slate-500 font-medium">
                            {{ $permintaans->firstItem() + $index }}
                        </td>
                        <!-- Pekerjaan -->
                        <td class="py-4 px-6 font-medium text-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900">{{ $item->pekerjaan }}</span>
                            </div>
                        </td>
                        <!-- Jenis Badge -->
                        <td class="py-4 px-6 text-center">
                            @if(strtolower($item->jenis) === 'supply')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                    <span>Supply</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-[#094886] border border-blue-200/80">
                                    <svg class="w-3.5 h-3.5 text-[#094886]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                                    <span>Maintenance</span>
                                </span>
                            @endif
                        </td>
                        <!-- Tanggal Permintaan -->
                        <td class="py-4 px-6 text-slate-600 text-xs font-medium">
                            {{ $item->tanggal ? $item->tanggal->translatedFormat('d M Y') : ($item->created_at ? $item->created_at->translatedFormat('d M Y') : '-') }}
                        </td>
                        <!-- Total RAB (Rp) -->
                        <td class="py-4 px-6 text-center font-bold text-slate-900">
                            Rp {{ number_format((float)$item->total_rab, 0, ',', '.') }}
                        </td>
                        <!-- Aksi -->
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <!-- View Icon -->
                                <a href="{{ route('permintaan.show', $item->id) }}" 
                                   class="p-1.5 text-slate-500 hover:text-blue-600 transition-colors rounded-md hover:bg-blue-50" title="Lihat Detail Permintaan">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>

                                <!-- KAK Document Icon -->
                                <a href="{{ route('permintaan.kak', $item->id) }}" 
                                   class="p-1.5 text-slate-500 hover:text-[#094886] transition-colors rounded-md hover:bg-blue-50" title="Generate / Cetak Dokumen KAK">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                    </svg>
                                </a>

                                {{-- BAST Document Icon (Khusus Supply) --}}
                                @if($item->isSupply())
                                <a href="{{ route('permintaan.bast', $item->id) }}" 
                                   class="p-1.5 text-slate-500 hover:text-emerald-700 transition-colors rounded-md hover:bg-emerald-50" title="Sesuaikan & Unduh Dokumen BAST">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 11l3 3L22 4"></path>
                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                    </svg>
                                </a>
                                @endif

                                @if(!\App\Services\SatkerService::isViewingOtherSatker())
                                <!-- Edit Icon -->
                                <a href="{{ route('permintaan.edit', $item->id) }}" class="p-1.5 text-slate-500 hover:text-amber-600 transition-colors rounded-md hover:bg-amber-50" title="Edit Data Permintaan">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                    </svg>
                                </a>

                                <!-- Delete Icon -->
                                <button type="button" 
                                        onclick="openDeleteModal('{{ $item->id }}', '{{ addslashes($item->pekerjaan) }}', '{{ $item->jenis }}')"
                                        class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-md transition-colors cursor-pointer" title="Hapus Permintaan">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>

                                <!-- Tombol Proses -->
                                <button type="button" 
                                        onclick="openProsesModal('{{ $item->id }}', '{{ addslashes($item->pekerjaan) }}', '{{ $item->jenis }}')"
                                        class="ml-1 inline-flex items-center px-3.5 py-1.5 bg-[#1b7a37] hover:bg-[#15652d] text-white text-xs font-semibold rounded-lg shadow-xs transition-colors cursor-pointer">
                                    Proses
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <div class="w-12 h-12 mx-auto mb-3 text-slate-300">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <p class="text-base font-semibold text-slate-700">Tidak ada data permintaan yang ditemukan</p>
                            <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter jenis.</p>
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
                Menampilkan <strong>{{ $permintaans->firstItem() ?? 0 }}</strong> - <strong>{{ $permintaans->lastItem() ?? 0 }}</strong> dari <strong>{{ number_format($permintaans->total(), 0, ',', '.') }}</strong> data Permintaan
            </div>

            <!-- Custom Clean Pagination Navigation & Per Page Selector -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Per Page selector -->
                <div class="flex items-center gap-2 text-xs text-slate-600 sm:border-r sm:border-slate-200 sm:pr-3">
                    <label for="per_page_select" class="text-slate-400 whitespace-nowrap">Tampilkan per halaman:</label>
                    <select id="per_page_select" 
                            onchange="window.location.href = updateQueryStringParameter(window.location.href, 'per_page', this.value)"
                            class="bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-xs rounded-md px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer">
                        <option value="10" {{ $permintaans->perPage() == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ $permintaans->perPage() == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $permintaans->perPage() == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $permintaans->perPage() == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="flex items-center gap-1">
                    @if ($permintaans->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center text-slate-300 bg-slate-50 rounded-lg cursor-not-allowed border border-slate-200/60" title="Sebelumnya">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </span>
                    @else
                        <a href="{{ $permintaans->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 rounded-lg border border-slate-300 transition-colors shadow-2xs" title="Sebelumnya">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </a>
                    @endif

                    <!-- Compact page numbers -->
                    @php
                        $currentPage = $permintaans->currentPage();
                        $lastPage = $permintaans->lastPage();
                        $start = max(1, $currentPage - 1);
                        $end = min($lastPage, $currentPage + 1);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $permintaans->url(1) }}" class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-300">1</a>
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
                            <a href="{{ $permintaans->url($p) }}" class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-300 transition-colors">
                                {{ $p }}
                            </a>
                        @endif
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="px-1 text-slate-400">...</span>
                        @endif
                        <a href="{{ $permintaans->url($lastPage) }}" class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-300">{{ $lastPage }}</a>
                    @endif

                    @if ($permintaans->hasMorePages())
                        <a href="{{ $permintaans->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 rounded-lg border border-slate-300 transition-colors shadow-2xs" title="Selanjutnya">
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

    <!-- Modal Proses Realisasi (Single Clean Modal Container) -->
    <div id="modalProses" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center z-50 p-4 hidden">
        <!-- Panel 1: Input Data Realisasi -->
        <div id="panelInputProses" class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden transition-all">
            <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900" id="modalProsesTitle">Input Data Realisasi</h3>
                    <p class="text-xs text-slate-500 mt-0.5" id="prosesAssetDesc">Lengkapi informasi FPA dan vendor sebelum proses dimulai.</p>
                </div>
                <button type="button" onclick="closeProsesModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 cursor-pointer">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="formProses" method="POST" action="" class="p-5 sm:p-6 space-y-4">
                @csrf
                <div>
                    <label id="labelProsesFpa" class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nomor FPA (Dari BOS) <span class="text-rose-500">*</span></label>
                    <input type="text" id="inputProsesFpa" name="no_fpa" placeholder="FPA/BPS/2023/10/..." class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>
                <div>
                    <label id="labelProsesVendor" class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nama Vendor / Pelaksana <span class="text-rose-500">*</span></label>
                    <input type="text" id="inputProsesVendor" name="vendor" placeholder="CV. Teknik Sejahtera" class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>
                <div>
                    <label id="labelProsesTanggal" class="block text-xs font-semibold text-slate-700 uppercase mb-1">Mulai Pengerjaan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_mulai" class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                    <span id="helpProsesTanggal" class="block text-[11px] text-slate-400 mt-1 hidden">Tanggal mulai pemesanan / pembelian persediaan</span>
                </div>
                
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeProsesModal()" class="px-4 py-2.5 border border-slate-300 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-semibold transition-colors cursor-pointer">Batal</button>
                    <button type="button" id="btnLanjutProses" onclick="triggerConfirmProses()" class="px-5 py-2.5 bg-[#1b7a37] hover:bg-[#15652d] text-white rounded-xl text-xs font-bold shadow-sm transition-all cursor-pointer">Lanjutkan Proses</button>
                </div>
            </form>
        </div>

        <!-- Panel 2: Konfirmasi Data Realisasi -->
        <div id="panelConfirmProses" class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-5 hidden transition-all">
            <div class="text-center">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl mx-auto flex items-center justify-center shadow-xs border border-emerald-100 mb-3.5">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 11 12 14 22 4"></polyline>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                </div>

                <h3 class="text-lg font-bold text-slate-900" id="confirmProsesTitle">Konfirmasi Proses Realisasi</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed" id="confirmProsesDesc">
                    Apakah Anda yakin ingin memproses permintaan ini ke tahap realisasi pemeliharaan?
                </p>
            </div>

            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 space-y-2.5 text-xs">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium" id="confirmLabelFpa">Nomor FPA:</span>
                    <span id="confirmProsesFpa" class="text-slate-800 font-semibold font-mono"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium" id="confirmLabelVendor">Vendor Pelaksana:</span>
                    <span id="confirmProsesVendor" class="text-slate-800 font-semibold"></span>
                </div>
                <div class="flex justify-between items-center pt-2.5 border-t border-slate-200/80">
                    <span class="text-slate-700 font-bold" id="confirmLabelTanggal">Tanggal Mulai:</span>
                    <span id="confirmProsesTanggal" class="text-[#094886] font-bold"></span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" onclick="backToInputProses()" class="px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                    Periksa Kembali
                </button>
                <button type="button" id="btnSubmitProses" onclick="executeSubmitProses()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#1b7a37] hover:bg-[#15652d] text-white rounded-xl text-xs font-bold shadow-sm transition-all cursor-pointer">
                    <span id="btnSubmitProsesText">Ya, Mulai Proses</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div id="modalDelete" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center z-50 p-4 hidden">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-5">
            <div class="text-center">
                <!-- Warning Icon -->
                <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl mx-auto flex items-center justify-center shadow-xs border border-rose-100 mb-3.5">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                </div>

                <h3 class="text-lg font-bold text-slate-900">Hapus Permintaan?</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Apakah Anda yakin ingin menghapus data permintaan ini? Tindakan ini bersifat permanen dan tidak dapat dibatalkan.
                </p>
            </div>

            <!-- Detail Permintaan to delete -->
            <div class="bg-rose-50/50 border border-rose-100 rounded-xl p-4 space-y-2 text-xs">
                <div class="flex justify-between items-start gap-2">
                    <span class="text-rose-600 font-medium">Pekerjaan:</span>
                    <span id="deleteItemPekerjaan" class="text-slate-800 font-semibold text-right"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-rose-600 font-medium">Kategori:</span>
                    <span id="deleteItemJenis" class="text-slate-800 font-semibold"></span>
                </div>
            </div>

            <!-- Form Action -->
            <form id="formDelete" method="POST" class="flex items-center justify-end gap-2.5 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs transition-colors cursor-pointer">
                    Ya, Hapus Permintaan
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function updateQueryStringParameter(uri, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
        if (uri.match(re)) {
            return uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            return uri + separator + key + "=" + value;
        }
    }

    let currentProsesJenis = 'Maintenance';

    function openProsesModal(id, pekerjaan, jenis) {
        currentProsesJenis = jenis || 'Maintenance';
        document.getElementById('formProses').action = "/permintaan/" + id + "/proses";

        const isSupply = (jenis || '').trim().toLowerCase() === 'supply';

        if (isSupply) {
            document.getElementById('modalProsesTitle').innerText = 'Input Data Pengadaan Persediaan';
            document.getElementById('prosesAssetDesc').innerText = 'Pengadaan: ' + pekerjaan + ' (Supply / Persediaan)';
            document.getElementById('labelProsesFpa').innerHTML = 'Nomor Dokumen FPA / Pengadaan <span class="text-rose-500">*</span>';
            document.getElementById('inputProsesFpa').placeholder = 'FPA/BPS/2026/...';

            document.getElementById('labelProsesVendor').innerHTML = 'Nama Toko / Penyedia Barang <span class="text-rose-500">*</span>';
            document.getElementById('inputProsesVendor').placeholder = 'Contoh: Toko ATK Sejahtera / CV Sumber Kertas';

            document.getElementById('labelProsesTanggal').innerHTML = 'Tanggal Pembelian / Pemesanan <span class="text-rose-500">*</span>';
            document.getElementById('helpProsesTanggal').classList.remove('hidden');
            document.getElementById('btnLanjutProses').innerText = 'Lanjutkan Proses Pengadaan';

            document.getElementById('confirmProsesTitle').innerText = 'Konfirmasi Proses Pengadaan';
            document.getElementById('confirmProsesDesc').innerText = 'Apakah Anda yakin ingin memproses permintaan barang persediaan ini ke tahap pengadaan & realisasi belanja?';
            document.getElementById('confirmLabelFpa').innerText = 'Nomor FPA:';
            document.getElementById('confirmLabelVendor').innerText = 'Toko / Rekanan:';
            document.getElementById('confirmLabelTanggal').innerText = 'Tanggal Pembelian:';
            document.getElementById('btnSubmitProsesText').innerText = 'Ya, Mulai Pengadaan';
        } else {
            // Maintenance (Tetap sesuai aslinya)
            document.getElementById('modalProsesTitle').innerText = 'Input Data Realisasi';
            document.getElementById('prosesAssetDesc').innerText = 'Pekerjaan: ' + pekerjaan + ' (' + jenis + ')';
            document.getElementById('labelProsesFpa').innerHTML = 'Nomor FPA (Dari BOS) <span class="text-rose-500">*</span>';
            document.getElementById('inputProsesFpa').placeholder = 'FPA/BPS/2023/10/...';

            document.getElementById('labelProsesVendor').innerHTML = 'Nama Vendor / Pelaksana <span class="text-rose-500">*</span>';
            document.getElementById('inputProsesVendor').placeholder = 'CV. Teknik Sejahtera';

            document.getElementById('labelProsesTanggal').innerHTML = 'Mulai Pengerjaan <span class="text-rose-500">*</span>';
            document.getElementById('helpProsesTanggal').classList.add('hidden');
            document.getElementById('btnLanjutProses').innerText = 'Lanjutkan Proses';

            document.getElementById('confirmProsesTitle').innerText = 'Konfirmasi Proses Realisasi';
            document.getElementById('confirmProsesDesc').innerText = 'Apakah Anda yakin ingin memproses permintaan ini ke tahap realisasi pemeliharaan?';
            document.getElementById('confirmLabelFpa').innerText = 'Nomor FPA:';
            document.getElementById('confirmLabelVendor').innerText = 'Vendor Pelaksana:';
            document.getElementById('confirmLabelTanggal').innerText = 'Tanggal Mulai:';
            document.getElementById('btnSubmitProsesText').innerText = 'Ya, Mulai Proses';
        }
        
        document.getElementById('panelInputProses').classList.remove('hidden');
        document.getElementById('panelConfirmProses').classList.add('hidden');
        document.getElementById('modalProses').classList.remove('hidden');
    }

    function closeProsesModal() {
        document.getElementById('modalProses').classList.add('hidden');
        document.getElementById('panelInputProses').classList.remove('hidden');
        document.getElementById('panelConfirmProses').classList.add('hidden');
    }

    function triggerConfirmProses() {
        const form = document.getElementById('formProses');
        const fpa = form.elements['no_fpa'].value.trim();
        const vendor = form.elements['vendor'].value.trim();
        const tgl = form.elements['tanggal_mulai'].value;

        if (!fpa || !vendor || !tgl) {
            const isSupply = (currentProsesJenis || '').trim().toLowerCase() === 'supply';
            window.showToast({
                type: 'warning',
                title: 'Data Belum Lengkap',
                message: isSupply
                    ? 'Nomor Dokumen FPA, Nama Toko/Penyedia, dan Tanggal Pembelian wajib diisi sebelum melanjutkan.'
                    : 'Nomor FPA, Nama Vendor, dan Mulai Pengerjaan wajib diisi sebelum melanjutkan.'
            });
            return;
        }

        document.getElementById('confirmProsesFpa').innerText = fpa;
        document.getElementById('confirmProsesVendor').innerText = vendor;
        document.getElementById('confirmProsesTanggal').innerText = tgl;
        
        document.getElementById('panelInputProses').classList.add('hidden');
        document.getElementById('panelConfirmProses').classList.remove('hidden');
    }

    function backToInputProses() {
        document.getElementById('panelConfirmProses').classList.add('hidden');
        document.getElementById('panelInputProses').classList.remove('hidden');
    }

    function executeSubmitProses() {
        const btn = document.getElementById('btnSubmitProses');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            btn.innerHTML = `
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Memproses...</span>
            `;
        }
        document.getElementById('formProses').submit();
    }

    function openDeleteModal(id, pekerjaan, jenis) {
        document.getElementById('deleteItemPekerjaan').innerText = pekerjaan;
        document.getElementById('deleteItemJenis').innerText = jenis;
        document.getElementById('formDelete').action = "/permintaan/" + id;
        document.getElementById('modalDelete').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('modalDelete').classList.add('hidden');
    }
</script>
@endsection
