@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div>
        <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 tracking-tight">Daftar Realisasi</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola dan pantau realisasi pemeliharaan dan pengadaan aset BPS.</p>
    </div>

    <!-- Status Tabs Nav -->
    <div class="border-b border-slate-200 overflow-x-auto overflow-y-hidden [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
        <nav class="-mb-px flex space-x-4 sm:space-x-6 min-w-max">
            <a href="{{ route('realisasi.index', array_merge(request()->except(['status', 'page']), ['status' => 'siap realisasi'])) }}" 
               class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm transition-colors cursor-pointer flex items-center gap-1.5 {{ $status === 'siap realisasi' ? 'border-[#094886] text-[#094886]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                <span>Siap Realisasi</span>
                <span class="px-2 py-0.5 text-xs rounded-full {{ $status === 'siap realisasi' ? 'bg-blue-100 text-[#094886]' : 'bg-slate-100 text-slate-600' }}">
                    {{ number_format($countSiapRealisasi, 0, ',', '.') }}
                </span>
            </a>
            <a href="{{ route('realisasi.index', array_merge(request()->except(['status', 'page']), ['status' => 'Selesai'])) }}" 
               class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm transition-colors cursor-pointer flex items-center gap-1.5 {{ $status === 'Selesai' ? 'border-[#094886] text-[#094886]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                <span>Selesai</span>
                <span class="px-2 py-0.5 text-xs rounded-full {{ $status === 'Selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ number_format($countSelesai, 0, ',', '.') }}
                </span>
            </a>
            <a href="{{ route('realisasi.index', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}" 
               class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm transition-colors cursor-pointer flex items-center gap-1.5 {{ $status === 'all' || $status === '' ? 'border-[#094886] text-[#094886]' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                <span>Semua Realisasi</span>
                <span class="px-2 py-0.5 text-xs rounded-full {{ $status === 'all' || $status === '' ? 'bg-slate-200 text-slate-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ number_format($countSemua, 0, ',', '.') }}
                </span>
            </a>
        </nav>
    </div>

    <!-- Filter Card with Search Bar -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-xs">
        <form method="GET" action="{{ route('realisasi.index') }}">
            <input type="hidden" name="status" value="{{ $status }}">
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
                           placeholder="Cari pekerjaan, nomor FPA, vendor..."
                           class="w-full bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm rounded-lg pl-10 pr-9 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    @if($search)
                    <a href="{{ route('realisasi.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
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
                        <a href="{{ route('realisasi.index', request()->except('search')) }}" class="hover:text-blue-900">✕</a>
                    </span>
                @endif
                @if($jenis)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-[#094886] font-medium border border-blue-200/60">
                        Jenis: {{ $jenis }}
                        <a href="{{ route('realisasi.index', request()->except('jenis')) }}" class="hover:text-blue-900">✕</a>
                    </span>
                @endif
                @if($date)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-[#094886] font-medium border border-blue-200/60">
                        Tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                        <a href="{{ route('realisasi.index', request()->except('date')) }}" class="hover:text-blue-900">✕</a>
                    </span>
                @endif
                <a href="{{ route('realisasi.index', ['status' => $status]) }}" class="text-blue-600 hover:text-blue-800 font-semibold underline ml-1">
                    Reset Filter
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px] text-left border-collapse">
                <thead>
                    <tr class="bg-[#edf2f9]/70 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3.5 px-5 text-[11px] font-bold text-slate-600 uppercase tracking-wider w-12 text-center">NO</th>
                        <th class="py-3.5 px-5 text-[11px] font-bold text-slate-600 uppercase tracking-wider">PEKERJAAN</th>
                        <th class="py-3.5 px-5 text-[11px] font-bold text-slate-600 uppercase tracking-wider">NO FPA</th>
                        <th class="py-3.5 px-5 text-[11px] font-bold text-slate-600 uppercase tracking-wider">JENIS</th>
                        <th class="py-3.5 px-5 text-[11px] font-bold text-slate-600 uppercase tracking-wider">TANGGAL PENGAJUAN</th>
                        <th class="py-3.5 px-5 text-[11px] font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">TANGGAL MULAI</th>
                        <th class="py-3.5 px-5 text-[11px] font-bold text-slate-600 uppercase tracking-wider whitespace-nowrap">TANGGAL SELESAI</th>
                        <th class="py-3.5 px-5 text-[11px] font-bold text-slate-600 uppercase tracking-wider text-center">TOTAL RINCIAN (RP)</th>
                        <th class="py-3.5 px-5 text-[11px] font-bold text-slate-600 uppercase tracking-wider text-center">STATUS</th>
                        <th class="py-3.5 px-5 text-[11px] font-bold text-slate-600 uppercase tracking-wider text-right pr-6">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($realisasis as $index => $item)
                    @php
                        $tglPengajuan = $item->tanggal_pengajuan ? $item->tanggal_pengajuan->translatedFormat('d M Y') : ($item->created_at ? $item->created_at->translatedFormat('d M Y') : '-');
                        $tglMulai = $item->tanggal_mulai ? $item->tanggal_mulai->translatedFormat('d M Y') : '-';
                        $tglSelesai = $item->tanggal_selesai ? $item->tanggal_selesai->translatedFormat('d M Y') : '-';
                        $totalRincian = (float)$item->total_realisasi > 0 ? (float)$item->total_realisasi : ($item->permintaan ? (float)$item->permintaan->total_rab : 0);
                    @endphp
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <!-- No -->
                        <td class="py-4 px-5 text-center text-slate-500 font-medium">
                            {{ $realisasis->firstItem() + $index }}
                        </td>
                        <!-- Pekerjaan -->
                        <td class="py-4 px-5 font-medium text-slate-700">
                            {{ $item->pekerjaan }}
                        </td>
                        <!-- No FPA -->
                        <td class="py-4 px-5 text-slate-600 font-mono text-xs">
                            {{ $item->no_fpa ?? '-' }}
                        </td>
                        <!-- Jenis -->
                        <td class="py-4 px-5 whitespace-nowrap">
                            @if($item->jenis === 'Supply')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Supply
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    Maintenance
                                </span>
                            @endif
                        </td>
                        <!-- Tanggal Pengajuan -->
                        <td class="py-4 px-5 text-slate-600 whitespace-nowrap text-xs">
                            {{ $tglPengajuan }}
                        </td>
                        <!-- Tanggal Mulai -->
                        <td class="py-4 px-5 text-slate-600 text-xs whitespace-nowrap">
                            {{ $tglMulai }}
                        </td>
                        <!-- Tanggal Selesai -->
                        <td class="py-4 px-5 text-slate-600 text-xs whitespace-nowrap">
                            @if($tglSelesai !== '-')
                                <span class="font-medium text-slate-800">{{ $tglSelesai }}</span>
                            @else
                                <span class="text-slate-400 font-normal">-</span>
                            @endif
                        </td>
                        <!-- Total Rincian (Rp) -->
                        <td class="py-4 px-5 text-center text-slate-800 font-medium whitespace-nowrap">
                            {{ number_format($totalRincian, 0, ',', '.') }}
                        </td>
                        <!-- Status -->
                        <td class="py-4 px-5 text-center whitespace-nowrap">
                            @if(strtolower($item->status) === 'siap realisasi')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-[#094886] border border-blue-200/90 shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#094886]"></span>
                                    <span>Siap Realisasi</span>
                                </span>
                            @elseif(strtolower($item->status) === 'selesai')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/90 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    <span>Selesai</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ ucwords($item->status) }}
                                </span>
                            @endif
                        </td>
                        <!-- Aksi -->
                        <td class="py-4 px-5 text-right pr-6">
                            <div class="flex items-center justify-end gap-2">
                                @if($item->status === 'siap realisasi')
                                    @if(!\App\Services\SatkerService::isViewingOtherSatker())
                                        <a href="{{ route('realisasi.proses', $item->id) }}" 
                                           class="inline-flex items-center px-3.5 py-1.5 bg-[#094886] hover:bg-[#073868] text-white text-[11px] font-bold rounded-md shadow-xs transition-colors cursor-pointer tracking-wider">
                                            PROSES REALISASI
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-[11px] font-medium bg-slate-100 text-slate-500 rounded-md">
                                            Siap Realisasi
                                        </span>
                                    @endif
                                @else
                                    <a href="{{ route('realisasi.show', $item->id) }}" 
                                       class="p-1.5 text-slate-500 hover:text-blue-600 transition-colors" title="Lihat Detail / KAK Realisasi">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    @if(!\App\Services\SatkerService::isViewingOtherSatker())
                                    <a href="{{ route('realisasi.edit', $item->id) }}" 
                                       class="p-1.5 text-slate-500 hover:text-amber-600 transition-colors" title="Edit Realisasi">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                        </svg>
                                    </a>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">
                            <div class="w-12 h-12 mx-auto mb-3 text-slate-300">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <p class="text-base font-semibold text-slate-700">Tidak ada data realisasi yang ditemukan</p>
                            <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter status.</p>
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
                Menampilkan <strong>{{ $realisasis->firstItem() ?? 0 }}</strong> - <strong>{{ $realisasis->lastItem() ?? 0 }}</strong> dari <strong>{{ number_format($realisasis->total(), 0, ',', '.') }}</strong> data Realisasi
            </div>

            <!-- Custom Clean Pagination Navigation & Per Page Selector -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Per Page selector -->
                <div class="flex items-center gap-2 text-xs text-slate-600 sm:border-r sm:border-slate-200 sm:pr-3">
                    <label for="per_page_select" class="text-slate-400 whitespace-nowrap">Tampilkan per halaman:</label>
                    <select id="per_page_select" 
                            onchange="window.location.href = updateQueryStringParameter(window.location.href, 'per_page', this.value)"
                            class="bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-xs rounded-md px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-pointer">
                        <option value="10" {{ $realisasis->perPage() == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ $realisasis->perPage() == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $realisasis->perPage() == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $realisasis->perPage() == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="flex items-center gap-1">
                    @if ($realisasis->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center text-slate-300 bg-slate-50 rounded-lg cursor-not-allowed border border-slate-200/60" title="Sebelumnya">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </span>
                    @else
                        <a href="{{ $realisasis->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 rounded-lg border border-slate-300 transition-colors shadow-2xs" title="Sebelumnya">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </a>
                    @endif

                    <!-- Compact page numbers -->
                    @php
                        $currentPage = $realisasis->currentPage();
                        $lastPage = $realisasis->lastPage();
                        $start = max(1, $currentPage - 1);
                        $end = min($lastPage, $currentPage + 1);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $realisasis->url(1) }}" class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-300">1</a>
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
                            <a href="{{ $realisasis->url($p) }}" class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-300 transition-colors">
                                {{ $p }}
                            </a>
                        @endif
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="px-1 text-slate-400">...</span>
                        @endif
                        <a href="{{ $realisasis->url($lastPage) }}" class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-300">{{ $lastPage }}</a>
                    @endif

                    @if ($realisasis->hasMorePages())
                        <a href="{{ $realisasis->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-50 rounded-lg border border-slate-300 transition-colors shadow-2xs" title="Selanjutnya">
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
        } else {
            return uri + separator + key + "=" + value;
        }
    }
</script>
@endsection
