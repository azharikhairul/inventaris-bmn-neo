<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\Permintaan;
use App\Models\PermintaanAset;
use App\Models\RabItem;
use App\Models\Realisasi;
use App\Models\Satker;
use App\Models\Supply;
use App\Services\SatkerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PermintaanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $jenis = $request->input('jenis');
        $date = $request->input('date');
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $basePermintaan = SatkerService::filterQuery(Permintaan::doesntHave('realisasis'));
        $query = (clone $basePermintaan)->latest('updated_at');

        if ($search) {
            $query->where('pekerjaan', 'like', "%{$search}%");
        }

        if ($jenis) {
            $query->where('jenis', $jenis);
        }

        if ($date) {
            $query->where(function ($q) use ($date) {
                $q->whereDate('tanggal', $date)
                    ->orWhere(function ($sub) use ($date) {
                        $sub->whereNull('tanggal')->whereDate('created_at', $date);
                    });
            });
        }

        $countSemua = (clone $basePermintaan)->count();
        $countMaintenance = (clone $basePermintaan)->where('jenis', 'Maintenance')->count();
        $countSupply = (clone $basePermintaan)->where('jenis', 'Supply')->count();

        $permintaans = $query->paginate($perPage)->withQueryString();

        return view('permintaan.index', compact(
            'permintaans',
            'search',
            'jenis',
            'date',
            'perPage',
            'countSemua',
            'countMaintenance',
            'countSupply'
        ));
    }

    public function create()
    {
        if (SatkerService::isViewingOtherSatker()) {
            return redirect()->route('permintaan.index')
                ->with('error', 'Mode Lihat Saja: Penambahan permintaan untuk satker lain tidak diperkenankan.');
        }

        $type = strtolower(request('type') ?? request('jenis') ?? 'maintenance');
        $jenis = $type === 'supply' ? 'Supply' : 'Maintenance';

        $selectedAsetId = request('aset_id');
        if ($selectedAsetId) {
            $preselected = Aset::where('id', $selectedAsetId)->get(['id', 'kode_barang', 'nup', 'nama_barang', 'merk', 'tipe', 'lokasi_ruang', 'kondisi']);
            $others = Aset::where('id', '!=', $selectedAsetId)->orderBy('nama_barang')->get(['id', 'kode_barang', 'nup', 'nama_barang', 'merk', 'tipe', 'lokasi_ruang', 'kondisi']);
            $asets = $preselected->merge($others);
        } else {
            $asets = Aset::orderBy('nama_barang')->get(['id', 'kode_barang', 'nup', 'nama_barang', 'merk', 'tipe', 'lokasi_ruang', 'kondisi']);
        }

        $supplies = Supply::orderBy('nama_barang')->get(['id', 'kode_barang', 'nama_barang', 'kategori', 'satuan', 'stok', 'harga_estimasi']);
        $riwayatBarang = $this->getRiwayatBarangOptions();

        return view('permintaan.create', compact('asets', 'supplies', 'jenis', 'riwayatBarang'));
    }

    public function searchAsets(Request $request)
    {
        $query = $request->get('q', '');
        $asets = Aset::search($query)
            ->limit(50)
            ->get(['id', 'kode_barang', 'nup', 'nama_barang', 'merk', 'tipe', 'lokasi_ruang']);

        return response()->json($asets);
    }

    public function store(Request $request)
    {
        SatkerService::abortIfViewingOtherSatker('penambahan permintaan');

        $request->validate([
            'pekerjaan' => 'required|string',
            'tanggal' => 'nullable|date',
            'jenis' => 'nullable|string',
            'asetList' => 'nullable|array',
            'aset_list' => 'nullable|array',
        ]);

        $jenis = $request->input('jenis', 'Maintenance');
        $jenis = in_array($jenis, ['Maintenance', 'Supply']) ? $jenis : 'Maintenance';

        $asetListData = $request->input('asetList') ?? $request->input('aset_list') ?? [];
        $this->validateAsetAndRab($asetListData, $jenis);

        $lockKey = 'permintaan_store_'.md5(
            ($request->ip() ?? '127.0.0.1').'_'.
            trim($request->pekerjaan ?? '').'_'.
            json_encode($asetListData)
        );

        $lock = Cache::lock($lockKey, 5);
        if (! $lock->get()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Permintaan yang sama sedang diproses. Mohon tidak menekan tombol berulang kali.',
                ], 429);
            }

            return redirect()->route('permintaan.index');
        }

        try {
            DB::transaction(function () use ($request, $asetListData, $jenis) {
                $satkerId = Auth::user()?->satker_id ?? SatkerService::getActiveSatkerId();
                if (! $satkerId) {
                    $defSatker = Satker::where('kode_satker', config('satker.default_kode_satker'))->first();
                    $satkerId = $defSatker?->id;
                }

                $permintaan = Permintaan::create([
                    'satker_id' => $satkerId,
                    'pekerjaan' => $request->pekerjaan,
                    'tanggal' => $request->tanggal ? $request->tanggal : now()->toDateString(),
                    'jenis' => $jenis,
                    'total_rab' => 0,
                ]);

                foreach ($asetListData as $asetData) {
                    $asetId = ! empty($asetData['selectedAsset']) ? $asetData['selectedAsset'] : (! empty($asetData['aset_id']) ? $asetData['aset_id'] : null);
                    $namaBarang = ! empty($asetData['nama_barang']) ? trim($asetData['nama_barang']) : null;
                    $rabItems = $asetData['rabItems'] ?? $asetData['rab_items'] ?? [];
                    if ($jenis === 'Supply' && ! $namaBarang && ! empty($rabItems) && ! empty($rabItems[0]['uraian'])) {
                        $namaBarang = trim($rabItems[0]['uraian']);
                    }
                    $deskripsi = $asetData['deskripsi_kerusakan'] ?? null;

                    // Ambil kondisi resmi langsung dari data aset master jika ada aset_id
                    $asetModel = $asetId ? Aset::find($asetId) : null;
                    $kondisi = $asetModel ? $asetModel->kondisi : ($asetData['kondisi_aset'] ?? null);
                    if ($asetModel && ! $namaBarang) {
                        $namaBarang = $asetModel->nama_barang;
                    }

                    $permintaanAset = $permintaan->permintaanAsets()->create([
                        'aset_id' => $asetId,
                        'nama_barang' => $namaBarang,
                        'deskripsi_kerusakan' => $deskripsi,
                        'kondisi_aset' => $kondisi,
                    ]);

                    $rabItems = $asetData['rabItems'] ?? $asetData['rab_items'] ?? [];
                    foreach ($rabItems as $rab) {
                        $uraian = trim($rab['uraian'] ?? '');
                        $vol = (int) ($rab['volume'] ?? 1);
                        $satuan = $rab['satuan'] ?? 'unit';
                        $harga = (float) ($rab['hargaSatuan'] ?? $rab['harga_satuan'] ?? 0);
                        $jumlah = $vol * $harga;

                        $permintaanAset->rabItems()->create([
                            'uraian' => $uraian,
                            'volume' => $vol,
                            'satuan' => $satuan,
                            'harga_satuan' => $harga,
                            'jumlah' => $jumlah,
                        ]);
                    }
                }

                $permintaan->hitungTotalRab();
            });
        } finally {
            $lock->release();
        }

        if ($request->wantsJson()) {
            session()->flash('success', 'Data permintaan baru berhasil ditambahkan.');

            return response()->json([
                'success' => true,
                'message' => 'Data permintaan baru berhasil ditambahkan.',
                'redirect' => route('permintaan.index'),
            ]);
        }

        return redirect()->route('permintaan.index')->with('success', 'Data permintaan baru berhasil ditambahkan.');
    }

    public function show($id)
    {
        $p = Permintaan::with(['permintaanAsets.aset', 'permintaanAsets.rabItems'])->findOrFail($id);

        $permintaan = [
            'id' => $p->id,
            'pekerjaan' => $p->pekerjaan,
            'tanggal' => $p->tanggal ? $p->tanggal->translatedFormat('d F Y') : ($p->created_at ? $p->created_at->translatedFormat('d F Y') : '-'),
            'tanggal_raw' => $p->tanggal ? $p->tanggal->format('Y-m-d') : ($p->created_at ? $p->created_at->format('Y-m-d') : ''),
            'jenis' => $p->jenis,
            'total_seluruh_anggaran' => (float) $p->total_rab,
            'asetList' => $p->permintaanAsets->map(function ($pa, $idx) use ($p) {
                $namaAset = $pa->aset ? $pa->aset->nama_barang : ($pa->nama_barang ?: ($p->jenis === 'Supply' ? 'Barang Persediaan '.($idx + 1) : 'Aset Non-Register'));
                $kodeInventaris = $pa->aset ? (($pa->aset->kode_barang ?? '-').' / '.($pa->aset->nup ?? '-')) : ($p->jenis === 'Supply' ? 'Supply / Persediaan' : '-');
                $deskripsi = $pa->deskripsi_kerusakan ?? '-';
                $kondisi = $pa->kondisi_aset ?? ($pa->aset ? $pa->aset->kondisi : '-');

                return [
                    'id' => $pa->id,
                    'nama_aset' => $namaAset,
                    'nama_barang' => $pa->nama_barang ?? ($pa->aset ? $pa->aset->nama_barang : ''),
                    'kode_inventaris' => $kodeInventaris,
                    'deskripsi_kerusakan' => $deskripsi,
                    'kondisi_aset' => $kondisi,
                    'rabItems' => $pa->rabItems->map(function ($rab, $rIdx) {
                        return [
                            'id' => $rab->id,
                            'uraian' => $rab->uraian,
                            'volume' => (int) $rab->volume,
                            'satuan' => $rab->satuan,
                            'hargaSatuan' => (float) $rab->harga_satuan,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray(),
        ];

        return view('permintaan.show', compact('permintaan'));
    }

    public function edit($id)
    {
        if (SatkerService::isViewingOtherSatker()) {
            return redirect()->route('permintaan.show', $id)
                ->with('error', 'Mode Lihat Saja: Perubahan data permintaan untuk satker lain tidak diperkenankan.');
        }

        $p = Permintaan::with(['permintaanAsets.aset', 'permintaanAsets.rabItems'])->findOrFail($id);
        $asets = Aset::orderBy('nama_barang')->get(['id', 'kode_barang', 'nup', 'nama_barang', 'merk', 'tipe', 'lokasi_ruang', 'kondisi']);
        $supplies = Supply::orderBy('nama_barang')->get(['id', 'kode_barang', 'nama_barang', 'kategori', 'satuan', 'stok', 'harga_estimasi']);

        $permintaan = [
            'id' => $p->id,
            'pekerjaan' => $p->pekerjaan,
            'tanggal' => $p->tanggal ? $p->tanggal->format('Y-m-d') : ($p->created_at ? $p->created_at->format('Y-m-d') : date('Y-m-d')),
            'jenis' => $p->jenis,
            'asetList' => $p->permintaanAsets->map(function ($pa) {
                return [
                    'id' => $pa->id,
                    'search' => '',
                    'selectedAsset' => $pa->aset_id ?? '',
                    'nama_barang' => $pa->nama_barang ?? ($pa->aset ? $pa->aset->nama_barang : ''),
                    'deskripsi_kerusakan' => $pa->deskripsi_kerusakan ?? '',
                    'kondisi_aset' => $pa->aset ? $pa->aset->kondisi : ($pa->kondisi_aset ?? ''),
                    'rabItems' => $pa->rabItems->map(function ($rab) {
                        return [
                            'id' => $rab->id,
                            'uraian' => $rab->uraian,
                            'volume' => (int) $rab->volume,
                            'satuan' => $rab->satuan,
                            'hargaSatuan' => (float) $rab->harga_satuan,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray(),
        ];

        $riwayatBarang = $this->getRiwayatBarangOptions();

        return view('permintaan.edit', compact('permintaan', 'asets', 'supplies', 'riwayatBarang'));
    }

    public function update(Request $request, $id)
    {
        SatkerService::abortIfViewingOtherSatker('perubahan data permintaan');

        $p = Permintaan::findOrFail($id);

        $request->validate([
            'pekerjaan' => 'required|string',
            'tanggal' => 'nullable|date',
            'jenis' => 'nullable|string',
            'asetList' => 'nullable|array',
            'aset_list' => 'nullable|array',
        ]);

        $jenis = $request->input('jenis', $p->jenis);
        $jenis = in_array($jenis, ['Maintenance', 'Supply']) ? $jenis : $p->jenis;

        $asetListData = $request->input('asetList') ?? $request->input('aset_list') ?? [];
        $this->validateAsetAndRab($asetListData, $jenis);

        DB::transaction(function () use ($p, $request, $asetListData, $jenis) {
            $p->update([
                'pekerjaan' => $request->pekerjaan,
                'tanggal' => $request->tanggal ? $request->tanggal : ($p->tanggal ?? now()->toDateString()),
                'jenis' => $jenis,
            ]);

            $p->permintaanAsets()->delete();

            foreach ($asetListData as $asetData) {
                $asetId = ! empty($asetData['selectedAsset']) ? $asetData['selectedAsset'] : (! empty($asetData['aset_id']) ? $asetData['aset_id'] : null);
                $namaBarang = ! empty($asetData['nama_barang']) ? trim($asetData['nama_barang']) : null;
                $rabItems = $asetData['rabItems'] ?? $asetData['rab_items'] ?? [];
                if ($jenis === 'Supply' && ! $namaBarang && ! empty($rabItems) && ! empty($rabItems[0]['uraian'])) {
                    $namaBarang = trim($rabItems[0]['uraian']);
                }
                $deskripsi = $asetData['deskripsi_kerusakan'] ?? null;

                // Ambil kondisi resmi langsung dari data aset master jika ada
                $asetModel = $asetId ? Aset::find($asetId) : null;
                $kondisi = $asetModel ? $asetModel->kondisi : ($asetData['kondisi_aset'] ?? null);
                if ($asetModel && ! $namaBarang) {
                    $namaBarang = $asetModel->nama_barang;
                }

                $permintaanAset = $p->permintaanAsets()->create([
                    'aset_id' => $asetId,
                    'nama_barang' => $namaBarang,
                    'deskripsi_kerusakan' => $deskripsi,
                    'kondisi_aset' => $kondisi,
                ]);

                $rabItems = $asetData['rabItems'] ?? $asetData['rab_items'] ?? [];
                foreach ($rabItems as $rab) {
                    $uraian = trim($rab['uraian'] ?? '');
                    $vol = (int) ($rab['volume'] ?? 1);
                    $satuan = $rab['satuan'] ?? 'unit';
                    $harga = (float) ($rab['hargaSatuan'] ?? $rab['harga_satuan'] ?? 0);
                    $jumlah = $vol * $harga;

                    $permintaanAset->rabItems()->create([
                        'uraian' => $uraian,
                        'volume' => $vol,
                        'satuan' => $satuan,
                        'harga_satuan' => $harga,
                        'jumlah' => $jumlah,
                    ]);
                }
            }

            $p->hitungTotalRab();
        });

        if ($request->wantsJson()) {
            session()->flash('success', 'Data permintaan berhasil diubah.');

            return response()->json([
                'success' => true,
                'message' => 'Data permintaan berhasil diubah.',
                'redirect' => route('permintaan.index'),
            ]);
        }

        return redirect()->route('permintaan.index')->with('success', 'Data permintaan berhasil diubah.');
    }

    public function destroy($id)
    {
        SatkerService::abortIfViewingOtherSatker('penghapusan data permintaan');

        $permintaan = Permintaan::findOrFail($id);
        $permintaan->delete();

        return redirect()->route('permintaan.index')->with('success', 'Data permintaan berhasil dihapus.');
    }

    public function prosesRealisasi(Request $request, $id)
    {
        SatkerService::abortIfViewingOtherSatker('pemrosesan realisasi');

        $request->validate([
            'no_fpa' => 'required|string',
            'vendor' => 'required|string',
            'tanggal_mulai' => 'required|date',
        ]);

        $permintaan = Permintaan::with(['permintaanAsets.aset', 'permintaanAsets.rabItems'])->findOrFail($id);

        $realisasi = DB::transaction(function () use ($permintaan, $request) {
            if ($permintaan->jenis === 'Supply') {
                $existing = Realisasi::where('permintaan_id', $permintaan->id)
                    ->where('status', 'siap realisasi')
                    ->latest()
                    ->first();

                if ($existing) {
                    $existing->update([
                        'no_fpa' => $request->no_fpa,
                        'vendor' => $request->vendor,
                        'tanggal_mulai' => $request->tanggal_mulai,
                    ]);

                    return $existing;
                }
            }

            $realisasi = Realisasi::create([
                'satker_id' => $permintaan->satker_id,
                'permintaan_id' => $permintaan->id,
                'no_fpa' => $request->no_fpa,
                'vendor' => $request->vendor,
                'pekerjaan' => $permintaan->pekerjaan,
                'jenis' => $permintaan->jenis,
                'tanggal_pengajuan' => $permintaan->tanggal ?? $permintaan->created_at ?? now(),
                'tanggal_mulai' => $request->tanggal_mulai,
                'status' => 'siap realisasi',
                'total_realisasi' => 0,
            ]);

            if ($permintaan->jenis === 'Supply') {
                $realisasiAset = $realisasi->realisasiAsets()->create([
                    'aset_id' => null,
                    'nama_aset' => 'Barang Persediaan',
                    'kode_inventaris' => 'Supply / Persediaan',
                    'deskripsi_kerusakan' => null,
                    'subtotal' => 0,
                ]);

                foreach ($permintaan->permintaanAsets as $pa) {
                    foreach ($pa->rabItems as $rab) {
                        $vol = (int) $rab->volume;
                        $sat = $rab->satuan;
                        $hrg = (float) $rab->harga_satuan;
                        $jml = (float) ($rab->jumlah ?: ($vol * $hrg));

                        $realisasiAset->realisasiItems()->create([
                            'uraian' => $rab->uraian,
                            'volume' => $vol,
                            'satuan' => $sat,
                            'harga_satuan' => $hrg,
                            'jumlah' => $jml,
                        ]);
                    }
                }

                $realisasiAset->hitungSubtotal();
            } else {
                foreach ($permintaan->permintaanAsets as $idx => $pa) {
                    $namaAset = $pa->aset ? $pa->aset->nama_barang : ($pa->nama_barang ?: 'Aset Non-Register');
                    $kodeInventaris = $pa->aset ? (($pa->aset->kode_barang ?? '-').' / '.($pa->aset->nup ?? '-')) : '-';

                    $realisasiAset = $realisasi->realisasiAsets()->create([
                        'aset_id' => $pa->aset_id,
                        'nama_aset' => $namaAset,
                        'kode_inventaris' => $kodeInventaris,
                        'deskripsi_kerusakan' => $pa->deskripsi_kerusakan,
                        'subtotal' => 0,
                    ]);

                    foreach ($pa->rabItems as $rab) {
                        $vol = (int) $rab->volume;
                        $sat = $rab->satuan;
                        $hrg = (float) $rab->harga_satuan;
                        $jml = (float) ($rab->jumlah ?: ($vol * $hrg));

                        $realisasiAset->realisasiItems()->create([
                            'uraian' => $rab->uraian,
                            'volume' => $vol,
                            'satuan' => $sat,
                            'harga_satuan' => $hrg,
                            'jumlah' => $jml,
                        ]);
                    }

                    $realisasiAset->hitungSubtotal();
                }
            }

            $realisasi->hitungTotalRealisasi();

            return $realisasi;
        });

        return redirect()->route('realisasi.proses', $realisasi->id)->with('success', 'Permintaan berhasil diproses ke tahap realisasi.');
    }

    /**
     * Validasi data aset dan kelengkapan item RAB sebelum disimpan ke database.
     *
     * @throws ValidationException
     */
    private function validateAsetAndRab(array $asetListData, string $jenis = 'Maintenance'): void
    {
        if (empty($asetListData)) {
            $itemLabel = $jenis === 'Supply' ? 'barang persediaan' : 'aset';
            throw ValidationException::withMessages([
                'asetList' => "Permintaan harus memiliki minimal 1 {$itemLabel}.",
            ]);
        }

        foreach ($asetListData as $index => $asetData) {
            $itemNumber = $index + 1;
            $itemLabel = $jenis === 'Supply' ? "Barang {$itemNumber}" : "Aset {$itemNumber}";
            $asetId = ! empty($asetData['selectedAsset']) ? $asetData['selectedAsset'] : (! empty($asetData['aset_id']) ? $asetData['aset_id'] : null);
            $namaBarang = ! empty($asetData['nama_barang']) ? trim($asetData['nama_barang']) : null;

            if ($jenis === 'Maintenance') {
                if (! $asetId) {
                    throw ValidationException::withMessages([
                        'asetList' => "Pada {$itemLabel}, Anda wajib memilih aset BMN yang akan dipelihara.",
                    ]);
                }

                $asetModel = Aset::find($asetId);
                if ($asetModel && $asetModel->is_rusak_berat) {
                    throw ValidationException::withMessages([
                        'pekerjaan' => "Aset '{$asetModel->nama_barang}' (NUP: {$asetModel->clean_nup}) berstatus Rusak Berat dan tidak dapat diajukan pemeliharaan.",
                    ]);
                }
            } else {
                // Untuk Supply: pastikan ada nama barang jika tidak memilih aset BMN
                if (! $namaBarang && ! $asetId) {
                    $hasRabUraian = false;
                    $rabItems = $asetData['rabItems'] ?? $asetData['rab_items'] ?? [];
                    if (! empty($rabItems) && ! empty($rabItems[0]['uraian'])) {
                        $hasRabUraian = true;
                    }

                    if (! $hasRabUraian) {
                        throw ValidationException::withMessages([
                            'asetList' => "Pada {$itemLabel}, mohon isi nama barang persediaan atau rincian item RAB.",
                        ]);
                    }
                }
            }

            $rabItems = $asetData['rabItems'] ?? $asetData['rab_items'] ?? [];
            if (empty($rabItems)) {
                throw ValidationException::withMessages([
                    'rab' => "Rincian kebutuhan/RAB pada {$itemLabel} wajib diisi. Mohon tambahkan minimal 1 item.",
                ]);
            }

            $hasFilledUraian = false;
            foreach ($rabItems as $rabIdx => $rab) {
                $rabNumber = $rabIdx + 1;
                $uraian = trim($rab['uraian'] ?? '');
                if ($uraian === '') {
                    throw ValidationException::withMessages([
                        'rab' => "Item ke-{$rabNumber} pada {$itemLabel} belum diisi. Seluruh item RAB wajib diisi sebelum menyimpan data.",
                    ]);
                }

                $vol = $rab['volume'] ?? 1;
                if (! is_numeric($vol) || $vol <= 0) {
                    throw ValidationException::withMessages([
                        'rab' => "Volume item ke-{$rabNumber} pada {$itemLabel} harus berupa angka lebih dari 0.",
                    ]);
                }

                $satuan = trim($rab['satuan'] ?? '');
                if ($satuan === '') {
                    throw ValidationException::withMessages([
                        'rab' => "Satuan item ke-{$rabNumber} pada {$itemLabel} wajib dipilih.",
                    ]);
                }

                $harga = $rab['hargaSatuan'] ?? $rab['harga_satuan'] ?? null;
                if (! is_numeric($harga) || (float) $harga <= 0) {
                    throw ValidationException::withMessages([
                        'rab' => "Harga Satuan item ke-{$rabNumber} pada {$itemLabel} belum diisi atau masih 0. Seluruh kolom RAB wajib diisi dengan nilai lebih dari 0.",
                    ]);
                }

                $hasFilledUraian = true;
            }

            if (! $hasFilledUraian) {
                throw ValidationException::withMessages([
                    'rab' => "Item atau uraian RAB pada {$itemLabel} wajib diisi.",
                ]);
            }
        }
    }

    /**
     * Generate / View Kerangka Acuan Kerja (KAK) according to official BPS template.
     */
    public function generateKak(Request $request, $id)
    {
        $permintaan = Permintaan::with(['permintaanAsets.aset', 'permintaanAsets.rabItems'])->findOrFail($id);

        $rabItems = [];
        $totalBiaya = 0;

        foreach ($permintaan->permintaanAsets as $pa) {
            foreach ($pa->rabItems as $rab) {
                $vol = (int) $rab->volume;
                $harga = (float) $rab->harga_satuan;
                $subtotal = (float) ($rab->jumlah ?: ($vol * $harga));

                $rabItems[] = [
                    'kegiatan' => $rab->uraian,
                    'volume' => $vol,
                    'satuan' => $rab->satuan,
                    'harga_satuan' => $harga,
                    'total' => $subtotal,
                ];

                $totalBiaya += $subtotal;
            }
        }

        if ($totalBiaya <= 0) {
            $totalBiaya = (float) $permintaan->total_rab;
        }

        // Count unit aset
        $totalUnit = $permintaan->permintaanAsets->count();
        if ($totalUnit <= 0) {
            $totalUnit = 1;
        }

        $tgl = $permintaan->tanggal ?? $permintaan->created_at ?? now();
        $tahun = $tgl->format('Y');
        $bulanTahun = $tgl->translatedFormat('F Y');
        $tanggalLengkap = $tgl->translatedFormat('j F Y');

        $totalBiayaTerbilang = $this->terbilang($totalBiaya).' rupiah';
        $totalUnitTerbilang = $this->terbilang($totalUnit);

        // Instansi & Pejabat default dari template resmi BPS Provinsi Gorontalo
        $isSupply = $permintaan->isSupply();

        if ($isSupply) {
            $defaultData = [
                'instansi' => 'BPS PROV. GORONTALO',
                'tahun_anggaran' => $tahun,
                'judul_dokumen' => 'BELANJA KEPERLUAN SEHARI-HARI PERKANTORAN (PERSEDIAAN)',
                'pekerjaan' => $permintaan->pekerjaan,
                'jenis' => 'Supply',
                'program' => '054.01.GG. Program Penyediaan & Pelayanan Informasi Statistik',
                'kegiatan' => '2902 BMA Publikasi/Laporan Sensus Ekonomi',
                'output' => '006. Publikasi/Laporan Sensus Ekonomi',
                'komponen' => '530 Pelaksanaan SE2026',
                'item_kegiatan' => '521811/Belanja Barang Persediaan Barang Konsumsi',
                'total_pagu' => $totalBiaya > 0 ? $totalBiaya : 4055000,
                'waktu_pengadaan' => 'Maret – Desember '.$tahun,
                'waktu_kontrak' => 'Maret – Desember '.$tahun,
                'pemanfaatan_hasil' => 'Maret – Desember '.$tahun,
                'kota' => 'Gorontalo',
                'tanggal' => $tanggalLengkap,
                'waktu_pelaksanaan' => $bulanTahun,
                'penanggung_jawab_nama' => 'DWI ALWI ASTUTI',
                'penanggung_jawab_nip' => '198404022007012005',
                'ppk_nama' => 'Sity Defriani Djou',
                'ppk_nip' => '19861217 200604 2 001',
            ];
        } else {
            $defaultData = [
                'instansi' => 'BPS Provinsi Gorontalo',
                'tahun_anggaran' => $tahun,
                'pekerjaan' => $permintaan->pekerjaan,
                'jenis' => $permintaan->jenis,
                'kota' => 'Gorontalo',
                'tanggal' => $tanggalLengkap,
                'waktu_pelaksanaan' => $bulanTahun,
                'penanggung_jawab_nama' => 'Dwi Alwi Astuti, SST, M.Ec.Dev',
                'penanggung_jawab_nip' => '19840402 200701 2 005',
                'ppk_nama' => 'Sity Defriani Djou, SE, M.AP.',
                'ppk_nip' => '19861217 200604 2 001',
            ];
        }

        return view('permintaan.kak', compact(
            'permintaan',
            'rabItems',
            'totalBiaya',
            'totalBiayaTerbilang',
            'totalUnit',
            'totalUnitTerbilang',
            'defaultData'
        ));
    }

    /**
     * Konversi angka ke terbilang bahasa Indonesia.
     */
    public function terbilang($nilai): string
    {
        $nilai = abs($nilai);
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($nilai < 12) {
            $temp = ' '.$huruf[$nilai];
        } elseif ($nilai < 20) {
            $temp = $this->terbilang($nilai - 10).' belas';
        } elseif ($nilai < 100) {
            $temp = $this->terbilang((int) ($nilai / 10)).' puluh '.$this->terbilang($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = ' seratus '.$this->terbilang($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = $this->terbilang((int) ($nilai / 100)).' ratus '.$this->terbilang($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = ' seribu '.$this->terbilang($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = $this->terbilang((int) ($nilai / 1000)).' ribu '.$this->terbilang($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $temp = $this->terbilang((int) ($nilai / 1000000)).' juta '.$this->terbilang($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            $temp = $this->terbilang((int) ($nilai / 1000000000)).' milyar '.$this->terbilang(fmod($nilai, 1000000000));
        } else {
            $temp = $this->terbilang((int) ($nilai / 1000000000000)).' triliun '.$this->terbilang(fmod($nilai, 1000000000000));
        }

        return preg_replace('/\s+/', ' ', trim($temp));
    }

    /**
     * Download or view Kerangka Acuan Kerja (KAK) as a native PDF.
     */
    public function downloadKakPdf(Request $request, $id)
    {
        $permintaan = Permintaan::with(['permintaanAsets.aset', 'permintaanAsets.rabItems'])->findOrFail($id);

        $rabItems = [];
        $totalBiaya = 0;

        foreach ($permintaan->permintaanAsets as $pa) {
            foreach ($pa->rabItems as $rab) {
                $vol = (int) $rab->volume;
                $harga = (float) $rab->harga_satuan;
                $subtotal = (float) ($rab->jumlah ?: ($vol * $harga));

                $rabItems[] = [
                    'kegiatan' => $rab->uraian,
                    'volume' => $vol,
                    'satuan' => $rab->satuan,
                    'harga_satuan' => $harga,
                    'total' => $subtotal,
                ];

                $totalBiaya += $subtotal;
            }
        }

        if ($totalBiaya <= 0) {
            $totalBiaya = (float) $permintaan->total_rab;
        }

        $totalUnit = $permintaan->permintaanAsets->count() ?: 1;
        $tgl = $permintaan->tanggal ?? $permintaan->created_at ?? now();
        $tahun = $tgl->format('Y');
        $bulanTahun = $tgl->translatedFormat('F Y');
        $tanggalLengkap = $tgl->translatedFormat('j F Y');

        $totalBiayaTerbilang = $this->terbilang($totalBiaya).' rupiah';
        $totalUnitTerbilang = $this->terbilang($totalUnit);

        $isSupply = $permintaan->isSupply();

        // Parameters (supports overrides from form POST/GET)
        if ($isSupply) {
            $form = [
                'instansi' => $request->input('instansi', 'BPS PROV. GORONTALO'),
                'tahun_anggaran' => $request->input('tahun_anggaran', $tahun),
                'judul_dokumen' => $request->input('judul_dokumen', 'BELANJA KEPERLUAN SEHARI-HARI PERKANTORAN (PERSEDIAAN)'),
                'pekerjaan' => $request->input('pekerjaan', $permintaan->pekerjaan),
                'jenis' => 'Supply',
                'program' => $request->input('program', '054.01.GG. Program Penyediaan & Pelayanan Informasi Statistik'),
                'kegiatan' => $request->input('kegiatan', '2902 BMA Publikasi/Laporan Sensus Ekonomi'),
                'output' => $request->input('output', '006. Publikasi/Laporan Sensus Ekonomi'),
                'komponen' => $request->input('komponen', '530 Pelaksanaan SE2026'),
                'item_kegiatan' => $request->input('item_kegiatan', '521811/Belanja Barang Persediaan Barang Konsumsi'),
                'total_pagu' => $request->input('total_pagu', $totalBiaya > 0 ? $totalBiaya : 4055000),
                'waktu_pengadaan' => $request->input('waktu_pengadaan', 'Maret – Desember '.$tahun),
                'waktu_kontrak' => $request->input('waktu_kontrak', 'Maret – Desember '.$tahun),
                'pemanfaatan_hasil' => $request->input('pemanfaatan_hasil', 'Maret – Desember '.$tahun),
                'kota' => $request->input('kota', 'Gorontalo'),
                'tanggal' => $request->input('tanggal', $tanggalLengkap),
                'waktu_pelaksanaan' => $request->input('waktu_pelaksanaan', $bulanTahun),
                'total_unit' => $request->input('total_unit', $totalUnit),
                'total_unit_terbilang' => $request->input('total_unit_terbilang', $totalUnitTerbilang),
                'total_biaya' => $request->input('total_biaya', $totalBiaya),
                'total_biaya_terbilang' => $request->input('total_biaya_terbilang', $totalBiayaTerbilang),
                'penanggung_jawab_nama' => $request->input('penanggung_jawab_nama', 'DWI ALWI ASTUTI'),
                'penanggung_jawab_nip' => $request->input('penanggung_jawab_nip', '198404022007012005'),
                'ppk_nama' => $request->input('ppk_nama', 'Sity Defriani Djou'),
                'ppk_nip' => $request->input('ppk_nip', '19861217 200604 2 001'),
            ];
        } else {
            $form = [
                'instansi' => $request->input('instansi', 'BPS Provinsi Gorontalo'),
                'tahun_anggaran' => $request->input('tahun_anggaran', $tahun),
                'pekerjaan' => $request->input('pekerjaan', $permintaan->pekerjaan),
                'jenis' => $request->input('jenis', $permintaan->jenis),
                'kota' => $request->input('kota', 'Gorontalo'),
                'tanggal' => $request->input('tanggal', $tanggalLengkap),
                'waktu_pelaksanaan' => $request->input('waktu_pelaksanaan', $bulanTahun),
                'total_unit' => $request->input('total_unit', $totalUnit),
                'total_unit_terbilang' => $request->input('total_unit_terbilang', $totalUnitTerbilang),
                'total_biaya' => $request->input('total_biaya', $totalBiaya),
                'total_biaya_terbilang' => $request->input('total_biaya_terbilang', $totalBiayaTerbilang),
                'penanggung_jawab_nama' => $request->input('penanggung_jawab_nama', 'Dwi Alwi Astuti, SST, M.Ec.Dev'),
                'penanggung_jawab_nip' => $request->input('penanggung_jawab_nip', '19840402 200701 2 005'),
                'ppk_nama' => $request->input('ppk_nama', 'Sity Defriani Djou, SE, M.AP.'),
                'ppk_nip' => $request->input('ppk_nip', '19861217 200604 2 001'),
            ];
        }

        // Base64 Logo for rock-solid Dompdf rendering (prioritizing KAK logo)
        $logoBase64 = null;
        $logoPath = public_path('logo_KAK_opt.png');
        if (! file_exists($logoPath)) {
            $logoPath = public_path('logo_KAK.png');
        }
        if (! file_exists($logoPath)) {
            $logoPath = public_path('logo_bps.png');
        }
        if (file_exists($logoPath)) {
            $imageData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,'.base64_encode($imageData);
        }

        $pdf = Pdf::loadView('permintaan.kak_pdf', compact('permintaan', 'rabItems', 'form', 'logoBase64'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $isSupply ? ($form['judul_dokumen'] ?? $form['pekerjaan']) : $form['pekerjaan']);
        $filename = "KAK-{$safeName}-{$permintaan->id}.pdf";

        if ($request->input('download') == '1' || $request->isMethod('post')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Tampilkan antarmuka sesuaikan data Berita Acara Serah Terima (BAST).
     */
    public function generateBast(int $id): View
    {
        $permintaan = Permintaan::with([
            'permintaanAsets.aset',
            'permintaanAsets.rabItems',
            'realisasi',
        ])->findOrFail($id);

        if (! $permintaan->isSupply()) {
            abort(404, 'Dokumen BAST hanya tersedia untuk jenis pengadaan Supply / Persediaan.');
        }

        return $this->renderBastForm($permintaan);
    }

    /**
     * Tampilkan antarmuka sesuaikan data BAST dari record Realisasi.
     */
    public function generateBastFromRealisasi(int $id): View
    {
        $realisasi = Realisasi::with(['permintaan.permintaanAsets.rabItems', 'permintaan.permintaanAsets.aset'])->findOrFail($id);

        if ($realisasi->jenis !== 'Supply') {
            abort(404, 'Dokumen BAST hanya tersedia untuk jenis pengadaan Supply / Persediaan.');
        }

        $permintaan = $realisasi->permintaan;
        if (! $permintaan) {
            $permintaan = new Permintaan([
                'id' => $realisasi->id,
                'pekerjaan' => $realisasi->pekerjaan,
                'jenis' => $realisasi->jenis,
                'tanggal_pengajuan' => $realisasi->tanggal_pengajuan,
            ]);
        }

        return $this->renderBastForm($permintaan, $realisasi);
    }

    /**
     * Helper render form penyesuaian BAST.
     */
    protected function renderBastForm(Permintaan $permintaan, ?Realisasi $realisasi = null): View
    {
        $rabItems = [];
        $totalBiaya = 0;
        $totalUnit = 0;

        if ($realisasi && $realisasi->relationLoaded('realisasiAsets') && $realisasi->realisasiAsets->isNotEmpty()) {
            foreach ($realisasi->realisasiAsets as $ra) {
                foreach ($ra->realisasiItems as $ri) {
                    $vol = (int) ($ri->volume ?: 1);
                    $harga = (float) ($ri->harga_satuan ?: 0);
                    $subtotal = $vol * $harga;
                    $rabItems[] = [
                        'uraian' => $ri->uraian ?: $ra->nama_aset,
                        'volume' => $vol,
                        'satuan' => $ri->satuan ?: 'unit',
                        'harga_satuan' => $harga,
                        'subtotal' => $subtotal,
                        'keterangan' => 'Kondisi Baik & Lengkap',
                    ];
                    $totalBiaya += $subtotal;
                    $totalUnit += $vol;
                }
            }
        } elseif ($permintaan->relationLoaded('permintaanAsets')) {
            foreach ($permintaan->permintaanAsets as $pa) {
                if ($pa->rabItems && $pa->rabItems->isNotEmpty()) {
                    foreach ($pa->rabItems as $rab) {
                        $vol = (int) ($rab->volume ?: 1);
                        $harga = (float) ($rab->harga_satuan ?: 0);
                        $subtotal = $vol * $harga;
                        $rabItems[] = [
                            'uraian' => $rab->uraian,
                            'volume' => $vol,
                            'satuan' => $rab->satuan ?: 'unit',
                            'harga_satuan' => $harga,
                            'subtotal' => $subtotal,
                            'keterangan' => 'Kondisi Baik & Lengkap',
                        ];
                        $totalBiaya += $subtotal;
                        $totalUnit += $vol;
                    }
                } else {
                    $rabItems[] = [
                        'uraian' => $pa->nama_barang ?: $pa->aset?->nama_barang ?: $permintaan->pekerjaan,
                        'volume' => 1,
                        'satuan' => 'unit',
                        'harga_satuan' => 0,
                        'subtotal' => 0,
                        'keterangan' => 'Kondisi Baik & Lengkap',
                    ];
                    $totalUnit += 1;
                }
            }
        }

        if (empty($rabItems)) {
            $rabItems[] = [
                'uraian' => $permintaan->pekerjaan,
                'volume' => 1,
                'satuan' => 'paket',
                'harga_satuan' => 0,
                'subtotal' => 0,
                'keterangan' => 'Kondisi Baik & Lengkap',
            ];
            $totalUnit = 1;
        }

        $now = now();
        $hariArr = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $bulanArr = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        $namaHari = $hariArr[$now->format('l')] ?? 'Rabu';
        $namaBulan = $bulanArr[$now->month] ?? 'November';

        $hariTanggalFormatted = "Pada hari ini, {$namaHari} tanggal {$now->day} bulan {$namaBulan} tahun {$now->year} ({$now->day} ".strtoupper($namaBulan)." {$now->year})";

        $defaultData = [
            'instansi' => 'Badan Pusat Statistik Provinsi Gorontalo',
            'nomor_bast' => 'BAST-'.$now->format('Y/m').'/'.str_pad($permintaan->id, 4, '0', STR_PAD_LEFT),
            'pekerjaan' => $permintaan->pekerjaan,
            'jenis' => $permintaan->jenis,
            'kota' => 'Gorontalo',
            'tanggal' => $now->translatedFormat('d F Y'),
            'hari_tanggal' => $hariTanggalFormatted,
            'pihak_pertama_nama' => $realisasi?->vendor ?: 'CV. Mitra Mandiri BPS',
            'pihak_pertama_jabatan' => 'Penyedia Barang / Jasa',
            'pihak_pertama_perusahaan' => $realisasi?->vendor ?: 'CV. Mitra Mandiri BPS',
            'pihak_kedua_nama' => 'Sity Defriani Djou, SE, M.AP.',
            'pihak_kedua_nip' => '19861217 200604 2 001',
            'pihak_kedua_jabatan' => 'Pejabat Pembuat Komitmen (PPK)',
            'kpa_nama' => 'Mukhamad Mukhanif',
            'kpa_nip' => '19710323 199301 1 001',
            'kpa_jabatan' => 'Kuasa Pengguna Anggaran (KPA)',
        ];

        return view('permintaan.bast', compact(
            'permintaan',
            'realisasi',
            'rabItems',
            'totalBiaya',
            'totalUnit',
            'defaultData'
        ));
    }

    /**
     * Download Berita Acara Serah Terima (BAST) format PDF.
     */
    public function downloadBastPdf(Request $request, int $id): Response
    {
        $permintaan = Permintaan::with([
            'permintaanAsets.aset',
            'permintaanAsets.rabItems',
            'realisasi',
        ])->findOrFail($id);

        if (! $permintaan->isSupply()) {
            abort(404, 'Dokumen BAST hanya tersedia untuk jenis pengadaan Supply / Persediaan.');
        }

        return $this->renderBastPdf($request, $permintaan);
    }

    /**
     * Download BAST PDF dari record Realisasi.
     */
    public function downloadBastPdfFromRealisasi(Request $request, int $id): Response
    {
        $realisasi = Realisasi::with(['permintaan.permintaanAsets.rabItems', 'permintaan.permintaanAsets.aset'])->findOrFail($id);

        if ($realisasi->jenis !== 'Supply') {
            abort(404, 'Dokumen BAST hanya tersedia untuk jenis pengadaan Supply / Persediaan.');
        }

        $permintaan = $realisasi->permintaan;
        if (! $permintaan) {
            $permintaan = new Permintaan([
                'id' => $realisasi->id,
                'pekerjaan' => $realisasi->pekerjaan,
                'jenis' => $realisasi->jenis,
                'tanggal_pengajuan' => $realisasi->tanggal_pengajuan,
            ]);
        }

        return $this->renderBastPdf($request, $permintaan, $realisasi);
    }

    /**
     * Helper render BAST PDF.
     */
    protected function renderBastPdf(Request $request, Permintaan $permintaan, ?Realisasi $realisasi = null): Response
    {
        $form = [
            'instansi' => $request->input('instansi', 'Badan Pusat Statistik Provinsi Gorontalo'),
            'nomor_bast' => $request->input('nomor_bast', 'BAST-'.date('Y/m').'/'.str_pad($permintaan->id, 4, '0', STR_PAD_LEFT)),
            'pekerjaan' => $request->input('pekerjaan', $permintaan->pekerjaan),
            'jenis' => $permintaan->jenis,
            'kota' => $request->input('kota', 'Gorontalo'),
            'tanggal' => $request->input('tanggal', now()->translatedFormat('d F Y')),
            'hari_tanggal' => $request->input('hari_tanggal', 'Pada hari ini, '.now()->translatedFormat('l, d F Y')),
            'pihak_pertama_nama' => $request->input('pihak_pertama_nama', $realisasi?->vendor ?: 'CV. Mitra Mandiri BPS'),
            'pihak_pertama_jabatan' => $request->input('pihak_pertama_jabatan', 'Penyedia Barang / Jasa'),
            'pihak_pertama_perusahaan' => $request->input('pihak_pertama_perusahaan', $realisasi?->vendor ?: 'CV. Mitra Mandiri BPS'),
            'pihak_kedua_nama' => $request->input('pihak_kedua_nama', 'Sity Defriani Djou, SE, M.AP.'),
            'pihak_kedua_nip' => $request->input('pihak_kedua_nip', '19861217 200604 2 001'),
            'pihak_kedua_jabatan' => $request->input('pihak_kedua_jabatan', 'Pejabat Pembuat Komitmen (PPK)'),
            'kpa_nama' => $request->input('kpa_nama', 'Mukhamad Mukhanif'),
            'kpa_nip' => $request->input('kpa_nip', '19710323 199301 1 001'),
            'kpa_jabatan' => $request->input('kpa_jabatan', 'Kuasa Pengguna Anggaran (KPA)'),
        ];

        $rabItemsRaw = $request->input('rabItems');
        if (is_string($rabItemsRaw)) {
            $rabItems = json_decode($rabItemsRaw, true) ?? [];
        } elseif (is_array($rabItemsRaw)) {
            $rabItems = $rabItemsRaw;
        } else {
            $rabItems = [];
        }

        if (empty($rabItems)) {
            $tempView = $this->renderBastForm($permintaan, $realisasi);
            $rabItems = $tempView->getData()['rabItems'] ?? [];
        }

        $logoBase64 = null;
        $logoPath = public_path('logo_bps.png');
        if (file_exists($logoPath)) {
            $imageData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,'.base64_encode($imageData);
        }

        $pdf = Pdf::loadView('permintaan.bast_pdf', compact('permintaan', 'realisasi', 'rabItems', 'form', 'logoBase64'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $cleanNomor = preg_replace('/[^a-zA-Z0-9_-]/', '_', $form['nomor_bast']);
        $filename = "BAST-{$cleanNomor}.pdf";

        if ($request->input('download') == '1' || $request->isMethod('post')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Ambil riwayat barang yang pernah diajukan sebelumnya & master persediaan
     */
    protected function getRiwayatBarangOptions()
    {
        $supplyItems = Supply::select('nama_barang as uraian', 'satuan', 'harga_estimasi as harga_satuan')
            ->whereNotNull('nama_barang')
            ->where('nama_barang', '!=', '')
            ->get()
            ->map(function ($item) {
                return [
                    'uraian' => trim($item->uraian),
                    'satuan' => strtolower(trim($item->satuan ?? 'buah')),
                    'harga_satuan' => (float) ($item->harga_satuan ?? 0),
                    'sumber' => 'Master Persediaan',
                ];
            });

        $rabItems = RabItem::select('uraian', 'satuan', 'harga_satuan')
            ->whereNotNull('uraian')
            ->where('uraian', '!=', '')
            ->latest('id')
            ->limit(500)
            ->get()
            ->map(function ($item) {
                return [
                    'uraian' => trim($item->uraian),
                    'satuan' => strtolower(trim($item->satuan ?? 'unit')),
                    'harga_satuan' => (float) ($item->harga_satuan ?? 0),
                    'sumber' => 'Riwayat Pengajuan',
                ];
            });

        $paItems = PermintaanAset::whereNotNull('nama_barang')
            ->where('nama_barang', '!=', '')
            ->latest('id')
            ->limit(200)
            ->get(['nama_barang'])
            ->map(function ($item) {
                return [
                    'uraian' => trim($item->nama_barang),
                    'satuan' => 'buah',
                    'harga_satuan' => 0,
                    'sumber' => 'Riwayat Pengajuan',
                ];
            });

        return $supplyItems->concat($rabItems)->concat($paItems)
            ->unique(function ($item) {
                return mb_strtolower(trim($item['uraian']));
            })
            ->values();
    }
}
