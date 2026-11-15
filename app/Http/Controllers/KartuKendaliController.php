<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\Realisasi;
use App\Models\RealisasiAset;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KartuKendaliController extends Controller
{
    /**
     * Display a listing of assets with maintenance history for generating Kartu Kendali.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tahun = $request->input('tahun', date('Y'));

        // Query assets that have maintenance records or all assets
        $query = Aset::query()->select('asets.*');

        if ($search) {
            $terms = array_unique(array_filter([
                $search,
                strcasecmp(trim($search), 'ac') === 0 ? 'A.C' : null,
                strcasecmp(trim($search), 'a.c') === 0 || strcasecmp(trim($search), 'a.c.') === 0 ? 'AC' : null,
            ]));

            $query->where(function ($q) use ($terms) {
                foreach ($terms as $idx => $term) {
                    $boolean = $idx === 0 ? 'and' : 'or';
                    $q->where(function ($sub) use ($term) {
                        $sub->where('nama_barang', 'like', "%{$term}%")
                            ->orWhere('kode_barang', 'like', "%{$term}%")
                            ->orWhere('nup', 'like', "%{$term}%")
                            ->orWhere('merk', 'like', "%{$term}%")
                            ->orWhere('lokasi_ruang', 'like', "%{$term}%");
                    }, null, null, $boolean);
                }
            });
        }

        // Subquery: timestamp saat aset terakhir selesai realisasi
        $query->addSelect([
            'latest_selesai_at' => Realisasi::select(DB::raw('COALESCE(realisasis.tanggal_selesai, realisasis.updated_at)'))
                ->join('realisasi_asets', 'realisasi_asets.realisasi_id', '=', 'realisasis.id')
                ->whereColumn('realisasi_asets.aset_id', 'asets.id')
                ->where('realisasis.status', 'Selesai')
                ->orderByDesc(DB::raw('COALESCE(realisasis.tanggal_selesai, realisasis.updated_at)'))
                ->limit(1),
        ]);

        // Pagination setup
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        // Count realisasi selesai per asset
        $asets = $query->withCount(['realisasiAsets' => function ($q) {
            $q->whereHas('realisasi', function ($sub) {
                $sub->where('status', 'Selesai');
            });
        }])
            ->with(['realisasiAsets.realisasi', 'permintaanAsets.permintaan'])
            ->orderByRaw('latest_selesai_at IS NULL, latest_selesai_at DESC')
            ->orderBy('nama_barang')
            ->paginate($perPage)
            ->withQueryString();

        return view('kartu-kendali.index', compact('asets', 'search', 'tahun'));
    }

    /**
     * Show the printable Kartu Kendali document for a specific asset.
     */
    public function show(Request $request, $id)
    {
        $aset = $this->resolveAsset($id);
        $tahun = $request->input('tahun', date('Y'));

        $rows = $this->getMaintenanceRows($aset, $tahun);
        $totalBiaya = array_sum(array_column($rows, 'biaya'));

        // Available years from realisasi
        $yearsAvailable = RealisasiAset::where('aset_id', $aset->id)
            ->whereHas('realisasi', function ($q) {
                $q->whereNotNull('tanggal_selesai');
            })
            ->with('realisasi')
            ->get()
            ->map(function ($ra) {
                return $ra->realisasi->tanggal_selesai ? $ra->realisasi->tanggal_selesai->format('Y') : null;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (! in_array(date('Y'), $yearsAvailable)) {
            $yearsAvailable[] = date('Y');
        }
        rsort($yearsAvailable);

        return view('kartu-kendali.show', compact('aset', 'rows', 'tahun', 'totalBiaya', 'yearsAvailable'));
    }

    /**
     * Export the Kartu Kendali document directly to a native Microsoft Excel (.xlsx) file.
     */
    public function exportExcel(Request $request, $id)
    {
        $aset = $this->resolveAsset($id);
        $tahun = $request->input('tahun', date('Y'));
        $rows = $this->getMaintenanceRows($aset, $tahun);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kartu Kendali');

        // Page setup
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.5)->setRight(0.5);

        // Optional Logo KAK - positioned near center of header row
        $logoPath = public_path('logo_KAK_opt.png');
        if (! file_exists($logoPath)) {
            $logoPath = public_path('logo_KAK.png');
        }
        if (! file_exists($logoPath)) {
            $logoPath = public_path('logo_bps.png');
        }
        if (file_exists($logoPath)) {
            try {
                $drawing = new Drawing;
                $drawing->setName('Logo KAK');
                $drawing->setDescription('Logo KAK');
                $drawing->setPath($logoPath);
                $drawing->setCoordinates('D1');
                $drawing->setOffsetX(40);
                $drawing->setHeight(48);
                $drawing->setWorksheet($sheet);
            } catch (\Exception $e) {
            }
        }

        // Header Instansi: Badan Pusat Statistik Provinsi Gorontalo (centered)
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'Badan Pusat Statistik Provinsi Gorontalo');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setItalic(true)->setSize(14)->setName('Calibri');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Title: KARTU KENDALI
        $sheet->mergeCells('A3:I3');
        $sheet->setCellValue('A3', 'KARTU KENDALI');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(13)->setName('Calibri');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Subtitle: PEMELIHARAAN {NAMA_BARANG} TAHUN ANGGARAN {TAHUN}
        $namaBarangUpper = strtoupper($aset->nama_barang ?? 'ASET BMN');
        $sheet->mergeCells('A4:I4');
        $sheet->setCellValue('A4', "PEMELIHARAAN {$namaBarangUpper} TAHUN ANGGARAN {$tahun}");
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11)->setName('Calibri');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Metadata Block (Rows 6, 7, 8)
        $sheet->getStyle('A6:I8')->getFont()->setSize(9)->setName('Calibri');

        // Column 1 (Col A-C)
        $sheet->setCellValue('A6', 'Nama Barang');
        $sheet->setCellValue('B6', ': '.($aset->nama_barang ?? '-'));
        $sheet->setCellValue('A7', 'Kode Barang');
        $sheet->setCellValue('B7', ': '.($aset->clean_kode_barang ?? '-'));
        $sheet->setCellValue('A8', 'Nomor Aset');
        $sheet->setCellValue('B8', ': '.($aset->clean_nup ?? '-'));

        // Column 2 (Col D-F)
        $merkTipe = trim(($aset->merk ?? '').' '.($aset->tipe ?? '')) ?: '-';
        $tahunPerolehan = $aset->tanggal_perolehan ? $aset->tanggal_perolehan->format('Y') : '-';
        $noSeri = $aset->no_sertifikat ?: ($aset->no_dokumen ?: '-');

        $sheet->setCellValue('D6', 'Merk/Tipe');
        $sheet->setCellValue('E6', ': '.$merkTipe);
        $sheet->setCellValue('D7', 'Nomor Seri');
        $sheet->setCellValue('E7', ': '.$noSeri);
        $sheet->setCellValue('D8', 'Tahun Perolehan');
        $sheet->setCellValue('E8', ': '.$tahunPerolehan);

        // Column 3 (Col G-I)
        $lokasiBmn = $aset->alamat ?: 'Lantai 1';
        $ruangan = $aset->clean_lokasi_ruang ?? '-';
        $statusKondisi = $aset->kondisi ?? 'Baik';

        $sheet->setCellValue('G6', 'Lokasi BMN');
        $sheet->setCellValue('H6', ': '.$lokasiBmn);
        $sheet->setCellValue('G7', 'Ruangan');
        $sheet->setCellValue('H7', ': '.$ruangan);
        $sheet->setCellValue('G8', 'Status Barang (B/RR)');
        $sheet->setCellValue('H8', ': '.$statusKondisi);

        // Table Header (Rows 10 - 12)
        // Row 10 & 11 merges:
        $sheet->mergeCells('A10:A11');
        $sheet->setCellValue('A10', 'NO');

        $sheet->mergeCells('B10:C10');
        $sheet->setCellValue('B10', 'Form Permintaan Anggaran');
        $sheet->setCellValue('B11', 'Nomor');
        $sheet->setCellValue('C11', 'Tanggal');

        $sheet->mergeCells('D10:E10');
        $sheet->setCellValue('D10', 'Tanggal Service Berkala');
        $sheet->setCellValue('D11', 'Mulai Pekerjaan');
        $sheet->setCellValue('E11', 'Selesai Pekerjaan');

        $sheet->mergeCells('F10:G10');
        $sheet->setCellValue('F10', 'Jenis Kegiatan');
        $sheet->setCellValue('F11', 'Jasa Service');
        $sheet->setCellValue('G11', 'Sparepart yang di ganti');

        $sheet->mergeCells('H10:H11');
        $sheet->setCellValue('H10', 'Bengkel yang mengerjakan');

        $sheet->mergeCells('I10:I11');
        $sheet->setCellValue('I10', 'Biaya');

        // Row 12 (Numbering indicators (1) - (9))
        $cols = ['(1)', '(2)', '(3)', '(4)', '(5)', '(6)', '(7)', '(8)', '(9)'];
        foreach ($cols as $idx => $label) {
            $colLetter = chr(65 + $idx); // A, B, C...
            $sheet->setCellValue("{$colLetter}12", $label);
        }

        // Header Styles
        $headerRange = 'A10:I12';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(9)->setName('Calibri');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setWrapText(true);

        // Minimum 15 rows for the table body (as seen in official control card)
        $totalDisplayRows = max(count($rows), 15);
        $startRow = 13;

        for ($i = 0; $i < $totalDisplayRows; $i++) {
            $currentRow = $startRow + $i;
            $data = $rows[$i] ?? null;

            if ($data) {
                $sheet->setCellValue("A{$currentRow}", $i + 1);
                $sheet->setCellValue("B{$currentRow}", $data['no_fpa']);
                $sheet->setCellValue("C{$currentRow}", $data['tgl_fpa']);
                $sheet->setCellValue("D{$currentRow}", $data['tgl_mulai']);
                $sheet->setCellValue("E{$currentRow}", $data['tgl_selesai']);
                $sheet->setCellValue("F{$currentRow}", $data['jasa_service']);
                $sheet->setCellValue("G{$currentRow}", $data['sparepart']);
                $sheet->setCellValue("H{$currentRow}", $data['vendor']);
                if ($data['biaya'] > 0) {
                    $sheet->setCellValue("I{$currentRow}", $data['biaya']);
                    $sheet->getStyle("I{$currentRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
                } else {
                    $sheet->setCellValue("I{$currentRow}", '');
                }
            } else {
                // Empty blank row with just index number
                $sheet->setCellValue("A{$currentRow}", $i + 1);
            }

            // Row font
            $sheet->getStyle("A{$currentRow}:I{$currentRow}")->getFont()->setSize(9)->setName('Calibri');

            // Alignments
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("C{$currentRow}:E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$currentRow}:H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("A{$currentRow}:I{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->getRowDimension($currentRow)->setRowHeight(22);
        }

        // Apply grid borders to entire table (Header + Data rows)
        $endRow = $startRow + $totalDisplayRows - 1;
        $tableRange = "A10:I{$endRow}";
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Column widths matching the official layout
        $sheet->getColumnDimension('A')->setWidth(5);   // NO
        $sheet->getColumnDimension('B')->setWidth(24);  // No. FPA
        $sheet->getColumnDimension('C')->setWidth(16);  // Tgl FPA
        $sheet->getColumnDimension('D')->setWidth(15);  // Mulai
        $sheet->getColumnDimension('E')->setWidth(15);  // Selesai
        $sheet->getColumnDimension('F')->setWidth(26);  // Jasa Service
        $sheet->getColumnDimension('G')->setWidth(26);  // Sparepart
        $sheet->getColumnDimension('H')->setWidth(22);  // Bengkel / Vendor
        $sheet->getColumnDimension('I')->setWidth(18);  // Biaya

        // Stream download
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $aset->nama_barang ?? 'Aset');
        $nup = $aset->clean_nup ?? '0';
        $filename = "Kartu_Kendali_{$cleanName}_NUP{$nup}_{$tahun}.xlsx";

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    /**
     * Export the Kartu Kendali document directly to a native PDF file.
     */
    public function exportPdf(Request $request, $id)
    {
        $aset = $this->resolveAsset($id);
        $tahun = $request->input('tahun', date('Y'));
        $rows = $this->getMaintenanceRows($aset, $tahun);
        $totalBiaya = array_sum(array_column($rows, 'biaya'));

        // Base64 Logo for rock-solid Dompdf rendering (use optimized KAK logo)
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

        $pdf = Pdf::loadView('kartu-kendali.pdf', compact('aset', 'rows', 'tahun', 'totalBiaya', 'logoBase64'))
            ->setPaper('a4', 'landscape')
            ->setOption([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $safeNup = preg_replace('/[^a-zA-Z0-9_-]/', '_', $aset->clean_nup ?: $aset->id);
        $filename = "Kartu-Kendali-{$safeNup}-{$tahun}.pdf";

        if ($request->input('download') == '1') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Resolve asset by ID, fallback, or search.
     */
    private function resolveAsset($id)
    {
        if (is_numeric($id)) {
            $aset = Aset::find($id);
            if ($aset) {
                return $aset;
            }
        }

        // Try to find by NUP or kode_barang
        $aset = Aset::where('nup', $id)
            ->orWhere('id', str_replace('aset_', '', $id))
            ->first();

        if ($aset) {
            return $aset;
        }

        // Try from RealisasiAset
        $ra = RealisasiAset::with('aset')->find($id);
        if ($ra && $ra->aset) {
            return $ra->aset;
        }

        // Return first asset as fallback
        return Aset::first() ?? new Aset([
            'nama_barang' => 'Air Conditioner (AC) Split',
            'kode_barang' => '3050204004',
            'nup' => '74',
            'merk' => 'Panasonic',
            'kondisi' => 'Rusak Ringan',
            'lokasi_ruang' => 'Sekretaris',
            'tanggal_perolehan' => now()->subYears(7),
        ]);
    }

    /**
     * Extract structured maintenance rows for the template.
     */
    private function getMaintenanceRows($aset, $tahun = null)
    {
        $query = RealisasiAset::with(['realisasi.permintaan', 'realisasiItems'])
            ->where(function ($q) use ($aset) {
                $q->where('aset_id', $aset->id)
                    ->orWhere('kode_inventaris', 'like', "%{$aset->clean_nup}%");
            })
            ->whereHas('realisasi', function ($q) {
                $q->where('status', 'Selesai');
            });

        $realisasiAsets = $query->get();

        $rows = [];

        // Sparepart detection keywords
        $sparepartKeywords = ['freon', 'sensor', 'remot', 'remote', 'kapasitor', 'filter', 'oli', 'aki', 'baterai', 'sparepart', 'spare part', 'ganti', 'penggantian', 'pipa', 'motor', 'kipas', 'kompresor', 'modul', 'kabel', 'baut'];

        foreach ($realisasiAsets as $ra) {
            $realisasi = $ra->realisasi;
            if (! $realisasi) {
                continue;
            }

            // Check year filter
            $tglSelesai = $realisasi->tanggal_selesai;
            $tglPengajuan = $realisasi->tanggal_pengajuan ?? $realisasi->created_at;

            if ($tahun && $tahun !== 'semua') {
                $itemYear = $tglSelesai ? $tglSelesai->format('Y') : ($tglPengajuan ? $tglPengajuan->format('Y') : null);
                if ($itemYear && $itemYear != $tahun) {
                    continue;
                }
            }

            $noFpa = $realisasi->no_fpa ?: ('FP-'.($tglPengajuan ? $tglPengajuan->format('Y') : date('Y')).'-636900-92000-'.str_pad($realisasi->id, 3, '0', STR_PAD_LEFT));
            $tglFpa = $tglPengajuan ? $tglPengajuan->translatedFormat('d F Y') : '-';
            $tglMulai = $realisasi->tanggal_mulai ? $realisasi->tanggal_mulai->translatedFormat('d F Y') : '-';
            $tglSelesaiStr = $tglSelesai ? $tglSelesai->translatedFormat('d F Y') : '-';
            $vendor = $realisasi->vendor ?? '-';

            if ($ra->realisasiItems && $ra->realisasiItems->count() > 0) {
                foreach ($ra->realisasiItems as $item) {
                    $uraianLower = strtolower($item->uraian ?? '');
                    $isSparepart = false;

                    foreach ($sparepartKeywords as $kw) {
                        if (str_contains($uraianLower, $kw)) {
                            $isSparepart = true;
                            break;
                        }
                    }

                    $rows[] = [
                        'no_fpa' => $noFpa,
                        'tgl_fpa' => $tglFpa,
                        'tgl_mulai' => $tglMulai,
                        'tgl_selesai' => $tglSelesaiStr,
                        'jasa_service' => $isSparepart ? '-' : $item->uraian,
                        'sparepart' => $isSparepart ? $item->uraian : '-',
                        'vendor' => $vendor,
                        'biaya' => (float) $item->jumlah,
                    ];
                }
            } else {
                $rows[] = [
                    'no_fpa' => $noFpa,
                    'tgl_fpa' => $tglFpa,
                    'tgl_mulai' => $tglMulai,
                    'tgl_selesai' => $tglSelesaiStr,
                    'jasa_service' => $ra->deskripsi_kerusakan ?: ($realisasi->pekerjaan ?: 'Service Berkala'),
                    'sparepart' => '-',
                    'vendor' => $vendor,
                    'biaya' => (float) $ra->subtotal,
                ];
            }
        }

        return $rows;
    }
}
