<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use App\Models\SupplyDistribusi;
use App\Models\SupplyPermintaan;
use App\Services\SatkerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupplyPermintaanController extends Controller
{
    /**
     * Tampilkan daftar permintaan supply / barang persediaan.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $date = $request->input('date');
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $query = SupplyPermintaan::with(['items.supply', 'distribusi'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_permintaan', 'like', "%{$search}%")
                    ->orWhere('nama_pemohon', 'like', "%{$search}%")
                    ->orWhere('unit_kerja', 'like', "%{$search}%")
                    ->orWhere('keperluan', 'like', "%{$search}%");
            });
        }

        if ($date) {
            $query->whereDate('tanggal_permintaan', $date);
        }

        $countMenunggu = SupplyPermintaan::where('status', 'Menunggu')->count();
        $countDidistribusikan = SupplyPermintaan::where('status', 'Didistribusikan')->count();
        $countSemua = SupplyPermintaan::count();

        $permintaans = $query->paginate($perPage)->withQueryString();

        return view('supply.permintaan.index', compact(
            'permintaans',
            'status',
            'search',
            'date',
            'perPage',
            'countMenunggu',
            'countDidistribusikan',
            'countSemua'
        ));
    }

    /**
     * Form pembuatan permintaan supply barang persediaan baru.
     */
    public function create(): View|RedirectResponse
    {
        if (SatkerService::isViewingOtherSatker()) {
            return redirect()->route('supply.permintaan.index')
                ->with('error', 'Mode Lihat Saja: Pembuatan permintaan supply untuk satker lain tidak diperkenankan.');
        }

        $supplies = Supply::orderBy('nama_barang')->get();
        $nomorBaru = SupplyPermintaan::generateNomorPermintaan();

        // Default Unit Kerja BPS
        $unitKerjaList = [
            'Bagian Umum (Subbagian Umum & Humas)',
            'Tim Statistik Sosial',
            'Tim Statistik Produksi',
            'Tim Statistik Distribusi',
            'Tim Neraca Wilayah dan Analisis Statistik (Nerwilis)',
            'Tim Integrasi Pengolahan dan Diseminasi Statistik (IPDS)',
            'Pimpinan / Kepala BPS',
            'Lainnya',
        ];

        return view('supply.permintaan.create', compact('supplies', 'nomorBaru', 'unitKerjaList'));
    }

    /**
     * Simpan permintaan barang persediaan.
     */
    public function store(Request $request): RedirectResponse
    {
        SatkerService::abortIfViewingOtherSatker('pembuatan permintaan supply');

        $request->validate([
            'tanggal_permintaan' => 'required|date',
            'unit_kerja' => 'required|string|max:255',
            'nama_pemohon' => 'required|string|max:255',
            'nip_pemohon' => 'nullable|string|max:50',
            'jabatan_pemohon' => 'nullable|string|max:100',
            'keperluan' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.supply_id' => 'required|exists:supplies,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.catatan' => 'nullable|string|max:255',
        ], [
            'items.required' => 'Minimal pilih 1 barang persediaan yang diminta.',
            'items.min' => 'Minimal pilih 1 barang persediaan yang diminta.',
            'items.*.supply_id.required' => 'Barang persediaan harus dipilih.',
            'items.*.jumlah.min' => 'Jumlah barang yang diminta minimal 1.',
        ]);

        $permintaan = DB::transaction(function () use ($request) {
            $nomorPermintaan = SupplyPermintaan::generateNomorPermintaan();

            $permintaan = SupplyPermintaan::create([
                'nomor_permintaan' => $nomorPermintaan,
                'tanggal_permintaan' => $request->input('tanggal_permintaan'),
                'unit_kerja' => $request->input('unit_kerja'),
                'nama_pemohon' => $request->input('nama_pemohon'),
                'nip_pemohon' => $request->input('nip_pemohon'),
                'jabatan_pemohon' => $request->input('jabatan_pemohon'),
                'keperluan' => $request->input('keperluan'),
                'status' => 'Menunggu',
                'catatan' => $request->input('catatan'),
                'user_id' => auth()->id(),
            ]);

            foreach ($request->input('items') as $it) {
                $supply = Supply::find($it['supply_id']);
                if ($supply) {
                    $permintaan->items()->create([
                        'supply_id' => $supply->id,
                        'jumlah_diminta' => (int) $it['jumlah'],
                        'satuan' => $supply->satuan,
                        'catatan' => $it['catatan'] ?? null,
                    ]);
                }
            }

            return $permintaan;
        });

        return redirect()->route('supply.permintaan.show', $permintaan->id)
            ->with('success', "Permintaan barang persediaan #{$permintaan->nomor_permintaan} berhasil dibuat. Silakan periksa atau proses distribusi.");
    }

    /**
     * Tampilkan detail permintaan supply.
     */
    public function show(int $id): View
    {
        $permintaan = SupplyPermintaan::with(['items.supply', 'distribusi'])->findOrFail($id);

        return view('supply.permintaan.show', compact('permintaan'));
    }

    /**
     * Hapus / batalkan permintaan supply jika masih berstatus Menunggu.
     */
    public function destroy(int $id): RedirectResponse
    {
        SatkerService::abortIfViewingOtherSatker('pembatalan permintaan supply');

        $permintaan = SupplyPermintaan::findOrFail($id);

        if ($permintaan->status === 'Didistribusikan') {
            return back()->with('error', 'Permintaan yang sudah didistribusikan tidak dapat dihapus.');
        }

        $nomor = $permintaan->nomor_permintaan;
        $permintaan->delete();

        return redirect()->route('supply.permintaan.index')
            ->with('success', "Permintaan #{$nomor} berhasil dibatalkan / dihapus.");
    }

    /**
     * PROSES DISTRIBUSI & GENERATE BAST
     * 1. Validasi ketersediaan stok
     * 2. Kurangi stok barang persediaan secara otomatis
     * 3. Buat Berita Acara Serah Terima (BAST) otomatis
     * 4. Ubah status permintaan menjadi 'Didistribusikan'
     */
    public function distribusikan(Request $request, int $id): RedirectResponse
    {
        SatkerService::abortIfViewingOtherSatker('proses distribusi supply');

        $permintaan = SupplyPermintaan::with(['items.supply'])->findOrFail($id);

        if ($permintaan->status === 'Didistribusikan') {
            return back()->with('error', 'Permintaan ini sudah didistribusikan sebelumnya.');
        }

        $request->validate([
            'tanggal_bast' => 'required|date',
            'pihak_pertama_nama' => 'nullable|string|max:255',
            'pihak_pertama_nip' => 'nullable|string|max:50',
            'pihak_pertama_jabatan' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:supply_permintaan_items,id',
            'items.*.jumlah_disetujui' => 'required|integer|min:0',
        ]);

        // Cek stok apakah mencukupi
        $itemsData = $request->input('items');
        $errorStok = [];

        foreach ($itemsData as $itData) {
            $pItem = $permintaan->items->firstWhere('id', $itData['id']);
            if ($pItem && $pItem->supply) {
                $qty = (int) $itData['jumlah_disetujui'];
                if ($qty > $pItem->supply->stok) {
                    $errorStok[] = "Stok {$pItem->supply->nama_barang} tidak mencukupi (Tersedia: {$pItem->supply->stok} {$pItem->supply->satuan}, Disetujui: {$qty})";
                }
            }
        }

        if (! empty($errorStok)) {
            return back()->withInput()->with('error', implode('<br>', $errorStok));
        }

        // Jalankan transaksi distribusi
        $distribusi = DB::transaction(function () use ($permintaan, $request, $itemsData) {
            $nomorBast = SupplyDistribusi::generateNomorBast();

            // 1. Buat BAST
            $currentUser = auth()->user();
            $pihakPertamaNama = $request->input('pihak_pertama_nama') ?: ($currentUser->name ?? 'Petugas BMN');
            $pihakPertamaNip = $request->input('pihak_pertama_nip') ?: ($currentUser->nip ?? null);
            $pihakPertamaJabatan = $request->input('pihak_pertama_jabatan') ?: 'Pengelola BMN & Persediaan';

            $distribusi = SupplyDistribusi::create([
                'supply_permintaan_id' => $permintaan->id,
                'nomor_bast' => $nomorBast,
                'tanggal_bast' => $request->input('tanggal_bast'),
                'pihak_pertama_nama' => $pihakPertamaNama,
                'pihak_pertama_nip' => $pihakPertamaNip,
                'pihak_pertama_jabatan' => $pihakPertamaJabatan,
                'pihak_kedua_nama' => $permintaan->nama_pemohon,
                'pihak_kedua_nip' => $permintaan->nip_pemohon,
                'pihak_kedua_jabatan' => $permintaan->jabatan_pemohon,
                'pihak_kedua_unit_kerja' => $permintaan->unit_kerja,
                'kegiatan_keperluan' => $permintaan->keperluan,
                'status' => 'Menunggu TTD',
                'user_id' => auth()->id(),
            ]);

            // 2. Loop setiap item yang disetujui, kurangi stok & catat mutasi & masukkan ke rincian BAST
            foreach ($itemsData as $itData) {
                $pItem = $permintaan->items->firstWhere('id', $itData['id']);
                if (! $pItem) {
                    continue;
                }

                $qtyDisetujui = (int) $itData['jumlah_disetujui'];
                $pItem->update(['jumlah_disetujui' => $qtyDisetujui]);

                if ($qtyDisetujui > 0 && $pItem->supply) {
                    // Kurangi stok persediaan
                    $pItem->supply->kurangiStok(
                        jumlah: $qtyDisetujui,
                        distribusi: $distribusi,
                        keterangan: "Distribusi ke {$permintaan->nama_pemohon} ({$permintaan->unit_kerja}) via BAST #{$nomorBast}",
                        userId: auth()->id()
                    );

                    // Buat item rincian BAST
                    $distribusi->items()->create([
                        'supply_id' => $pItem->supply->id,
                        'nama_barang' => $pItem->supply->nama_barang,
                        'spesifikasi' => $pItem->supply->spesifikasi,
                        'jumlah' => $qtyDisetujui,
                        'satuan' => $pItem->satuan,
                        'keterangan' => $pItem->catatan ?? $permintaan->keperluan,
                    ]);
                }
            }

            // 3. Update status permintaan
            $permintaan->update(['status' => 'Didistribusikan']);

            return $distribusi;
        });

        return redirect()->route('supply.distribusi.show', $distribusi->id)
            ->with('success', "Distribusi barang berhasil diproses! Stok inventory telah berkurang dan Berita Acara Serah Terima (BAST) #{$distribusi->nomor_bast} telah berhasil digenerate.");
    }
}
