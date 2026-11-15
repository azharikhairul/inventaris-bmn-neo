<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use App\Services\SatkerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplyInventoryController extends Controller
{
    /**
     * Tampilkan katalog dan stok barang persediaan.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $kategori = $request->input('kategori');
        $statusStok = $request->input('status_stok');
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $baseSupply = SatkerService::filterQuery(Supply::query());
        $query = clone $baseSupply;

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%")
                    ->orWhere('spesifikasi', 'like', "%{$search}%");
            });
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($statusStok === 'habis') {
            $query->where('stok', '<=', 0);
        } elseif ($statusStok === 'menipis') {
            $query->where('stok', '>', 0)->whereColumn('stok', '<=', 'stok_minimum');
        } elseif ($statusStok === 'tersedia') {
            $query->whereColumn('stok', '>', 'stok_minimum');
        }

        // Summary cards counter
        $totalItems = (clone $baseSupply)->count();
        $totalStok = (clone $baseSupply)->sum('stok');
        $totalMenipis = (clone $baseSupply)->where('stok', '>', 0)->whereColumn('stok', '<=', 'stok_minimum')->count();
        $totalHabis = (clone $baseSupply)->where('stok', '<=', 0)->count();

        $kategoris = (clone $baseSupply)->distinct()->pluck('kategori')->filter()->values();

        $supplies = $query->orderBy('nama_barang')->paginate($perPage)->withQueryString();

        return view('supply.inventory.index', compact(
            'supplies',
            'search',
            'kategori',
            'statusStok',
            'perPage',
            'totalItems',
            'totalStok',
            'totalMenipis',
            'totalHabis',
            'kategoris'
        ));
    }

    /**
     * Simpan barang persediaan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        SatkerService::abortIfViewingOtherSatker('penambahan barang persediaan');

        $validated = $request->validate([
            'kode_barang' => 'required|string|max:50|unique:supplies,kode_barang',
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'satuan' => 'required|string|max:50',
            'stok' => 'required|integer|min:0',
            'stok_minimum' => 'nullable|integer|min:0',
            'spesifikasi' => 'nullable|string',
            'harga_estimasi' => 'nullable|numeric|min:0',
        ]);

        $stokAwal = (int) $validated['stok'];
        $validated['stok_minimum'] = $validated['stok_minimum'] ?? 5;
        $validated['satker_id'] = auth()->user()?->satker_id ?? SatkerService::getActiveSatkerId();

        $supply = Supply::create($validated);

        if ($stokAwal > 0) {
            $supply->mutasis()->create([
                'jenis' => 'masuk',
                'jumlah' => $stokAwal,
                'stok_sebelum' => 0,
                'stok_sesudah' => $stokAwal,
                'tanggal' => now()->toDateString(),
                'keterangan' => 'Saldo awal pendaftaran barang',
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('supply.inventory.index')
            ->with('success', "Barang persediaan '{$supply->nama_barang}' berhasil ditambahkan.");
    }

    /**
     * Perbarui data master barang persediaan.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        SatkerService::abortIfViewingOtherSatker('perubahan master barang persediaan');

        $supply = Supply::findOrFail($id);

        $validated = $request->validate([
            'kode_barang' => 'required|string|max:50|unique:supplies,kode_barang,'.$supply->id,
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'satuan' => 'required|string|max:50',
            'stok_minimum' => 'nullable|integer|min:0',
            'spesifikasi' => 'nullable|string',
            'harga_estimasi' => 'nullable|numeric|min:0',
        ]);

        $validated['stok_minimum'] = $validated['stok_minimum'] ?? 5;
        $supply->update($validated);

        return redirect()->route('supply.inventory.index')
            ->with('success', "Data barang '{$supply->nama_barang}' berhasil diperbarui.");
    }

    /**
     * Hapus barang persediaan.
     */
    public function destroy(int $id): RedirectResponse
    {
        SatkerService::abortIfViewingOtherSatker('penghapusan master barang persediaan');

        $supply = Supply::findOrFail($id);
        $nama = $supply->nama_barang;
        $supply->delete();

        return redirect()->route('supply.inventory.index')
            ->with('success', "Barang '{$nama}' berhasil dihapus dari persediaan.");
    }

    /**
     * Catat barang masuk (penambahan stok persediaan).
     */
    public function stokMasuk(Request $request, int $id): RedirectResponse
    {
        SatkerService::abortIfViewingOtherSatker('penambahan stok barang');

        $supply = Supply::findOrFail($id);

        $request->validate([
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $jumlah = (int) $request->input('jumlah');
        $keterangan = $request->input('keterangan') ?: 'Barang masuk / Pengadaan persediaan';

        $supply->tambahStok($jumlah, $keterangan, auth()->id());

        return redirect()->route('supply.inventory.index')
            ->with('success', "Berhasil menambahkan {$jumlah} {$supply->satuan} stok untuk {$supply->nama_barang}.");
    }

    /**
     * Tampilkan riwayat mutasi stok untuk barang persediaan.
     */
    public function mutasi(int $id): View
    {
        $supply = Supply::with(['mutasis.distribusi', 'mutasis.realisasi', 'mutasis.user'])->findOrFail($id);

        return view('supply.inventory.mutasi', compact('supply'));
    }

    /**
     * API JSON search barang persediaan (untuk autocomplete di form permintaan).
     */
    public function apiSearch(Request $request): JsonResponse
    {
        $search = $request->input('q', '');

        $supplies = Supply::query()
            ->when($search, function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%");
            })
            ->orderBy('nama_barang')
            ->limit(30)
            ->get(['id', 'kode_barang', 'nama_barang', 'kategori', 'satuan', 'stok', 'spesifikasi']);

        return response()->json($supplies);
    }

    /**
     * Export daftar stok barang persediaan ke format CSV (Excel compatible).
     */
    public function export(Request $request): StreamedResponse
    {
        $search = $request->input('search');
        $kategori = $request->input('kategori');
        $statusStok = $request->input('status_stok');

        $query = Supply::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%")
                    ->orWhere('spesifikasi', 'like', "%{$search}%");
            });
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($statusStok === 'habis') {
            $query->where('stok', '<=', 0);
        } elseif ($statusStok === 'menipis') {
            $query->where('stok', '>', 0)->whereColumn('stok', '<=', 'stok_minimum');
        } elseif ($statusStok === 'tersedia') {
            $query->whereColumn('stok', '>', 'stok_minimum');
        }

        $filename = 'stok_persediaan_supply_'.date('Ymd_His').'.csv';

        return new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM untuk Microsoft Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // CSV Header
            fputcsv($handle, [
                'Kode Barang',
                'Nama Barang',
                'Kategori',
                'Satuan',
                'Stok Saat Ini',
                'Stok Minimum',
                'Status Stok',
                'Harga Estimasi (Rp)',
                'Spesifikasi',
            ]);

            $query->orderBy('nama_barang')->chunk(500, function ($supplies) use ($handle) {
                foreach ($supplies as $item) {
                    fputcsv($handle, [
                        $item->kode_barang,
                        $item->nama_barang,
                        $item->kategori,
                        $item->satuan,
                        $item->stok,
                        $item->stok_minimum,
                        $item->status_stok,
                        $item->harga_estimasi ?? 0,
                        $item->spesifikasi ?? '-',
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
