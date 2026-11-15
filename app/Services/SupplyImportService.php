<?php

namespace App\Services;

use App\Models\Supply;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;

class SupplyImportService
{
    /**
     * Dictionary mapping header spreadsheet ke kolom database supplies.
     */
    protected static array $headerMap = [
        'kode_barang' => 'kode_barang',
        'kd_barang' => 'kode_barang',
        'kode' => 'kode_barang',
        'kd_brg' => 'kode_barang',
        'item_code' => 'kode_barang',
        'nama_barang' => 'nama_barang',
        'nama' => 'nama_barang',
        'uraian_barang' => 'nama_barang',
        'item_name' => 'nama_barang',
        'kategori' => 'kategori',
        'jenis' => 'kategori',
        'category' => 'kategori',
        'satuan' => 'satuan',
        'unit' => 'satuan',
        'stok' => 'stok',
        'jumlah_stok' => 'stok',
        'stok_awal' => 'stok',
        'qty' => 'stok',
        'stock' => 'stok',
        'stok_minimum' => 'stok_minimum',
        'min_stok' => 'stok_minimum',
        'minimum_stok' => 'stok_minimum',
        'min_stock' => 'stok_minimum',
        'harga_estimasi' => 'harga_estimasi',
        'harga' => 'harga_estimasi',
        'harga_satuan' => 'harga_estimasi',
        'price' => 'harga_estimasi',
        'spesifikasi' => 'spesifikasi',
        'deskripsi' => 'spesifikasi',
        'keterangan' => 'spesifikasi',
        'spec' => 'spesifikasi',
    ];

