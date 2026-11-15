@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="dashboardPage()">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 tracking-tight">Dashboard Analytics</h1>
            <p class="text-sm text-slate-500 mt-1">Ringkasan data inventaris, permintaan, dan realisasi pemeliharaan BMN.</p>
        </div>
    </div>

    <!-- Summary Cards Row 1 -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Total Aset BMN -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-[#094886] flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">BMN</span>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ number_format($totalAset) }}</p>
            <p class="text-xs text-slate-500 mt-1">Total Aset Terdaftar</p>
        </div>

        <!-- Total Permintaan -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">PERMINTAAN</span>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ number_format($totalPermintaan) }}</p>
            <p class="text-xs text-slate-500 mt-1">Total Permintaan Masuk</p>
            @if($permintaanMenunggu > 0)
                <div class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[11px] font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    {{ $permintaanMenunggu }} menunggu
                </div>
            @endif
        </div>

        <!-- Realisasi Proses -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">PROSES</span>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ number_format($realisasiProses) }}</p>
            <p class="text-xs text-slate-500 mt-1">Sedang Dikerjakan</p>
        </div>

        <!-- Realisasi Selesai -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">SELESAI</span>
            </div>
            <p class="text-2xl font-extrabold text-slate-900">{{ number_format($realisasiSelesai) }}</p>
            <p class="text-xs text-slate-500 mt-1">Realisasi Selesai</p>
        </div>
    </div>

    <!-- Supply & Persediaan Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('supply.inventory.index') }}" class="bg-white rounded-xl border border-slate-200/90 shadow-xs p-5 hover:border-slate-300 transition-all flex items-center justify-between group">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Stok Persediaan</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalSupplyItem ?? 0) }} Item</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ number_format($totalStokSupply ?? 0) }} total fisik stok barang</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#094886] flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('supply.distribusi.index') }}" class="bg-white rounded-xl border border-slate-200/90 shadow-xs p-5 hover:border-slate-300 transition-all flex items-center justify-between group">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Distribusi Barang</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalDistribusi ?? $totalBastSupply ?? 0) }} Transaksi</p>
                <p class="text-xs text-slate-500 mt-0.5">Pengeluaran & penyaluran barang gudang</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
            </div>
        </a>

        <a href="{{ route('realisasi.index', ['jenis' => 'Supply']) }}" class="bg-white rounded-xl border border-slate-200/90 shadow-xs p-5 hover:border-slate-300 transition-all flex items-center justify-between group">
            <div>
                <p class="text-xs font-bold text-[#094886] uppercase tracking-wider">Realisasi Supply</p>
                <p class="text-2xl font-extrabold text-[#094886] mt-1">Belanja Persediaan</p>
                <p class="text-xs text-slate-500 mt-0.5">Unggah & kelola BAST pengadaan</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#094886] flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
        </a>
    </div>

    <!-- Summary Cards Row 2 - Financial -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Total RAB Permintaan -->
        <div class="bg-gradient-to-br from-[#094886] to-[#0a5da8] rounded-xl shadow-md p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 rounded-lg bg-white/15 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-white/60 uppercase tracking-widest">ESTIMASI RAB</span>
            </div>
            <p class="text-sm font-medium text-white/70 mb-1">Total Anggaran RAB Permintaan</p>
            <p class="text-3xl font-extrabold tracking-tight">Rp {{ number_format($totalBiayaRAB, 0, ',', '.') }}</p>
        </div>

        <!-- Total Biaya Realisasi -->
        <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-xl shadow-md p-6 text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 rounded-lg bg-white/15 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-white/60 uppercase tracking-widest">REALISASI</span>
            </div>
            <p class="text-sm font-medium text-white/70 mb-1">Total Biaya Realisasi (Selesai)</p>
            <p class="text-3xl font-extrabold tracking-tight">Rp {{ number_format($totalBiayaRealisasi, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Aset by Jenis BMN -->
        @if(count($asetByJenis) > 0)
        <div class="bg-white rounded-xl col-span-1 lg:col-span-2 border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800">Distribusi Aset per Jenis BMN</h2>
                <p class="text-xs text-slate-400 mt-0.5">Kategori barang milik negara berdasarkan jumlah</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
                    @foreach($asetByJenis as $jenis)
                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-100 hover:border-[#094886]/30 hover:shadow-sm transition-all group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="w-8 h-8 rounded-md bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover:text-[#094886] group-hover:border-[#094886]/20 transition-colors">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Aset</span>
                        </div>
                        <p class="text-xs font-semibold text-slate-600 mb-1 line-clamp-2 min-h-[32px]" title="{{ $jenis['jenis_bmn'] }}">{{ $jenis['jenis_bmn'] }}</p>
                        <p class="text-xl font-bold text-slate-900">{{ number_format($jenis['total']) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Kondisi Aset Doughnut (1/3 width) -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800">Kondisi Aset</h2>
                <p class="text-xs text-slate-400 mt-0.5">Distribusi berdasarkan kondisi</p>
            </div>
            <div class="p-6 flex flex-col items-center">
                <div class="relative w-full h-[200px]">
                    <canvas id="kondisiChart"></canvas>
                </div>
                <div class="mt-4 w-full space-y-1.5">
                    @foreach($asetByKondisi as $k)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-medium">{{ $k['kondisi'] }}</span>
                        <span class="font-bold text-slate-800">{{ number_format($k['total']) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Biaya & Jenis Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Biaya Per Bulan Bar Chart -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800">Biaya Realisasi per Bulan</h2>
                <p class="text-xs text-slate-400 mt-0.5">Total pengeluaran yang sudah selesai</p>
            </div>
            <div class="p-6">
                <div class="relative w-full h-[260px]">
                    <canvas id="biayaChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Jenis Pekerjaan Breakdown -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800">Biaya per Jenis Pekerjaan</h2>
                <p class="text-xs text-slate-400 mt-0.5">Persentase berdasarkan jenis pemeliharaan</p>
            </div>
            <div class="p-6">
                @if(count($biayaPerJenis) > 0)
                <div class="relative w-full h-[260px]">
                    <canvas id="jenisChart"></canvas>
                </div>
                @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Belum ada data realisasi selesai</p>
                    <p class="text-xs text-slate-400 mt-1">Grafik akan muncul saat ada data realisasi yang telah diselesaikan.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<script>
function dashboardPage() {
    return {
        init() {
            this.$nextTick(() => {
                this.initCharts();
            });
        },
        initCharts() {
            const monthlyData = @json($monthlyData);
            const kondisiData = @json($asetByKondisi);
            const biayaPerJenis = @json($biayaPerJenis);
            const rabVsRealisasi = @json($rabVsRealisasi);
            const asetByJenis = @json($asetByJenis);

            // === Trend Chart ===
            const trendCtx = document.getElementById('trendChart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: monthlyData.map(d => d.label),
                        datasets: [
                            {
                                label: 'Permintaan',
                                data: monthlyData.map(d => d.permintaan),
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 4,
                                pointBackgroundColor: '#f59e0b',
                            },
                            {
                                label: 'Realisasi',
                                data: monthlyData.map(d => d.realisasi),
                                borderColor: '#094886',
                                backgroundColor: 'rgba(9, 72, 134, 0.08)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 4,
                                pointBackgroundColor: '#094886',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                            x: { ticks: { font: { size: 10 }, maxRotation: 45 }, grid: { display: false } }
                        }
                    }
                });
            }

            // === Kondisi Chart ===
            const kondisiCtx = document.getElementById('kondisiChart');
            if (kondisiCtx && kondisiData.length > 0) {
                const colors = ['#094886', '#0ea5e9', '#f59e0b', '#ef4444', '#8b5cf6', '#10b981', '#6366f1'];
                new Chart(kondisiCtx, {
                    type: 'doughnut',
                    data: {
                        labels: kondisiData.map(d => d.kondisi),
                        datasets: [{
                            data: kondisiData.map(d => d.total),
                            backgroundColor: colors.slice(0, kondisiData.length),
                            borderWidth: 2,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: { legend: { display: false } }
                    }
                });
            }

            // === Biaya Chart ===
            const biayaCtx = document.getElementById('biayaChart');
            if (biayaCtx) {
                new Chart(biayaCtx, {
                    type: 'bar',
                    data: {
                        labels: monthlyData.map(d => d.label),
                        datasets: [{
                            label: 'Biaya Realisasi',
                            data: monthlyData.map(d => d.biaya),
                            backgroundColor: 'rgba(9, 72, 134, 0.75)',
                            borderColor: '#094886',
                            borderWidth: 1,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { font: { size: 11 }, callback: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) }, grid: { color: 'rgba(0,0,0,0.04)' } },
                            x: { ticks: { font: { size: 10 }, maxRotation: 45 }, grid: { display: false } }
                        }
                    }
                });
            }

            // === Jenis Pekerjaan Chart ===
            const jenisCtx = document.getElementById('jenisChart');
            if (jenisCtx && biayaPerJenis.length > 0) {
                const jColors = ['#094886', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
                new Chart(jenisCtx, {
                    type: 'pie',
                    data: {
                        labels: biayaPerJenis.map(d => d.jenis),
                        datasets: [{
                            data: biayaPerJenis.map(d => parseFloat(d.total_biaya)),
                            backgroundColor: jColors.slice(0, biayaPerJenis.length),
                            borderWidth: 2,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 15 } },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => ctx.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                                }
                            }
                        }
                    }
                });
            }

            // === RAB vs Realisasi Chart ===
            const rabCtx = document.getElementById('rabRealisasiChart');
            if (rabCtx && rabVsRealisasi.length > 0) {
                new Chart(rabCtx, {
                    type: 'bar',
                    data: {
                        labels: rabVsRealisasi.map(d => d.pekerjaan.length > 30 ? d.pekerjaan.substring(0, 30) + '...' : d.pekerjaan),
                        datasets: [
                            {
                                label: 'RAB',
                                data: rabVsRealisasi.map(d => d.rab),
                                backgroundColor: 'rgba(9, 72, 134, 0.7)',
                                borderColor: '#094886',
                                borderWidth: 1,
                                borderRadius: 4,
                            },
                            {
                                label: 'Realisasi',
                                data: rabVsRealisasi.map(d => d.realisasi),
                                backgroundColor: 'rgba(16, 185, 129, 0.7)',
                                borderColor: '#10b981',
                                borderWidth: 1,
                                borderRadius: 4,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 11 } } },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => ctx.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                                }
                            }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { font: { size: 11 }, callback: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) }, grid: { color: 'rgba(0,0,0,0.04)' } },
                            x: { ticks: { font: { size: 10 }, maxRotation: 45 }, grid: { display: false } }
                        }
                    }
                });
            }

            // === Jenis BMN Widget ===
            // Diagram horizontal diubah menjadi widget grid.
        }
    }
}
</script>
@endsection
