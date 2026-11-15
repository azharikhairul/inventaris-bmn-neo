<?php

namespace App\Services;

use App\Models\Aset;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AsetImportService
{
    /**
     * Comprehensive header dictionary mapping spreadsheet headers to database columns.
     */
    protected static array $headerMap = [
        'no' => 'no',
        'jenis_bmn' => 'jenis_bmn',
        'kategori' => 'jenis_bmn',
        'kode_satker' => 'kode_satker',
        'nama_satker' => 'nama_satker',
        'kode_barang' => 'kode_barang',
        'kd_brg' => 'kode_barang',
        'kode_bmn' => 'kode_barang',
        'nup' => 'nup',
        'no_nup' => 'nup',
        'nama_barang' => 'nama_barang',
        'uraian_barang' => 'nama_barang',
        'nama_bmn' => 'nama_barang',
        'merk' => 'merk',
        'tipe' => 'tipe',
        'kondisi' => 'kondisi',
        'kondisi_barang' => 'kondisi',
        'umur_aset' => 'umur_aset',
        'umur_aset_thn' => 'umur_aset',
        'intra_extra' => 'intra_extra',
        'henti_guna' => 'henti_guna',
        'status_sbsn' => 'status_sbsn',
        'status_bmn_idle' => 'status_bmn_idle',
        'status_kemitraan' => 'status_kemitraan',
        'bpybds' => 'bpybds',
        'usulan_barang_hilang' => 'usulan_barang_hilang',
        'usulan_barang_rb' => 'usulan_barang_rb',
        'usul_hapus' => 'usul_hapus',
        'hibah_dktp' => 'hibah_dktp',
        'konsensi_jasa' => 'konsensi_jasa',
        'properti_investasi' => 'properti_investasi',
        'jenis_dokumen' => 'jenis_dokumen',
        'no_dokumen' => 'no_dokumen',
        'no_bpkp' => 'no_bpkp',
        'no_polisi' => 'no_polisi',
        'status_sertifikasi' => 'status_sertifikasi',
        'jenis_sertipikat' => 'jenis_sertipikat',
        'no_sertifikat' => 'no_sertifikat',
        'nama' => 'nama',
        'tanggal_buku_pertama' => 'tanggal_buku_pertama',
        'tanggal_perolehan' => 'tanggal_perolehan',
        'tgl_perolehan' => 'tanggal_perolehan',
        'tanggal_pengapusan' => 'tanggal_pengapusan',
        'nilai_perolehan_pertama' => 'nilai_perolehan_pertama',
        'nilai_mutasi' => 'nilai_mutasi',
        'nilai_perolehan' => 'nilai_perolehan',
        'nilai_perolehan_rp' => 'nilai_perolehan',
        'nilai_penyusutan' => 'nilai_penyusutan',
        'nilai_buku' => 'nilai_buku',
        'nilai_buku_rp' => 'nilai_buku',
        'luas_tanah_seluruhnya' => 'luas_tanah_seluruhnya',
        'luas_tanah_untuk_bangunan' => 'luas_tanah_untuk_bangunan',
        'luas_tanah_untuk_sarana' => 'luas_tanah_untuk_sarana',
        'luas_tanah_untuk_sarana_lingkungan' => 'luas_tanah_untuk_sarana',
        'luas_lahan_kosong' => 'luas_lahan_kosong',
        'luas_bangunan' => 'luas_bangunan',
        'luas_tapak_bangunan' => 'luas_tapak_bangunan',
        'luas_pemanfaatan' => 'luas_pemanfaatan',
        'jumlah_lantai' => 'jumlah_lantai',
        'jumlah_foto' => 'jumlah_foto',
        'status_penggunaan' => 'status_penggunaan',
        'no_psp' => 'no_psp',
        'tanggal_psp' => 'tanggal_psp',
        'tgl_psp' => 'tanggal_psp',
        'alamat' => 'alamat',
        'rt_rw' => 'rt_rw',
        'kelurahan_desa' => 'kelurahan_desa',
        'kecamatan' => 'kecamatan',
        'kab_kota' => 'kab_kota',
        'kode_kab_kota' => 'kode_kab_kota',
        'provinsi' => 'provinsi',
        'kode_provinsi' => 'kode_provinsi',
        'kode_pos' => 'kode_pos',
        'sbsk' => 'sbsk',
        'optimalisasi' => 'optimalisasi',
        'penghuni' => 'penghuni',
        'pengguna' => 'pengguna',
        'kode_kpknl' => 'kode_kpknl',
        'uraian_kpknl' => 'uraian_kpknl',
        'uraian_kanwil_djkn' => 'uraian_kanwil',
        'uraian_kanwil' => 'uraian_kanwil',
        'nama_kl' => 'nama_kl',
        'nama_k_l' => 'nama_kl',
        'nama_e1' => 'nama_e1',
        'nama_korwil' => 'nama_korwil',
        'kode_register' => 'kode_register',
        'lokasi_ruang' => 'lokasi_ruang',
        'ruangan' => 'lokasi_ruang',
        'ruang' => 'lokasi_ruang',
    ];

    /**
     * Import a single Excel or CSV file.
     *
     * @param  string|UploadedFile  $file  File path or uploaded file instance
     * @param  string  $mode  'safe_update' (default, protects manual survey fields), 'skip' (ignore duplicates), 'overwrite_all'
     * @param  bool  $ignoreBlank  Whether to ignore blank/empty spreadsheet cells during update
     * @return array Result counts and details
     */
    public function importFile(string|UploadedFile $file, string $mode = 'safe_update', bool $ignoreBlank = true): array
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $originalName = $file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $totalProcessed = 0;

        try {
            // Configure reader based on file type
            if ($extension === 'csv') {
                $reader = new CsvReader;
                // Auto-detect delimiter (comma or semicolon)
                $firstLine = '';
                if ($h = fopen($filePath, 'r')) {
                    $firstLine = fgets($h);
                    fclose($h);
                }
                $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
                $reader->setDelimiter($delimiter);
                $reader->setInputEncoding('UTF-8');
                $spreadsheet = $reader->load($filePath);
            } else {
                $spreadsheet = IOFactory::load($filePath);
            }

            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, true, false);

            if (empty($rows)) {
                return [
                    'success' => false,
                    'message' => 'File kosong atau tidak memiliki baris data.',
                    'imported' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                    'total' => 0,
                ];
            }

            // Extract and normalize header columns
            $rawHeader = array_shift($rows);
            $headers = [];
            foreach ($rawHeader as $index => $colName) {
                if ($colName !== null) {
                    $cleanName = strtolower(trim((string) $colName));
                    $cleanName = str_replace([' ', '/', '-', '(', ')', '[', ']', '.', ':', '\\'], '_', $cleanName);
                    $cleanName = preg_replace('/_+/', '_', $cleanName);
                    $cleanName = trim($cleanName, '_');
                    $headers[$index] = $cleanName;
                }
            }

            $batchSize = 200;
            DB::beginTransaction();

            foreach ($rows as $rowIndex => $row) {
                // Check if row is completely empty
                $nonEmptyValues = array_filter($row, fn ($cell) => $cell !== null && trim((string) $cell) !== '');
                if (empty($nonEmptyValues)) {
                    continue;
                }

                $totalProcessed++;

                $rowAssoc = [];
                foreach ($headers as $colIndex => $colKey) {
                    $rowAssoc[$colKey] = $row[$colIndex] ?? null;
                }

                $kodeBarang = $this->cleanIdentifier($rowAssoc['kode_barang'] ?? $rowAssoc['kd_brg'] ?? $rowAssoc['kode_bmn'] ?? null);
                $nup = $this->cleanIdentifier($rowAssoc['nup'] ?? $rowAssoc['no_nup'] ?? null);

                // If both identifiers are missing, skip row
                if (! $kodeBarang && ! $nup) {
                    $skipped++;

                    continue;
                }

                // Build mapped asset data
                $asetData = [];
                foreach (self::$headerMap as $headerKey => $dbCol) {
                    if (array_key_exists($headerKey, $rowAssoc) && ! array_key_exists($dbCol, $asetData)) {
                        $rawVal = $rowAssoc[$headerKey];
                        $asetData[$dbCol] = $this->castValue($dbCol, $rawVal);
                    }
                }

                // Standardize kode_barang and nup
                $asetData['kode_barang'] = $kodeBarang;
                $asetData['nup'] = $nup;

                // Handle compound "merk_tipe" if separate merk/tipe were missing
                if (empty($asetData['merk']) && empty($asetData['tipe'])) {
                    $compoundMerkTipe = $this->cleanStr($rowAssoc['merk_tipe'] ?? $rowAssoc['merk_dan_tipe'] ?? null);
                    if ($compoundMerkTipe) {
                        $parts = explode(' ', $compoundMerkTipe, 2);
                        $asetData['merk'] = $parts[0] ?? null;
                        $asetData['tipe'] = $parts[1] ?? null;
                    }
                }

                // Standardize Kondisi values (Baik, Rusak Ringan, Rusak Berat)
                if (! empty($asetData['kondisi'])) {
                    $kLower = strtolower(trim($asetData['kondisi']));
                    if (str_contains($kLower, 'berat')) {
                        $asetData['kondisi'] = 'Rusak Berat';
                    } elseif (str_contains($kLower, 'ringan')) {
                        $asetData['kondisi'] = 'Rusak Ringan';
                    } elseif (str_contains($kLower, 'baik')) {
                        $asetData['kondisi'] = 'Baik';
                    } else {
                        $asetData['kondisi'] = ucwords($kLower);
                    }
                }

                // Check duplicate by (kode_barang, nup)
                $existingQuery = Aset::where('kode_barang', $kodeBarang);
                if ($nup !== null) {
                    $existingQuery->where('nup', $nup);
                }
                $existing = $existingQuery->first();

                if ($existing) {
                    if ($mode === 'skip') {
                        // Skip existing record completely
                        $skipped++;
                    } else {
                        // Mode: 'safe_update' (default) or 'overwrite_all' / 'update'
                        $updateData = [];
                        $protectedSurveyCols = ['kondisi', 'lokasi_ruang', 'pengguna', 'penghuni'];

                        foreach ($asetData as $col => $val) {
                            // Rule 1: Ignore blank cells if requested (don't erase existing values with blank cells)
                            if ($ignoreBlank && ($val === null || trim((string) $val) === '')) {
                                continue;
                            }

                            // Rule 2: In 'safe_update' mode, protect manual survey data if existing already has value
                            if (($mode === 'safe_update' || $mode === 'update') && in_array($col, $protectedSurveyCols, true)) {
                                if (! empty($existing->{$col})) {
                                    continue;
                                }
                            }

                            $updateData[$col] = $val;
                        }

                        if (! empty($updateData)) {
                            $existing->update($updateData);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    }
                } else {
                    // Create new asset
                    Aset::create($asetData);
                    $imported++;
                }

                // Commit in batches for memory and performance
                if ($totalProcessed % $batchSize === 0) {
                    DB::commit();
                    DB::beginTransaction();
                }
            }

            DB::commit();

            return [
                'success' => true,
                'file' => $originalName,
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'total' => $totalProcessed,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error importing aset from {$originalName}: ".$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal membaca atau mengimpor file: '.$e->getMessage(),
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'total' => $totalProcessed,
            ];
        }
    }

    /**
     * Import all default asset files from storage.
     */
    public function importAll(): array
    {
        $files = [
            storage_path('app/public/daftar-aset-1.xlsx'),
            storage_path('app/public/daftar-aset-2.xlsx'),
            storage_path('app/public/daftar-aset-3.xlsx'),
            storage_path('app/public/daftar-aset-4.xlsx'),
            storage_path('app/public/daftar-aset-1.csv'),
        ];

        $totalImported = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $processedFiles = [];

        foreach ($files as $filePath) {
            if (! file_exists($filePath)) {
                continue;
            }

            $res = $this->importFile($filePath, 'skip');
            $totalImported += $res['imported'] ?? 0;
            $totalUpdated += $res['updated'] ?? 0;
            $totalSkipped += $res['skipped'] ?? 0;
            $processedFiles[] = [
                'file' => basename($filePath),
                'imported' => $res['imported'] ?? 0,
                'updated' => $res['updated'] ?? 0,
                'skipped' => $res['skipped'] ?? 0,
            ];
        }

        return [
            'total_imported' => $totalImported,
            'total_updated' => $totalUpdated,
            'total_skipped' => $totalSkipped,
            'files' => $processedFiles,
        ];
    }

    protected function castValue(string $field, mixed $val): mixed
    {
        $dateFields = ['tanggal_buku_pertama', 'tanggal_perolehan', 'tanggal_pengapusan', 'tanggal_psp'];
        $floatFields = [
            'nilai_perolehan_pertama', 'nilai_mutasi', 'nilai_perolehan', 'nilai_penyusutan', 'nilai_buku',
            'luas_tanah_seluruhnya', 'luas_tanah_untuk_bangunan', 'luas_tanah_untuk_sarana',
            'luas_lahan_kosong', 'luas_bangunan', 'luas_tapak_bangunan', 'luas_pemanfaatan',
            'sbsk', 'optimalisasi',
        ];
        $intFields = ['jumlah_lantai', 'jumlah_foto', 'umur_aset'];

        if (in_array($field, $dateFields)) {
            return $this->cleanDate($val);
        }

        if (in_array($field, $floatFields)) {
            return $this->cleanFloat($val);
        }

        if (in_array($field, $intFields)) {
            return $this->cleanInt($val);
        }

        $str = $this->cleanStr($val);

        $textFields = ['alamat', 'nama', 'uraian_kpknl', 'uraian_kanwil', 'no_dokumen', 'no_sertifikat'];
        if ($str !== null && ! in_array($field, $textFields) && mb_strlen($str) > 255) {
            return mb_substr($str, 0, 255);
        }

        return $str;
    }

    public function cleanIdentifier(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        $s = trim((string) $v);
        // Remove trailing .0 from floating representation in Excel
        $s = preg_replace('/\.0+$/', '', $s);
        if (in_array(strtolower($s), ['nan', '-', 'null', ''])) {
            return null;
        }

        return $s;
    }

    public function cleanStr(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        $s = trim((string) $v);
        if (in_array(strtolower($s), ['nan', '-', 'null', ''])) {
            return null;
        }

        return $s;
    }

    public function cleanDate(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $v))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
        $s = trim((string) $v);
        if (in_array(strtolower($s), ['nan', '-', 'null', ''])) {
            return null;
        }
        try {
            return Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    public function cleanFloat(mixed $v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v)) {
            return (float) $v;
        }
        $clean = str_replace([',', ' '], '', trim((string) $v));
        if (is_numeric($clean)) {
            return (float) $clean;
        }

        return null;
    }

    public function cleanInt(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v)) {
            return (int) $v;
        }
        $clean = str_replace([',', ' '], '', trim((string) $v));
        if (is_numeric($clean)) {
            return (int) $clean;
        }

        return null;
    }

    /**
     * Create a backup snapshot of current assets before an import operation.
     */
    public function createSnapshot(string $reason = 'sebelum_import'): array
    {
        $dir = storage_path('app/backups/aset');
        if (! file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "snapshot_aset_{$reason}_{$timestamp}.json";
        $filepath = "{$dir}/{$filename}";

        $records = Aset::all()->toArray();
        $payload = [
            'timestamp' => now()->toIso8601String(),
            'formatted_time' => now()->translatedFormat('d F Y H:i:s'),
            'reason' => $reason,
            'total_records' => count($records),
            'data' => $records,
        ];

        file_put_contents($filepath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Keep last 10 snapshots, cleanup older
        $files = glob("{$dir}/snapshot_aset_*.json");
        if ($files && count($files) > 10) {
            usort($files, fn ($a, $b) => filemtime($a) <=> filemtime($b));
            while (count($files) > 10) {
                $oldFile = array_shift($files);
                @unlink($oldFile);
            }
        }

        return [
            'path' => $filepath,
            'filename' => $filename,
            'total_records' => count($records),
            'timestamp' => now()->translatedFormat('d F Y H:i:s'),
        ];
    }

    /**
     * Rollback asset table from a snapshot file.
     */
    public function rollbackSnapshot(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return [
                'success' => false,
                'message' => 'File snapshot cadangan tidak ditemukan.',
            ];
        }

        $raw = file_get_contents($filePath);
        $json = json_decode($raw, true);

        if (! is_array($json) || ! isset($json['data']) || ! is_array($json['data'])) {
            return [
                'success' => false,
                'message' => 'Format file snapshot cadangan tidak valid.',
            ];
        }

        $restored = 0;
        $created = 0;

        DB::beginTransaction();
        try {
            $snapshotIds = collect($json['data'])->pluck('id')->filter()->all();

            // 1. Remove extraneous new assets mistakenly imported that don't belong to any transactions
            if (! empty($snapshotIds)) {
                Aset::whereNotIn('id', $snapshotIds)
                    ->whereDoesntHave('permintaanAsets')
                    ->whereDoesntHave('realisasiAsets')
                    ->delete();
            }

            // 2. Restore all records from snapshot
            foreach ($json['data'] as $item) {
                $existing = null;
                if (! empty($item['id'])) {
                    $existing = Aset::find($item['id']);
                }

                if (! $existing && ! empty($item['kode_barang'])) {
                    $q = Aset::where('kode_barang', $item['kode_barang']);
                    if (isset($item['nup'])) {
                        $q->where('nup', $item['nup']);
                    }
                    $existing = $q->first();
                }

                if ($existing) {
                    $existing->update($item);
                    $restored++;
                } else {
                    Aset::create($item);
                    $created++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'restored' => $restored,
                'created' => $created,
                'total' => count($json['data']),
                'timestamp' => $json['formatted_time'] ?? $json['timestamp'] ?? now()->toIso8601String(),
                'message' => "Pemulihan data berhasil! {$restored} data aset dikembalikan ke kondisi semula.",
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error during aset rollback: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memulihkan snapshot data: '.$e->getMessage(),
            ];
        }
    }
}
