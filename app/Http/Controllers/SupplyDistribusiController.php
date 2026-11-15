<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use App\Models\Realisasi;
use App\Models\Supply;
use App\Models\SupplyDistribusi;
use App\Models\SupplyDistribusiItem;
use App\Services\SatkerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SupplyDistribusiController extends Controller
{
    /**
     * Tampilkan daftar transaksi distribusi pengeluaran barang persediaan.
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

        $baseDistribusi = SatkerService::filterQuery(SupplyDistribusi::query());
        $query = (clone $baseDistribusi)->with(['items.supply', 'permintaan', 'realisasi'])->latest('tanggal_bast')->latest('id');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_bast', 'like', "%{$search}%")
                    ->orWhere('pihak_kedua_nama', 'like', "%{$search}%")
                    ->orWhere('pihak_kedua_unit_kerja', 'like', "%{$search}%")
                    ->orWhere('kegiatan_keperluan', 'like', "%{$search}%")
                    ->orWhereHas('items', function ($iq) use ($search) {
                        $iq->where('nama_barang', 'like', "%{$search}%");
                    });
            });
        }

        if ($date) {
            $query->whereDate('tanggal_bast', $date);
        }

        $totalDistribusi = (clone $baseDistribusi)->count();
        $countMenungguTtd = (clone $baseDistribusi)->where('status', 'Menunggu TTD')->count();
        $countSelesai = (clone $baseDistribusi)->where('status', 'Selesai')->count();
        $distribusiBulanIni = (clone $baseDistribusi)->whereMonth('tanggal_bast', now()->month)
            ->whereYear('tanggal_bast', now()->year)
            ->count();
        $totalItemDisalurkan = (int) SupplyDistribusiItem::whereIn('supply_distribusi_id', (clone $baseDistribusi)->select('id'))->sum('jumlah');

        $distribusis = $query->paginate($perPage)->withQueryString();

        return view('supply.distribusi.index', compact(
            'distribusis',
            'status',
            'search',
            'date',
            'perPage',
            'totalDistribusi',
            'countMenungguTtd',
            'countSelesai',
            'totalItemDisalurkan',
            'distribusiBulanIni'
        ));
    }

    /**
     * Form pembuatan Distribusi barang baru dari stok persediaan atau pengadaan.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (SatkerService::isViewingOtherSatker()) {
            return redirect()->route('supply.distribusi.index')
                ->with('error', 'Mode Lihat Saja: Pencatatan distribusi barang untuk satker lain tidak diperkenankan.');
        }

        $realisasiId = $request->input('realisasi_id');
        $permintaanId = $request->input('permintaan_id');
        $supplyId = $request->input('supply_id');

        $realisasi = null;
        $permintaan = null;
        $prefilledItems = [];

        if ($realisasiId) {
            $realisasi = Realisasi::with(['permintaan.permintaanAsets.rabItems', 'realisasiAsets.realisasiItems'])->find($realisasiId);
            if ($realisasi) {
                $permintaan = $realisasi->permintaan;
                foreach ($realisasi->realisasiAsets as $ra) {
                    foreach ($ra->realisasiItems as $ri) {
                        $nama = $ri->uraian ?: $ra->nama_aset;
                        $matchedSupply = Supply::whereRaw('LOWER(TRIM(nama_barang)) = ?', [strtolower(trim($nama))])->first()
                            ?: Supply::where('nama_barang', 'like', "%{$nama}%")->first();

                        $prefilledItems[] = [
                            'supply_id' => $matchedSupply?->id,
                            'nama_barang' => $matchedSupply ? $matchedSupply->nama_barang : $nama,
                            'spesifikasi' => $ra->nama_aset,
                            'jumlah' => (int) ($ri->volume ?: 1),
                            'satuan' => $ri->satuan ?: 'unit',
                            'keterangan' => 'Realisasi '.$realisasi->pekerjaan,
                        ];
                    }
                }
            }
        } elseif ($permintaanId) {
            $permintaan = Permintaan::with('permintaanAsets.rabItems')->find($permintaanId);
            if ($permintaan) {
                foreach ($permintaan->permintaanAsets as $pa) {
                    foreach ($pa->rabItems as $rab) {
                        $matchedSupply = Supply::whereRaw('LOWER(TRIM(nama_barang)) = ?', [strtolower(trim($rab->uraian))])->first()
                            ?: Supply::where('nama_barang', 'like', "%{$rab->uraian}%")->first();

                        $prefilledItems[] = [
                            'supply_id' => $matchedSupply?->id,
                            'nama_barang' => $matchedSupply ? $matchedSupply->nama_barang : $rab->uraian,
                            'spesifikasi' => $pa->nama_barang,
                            'jumlah' => (int) ($rab->volume ?: 1),
                            'satuan' => $rab->satuan ?: 'unit',
                            'keterangan' => 'Permintaan '.$permintaan->pekerjaan,
                        ];
                    }
                }
            }
        } elseif ($supplyId) {
            $singleSupply = Supply::find($supplyId);
            if ($singleSupply) {
                $prefilledItems[] = [
                    'supply_id' => $singleSupply->id,
                    'nama_barang' => $singleSupply->nama_barang,
                    'spesifikasi' => $singleSupply->spesifikasi ?: $singleSupply->kategori,
                    'jumlah' => 1,
                    'satuan' => $singleSupply->satuan ?: 'Pcs',
                    'keterangan' => '',
                ];
            }
        }

        if (empty($prefilledItems)) {
            $prefilledItems = [
                [
                    'supply_id' => null,
                    'nama_barang' => '',
                    'spesifikasi' => '',
                    'jumlah' => 1,
                    'satuan' => 'buah',
                    'keterangan' => '',
                ],
            ];
        }

        $availableRealisasis = Realisasi::where('jenis', 'Supply')
            ->whereDoesntHave('distribusi')
            ->orderByDesc('id')
            ->get(['id', 'pekerjaan', 'no_fpa', 'total_realisasi', 'tanggal_selesai']);

        $supplies = SatkerService::filterQuery(Supply::query())
            ->orderBy('nama_barang')
            ->get(['id', 'nama_barang', 'kode_barang', 'kategori', 'stok', 'satuan', 'spesifikasi']);

        return view('supply.distribusi.create', compact(
            'realisasi',
            'permintaan',
            'prefilledItems',
            'availableRealisasis',
            'supplies'
        ));
    }

    /**
     * Simpan transaksi distribusi pengeluaran barang & terbitkan BAST.
     */
    public function store(Request $request): RedirectResponse
    {
        SatkerService::abortIfViewingOtherSatker('pembuatan distribusi barang');

        $request->validate([
            'tanggal_bast' => 'required|date',
            'pihak_kedua_nama' => 'required|string|max:255',
            'pihak_kedua_nip' => 'nullable|string|max:50',
            'pihak_kedua_jabatan' => 'nullable|string|max:100',
            'pihak_kedua_unit_kerja' => 'required|string|max:150',
            'kegiatan_keperluan' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string|max:255',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.satuan' => 'required|string|max:50',
        ], [
            'tanggal_bast.required' => 'Tanggal penyerahan/distribusi wajib diisi.',
            'pihak_kedua_nama.required' => 'Nama pihak penerima barang wajib diisi.',
            'pihak_kedua_unit_kerja.required' => 'Unit kerja / seksi penerima wajib diisi.',
            'items.required' => 'Daftar barang yang didistribusikan minimal 1 item.',
            'items.*.nama_barang.required' => 'Nama barang pada baris item wajib diisi.',
            'items.*.jumlah.required' => 'Jumlah barang wajib diisi minimal 1.',
        ]);

        $items = $request->input('items', []);
        $stokErrors = [];
        foreach ($items as $it) {
            $supplyId = ! empty($it['supply_id']) ? (int) $it['supply_id'] : null;
            $namaBarang = trim($it['nama_barang'] ?? '');
            $jumlah = (int) ($it['jumlah'] ?? 1);

            $supplyModel = $supplyId ? Supply::find($supplyId) : null;
            if (! $supplyModel && $namaBarang) {
                $supplyModel = Supply::whereRaw('LOWER(TRIM(nama_barang)) = ?', [strtolower($namaBarang)])->first()
                    ?: Supply::where('nama_barang', 'like', "%{$namaBarang}%")->first();
            }

            if ($supplyModel && $jumlah > $supplyModel->stok) {
                $stokErrors[] = "Stok untuk '{$supplyModel->nama_barang}' tidak mencukupi (Tersedia: {$supplyModel->stok} {$supplyModel->satuan}, diminta: {$jumlah} {$supplyModel->satuan}).";
            }
        }

        if (! empty($stokErrors)) {
            return back()->withInput()->withErrors(['items' => implode(' ', $stokErrors)]);
        }

        $distribusi = DB::transaction(function () use ($request) {
            $nomorBast = SupplyDistribusi::generateNomorBast();
            $currentUser = auth()->user();
            $pihakPertamaNama = $request->input('pihak_pertama_nama') ?: ($currentUser->name ?? 'Administrator BMN');
            $pihakPertamaNip = $request->input('pihak_pertama_nip') ?: ($currentUser->nip ?? null);
            $pihakPertamaJabatan = $request->input('pihak_pertama_jabatan') ?: 'Pengelola BMN & Persediaan';

            $distribusi = SupplyDistribusi::create([
                'realisasi_id' => $request->input('realisasi_id'),
                'permintaan_id' => $request->input('permintaan_id'),
                'nomor_bast' => $nomorBast,
                'tanggal_bast' => $request->input('tanggal_bast'),
                'pihak_pertama_nama' => $pihakPertamaNama,
                'pihak_pertama_nip' => $pihakPertamaNip,
                'pihak_pertama_jabatan' => $pihakPertamaJabatan,
                'pihak_kedua_nama' => $request->input('pihak_kedua_nama'),
                'pihak_kedua_nip' => $request->input('pihak_kedua_nip'),
                'pihak_kedua_jabatan' => $request->input('pihak_kedua_jabatan'),
                'pihak_kedua_unit_kerja' => $request->input('pihak_kedua_unit_kerja'),
                'kegiatan_keperluan' => $request->input('kegiatan_keperluan'),
                'status' => 'Menunggu TTD',
                'user_id' => auth()->id(),
            ]);

            foreach ($request->input('items', []) as $it) {
                $namaBarang = trim($it['nama_barang'] ?? '');
                $jumlah = (int) ($it['jumlah'] ?? 1);
                $satuan = trim($it['satuan'] ?? 'unit');
                $spesifikasi = trim($it['spesifikasi'] ?? '');
                $keterangan = trim($it['keterangan'] ?? '');
                $supplyId = ! empty($it['supply_id']) ? (int) $it['supply_id'] : null;

                $supplyModel = $supplyId ? Supply::find($supplyId) : null;
                if (! $supplyModel) {
                    $supplyModel = Supply::whereRaw('LOWER(TRIM(nama_barang)) = ?', [strtolower($namaBarang)])->first()
                        ?: Supply::where('nama_barang', 'like', "%{$namaBarang}%")->first();
                }

                if (! $supplyModel) {
                    $allSupplies = Supply::all();
                    foreach ($allSupplies as $s) {
                        if (stripos($namaBarang, $s->nama_barang) !== false || stripos($s->nama_barang, $namaBarang) !== false) {
                            $supplyModel = $s;
                            break;
                        }
                    }
                }

                $distribusi->items()->create([
                    'supply_id' => $supplyModel ? $supplyModel->id : null,
                    'nama_barang' => $namaBarang,
                    'spesifikasi' => $spesifikasi,
                    'jumlah' => $jumlah,
                    'satuan' => $satuan,
                    'keterangan' => $keterangan,
                ]);

                if ($supplyModel && $jumlah > 0) {
                    $supplyModel->kurangiStok(
                        jumlah: $jumlah,
                        distribusi: $distribusi,
                        keterangan: "Distribusi BAST #{$nomorBast} ke {$distribusi->pihak_kedua_nama} ({$distribusi->pihak_kedua_unit_kerja})",
                        userId: auth()->id()
                    );
                }
            }

            return $distribusi;
        });

        return redirect()->route('supply.distribusi.show', $distribusi->id)
            ->with('success', "Distribusi barang berhasil dicatat dan dokumen BAST #{$distribusi->nomor_bast} telah diterbitkan.");
    }

    /**
     * Tampilkan detail dokumen BAST dan transaksi distribusi pengeluaran barang.
     */
    public function show(int $id): View
    {
        $distribusi = SupplyDistribusi::with(['items.supply', 'permintaan', 'realisasi', 'user'])->findOrFail($id);
        $bast = $distribusi;

        return view('supply.distribusi.show', compact('distribusi', 'bast'));
    }

    /**
     * Form penyesuaian / edit data dokumen BAST.
     */
    public function edit(int $id): View|RedirectResponse
    {
        if (SatkerService::isViewingOtherSatker()) {
            return redirect()->route('supply.distribusi.index')
                ->with('error', 'Mode Lihat Saja: Pengubahan data distribusi untuk satker lain tidak diperkenankan.');
        }

        $distribusi = SupplyDistribusi::with(['items.supply', 'permintaan', 'realisasi'])->findOrFail($id);
        $bast = $distribusi;

        return view('supply.distribusi.edit', compact('distribusi', 'bast'));
    }

    /**
     * Simpan penyesuaian data BAST.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        SatkerService::abortIfViewingOtherSatker('pengubahan data distribusi');

        $distribusi = SupplyDistribusi::with('items.supply')->findOrFail($id);

        $request->validate([
            'nomor_bast' => 'required|string|max:100|unique:supply_distribusis,nomor_bast,'.$distribusi->id,
            'tanggal_bast' => 'required|date',
            'pihak_pertama_nama' => 'required|string|max:255',
            'pihak_pertama_nip' => 'nullable|string|max:50',
            'pihak_pertama_jabatan' => 'required|string|max:100',
            'pihak_kedua_nama' => 'required|string|max:255',
            'pihak_kedua_nip' => 'nullable|string|max:50',
            'pihak_kedua_jabatan' => 'nullable|string|max:100',
            'pihak_kedua_unit_kerja' => 'required|string|max:150',
            'kegiatan_keperluan' => 'nullable|string|max:500',
            'items' => 'nullable|array',
            'items.*.jumlah' => 'nullable|integer|min:1',
            'items.*.satuan' => 'nullable|string|max:50',
            'items.*.spesifikasi' => 'nullable|string|max:255',
            'items.*.keterangan' => 'nullable|string|max:500',
        ], [
            'nomor_bast.required' => 'Nomor BAST wajib diisi.',
            'tanggal_bast.required' => 'Tanggal BAST wajib diisi.',
            'pihak_pertama_nama.required' => 'Nama pihak penyerah wajib diisi.',
            'pihak_pertama_jabatan.required' => 'Jabatan pihak penyerah wajib diisi.',
            'pihak_kedua_nama.required' => 'Nama pihak penerima wajib diisi.',
            'pihak_kedua_unit_kerja.required' => 'Unit kerja penerima wajib diisi.',
        ]);

        try {
            DB::transaction(function () use ($request, $distribusi) {
                $distribusi->update([
                    'nomor_bast' => $request->input('nomor_bast'),
                    'tanggal_bast' => $request->input('tanggal_bast'),
                    'pihak_pertama_nama' => $request->input('pihak_pertama_nama'),
                    'pihak_pertama_nip' => $request->input('pihak_pertama_nip'),
                    'pihak_pertama_jabatan' => $request->input('pihak_pertama_jabatan'),
                    'pihak_kedua_nama' => $request->input('pihak_kedua_nama'),
                    'pihak_kedua_nip' => $request->input('pihak_kedua_nip'),
                    'pihak_kedua_jabatan' => $request->input('pihak_kedua_jabatan'),
                    'pihak_kedua_unit_kerja' => $request->input('pihak_kedua_unit_kerja'),
                    'kegiatan_keperluan' => $request->input('kegiatan_keperluan'),
                ]);

                // Update detail item jika disediakan
                $itemsInput = $request->input('items', []);
                foreach ($itemsInput as $itemId => $itemData) {
                    $item = $distribusi->items->find($itemId);
                    if (! $item) {
                        continue;
                    }

                    $newJumlah = isset($itemData['jumlah']) ? (int) $itemData['jumlah'] : $item->jumlah;
                    $diff = $newJumlah - $item->jumlah;

                    if ($diff !== 0 && $item->supply_id) {
                        $supply = Supply::find($item->supply_id);
                        if ($supply) {
                            if ($diff > 0) {
                                if ($supply->stok < $diff) {
                                    throw new \Exception("Stok {$supply->nama_barang} tidak mencukupi untuk penambahan (Tersedia: {$supply->stok}, dibutuhkan: {$diff}).");
                                }
                                $supply->kurangiStok(
                                    jumlah: $diff,
                                    distribusi: $distribusi,
                                    keterangan: "Koreksi penyesuaian BAST #{$distribusi->nomor_bast} (tambah {$diff})",
                                    userId: auth()->id()
                                );
                            } else {
                                $supply->tambahStok(
                                    jumlah: abs($diff),
                                    keterangan: "Koreksi penyesuaian BAST #{$distribusi->nomor_bast} (kembali ".abs($diff).')',
                                    userId: auth()->id()
                                );
                            }
                        }
                    }

                    $item->update([
                        'jumlah' => $newJumlah,
                        'satuan' => $itemData['satuan'] ?? $item->satuan,
                        'spesifikasi' => $itemData['spesifikasi'] ?? $item->spesifikasi,
                        'keterangan' => $itemData['keterangan'] ?? $item->keterangan,
                    ]);
                }
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('supply.distribusi.show', $distribusi->id)
            ->with('success', "Penyesuaian data BAST #{$distribusi->nomor_bast} berhasil disimpan.");
    }

    /**
     * Tampilan dokumen khusus cetak browser (window.print()).
     */
    public function print(int $id): View
    {
        $distribusi = SupplyDistribusi::with(['items.supply', 'permintaan', 'realisasi'])->findOrFail($id);
        $bast = $distribusi;

        return view('supply.distribusi.print', compact('distribusi', 'bast'));
    }

    /**
     * Ekspor dokumen BAST ke format PDF resmi menggunakan Dompdf.
     */
    public function exportPdf(Request $request, int $id): Response
    {
        $distribusi = SupplyDistribusi::with(['items.supply', 'permintaan', 'realisasi'])->findOrFail($id);
        $bast = $distribusi;

        // Siapkan logo BPS base64 agar aman di Dompdf
        $logoBase64 = null;
        $logoPath = public_path('logo_bps.png');
        if (file_exists($logoPath)) {
            $imageData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,'.base64_encode($imageData);
        }

        $pdf = Pdf::loadView('supply.distribusi.pdf', compact('distribusi', 'bast', 'logoBase64'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $cleanNomor = preg_replace('/[^a-zA-Z0-9_-]/', '_', $distribusi->nomor_bast);
        $filename = "BAST-Distribusi-{$cleanNomor}.pdf";

        if ($request->input('download') === '1' || $request->has('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Unggah hasil scan BAST yang telah ditandatangani manual (tanda tangan basah).
     */
    public function uploadTtd(Request $request, int $id): RedirectResponse
    {
        SatkerService::abortIfViewingOtherSatker('unggah scan BAST distribusi');

        $distribusi = SupplyDistribusi::findOrFail($id);

        $request->validate([
            'file_bast_ttd' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
            'catatan' => 'nullable|string|max:500',
        ], [
            'file_bast_ttd.required' => 'File scan BAST wajib dipilih.',
            'file_bast_ttd.mimes' => 'Format berkas harus berupa PDF, JPG, JPEG, atau PNG.',
            'file_bast_ttd.max' => 'Ukuran berkas maksimal adalah 10 MB.',
        ]);

        // Hapus berkas lama jika ada
        if ($distribusi->file_bast_ttd && Storage::disk('public')->exists($distribusi->file_bast_ttd)) {
            Storage::disk('public')->delete($distribusi->file_bast_ttd);
        }

        $path = $request->file('file_bast_ttd')->store('bast_distribusi_scans', 'public');

        $distribusi->update([
            'file_bast_ttd' => $path,
            'status' => 'Selesai',
            'uploaded_at' => now(),
            'catatan' => $request->input('catatan') ?: $distribusi->catatan,
        ]);

        return back()->with('success', "Dokumen BAST #{$distribusi->nomor_bast} bertanda tangan basah berhasil diunggah. Status kini Selesai.");
    }

    /**
     * Hapus berkas scan tanda tangan manual jika ada koreksi.
     */
    public function deleteTtd(int $id): RedirectResponse
    {
        SatkerService::abortIfViewingOtherSatker('penghapusan berkas BAST distribusi');

        $distribusi = SupplyDistribusi::findOrFail($id);

        if ($distribusi->file_bast_ttd && Storage::disk('public')->exists($distribusi->file_bast_ttd)) {
            Storage::disk('public')->delete($distribusi->file_bast_ttd);
        }

        $distribusi->update([
            'file_bast_ttd' => null,
            'status' => 'Menunggu TTD',
            'uploaded_at' => null,
        ]);

        return back()->with('info', "Berkas scan BAST #{$distribusi->nomor_bast} telah dihapus. Status kembali Menunggu TTD.");
    }

    /**
     * Batalkan/hapus distribusi barang dan kembalikan stok fisik ke gudang.
     */
    public function destroy(int $id): RedirectResponse
    {
        SatkerService::abortIfViewingOtherSatker('penghapusan distribusi barang');

        $distribusi = SupplyDistribusi::with('items')->findOrFail($id);

        if ($distribusi->file_bast_ttd && Storage::disk('public')->exists($distribusi->file_bast_ttd)) {
            Storage::disk('public')->delete($distribusi->file_bast_ttd);
        }

        foreach ($distribusi->items as $it) {
            if ($it->supply_id && $it->jumlah > 0) {
                $supply = Supply::find($it->supply_id);
                if ($supply) {
                    $supply->tambahStok(
                        jumlah: $it->jumlah,
                        keterangan: "Pengembalian stok pembatalan distribusi #{$distribusi->nomor_bast}",
                        userId: auth()->id()
                    );
                }
            }
        }

        $nomor = $distribusi->nomor_bast;
        $distribusi->delete();

        return redirect()->route('supply.distribusi.index')
            ->with('success', "Transaksi distribusi barang #{$nomor} berhasil dibatalkan dan stok telah dikembalikan ke gudang.");
    }
}