    /**
     * Import file Excel atau CSV ke database Supply.
     *
     * @param  string  $mode  'update' untuk update jika kode_barang sama, 'skip' untuk lewati
     */
    public function importFile(string|UploadedFile $file, string $mode = 'update'): array
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
            if ($extension === 'csv' || $extension === 'txt') {
                $reader = new CsvReader;
                $firstLine = '';
                if ($h = fopen($filePath, 'r')) {
                    $firstLine = fgets($h);
                    fclose($h);
                }
                $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
                $reader->setDelimiter($delimiter);
                $reader->setEnclosure('"');
            } else {
                $reader = IOFactory::createReaderForFile($filePath);
                if (method_exists($reader, 'setReadDataOnly')) {
                    $reader->setReadDataOnly(true);
                }
            }

            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestDataRow();
            $highestCol = $worksheet->getHighestDataColumn();
        } catch (\Throwable $e) {
            Log::error("SupplyImport: Gagal memuat file {$originalName}: ".$e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal membaca file: '.$e->getMessage(),
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'total' => 0,
            ];
        }

        // Temukan header row
        $headerRow = 1;
        $columnMapping = [];

        for ($row = 1; $row <= min(10, $highestRow); $row++) {
            $matchedHeaders = 0;
            $tempMap = [];

            for ($col = 'A'; $col <= $highestCol; $col++) {
                $cellVal = trim((string) $worksheet->getCell($col.$row)->getValue());
                if (empty($cellVal)) {
                    continue;
                }

                $norm = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($cellVal)));
                $norm = trim(preg_replace('/_+/', '_', $norm), '_');

                if (isset(self::$headerMap[$norm])) {
                    $tempMap[$col] = self::$headerMap[$norm];
                    $matchedHeaders++;
                }
            }

            if ($matchedHeaders >= 2) {
                $headerRow = $row;
                $columnMapping = $tempMap;
                break;
            }
        }

        if (empty($columnMapping)) {
            return [
                'success' => false,
                'message' => 'Format kolom tidak dikenali. Pastikan file memiliki kolom Nama Barang / Kode Barang / Satuan / Stok.',
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'total' => 0,
            ];
        }

        DB::beginTransaction();
        try {
            for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
                $rowData = [];
                $hasData = false;

                foreach ($columnMapping as $col => $dbCol) {
                    $val = $worksheet->getCell($col.$row)->getValue();
                    if ($val !== null && trim((string) $val) !== '') {
                        $hasData = true;
                    }
                    $rowData[$dbCol] = $val !== null ? trim((string) $val) : null;
                }

                if (! $hasData) {
                    continue;
                }

                $namaBarang = $rowData['nama_barang'] ?? null;
                if (empty($namaBarang)) {
                    $skipped++;

                    continue;
                }

                $totalProcessed++;

                // Generate kode barang jika kosong
                $kodeBarang = ! empty($rowData['kode_barang'])
                    ? trim($rowData['kode_barang'])
                    : 'SUP-'.strtoupper(substr(md5($namaBarang.microtime()), 0, 8));

                $kategori = ! empty($rowData['kategori']) ? trim($rowData['kategori']) : 'Alat Tulis Kantor (ATK)';
                $satuan = ! empty($rowData['satuan']) ? trim($rowData['satuan']) : 'Pcs';
                $stok = isset($rowData['stok']) && is_numeric($rowData['stok']) ? max(0, (int) $rowData['stok']) : 0;
                $stokMinimum = isset($rowData['stok_minimum']) && is_numeric($rowData['stok_minimum']) ? max(0, (int) $rowData['stok_minimum']) : 5;
                $spesifikasi = $rowData['spesifikasi'] ?? null;

                $hargaEstimasi = null;
                if (! empty($rowData['harga_estimasi'])) {
                    $cleanHarga = preg_replace('/[^\d.,]/', '', (string) $rowData['harga_estimasi']);
                    if (str_contains($cleanHarga, '.') && str_contains($cleanHarga, ',')) {
                        $cleanHarga = str_replace('.', '', $cleanHarga);
                        $cleanHarga = str_replace(',', '.', $cleanHarga);
                    } elseif (str_contains($cleanHarga, ',')) {
                        $cleanHarga = str_replace(',', '.', $cleanHarga);
                    }
                    if (is_numeric($cleanHarga)) {
                        $hargaEstimasi = (float) $cleanHarga;
                    }
                }

                $existing = Supply::where('kode_barang', $kodeBarang)->first();

                if ($existing) {
                    if ($mode === 'update') {
                        $existing->update([
                            'nama_barang' => $namaBarang,
                            'kategori' => $kategori,
                            'satuan' => $satuan,
                            'stok_minimum' => $stokMinimum,
                            'spesifikasi' => $spesifikasi ?? $existing->spesifikasi,
                            'harga_estimasi' => $hargaEstimasi ?? $existing->harga_estimasi,
                        ]);

                        // Jika stok di file lebih tinggi atau berbeda dan ingin disesuaikan
                        if (isset($rowData['stok']) && $stok !== $existing->stok) {
                            $diff = $stok - $existing->stok;
                            if ($diff > 0) {
                                $existing->tambahStok($diff, 'Penyesuaian stok via Import Excel/CSV', auth()->id());
                            } else {
                                $existing->update(['stok' => $stok]);
                            }
                        }

                        $updated++;
                    } else {
                        $skipped++;
                    }
                } else {
                    $supply = Supply::create([
                        'kode_barang' => $kodeBarang,
                        'nama_barang' => $namaBarang,
                        'kategori' => $kategori,
                        'satuan' => $satuan,
                        'stok' => $stok,
                        'stok_minimum' => $stokMinimum,
                        'spesifikasi' => $spesifikasi,
                        'harga_estimasi' => $hargaEstimasi,
                    ]);

                    if ($stok > 0) {
                        $supply->mutasis()->create([
                            'jenis' => 'masuk',
                            'jumlah' => $stok,
                            'stok_sebelum' => 0,
                            'stok_sesudah' => $stok,
                            'tanggal' => now()->toDateString(),
                            'keterangan' => 'Saldo awal pendaftaran via Import File',
                            'user_id' => auth()->id(),
                        ]);
                    }

                    $imported++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Import data barang persediaan berhasil.',
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'total' => $totalProcessed,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('SupplyImport: Gagal menyimpan data: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data: '.$e->getMessage(),
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'total' => $totalProcessed,
            ];
        }
    }
}
