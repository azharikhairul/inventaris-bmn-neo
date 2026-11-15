<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Services\AsetImportService;
use App\Services\SatkerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AsetController extends Controller
{
    /**
     * Display a listing of assets (Read-Only Reference Data).
     */
    public function index(Request $request)
    {
        $baseAset = SatkerService::filterQuery(Aset::query(), 'kode_satker');

        // 1. Calculate overall metrics for top summary cards
        $totalAset = (clone $baseAset)->count();
        $totalBaik = (clone $baseAset)->where('kondisi', 'Baik')->count();
        $totalRusakRingan = (clone $baseAset)->where('kondisi', 'Rusak Ringan')->count();
        $totalRusakBerat = (clone $baseAset)->where('kondisi', 'Rusak Berat')->count();

        $totalRuangan = (clone $baseAset)->whereNotNull('lokasi_ruang')
            ->where('lokasi_ruang', '!=', 'Belum berlokasi')
            ->distinct('lokasi_ruang')
            ->count('lokasi_ruang');

        $persenBaik = $totalAset > 0 ? round(($totalBaik / $totalAset) * 100, 1) : 0;
        $persenRusakRingan = $totalAset > 0 ? round(($totalRusakRingan / $totalAset) * 100, 1) : 0;
        $persenRusakBerat = $totalAset > 0 ? round(($totalRusakBerat / $totalAset) * 100, 1) : 0;

        // 2. Dropdown options
        $kategoriList = (clone $baseAset)->whereNotNull('jenis_bmn')
            ->where('jenis_bmn', '!=', '')
            ->distinct()
            ->orderBy('jenis_bmn')
            ->pluck('jenis_bmn');

        $ruanganRawList = (clone $baseAset)->whereNotNull('lokasi_ruang')
            ->where('lokasi_ruang', '!=', '')
            ->distinct()
            ->orderBy('lokasi_ruang')
            ->pluck('lokasi_ruang');

        // Clean room list for display
        $ruanganList = $ruanganRawList->map(function ($raw) {
            $cleaned = trim(preg_replace('/^[-–—\s]+/', '', (string) $raw));

            return [
                'raw' => $raw,
                'cleaned' => empty($cleaned) ? 'Belum berlokasi' : $cleaned,
            ];
        })->unique('raw')->values();

        $kondisiList = ['Baik', 'Rusak Ringan', 'Rusak Berat'];

        // 3. Query with filters
        $query = clone $baseAset;

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nup', 'like', "%{$search}%")
                    ->orWhere('merk', 'like', "%{$search}%")
                    ->orWhere('tipe', 'like', "%{$search}%")
                    ->orWhere('pengguna', 'like', "%{$search}%")
                    ->orWhere('penghuni', 'like', "%{$search}%");
            });
        }

        // Kategori (Jenis BMN)
        if ($kategori = $request->input('kategori')) {
            $query->where('jenis_bmn', $kategori);
        }

        // Ruangan
        if ($ruangan = $request->input('ruangan')) {
            $query->where('lokasi_ruang', $ruangan);
        }

        // Kondisi
        if ($kondisi = $request->input('kondisi')) {
            $query->where('kondisi', $kondisi);
        }

        // Pagination setup
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $asets = $query->orderBy('kode_barang')
            ->orderBy('nup')
            ->paginate($perPage)
            ->withQueryString();

        return view('aset.index', compact(
            'asets',
            'totalAset',
            'totalBaik',
            'totalRusakRingan',
            'totalRusakBerat',
            'totalRuangan',
            'persenBaik',
            'persenRusakRingan',
            'persenRusakBerat',
            'kategoriList',
            'ruanganList',
            'kondisiList'
        ));
    }

    /**
     * Get detail asset data as JSON for modal view.
     */
    /**
     * Get detail asset data as JSON for modal view and edit form.
     */
    public function show($id)
    {
        $aset = Aset::with([
            'permintaanAsets.permintaan',
            'realisasiAsets.realisasi',
        ])->findOrFail($id);

        return response()->json([
            'id' => $aset->id,
            'kode_barang' => $aset->clean_kode_barang,
            'nup' => $aset->clean_nup,
            'nama_barang' => $aset->nama_barang ?? '-',
            'merk' => $aset->merk ?? '-',
            'tipe' => $aset->tipe ?? '-',
            'kondisi' => $aset->kondisi ?? '-',
            'is_rusak_berat' => $aset->is_rusak_berat,
            'is_rusak_ringan' => $aset->is_rusak_ringan,
            'is_baik' => $aset->is_baik,
            'can_maintenance' => $aset->can_maintenance,
            'jenis_bmn' => $aset->jenis_bmn ?? '-',
            'lokasi_ruang' => $aset->clean_lokasi_ruang,
            'kode_satker' => $aset->kode_satker ?? '-',
            'nama_satker' => $aset->nama_satker ?? '-',
            'tanggal_perolehan' => $aset->tanggal_perolehan ? $aset->tanggal_perolehan->translatedFormat('d F Y') : '-',
            'tanggal_buku_pertama' => $aset->tanggal_buku_pertama ? $aset->tanggal_buku_pertama->translatedFormat('d F Y') : '-',
            'umur_aset' => $aset->umur_aset ? $aset->umur_aset.' Tahun' : '-',
            'nilai_perolehan' => $aset->nilai_perolehan ? 'Rp '.number_format($aset->nilai_perolehan, 0, ',', '.') : '-',
            'nilai_buku' => $aset->nilai_buku ? 'Rp '.number_format($aset->nilai_buku, 0, ',', '.') : '-',
            'nilai_penyusutan' => $aset->nilai_penyusutan ? 'Rp '.number_format($aset->nilai_penyusutan, 0, ',', '.') : '-',
            'pengguna' => $aset->pengguna ?? '-',
            'penghuni' => $aset->penghuni ?? '-',
            'no_psp' => $aset->no_psp ?? '-',
            'tanggal_psp' => $aset->tanggal_psp ? $aset->tanggal_psp->translatedFormat('d F Y') : '-',
            'status_sbsn' => $aset->status_sbsn ?? '-',
            'status_bmn_idle' => $aset->status_bmn_idle ?? '-',
            'usul_hapus' => $aset->usul_hapus ?? '-',
            // Raw values for edit form
            'raw_kode_barang' => $aset->clean_kode_barang !== '-' ? $aset->clean_kode_barang : (string) ($aset->kode_barang ?? ''),
            'raw_nup' => $aset->clean_nup !== '-' ? $aset->clean_nup : (string) ($aset->nup ?? ''),
            'raw_nama_barang' => $aset->nama_barang ?? '',
            'raw_merk' => $aset->merk ?? '',
            'raw_tipe' => $aset->tipe ?? '',
            'raw_kondisi' => $aset->kondisi ?? 'Baik',
            'raw_jenis_bmn' => $aset->jenis_bmn ?? '',
            'raw_lokasi_ruang' => $aset->lokasi_ruang ?? '',
            'raw_pengguna' => $aset->pengguna ?? '',
            'raw_penghuni' => $aset->penghuni ?? '',
            'raw_tanggal_perolehan' => $aset->tanggal_perolehan ? $aset->tanggal_perolehan->format('Y-m-d') : '',
            'raw_umur_aset' => $aset->umur_aset ?? '',
            'raw_nilai_perolehan' => $aset->nilai_perolehan !== null ? (float) $aset->nilai_perolehan : '',
            'raw_nilai_buku' => $aset->nilai_buku !== null ? (float) $aset->nilai_buku : '',
            'raw_nilai_penyusutan' => $aset->nilai_penyusutan !== null ? (float) $aset->nilai_penyusutan : '',
            'raw_kode_satker' => $aset->kode_satker ?? '',
            'raw_nama_satker' => $aset->nama_satker ?? '',
            'raw_no_psp' => $aset->no_psp ?? '',
            'raw_tanggal_psp' => $aset->tanggal_psp ? $aset->tanggal_psp->format('Y-m-d') : '',
            'raw_status_sbsn' => $aset->status_sbsn ?? '',
            'raw_status_bmn_idle' => $aset->status_bmn_idle ?? '',
            'raw_usul_hapus' => $aset->usul_hapus ?? '',
            'riwayat_permintaan' => $aset->permintaanAsets->map(function ($pa) {
                return [
                    'id' => $pa->permintaan_id,
                    'pekerjaan' => $pa->permintaan->pekerjaan ?? '-',
                    'tanggal' => $pa->permintaan && $pa->permintaan->created_at ? $pa->permintaan->created_at->translatedFormat('d M Y') : '-',
                    'deskripsi_kerusakan' => $pa->deskripsi_kerusakan ?? '-',
                ];
            }),
        ]);
    }

    /**
     * Store a newly created asset in storage (Manual Create).
     */
    public function store(Request $request)
    {
        SatkerService::abortIfViewingOtherSatker('penambahan data aset');

        $validated = $request->validate([
            'kode_barang' => ['required', 'string', 'max:50'],
            'nup' => ['required', 'string', 'max:20'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'merk' => ['nullable', 'string', 'max:100'],
            'tipe' => ['nullable', 'string', 'max:100'],
            'kondisi' => ['required', 'string', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'jenis_bmn' => ['nullable', 'string', 'max:100'],
            'lokasi_ruang' => ['nullable', 'string', 'max:255'],
            'pengguna' => ['nullable', 'string', 'max:255'],
            'penghuni' => ['nullable', 'string', 'max:255'],
            'tanggal_perolehan' => ['nullable', 'date'],
            'umur_aset' => ['nullable', 'integer', 'min:0', 'max:200'],
            'nilai_perolehan' => ['nullable'],
            'nilai_buku' => ['nullable'],
            'nilai_penyusutan' => ['nullable'],
            'kode_satker' => ['nullable', 'string', 'max:50'],
            'nama_satker' => ['nullable', 'string', 'max:255'],
            'no_psp' => ['nullable', 'string', 'max:100'],
            'tanggal_psp' => ['nullable', 'date'],
            'status_sbsn' => ['nullable', 'string', 'max:50'],
            'status_bmn_idle' => ['nullable', 'string', 'max:50'],
            'usul_hapus' => ['nullable', 'string', 'max:50'],
        ], [
            'kode_barang.required' => 'Kode Barang wajib diisi.',
            'nup.required' => 'Nomor Urut Pendaftaran (NUP) wajib diisi.',
            'nama_barang.required' => 'Nama Barang wajib diisi.',
            'kondisi.required' => 'Kondisi aset wajib dipilih.',
            'kondisi.in' => 'Kondisi aset harus salah satu dari: Baik, Rusak Ringan, atau Rusak Berat.',
            'tanggal_perolehan.date' => 'Format tanggal perolehan tidak valid.',
            'tanggal_psp.date' => 'Format tanggal PSP tidak valid.',
        ]);

        $cleanKodeBarang = trim($request->input('kode_barang'));
        $cleanNup = trim($request->input('nup'));

        // Check uniqueness for (kode_barang, nup)
        $duplicate = Aset::where(function ($q) use ($cleanKodeBarang) {
            $q->where('kode_barang', $cleanKodeBarang)
                ->orWhere('kode_barang', $cleanKodeBarang.'.0');
        })->where(function ($q) use ($cleanNup) {
            $q->where('nup', $cleanNup)
                ->orWhere('nup', $cleanNup.'.0');
        })->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'nup' => "Aset dengan Kode Barang {$cleanKodeBarang} dan NUP {$cleanNup} sudah terdaftar di sistem.",
            ]);
        }

        // Auto-calculate umur_aset if not filled
        $umurAset = $request->input('umur_aset');
        if (($umurAset === null || $umurAset === '') && ! empty($validated['tanggal_perolehan'])) {
            $umurAset = Carbon::parse($validated['tanggal_perolehan'])->diffInYears(now());
        }

        $nilaiPerolehan = $this->cleanNumeric($request->input('nilai_perolehan'));
        $nilaiBuku = $this->cleanNumeric($request->input('nilai_buku'));
        $nilaiPenyusutan = $this->cleanNumeric($request->input('nilai_penyusutan'));

        $lokasiRuang = trim($request->input('lokasi_ruang') ?? '');

        $aset = Aset::create([
            'kode_barang' => $cleanKodeBarang,
            'nup' => $cleanNup,
            'nama_barang' => trim($validated['nama_barang']),
            'merk' => ! empty($validated['merk']) ? trim($validated['merk']) : null,
            'tipe' => ! empty($validated['tipe']) ? trim($validated['tipe']) : null,
            'kondisi' => $validated['kondisi'],
            'jenis_bmn' => ! empty($validated['jenis_bmn']) ? trim($validated['jenis_bmn']) : null,
            'lokasi_ruang' => ! empty($lokasiRuang) ? $lokasiRuang : null,
            'pengguna' => ! empty($validated['pengguna']) ? trim($validated['pengguna']) : null,
            'penghuni' => ! empty($validated['penghuni']) ? trim($validated['penghuni']) : null,
            'tanggal_perolehan' => $validated['tanggal_perolehan'] ?? null,
            'umur_aset' => ($umurAset !== null && $umurAset !== '') ? (int) $umurAset : null,
            'nilai_perolehan' => $nilaiPerolehan,
            'nilai_buku' => $nilaiBuku,
            'nilai_penyusutan' => $nilaiPenyusutan,
            'kode_satker' => ! empty($validated['kode_satker']) ? trim($validated['kode_satker']) : null,
            'nama_satker' => ! empty($validated['nama_satker']) ? trim($validated['nama_satker']) : null,
            'no_psp' => ! empty($validated['no_psp']) ? trim($validated['no_psp']) : null,
            'tanggal_psp' => $validated['tanggal_psp'] ?? null,
            'status_sbsn' => $validated['status_sbsn'] ?? null,
            'status_bmn_idle' => $validated['status_bmn_idle'] ?? null,
            'usul_hapus' => $validated['usul_hapus'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data aset BMN berhasil ditambahkan.',
                'data' => $aset,
            ]);
        }

        return redirect()->route('aset.index')
            ->with('success', 'Data aset BMN ('.$aset->nama_barang.') berhasil ditambahkan secara manual.');
    }

    /**
     * Update the specified asset in storage (Manual Edit).
     */
    public function update(Request $request, $id)
    {
        SatkerService::abortIfViewingOtherSatker('perubahan data aset');

        $aset = Aset::findOrFail($id);

        $validated = $request->validate([
            'kode_barang' => ['required', 'string', 'max:50'],
            'nup' => ['required', 'string', 'max:20'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'merk' => ['nullable', 'string', 'max:100'],
            'tipe' => ['nullable', 'string', 'max:100'],
            'kondisi' => ['required', 'string', 'in:Baik,Rusak Ringan,Rusak Berat'],
            'jenis_bmn' => ['nullable', 'string', 'max:100'],
            'lokasi_ruang' => ['nullable', 'string', 'max:255'],
            'pengguna' => ['nullable', 'string', 'max:255'],
            'penghuni' => ['nullable', 'string', 'max:255'],
            'tanggal_perolehan' => ['nullable', 'date'],
            'umur_aset' => ['nullable', 'integer', 'min:0', 'max:200'],
            'nilai_perolehan' => ['nullable'],
            'nilai_buku' => ['nullable'],
            'nilai_penyusutan' => ['nullable'],
            'kode_satker' => ['nullable', 'string', 'max:50'],
            'nama_satker' => ['nullable', 'string', 'max:255'],
            'no_psp' => ['nullable', 'string', 'max:100'],
            'tanggal_psp' => ['nullable', 'date'],
            'status_sbsn' => ['nullable', 'string', 'max:50'],
            'status_bmn_idle' => ['nullable', 'string', 'max:50'],
            'usul_hapus' => ['nullable', 'string', 'max:50'],
        ], [
            'kode_barang.required' => 'Kode Barang wajib diisi.',
            'nup.required' => 'Nomor Urut Pendaftaran (NUP) wajib diisi.',
            'nama_barang.required' => 'Nama Barang wajib diisi.',
            'kondisi.required' => 'Kondisi aset wajib dipilih.',
            'kondisi.in' => 'Kondisi aset harus salah satu dari: Baik, Rusak Ringan, atau Rusak Berat.',
            'tanggal_perolehan.date' => 'Format tanggal perolehan tidak valid.',
            'tanggal_psp.date' => 'Format tanggal PSP tidak valid.',
        ]);

        $cleanKodeBarang = trim($request->input('kode_barang'));
        $cleanNup = trim($request->input('nup'));

        // Check duplicate excluding this asset
        $duplicate = Aset::where(function ($q) use ($cleanKodeBarang) {
            $q->where('kode_barang', $cleanKodeBarang)
                ->orWhere('kode_barang', $cleanKodeBarang.'.0');
        })->where(function ($q) use ($cleanNup) {
            $q->where('nup', $cleanNup)
                ->orWhere('nup', $cleanNup.'.0');
        })->where('id', '!=', $aset->id)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'nup' => "Aset dengan Kode Barang {$cleanKodeBarang} dan NUP {$cleanNup} sudah terdaftar pada aset lain.",
            ]);
        }

        $umurAset = $request->input('umur_aset');
        if (($umurAset === null || $umurAset === '') && ! empty($validated['tanggal_perolehan'])) {
            $umurAset = Carbon::parse($validated['tanggal_perolehan'])->diffInYears(now());
        }

        $nilaiPerolehan = $this->cleanNumeric($request->input('nilai_perolehan'));
        $nilaiBuku = $this->cleanNumeric($request->input('nilai_buku'));
        $nilaiPenyusutan = $this->cleanNumeric($request->input('nilai_penyusutan'));

        $lokasiRuang = trim($request->input('lokasi_ruang') ?? '');

        $aset->update([
            'kode_barang' => $cleanKodeBarang,
            'nup' => $cleanNup,
            'nama_barang' => trim($validated['nama_barang']),
            'merk' => ! empty($validated['merk']) ? trim($validated['merk']) : null,
            'tipe' => ! empty($validated['tipe']) ? trim($validated['tipe']) : null,
            'kondisi' => $validated['kondisi'],
            'jenis_bmn' => ! empty($validated['jenis_bmn']) ? trim($validated['jenis_bmn']) : null,
            'lokasi_ruang' => ! empty($lokasiRuang) ? $lokasiRuang : null,
            'pengguna' => ! empty($validated['pengguna']) ? trim($validated['pengguna']) : null,
            'penghuni' => ! empty($validated['penghuni']) ? trim($validated['penghuni']) : null,
            'tanggal_perolehan' => $validated['tanggal_perolehan'] ?? null,
            'umur_aset' => ($umurAset !== null && $umurAset !== '') ? (int) $umurAset : null,
            'nilai_perolehan' => $nilaiPerolehan,
            'nilai_buku' => $nilaiBuku,
            'nilai_penyusutan' => $nilaiPenyusutan,
            'kode_satker' => ! empty($validated['kode_satker']) ? trim($validated['kode_satker']) : null,
            'nama_satker' => ! empty($validated['nama_satker']) ? trim($validated['nama_satker']) : null,
            'no_psp' => ! empty($validated['no_psp']) ? trim($validated['no_psp']) : null,
            'tanggal_psp' => $validated['tanggal_psp'] ?? null,
            'status_sbsn' => $validated['status_sbsn'] ?? null,
            'status_bmn_idle' => $validated['status_bmn_idle'] ?? null,
            'usul_hapus' => $validated['usul_hapus'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data aset BMN berhasil diperbarui.',
                'data' => $aset,
            ]);
        }

        return redirect()->route('aset.index')
            ->with('success', 'Data aset BMN ('.$aset->nama_barang.') berhasil diperbarui.');
    }

    /**
     * Clean numeric string into float.
     */
    private function cleanNumeric(?string $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        $raw = trim((string) $value);
        // Strip Rp, spaces, letters
        $clean = preg_replace('/[^\d.,]/', '', $raw);
        if (empty($clean)) {
            return null;
        }
        if (str_contains($clean, '.') && str_contains($clean, ',')) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (str_contains($clean, ',')) {
            $clean = str_replace(',', '.', $clean);
        }

        return is_numeric($clean) ? (float) $clean : null;
    }

    /**
     * Export filtered assets to CSV (Excel compatible).
     */
    public function export(Request $request)
    {
        $query = Aset::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nup', 'like', "%{$search}%")
                    ->orWhere('merk', 'like', "%{$search}%")
                    ->orWhere('tipe', 'like', "%{$search}%");
            });
        }

        if ($kategori = $request->input('kategori')) {
            $query->where('jenis_bmn', $kategori);
        }

        if ($ruangan = $request->input('ruangan')) {
            $query->where('lokasi_ruang', $ruangan);
        }

        if ($kondisi = $request->input('kondisi')) {
            $query->where('kondisi', $kondisi);
        }

        $filename = 'daftar_aset_bmn_'.date('Ymd_His').'.csv';

        return new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // CSV Header
            fputcsv($handle, [
                'Kode Barang',
                'NUP',
                'Nama Barang',
                'Merk / Tipe',
                'Jenis BMN',
                'Lokasi Ruang',
                'Tanggal Perolehan',
                'Kondisi',
                'Status Kelayakan Maintenance',
                'Umur Aset (Thn)',
                'Nilai Perolehan (Rp)',
                'Nilai Buku (Rp)',
            ]);

            $query->chunk(500, function ($asets) use ($handle) {
                foreach ($asets as $item) {
                    $kelayakan = $item->is_rusak_berat ? 'TIDAK DIMAINENANCE (USULAN HAPUS)' : 'LAYAK PEMELIHARAAN';
                    fputcsv($handle, [
                        $item->clean_kode_barang,
                        $item->clean_nup,
                        $item->nama_barang,
                        trim(($item->merk ?? '').' '.($item->tipe ?? '')),
                        $item->jenis_bmn,
                        $item->clean_lokasi_ruang,
                        $item->tanggal_perolehan ? $item->tanggal_perolehan->format('Y-m-d') : '-',
                        $item->kondisi,
                        $kelayakan,
                        $item->umur_aset ?? 0,
                        $item->nilai_perolehan ?? 0,
                        $item->nilai_buku ?? 0,
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Import asset data from uploaded CSV or Excel file.
     */
    public function import(Request $request, AsetImportService $importService)
    {
        SatkerService::abortIfViewingOtherSatker('import data aset');

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:51200'],
            'mode' => ['nullable', 'string', 'in:safe_update,skip,overwrite_all,update'],
            'ignore_blank' => ['nullable'],
        ], [
            'file.required' => 'Silakan pilih file Excel (.xlsx, .xls) atau CSV (.csv) untuk diimpor.',
            'file.mimes' => 'Format file yang didukung hanya file Excel (.xlsx, .xls) atau CSV (.csv).',
            'file.max' => 'Ukuran file maksimal yang diizinkan adalah 50MB.',
        ]);

        $mode = $request->input('mode', 'safe_update');
        $ignoreBlank = $request->boolean('ignore_blank', true);
        $file = $request->file('file');

        // Otomatis buat snapshot backup data aset sebelum proses import berjalan
        $snapshot = $importService->createSnapshot('sebelum_import');

        $result = $importService->importFile($file, $mode, $ignoreBlank);

        if (! $result['success']) {
            return redirect()->route('aset.index')
                ->with('error', $result['message'] ?? 'Gagal mengimpor data aset.');
        }

        // Simpan metadata snapshot di session agar admin dapat membatalkan (rollback) sewaktu-waktu
        session([
            'last_aset_backup' => $snapshot,
            'can_rollback_import' => true,
        ]);

        $parts = [];
        if ($result['imported'] > 0) {
            $parts[] = "{$result['imported']} data baru ditambahkan";
        }
        if ($result['updated'] > 0) {
            $modeLabel = ($mode === 'safe_update' || $mode === 'update') ? ' (data lapangan dilindungi)' : '';
            $parts[] = "{$result['updated']} data diperbarui{$modeLabel}";
        }
        if ($result['skipped'] > 0) {
            $parts[] = "{$result['skipped']} baris dilewati";
        }

        $detail = empty($parts) ? 'Tidak ada data yang berubah.' : implode(', ', $parts);
        $msg = "Import data aset selesai! {$detail} (Total baris diproses: {$result['total']}). Snapshot cadangan otomatis telah tersimpan.";

        return redirect()->route('aset.index')->with('success', $msg);
    }

    /**
     * Rollback the last asset import using the automated snapshot.
     */
    public function rollbackImport(Request $request, AsetImportService $importService)
    {
        SatkerService::abortIfViewingOtherSatker('rollback import data aset');

        $backupInfo = session('last_aset_backup');

        if (! $backupInfo || empty($backupInfo['path']) || ! file_exists($backupInfo['path'])) {
            return redirect()->route('aset.index')
                ->with('error', 'File snapshot cadangan sebelum import tidak ditemukan atau telah kedaluwarsa.');
        }

        $result = $importService->rollbackSnapshot($backupInfo['path']);

        if (! $result['success']) {
            return redirect()->route('aset.index')
                ->with('error', $result['message']);
        }

        // Bersihkan session rollback
        session()->forget(['last_aset_backup', 'can_rollback_import']);

        return redirect()->route('aset.index')
            ->with('success', "Rollback Berhasil! {$result['message']} (Waktu snapshot: {$backupInfo['timestamp']}).");
    }
}
