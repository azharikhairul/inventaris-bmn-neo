<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\Permintaan;
use App\Models\Realisasi;
use App\Models\RealisasiAset;
use App\Models\Supply;
use App\Models\SupplyDistribusi;
use App\Services\SatkerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // === SCOPED QUERIES FOR MULTI-SATKER ===
        $asetQuery = SatkerService::filterQuery(Aset::query(), 'kode_satker');
        $permintaanQuery = SatkerService::filterQuery(Permintaan::query());
        $realisasiQuery = SatkerService::filterQuery(Realisasi::query());
        $supplyQuery = SatkerService::filterQuery(Supply::query());
        $distribusiQuery = SatkerService::filterQuery(SupplyDistribusi::query());

        // === SUMMARY CARDS ===
        $totalAset = (clone $asetQuery)->count();
        $totalPermintaan = (clone $permintaanQuery)->count();
        $totalRealisasi = (clone $realisasiQuery)->count();
        $realisasiSelesai = (clone $realisasiQuery)->where('status', 'Selesai')->count();
        $realisasiProses = (clone $realisasiQuery)->where('status', 'siap realisasi')->count();
        $permintaanMenunggu = (clone $permintaanQuery)->doesntHave('realisasis')->count();

        // Supply metrics
        $totalSupplyItem = (clone $supplyQuery)->count();
        $totalStokSupply = (clone $supplyQuery)->sum('stok');
        $totalDistribusi = (clone $distribusiQuery)->count();
        $totalBastSupply = $totalDistribusi;

        $totalBiayaRealisasi = (clone $realisasiQuery)->where('status', 'Selesai')->sum('total_realisasi');
        $totalBiayaRAB = (clone $permintaanQuery)->sum('total_rab');

        // Aset conditions
        $asetByKondisi = (clone $asetQuery)->select('kondisi', DB::raw('count(*) as total'))
            ->whereNotNull('kondisi')
            ->where('kondisi', '!=', '')
            ->groupBy('kondisi')
            ->orderByDesc('total')
            ->get()
            ->toArray();

        // === MONTHLY TREND (Last 12 months) ===
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->format('Y-m');
            $label = $date->translatedFormat('M Y');

            $permintaanCount = (clone $permintaanQuery)->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $realisasiCount = (clone $realisasiQuery)->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $realisasiBiaya = (clone $realisasiQuery)->where('status', 'Selesai')
                ->whereYear('tanggal_selesai', $date->year)
                ->whereMonth('tanggal_selesai', $date->month)
                ->sum('total_realisasi');

            $monthlyData[] = [
                'label' => $label,
                'permintaan' => $permintaanCount,
                'realisasi' => $realisasiCount,
                'biaya' => (float) $realisasiBiaya,
            ];
        }

        // === TOP ASET PALING SERING DIPERBAIKI ===
        $topAsets = RealisasiAset::select('aset_id', 'nama_aset', DB::raw('count(*) as total_perbaikan'), DB::raw('sum(subtotal) as total_biaya'))
            ->whereHas('realisasi', function ($q) {
                $q->where('status', 'Selesai');
                SatkerService::filterQuery($q);
            })
            ->groupBy('aset_id', 'nama_aset')
            ->orderByDesc('total_perbaikan')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $nama = $item->nama_aset;
                if ($item->aset_id) {
                    $aset = Aset::find($item->aset_id);
                    if ($aset) {
                        $nama = $aset->nama_barang;
                    }
                }

                return [
                    'nama' => $nama ?? 'Aset Non-Register',
                    'total_perbaikan' => $item->total_perbaikan,
                    'total_biaya' => (float) $item->total_biaya,
                ];
            })
            ->toArray();

        // === REALISASI TERBARU ===
        $realisasiTerbaru = (clone $realisasiQuery)->with('realisasiAsets')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'pekerjaan' => $r->pekerjaan,
                    'vendor' => $r->vendor,
                    'status' => $r->status,
                    'total' => (float) $r->total_realisasi,
                    'tanggal' => $r->created_at->translatedFormat('d M Y'),
                    'jumlah_aset' => $r->realisasiAsets->count(),
                ];
            })
            ->toArray();

        // === BIAYA PER JENIS PEKERJAAN ===
        $biayaPerJenis = (clone $realisasiQuery)->select('jenis', DB::raw('count(*) as jumlah'), DB::raw('sum(total_realisasi) as total_biaya'))
            ->where('status', 'Selesai')
            ->whereNotNull('jenis')
            ->groupBy('jenis')
            ->orderByDesc('total_biaya')
            ->get()
            ->toArray();

        // === PERBANDINGAN RAB VS REALISASI ===
        $rabVsRealisasi = (clone $realisasiQuery)->with('permintaan')
            ->where('status', 'Selesai')
            ->whereNotNull('permintaan_id')
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                return [
                    'pekerjaan' => $r->pekerjaan,
                    'rab' => $r->permintaan ? (float) $r->permintaan->total_rab : 0,
                    'realisasi' => (float) $r->total_realisasi,
                ];
            })
            ->filter(fn ($item) => $item['rab'] > 0)
            ->values()
            ->take(10)
            ->toArray();

        // === ASET BY JENIS BMN (Top 10) ===
        $asetByJenis = (clone $asetQuery)->select('jenis_bmn', DB::raw('count(*) as total'))
            ->whereNotNull('jenis_bmn')
            ->where('jenis_bmn', '!=', '')
            ->groupBy('jenis_bmn')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->toArray();

        $activeSatker = SatkerService::getActiveSatker();
        $allSatkers = SatkerService::getAllSatkers();

        return view('dashboard.index', compact(
            'totalAset',
            'totalPermintaan',
            'totalRealisasi',
            'realisasiSelesai',
            'realisasiProses',
            'permintaanMenunggu',
            'totalBiayaRealisasi',
            'totalBiayaRAB',
            'asetByKondisi',
            'monthlyData',
            'topAsets',
            'realisasiTerbaru',
            'biayaPerJenis',
            'rabVsRealisasi',
            'asetByJenis',
            'totalSupplyItem',
            'totalStokSupply',
            'totalDistribusi',
            'totalBastSupply',
            'activeSatker',
            'allSatkers'
        ));
    }
}
