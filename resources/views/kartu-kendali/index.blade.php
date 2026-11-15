@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <h1 class="text-xl sm:text-2xl md:text-[28px] font-bold text-slate-900 tracking-tight">Kartu Kendali Pemeliharaan</h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#e9f1fa] text-[#094886] border border-blue-200">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                        <line x1="7" y1="8" x2="17" y2="8"></line>
                        <line x1="7" y1="12" x2="13" y2="12"></line>
                    </svg>
                    Format Resmi BPS Gorontalo
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Pilih aset untuk melihat, mencetak, atau mengunduh Kartu Kendali Pemeliharaan dalam format cetak (A4) maupun Microsoft Excel (.xlsx).</p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-5">
        <form method="GET" action="{{ route('kartu-kendali.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? '' }}" 
                       placeholder="Cari berdasarkan nama aset, NUP, kode barang, merk, atau ruangan..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-hidden focus:ring-2 focus:ring-[#094886] focus:bg-white transition-all text-slate-800 placeholder-slate-400">
            </div>

            <div class="flex items-center gap-2">
                <select name="tahun" 
                        class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:outline-hidden focus:ring-2 focus:ring-[#094886] focus:bg-white transition-all">
                    @php
                        $currentYear = (int)date('Y');
                    @endphp
                    <option value="semua" {{ ($tahun ?? '') == 'semua' ? 'selected' : '' }}>Semua Tahun</option>
                    @for($y = $currentYear + 1; $y >= $currentYear - 4; $y--)
                        <option value="{{ $y }}" {{ ($tahun ?? $currentYear) == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>

                <button type="submit" 
                        class="px-5 py-2.5 bg-[#094886] hover:bg-[#073868] text-white text-sm font-semibold rounded-xl shadow-xs transition-colors cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <span>Filter</span>
                </button>

                @if($search || ($tahun && $tahun != date('Y')))
                    <a href="{{ route('kartu-kendali.index') }}" 
                       class="p-2.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors"
                       title="Reset Filter">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Asset Table -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#edf2f9]/70 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-5">KODE & NUP</th>
                        <th class="py-3.5 px-5">NAMA BARANG / ASET</th>
                        <th class="py-3.5 px-5 whitespace-nowrap">TANGGAL PERMINTAAN</th>
                        <th class="py-3.5 px-5">LOKASI & RUANGAN</th>
                        <th class="py-3.5 px-5 text-center">PEMELIHARAAN</th>
                        <th class="py-3.5 px-5 text-right">AKSI KARTU KENDALI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($asets as $aset)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <!-- Kode & NUP -->
                            <td class="py-3.5 px-5 whitespace-nowrap">
                                <span class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-1 rounded">
                                    {{ $aset->clean_kode_barang }}
                                </span>
                                <div class="text-[11px] text-slate-500 font-mono mt-1">
                                    NUP: <span class="font-bold text-slate-800">{{ $aset->clean_nup }}</span>
                                </div>
                            </td>

                            <!-- Nama Barang -->
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-slate-900">{{ $aset->nama_barang }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    @if($aset->merk || $aset->tipe)
                                        <span class="text-slate-600 font-medium">{{ trim(($aset->merk ?? '') . ' ' . ($aset->tipe ?? '')) }} &bull;</span>
                                    @endif
                                    Perolehan: {{ $aset->tanggal_perolehan ? $aset->tanggal_perolehan->format('Y') : '-' }} &bull; Kondisi: {{ $aset->kondisi ?? 'Baik' }}
                                </div>
                            </td>

                            <!-- Tanggal Permintaan -->
                            <td class="py-3.5 px-5 text-slate-600 text-xs whitespace-nowrap">
                                @if($aset->latest_tanggal_permintaan)
                                    <div class="font-semibold text-slate-800">{{ $aset->latest_tanggal_permintaan }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- Lokasi & Ruangan -->
                            <td class="py-3.5 px-5 text-slate-600 text-xs">
                                <div class="font-medium text-slate-800">{{ $aset->clean_lokasi_ruang ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $aset->alamat ?: 'Lantai 1' }}</div>
                            </td>

                            <!-- Pemeliharaan Count -->
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                @if($aset->realisasi_asets_count > 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        {{ $aset->realisasi_asets_count }} Riwayat Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500">
                                        Belum Ada Riwayat
                                    </span>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <!-- View & Print Button -->
                                    <a href="{{ route('kartu-kendali.show', [$aset->id, 'tahun' => $tahun]) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#094886] hover:bg-[#073868] text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors cursor-pointer"
                                       title="Lihat & Cetak Kartu Kendali">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                        </svg>
                                        <span>Lihat</span>
                                    </a>

                                    <!-- Direct PDF Download Button -->
                                    <a href="{{ route('kartu-kendali.pdf', [$aset->id, 'tahun' => $tahun, 'download' => 1]) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors cursor-pointer"
                                       title="Unduh PDF Kartu Kendali">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                        <span>PDF</span>
                                    </a>

                                    <!-- Excel Download Button -->
                                    <a href="{{ route('kartu-kendali.export', [$aset->id, 'tahun' => $tahun]) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition-colors cursor-pointer"
                                       title="Unduh Microsoft Excel (.xlsx)">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="8" y1="13" x2="16" y2="17"></line>
                                            <line x1="16" y1="13" x2="8" y2="17"></line>
                                        </svg>
                                        <span>Excel</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <svg class="w-12 h-12 mx-auto text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                                        <line x1="7" y1="8" x2="17" y2="8"></line>
                                        <line x1="7" y1="12" x2="13" y2="12"></line>
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-600">Tidak ada data aset ditemukan</p>
                                    <p class="text-xs text-slate-400">Silakan ubah kata kunci pencarian atau reset filter.</p>
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
                Menampilkan <strong>{{ $asets->firstItem() ?? 0 }}</strong> - <strong>{{ $asets->lastItem() ?? 0 }}</strong> dari <strong>{{ number_format($asets->total(), 0, ',', '.') }}</strong> data Kartu Kendali
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
</div>

<script>
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
