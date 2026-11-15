<?php

namespace App\Http\Controllers;

use App\Models\Realisasi;
use App\Models\Supply;
use App\Services\SatkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RealisasiController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'siap realisasi');
        $search = $request->input('search');
        $jenis = $request->input('jenis');
        $date = $request->input('date');
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $baseRealisasi = SatkerService::filterQuery(Realisasi::query());
        $query = (clone $baseRealisasi)->with(['permintaan', 'distribusi'])->latest('updated_at');

        if ($status !== '' && $status !== null && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($jenis) {
            $query->where('jenis', $jenis);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pekerjaan', 'like', "%{$search}%")
                    ->orWhere('no_fpa', 'like', "%{$search}%")
                    ->orWhere('vendor', 'like', "%{$search}%");
            });
        }

        if ($date) {
            $query->where(function ($q) use ($date) {
                $q->whereDate('tanggal_mulai', $date)
                    ->orWhereDate('created_at', $date);
            });
        }

        $countQuery = clone $baseRealisasi;
        if ($jenis) {
            $countQuery->where('jenis', $jenis);
        }

        $countSiapRealisasi = (clone $countQuery)->where('status', 'siap realisasi')->count();
        $countSelesai = (clone $countQuery)->where('status', 'Selesai')->count();
        $countSemua = (clone $countQuery)->count();

        $realisasis = $query->paginate($perPage)->withQueryString();

        return view('realisasi.index', compact(
            'realisasis',
            'status',
            'search',
            'jenis',
            'date',
            'perPage',
            'countSiapRealisasi',
            'countSelesai',
            'countSemua'
        ));
    }

    public function show($id)
    {
        $r = Realisasi::with(['permintaan.permintaanAsets.aset', 'permintaan.permintaanAsets.rabItems', 'realisasiAsets.aset', 'realisasiAsets.realisasiItems', 'distribusi'])->findOrFail($id);

        $rabItemsPermintaan = [];
        if ($r->permintaan) {
            foreach ($r->permintaan->permintaanAsets as $pa) {
                foreach ($pa->rabItems as $rab) {
                    $rabItemsPermintaan[] = [
                        'uraian' => $rab->uraian,
                        'vol' => (int) $rab->volume,
                        'satuan' => $rab->satuan,
                        'hargaSatuan' => (float) $rab->harga_satuan,
                        'total' => (float) $rab->jumlah,
                    ];
                }
            }

            if (empty($rabItemsPermintaan) && (float) $r->permintaan->total_rab > 0) {
                $rabItemsPermintaan[] = [
                    'uraian' => $r->permintaan->pekerjaan ?: 'Pengadaan Barang Persediaan',
                    'vol' => 1,
                    'satuan' => 'paket',
                    'hargaSatuan' => (float) $r->permintaan->total_rab,
                    'total' => (float) $r->permintaan->total_rab,
                ];
            }
        }

        $supplyItems = [];
        $supplyPhotos = [];
        if ($r->jenis === 'Supply') {
            foreach ($r->realisasiAsets as $ra) {
                foreach ($ra->foto_urls as $photo) {
                    $supplyPhotos[] = $photo;
                }
                foreach ($ra->realisasiItems as $ri) {
                    $supplyItems[] = [
                        'id' => $ri->id,
                        'uraian' => $ri->uraian,
                        'vol' => (int) $ri->volume,
                        'satuan' => $ri->satuan,
                        'hargaSatuan' => (float) $ri->harga_satuan,
                        'total' => (float) $ri->jumlah,
                    ];
                }
            }
        }

        $realisasi = [
            'id' => $r->id,
            'no_fpa' => $r->no_fpa,
            'vendor' => $r->vendor,
            'pekerjaan' => $r->pekerjaan,
            'jenis' => $r->jenis,
            'status' => $r->status,
            'tanggal_mulai' => $r->tanggal_mulai ? $r->tanggal_mulai->translatedFormat('d F Y') : '-',
            'tanggal_selesai' => $r->tanggal_selesai ? $r->tanggal_selesai->translatedFormat('d F Y') : null,
            'total_realisasi' => (float) $r->total_realisasi,
            'rab_permintaan' => $r->permintaan ? (float) $r->permintaan->total_rab : (float) $r->total_realisasi,
            'kak_file' => $r->kak_file ? Storage::url($r->kak_file) : null,
            'kuitansi_file' => $r->kuitansi_file ? Storage::url($r->kuitansi_file) : null,
            'distribusi' => $r->distribusi ? [
                'id' => $r->distribusi->id,
                'nomor_bast' => $r->distribusi->nomor_bast,
                'status' => $r->distribusi->status,
                'tanggal_bast' => $r->distribusi->tanggal_bast ? $r->distribusi->tanggal_bast->translatedFormat('d F Y') : '-',
                'pihak_kedua_nama' => $r->distribusi->pihak_kedua_nama,
                'pihak_kedua_unit_kerja' => $r->distribusi->pihak_kedua_unit_kerja,
            ] : null,
            'rabItemsPermintaan' => $rabItemsPermintaan,
            'supplyItems' => $supplyItems,
            'supplyPhotos' => $supplyPhotos,
            'asetList' => $r->realisasiAsets->map(function ($ra) use ($r) {
                $pa = null;
                if ($r->permintaan) {
                    if ($ra->aset_id) {
                        $pa = $r->permintaan->permintaanAsets->where('aset_id', $ra->aset_id)->first();
                    } else {
                        $pa = $r->permintaan->permintaanAsets->firstWhere('nama_barang', $ra->nama_aset);
                    }
                }

                $rabItems = [];
                if ($pa) {
                    foreach ($pa->rabItems as $rab) {
                        $rabItems[] = [
                            'uraian' => $rab->uraian,
                            'vol' => (int) $rab->volume,
                            'satuan' => $rab->satuan,
                            'hargaSatuan' => (float) $rab->harga_satuan,
                            'total' => (float) $rab->jumlah,
                        ];
                    }
                }

                return [
                    'id' => $ra->id,
                    'nama' => $ra->nama_aset ?? ($ra->aset ? $ra->aset->nama_barang : 'Aset Non-Register'),
                    'kode_inventaris' => $ra->kode_inventaris ?? '-',
                    'deskripsi_kerusakan' => $ra->deskripsi_kerusakan ?? '-',
                    'subtotal' => (float) $ra->subtotal,
                    'photos' => $ra->foto_urls,
                    'rabItems' => $rabItems,
                    'items' => $ra->realisasiItems->map(function ($ri) {
                        return [
                            'uraian' => $ri->uraian,
                            'vol' => (int) $ri->volume,
                            'satuan' => $ri->satuan,
                            'hargaSatuan' => (float) $ri->harga_satuan,
                            'total' => (float) $ri->jumlah,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray(),
        ];

        return view('realisasi.show', compact('realisasi'));
    }

    public function proses($id)
    {
        if (SatkerService::isViewingOtherSatker()) {
            return redirect()->route('realisasi.index')
                ->with('error', 'Mode Lihat Saja: Pemrosesan realisasi untuk satker lain tidak diperkenankan.');
        }

        $r = Realisasi::with(['permintaan.permintaanAsets.aset', 'permintaan.permintaanAsets.rabItems', 'realisasiAsets.aset', 'realisasiAsets.realisasiItems'])->findOrFail($id);

        $rabItemsPermintaan = [];
        if ($r->permintaan) {
            foreach ($r->permintaan->permintaanAsets as $pa) {
                foreach ($pa->rabItems as $rab) {
                    $rabItemsPermintaan[] = [
                        'id' => 'rab_'.$rab->id,
                        'uraian' => $rab->uraian,
                        'vol' => (int) $rab->volume,
                        'satuan' => $rab->satuan,
                        'hargaSatuan' => (float) $rab->harga_satuan,
                        'total' => (float) $rab->jumlah,
                    ];
                }
            }

            if (empty($rabItemsPermintaan) && (float) $r->permintaan->total_rab > 0) {
                $rabItemsPermintaan[] = [
                    'id' => 'rab_req_'.$r->permintaan->id,
                    'uraian' => $r->permintaan->pekerjaan ?: 'Pengadaan Barang Persediaan',
                    'vol' => 1,
                    'satuan' => 'paket',
                    'hargaSatuan' => (float) $r->permintaan->total_rab,
                    'total' => (float) $r->permintaan->total_rab,
                ];
            }
        }

        $supplyItems = [];
        $supplyPhotos = [];
        if ($r->jenis === 'Supply') {
            foreach ($r->realisasiAsets as $ra) {
                foreach ($ra->foto_urls as $photo) {
                    $supplyPhotos[] = $photo;
                }
                foreach ($ra->realisasiItems as $ri) {
                    $supplyItems[] = [
                        'id' => $ri->id,
                        'uraian' => $ri->uraian,
                        'vol' => (int) $ri->volume,
                        'satuan' => $ri->satuan,
                        'hargaSatuan' => (float) $ri->harga_satuan,
                    ];
                }
            }

            if (empty($supplyItems) && ! empty($rabItemsPermintaan)) {
                $supplyItems = array_map(function ($rab) {
                    return [
                        'id' => $rab['id'],
                        'uraian' => $rab['uraian'],
                        'vol' => (int) $rab['vol'],
                        'satuan' => $rab['satuan'],
                        'hargaSatuan' => (float) $rab['hargaSatuan'],
                    ];
                }, $rabItemsPermintaan);
            }

            if (empty($supplyItems)) {
                $supplyItems = [
                    [
                        'id' => 1,
                        'uraian' => '',
                        'vol' => 1,
                        'satuan' => 'buah',
                        'hargaSatuan' => 0,
                    ],
                ];
            }
        }

        $asetList = [];
        if ($r->realisasiAsets->isNotEmpty()) {
            $asetList = $r->realisasiAsets->map(function ($ra) use ($r) {
                $pa = null;
                if ($r->permintaan) {
                    if ($ra->aset_id) {
                        $pa = $r->permintaan->permintaanAsets->where('aset_id', $ra->aset_id)->first();
                    } else {
                        $pa = $r->permintaan->permintaanAsets->firstWhere('nama_barang', $ra->nama_aset);
                    }
                }

                $rabItems = [];
                if ($pa) {
                    foreach ($pa->rabItems as $rab) {
                        $rabItems[] = [
                            'id' => 'rab_'.$rab->id,
                            'uraian' => $rab->uraian,
                            'vol' => (int) $rab->volume,
                            'satuan' => $rab->satuan,
                            'hargaSatuan' => (float) $rab->harga_satuan,
                        ];
                    }
                }

                if (empty($rabItems) && $ra->realisasiItems->isNotEmpty()) {
                    foreach ($ra->realisasiItems as $ri) {
                        $rabItems[] = [
                            'id' => 'rab_'.$ri->id,
                            'uraian' => $ri->uraian,
                            'vol' => (int) $ri->volume,
                            'satuan' => $ri->satuan,
                            'hargaSatuan' => (float) $ri->harga_satuan,
                        ];
                    }
                }

                return [
                    'id' => $ra->id,
                    'nama' => $ra->nama_aset ?? ($ra->aset ? $ra->aset->nama_barang : 'Aset Non-Register'),
                    'photos' => $ra->foto_urls,
                    'rabItems' => $rabItems,
                    'items' => $ra->realisasiItems->map(function ($ri) {
                        return [
                            'id' => $ri->id,
                            'uraian' => $ri->uraian,
                            'vol' => (int) $ri->volume,
                            'satuan' => $ri->satuan,
                            'hargaSatuan' => (float) $ri->harga_satuan,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray();
        } elseif ($r->permintaan && $r->permintaan->permintaanAsets->isNotEmpty()) {
            $asetList = $r->permintaan->permintaanAsets->map(function ($pa) {
                $rabItems = [];
                foreach ($pa->rabItems as $rab) {
                    $rabItems[] = [
                        'id' => 'rab_'.$rab->id,
                        'uraian' => $rab->uraian,
                        'vol' => (int) $rab->volume,
                        'satuan' => $rab->satuan,
                        'hargaSatuan' => (float) $rab->harga_satuan,
                    ];
                }

                return [
                    'id' => $pa->id,
                    'nama' => $pa->nama_barang ?? ($pa->aset ? $pa->aset->nama_barang : 'Aset Non-Register'),
                    'photos' => [],
                    'rabItems' => $rabItems,
                    'items' => $rabItems,
                ];
            })->values()->toArray();
        } elseif ($r->jenis !== 'Supply') {
            $asetList = [
                [
                    'id' => 1,
                    'nama' => 'Laptop Dell Latitude 5420',
                    'photos' => [],
                    'rabItems' => [
                        [
                            'id' => 'rab_1',
                            'uraian' => 'Penggantian Baterai Original',
                            'vol' => 1,
                            'satuan' => 'unit',
                            'hargaSatuan' => 1250000,
                        ],
                        [
                            'id' => 'rab_2',
                            'uraian' => 'Jasa Pemasangan',
                            'vol' => 1,
                            'satuan' => 'ls',
                            'hargaSatuan' => 150000,
                        ],
                    ],
                    'items' => [
                        [
                            'id' => 1,
                            'uraian' => 'Penggantian Baterai Original',
                            'vol' => 1,
                            'satuan' => 'unit',
                            'hargaSatuan' => 1250000,
                        ],
                        [
                            'id' => 2,
                            'uraian' => 'Jasa Pemasangan',
                            'vol' => 1,
                            'satuan' => 'ls',
                            'hargaSatuan' => 150000,
                        ],
                    ],
                ],
            ];
        }

        if (empty($rabItemsPermintaan) && ! empty($asetList)) {
            foreach ($asetList as $a) {
                foreach ($a['rabItems'] ?? [] as $rab) {
                    $rabItemsPermintaan[] = [
                        'id' => $rab['id'] ?? ('rab_'.uniqid()),
                        'uraian' => $rab['uraian'],
                        'vol' => (int) ($rab['vol'] ?? 1),
                        'satuan' => $rab['satuan'] ?? 'unit',
                        'hargaSatuan' => (float) ($rab['hargaSatuan'] ?? 0),
                        'total' => (float) (($rab['vol'] ?? 1) * ($rab['hargaSatuan'] ?? 0)),
                    ];
                }
            }
        }

        $realisasi = [
            'id' => $r->id,
            'pekerjaan' => $r->pekerjaan,
            'no_fpa' => $r->no_fpa,
            'vendor' => $r->vendor,
            'jenis' => $r->jenis,
            'tanggal_mulai' => $r->tanggal_mulai ? $r->tanggal_mulai->format('Y-m-d') : date('Y-m-d'),
            'tanggal_mulai_formatted' => $r->tanggal_mulai ? $r->tanggal_mulai->translatedFormat('d M Y') : date('d M Y'),
            'tanggal_selesai' => $r->tanggal_selesai ? $r->tanggal_selesai->format('Y-m-d') : '',
            'kak_file' => $r->kak_file,
            'kuitansi_file' => $r->kuitansi_file,
            'total_realisasi' => (float) $r->total_realisasi,
            'rabItemsPermintaan' => $rabItemsPermintaan,
            'supplyItems' => $supplyItems,
            'supplyPhotos' => $supplyPhotos,
            'asetList' => $asetList,
        ];

        $supplies = Supply::orderBy('nama_barang')->get(['id', 'kode_barang', 'nama_barang', 'kategori', 'satuan', 'stok', 'harga_estimasi']);

        return view('realisasi.proses', compact('realisasi', 'supplies'));
    }

    public function selesai(Request $request, $id)
    {
        SatkerService::abortIfViewingOtherSatker('penyelesaian realisasi');

        $r = Realisasi::with('realisasiAsets.realisasiItems')->findOrFail($id);

        $rules = [
            'tanggal_selesai' => 'required|date',
        ];

        $messages = [
            'tanggal_selesai.required' => 'Tanggal selesai pengerjaan wajib diisi.',
            'kak.required' => 'Dokumen KAK (Kerangka Acuan Kerja) format PDF wajib diunggah.',
            'kuitansi.required' => 'Dokumen Kuitansi / bukti pembayaran wajib diunggah.',
        ];

        if ($r->jenis === 'Supply') {
            if ($request->has('no_fpa')) {
                $rules['no_fpa'] = 'required|string|max:255';
            }
            if ($request->has('vendor')) {
                $rules['vendor'] = 'required|string|max:255';
            }
            if ($request->has('tanggal_mulai')) {
                $rules['tanggal_mulai'] = 'required|date';
            }
            if ($request->filled('tanggal_mulai')) {
                $rules['tanggal_selesai'] = 'required|date|after_or_equal:tanggal_mulai';
            }

            $messages['no_fpa.required'] = 'Nomor dokumen FPA wajib diisi.';
            $messages['vendor.required'] = 'Nama vendor / pelaksana wajib diisi.';
            $messages['tanggal_mulai.required'] = 'Tanggal mulai pengerjaan wajib diisi.';
            $messages['tanggal_selesai.after_or_equal'] = 'Tanggal selesai tidak boleh sebelum tanggal mulai pengerjaan.';
        }

        if (empty($r->kak_file)) {
            $rules['kak'] = 'required|file|mimes:pdf|max:10240';
        } else {
            $rules['kak'] = 'nullable|file|mimes:pdf|max:10240';
        }

        $hasDocAlready = ! empty($r->bast_file) || ! empty($r->kuitansi_file);
        if (! $hasDocAlready) {
            if ($r->jenis === 'Supply') {
                $rules['bast'] = 'required_without:kuitansi|file|mimes:pdf,jpg,jpeg,png|max:10240';
                $rules['kuitansi'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
                $messages['bast.required_without'] = 'Dokumen BAST bertanda tangan basah wajib diunggah.';
            } else {
                $rules['kuitansi'] = 'required_without:bast|file|mimes:pdf,jpg,jpeg,png|max:10240';
                $rules['bast'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
                $messages['kuitansi.required_without'] = 'Dokumen Kuitansi / bukti pembayaran wajib diunggah.';
            }
        } else {
            $rules['bast'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['kuitansi'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
        }

        $request->validate($rules, $messages);

        if ($r->jenis === 'Supply') {
            $supplyItemsRaw = $request->input('supplyItems') ?? $request->input('asetList');
            if (is_string($supplyItemsRaw)) {
                $supplyItems = json_decode($supplyItemsRaw, true) ?? [];
            } else {
                $supplyItems = $supplyItemsRaw ?? [];
            }

            if (isset($supplyItems[0]['items'])) {
                $supplyItems = $supplyItems[0]['items'];
            }

            if (empty($supplyItems)) {
                return back()->withInput()->with('error', 'Daftar rincian barang persediaan wajib diisi minimal 1 item.');
            }

            foreach ($supplyItems as $itemIdx => $it) {
                $num = $itemIdx + 1;
                if (empty(trim($it['uraian'] ?? ''))) {
                    return back()->withInput()->with('error', 'Nama barang persediaan pada baris ke-'.$num.' wajib diisi.');
                }
                if (! isset($it['vol']) || (int) $it['vol'] <= 0) {
                    return back()->withInput()->with('error', 'Volume barang persediaan pada baris ke-'.$num.' harus berupa angka lebih dari 0.');
                }
                if (empty(trim($it['satuan'] ?? ''))) {
                    return back()->withInput()->with('error', 'Satuan barang persediaan pada baris ke-'.$num.' wajib diisi.');
                }
                if (! isset($it['hargaSatuan']) || (float) $it['hargaSatuan'] <= 0) {
                    return back()->withInput()->with('error', 'Harga satuan barang persediaan pada baris ke-'.$num.' belum diisi atau masih 0.');
                }
            }

            DB::transaction(function () use ($r, $request, $supplyItems) {
                $updateData = [
                    'tanggal_selesai' => $request->input('tanggal_selesai'),
                    'status' => 'Selesai',
                ];

                if ($request->filled('no_fpa')) {
                    $updateData['no_fpa'] = $request->input('no_fpa');
                }
                if ($request->filled('vendor')) {
                    $updateData['vendor'] = $request->input('vendor');
                }
                if ($request->filled('tanggal_mulai')) {
                    $updateData['tanggal_mulai'] = $request->input('tanggal_mulai');
                }

                if ($request->hasFile('kak')) {
                    if ($r->kak_file && Storage::disk('public')->exists($r->kak_file)) {
                        Storage::disk('public')->delete($r->kak_file);
                    }
                    $path = $request->file('kak')->store('realisasi_docs', 'public');
                    $updateData['kak_file'] = $path;
                }

                $bastOrKuitansi = $request->file('bast') ?: $request->file('kuitansi');
                if ($bastOrKuitansi) {
                    if ($r->bast_file && Storage::disk('public')->exists($r->bast_file)) {
                        Storage::disk('public')->delete($r->bast_file);
                    }
                    if ($r->kuitansi_file && Storage::disk('public')->exists($r->kuitansi_file)) {
                        Storage::disk('public')->delete($r->kuitansi_file);
                    }
                    $path = $bastOrKuitansi->store('realisasi_docs', 'public');
                    $updateData['bast_file'] = $path;
                    $updateData['kuitansi_file'] = $path;
                }

                $r->update($updateData);

                $ra = $r->realisasiAsets()->first();
                if (! $ra) {
                    $ra = $r->realisasiAsets()->create([
                        'nama_aset' => 'Barang Persediaan',
                        'kode_inventaris' => 'Supply / Persediaan',
                        'subtotal' => 0,
                    ]);
                } else {
                    $ra->update([
                        'nama_aset' => 'Barang Persediaan',
                        'kode_inventaris' => 'Supply / Persediaan',
                    ]);
                }

                $extraAsets = $r->realisasiAsets()->where('id', '!=', $ra->id)->get();
                foreach ($extraAsets as $extra) {
                    $extra->realisasiItems()->delete();
                    $extra->delete();
                }

                $uploadedFiles = [];
                $possibleKeys = ['foto_supply', 'foto_dokumentasi', "foto_aset_{$ra->id}", 'foto_aset_0'];
                foreach ($possibleKeys as $key) {
                    if ($request->hasFile($key)) {
                        $f = $request->file($key);
                        $uploadedFiles = is_array($f) ? $f : [$f];
                        break;
                    }
                }

                if (! empty($uploadedFiles)) {
                    foreach ($ra->foto_list as $oldPhoto) {
                        if (Storage::disk('public')->exists($oldPhoto)) {
                            Storage::disk('public')->delete($oldPhoto);
                        }
                    }
                    $photoPaths = [];
                    foreach ($uploadedFiles as $file) {
                        if ($file && $file->isValid()) {
                            $photoPaths[] = $file->store('realisasi_photos', 'public');
                        }
                    }
                    if (! empty($photoPaths)) {
                        $ra->update(['foto_dokumentasi' => json_encode($photoPaths)]);
                    }
                }

                $ra->realisasiItems()->delete();
                foreach ($supplyItems as $item) {
                    $uraian = trim($item['uraian'] ?? '');
                    if (empty($uraian)) {
                        continue;
                    }
                    $vol = (int) ($item['vol'] ?? 1);
                    $satuan = $item['satuan'] ?? 'buah';
                    $harga = (float) ($item['hargaSatuan'] ?? 0);
                    $jumlah = $vol * $harga;

                    $ra->realisasiItems()->create([
                        'uraian' => $uraian,
                        'volume' => $vol,
                        'satuan' => $satuan,
                        'harga_satuan' => $harga,
                        'jumlah' => $jumlah,
                    ]);
                }

                $ra->hitungSubtotal();
                $r->hitungTotalRealisasi();
                $r->syncStokPersediaanMasuk();
            });

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Data realisasi supply berhasil diselesaikan.']);
            }

            return redirect()->route('realisasi.index')->with('success', 'Data realisasi supply berhasil disimpan dan diselesaikan.');
        }

        // Validate items in asetList (Maintenance)
        $asetListRaw = $request->input('asetList');
        if (is_string($asetListRaw)) {
            $asetList = json_decode($asetListRaw, true) ?? [];
        } else {
            $asetList = $asetListRaw ?? [];
        }

        if (empty($asetList)) {
            return back()->withInput()->with('error', 'Daftar aset realisasi tidak ditemukan.');
        }

        foreach ($asetList as $idx => $a) {
            $namaAset = $a['nama'] ?? ('Aset '.($idx + 1));
            $items = $a['items'] ?? [];
            if (empty($items)) {
                return back()->withInput()->with('error', 'Rincian hasil pengerjaan pada '.$namaAset.' wajib diisi minimal 1 item.');
            }
            foreach ($items as $itemIdx => $it) {
                $num = $itemIdx + 1;
                if (empty(trim($it['uraian'] ?? ''))) {
                    return back()->withInput()->with('error', 'Uraian rincian pengerjaan pada '.$namaAset.' baris ke-'.$num.' wajib diisi.');
                }
                if (! isset($it['vol']) || (int) $it['vol'] <= 0) {
                    return back()->withInput()->with('error', 'Volume pada '.$namaAset.' baris ke-'.$num.' harus berupa angka lebih dari 0.');
                }
                if (empty(trim($it['satuan'] ?? ''))) {
                    return back()->withInput()->with('error', 'Satuan pada '.$namaAset.' baris ke-'.$num.' wajib diisi.');
                }
                if (! isset($it['hargaSatuan']) || (float) $it['hargaSatuan'] <= 0) {
                    return back()->withInput()->with('error', 'Harga satuan pada '.$namaAset.' baris ke-'.$num.' belum diisi atau masih 0.');
                }
            }
        }

        DB::transaction(function () use ($r, $request, $asetList) {
            $updateData = [
                'tanggal_selesai' => $request->input('tanggal_selesai'),
                'status' => 'Selesai',
            ];

            if ($request->hasFile('kak')) {
                // Delete old KAK file from storage
                if ($r->kak_file && Storage::disk('public')->exists($r->kak_file)) {
                    Storage::disk('public')->delete($r->kak_file);
                }
                $path = $request->file('kak')->store('realisasi_docs', 'public');
                $updateData['kak_file'] = $path;
            }

            if ($request->hasFile('kuitansi')) {
                // Delete old kuitansi file from storage
                if ($r->kuitansi_file && Storage::disk('public')->exists($r->kuitansi_file)) {
                    Storage::disk('public')->delete($r->kuitansi_file);
                }
                $path = $request->file('kuitansi')->store('realisasi_docs', 'public');
                $updateData['kuitansi_file'] = $path;
            }

            $r->update($updateData);

            foreach ($asetList as $index => $asetData) {
                $raId = $asetData['id'] ?? null;
                $ra = $raId ? $r->realisasiAsets()->find($raId) : null;

                // Fallback: match by index or create if missing
                if (! $ra) {
                    $ra = $r->realisasiAsets()->skip($index)->first();
                    if (! $ra) {
                        $ra = $r->realisasiAsets()->create([
                            'nama_aset' => $asetData['nama'] ?? ('Aset '.($index + 1)),
                            'subtotal' => 0,
                        ]);
                    }
                }

                // Handle multi-photo upload per asset
                $uploadedFiles = [];
                $possibleKeys = [
                    "foto_aset_{$ra->id}",
                    "foto_aset_{$index}",
                    'foto_aset_'.($asetData['id'] ?? ''),
                ];

                foreach ($possibleKeys as $key) {
                    if ($request->hasFile($key)) {
                        $f = $request->file($key);
                        $uploadedFiles = is_array($f) ? $f : [$f];
                        break;
                    }
                }

                if (! empty($uploadedFiles)) {
                    // Delete old photos from storage
                    foreach ($ra->foto_list as $oldPhoto) {
                        if (Storage::disk('public')->exists($oldPhoto)) {
                            Storage::disk('public')->delete($oldPhoto);
                        }
                    }
                    $photoPaths = [];
                    foreach ($uploadedFiles as $file) {
                        if ($file && $file->isValid()) {
                            $photoPaths[] = $file->store('realisasi_photos', 'public');
                        }
                    }
                    if (! empty($photoPaths)) {
                        $ra->update(['foto_dokumentasi' => json_encode($photoPaths)]);
                    }
                }

                $items = $asetData['items'] ?? [];
                if (! empty($items)) {
                    $ra->realisasiItems()->delete();
                    foreach ($items as $item) {
                        $uraian = $item['uraian'] ?? '';
                        if (empty(trim($uraian))) {
                            continue;
                        }
                        $vol = (int) ($item['vol'] ?? 1);
                        $satuan = $item['satuan'] ?? 'unit';
                        $harga = (float) ($item['hargaSatuan'] ?? 0);
                        $jumlah = $vol * $harga;

                        $ra->realisasiItems()->create([
                            'uraian' => $uraian,
                            'volume' => $vol,
                            'satuan' => $satuan,
                            'harga_satuan' => $harga,
                            'jumlah' => $jumlah,
                        ]);
                    }
                }

                $ra->hitungSubtotal();
            }

            $r->hitungTotalRealisasi();
            $r->syncStokPersediaanMasuk();
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data realisasi berhasil diselesaikan.']);
        }

        return redirect()->route('realisasi.index')->with('success', 'Data realisasi perbaikan berhasil disimpan dan diselesaikan.');
    }

    public function edit($id)
    {
        if (SatkerService::isViewingOtherSatker()) {
            return redirect()->route('realisasi.index')
                ->with('error', 'Mode Lihat Saja: Pengubahan data realisasi untuk satker lain tidak diperkenankan.');
        }

        $r = Realisasi::with(['permintaan.permintaanAsets.aset', 'permintaan.permintaanAsets.rabItems', 'realisasiAsets.realisasiItems', 'realisasiAsets.aset'])->findOrFail($id);

        $rabItemsPermintaan = [];
        if ($r->permintaan) {
            foreach ($r->permintaan->permintaanAsets as $pa) {
                foreach ($pa->rabItems as $rab) {
                    $rabItemsPermintaan[] = [
                        'uraian' => $rab->uraian,
                        'vol' => (int) $rab->volume,
                        'satuan' => $rab->satuan,
                        'hargaSatuan' => (float) $rab->harga_satuan,
                        'total' => (float) $rab->jumlah,
                    ];
                }
            }

            if (empty($rabItemsPermintaan) && (float) $r->permintaan->total_rab > 0) {
                $rabItemsPermintaan[] = [
                    'uraian' => $r->permintaan->pekerjaan ?: 'Pengadaan Barang Persediaan',
                    'vol' => 1,
                    'satuan' => 'paket',
                    'hargaSatuan' => (float) $r->permintaan->total_rab,
                    'total' => (float) $r->permintaan->total_rab,
                ];
            }
        }

        $supplyItems = [];
        $supplyPhotos = [];
        if ($r->jenis === 'Supply') {
            foreach ($r->realisasiAsets as $ra) {
                foreach ($ra->foto_list as $path) {
                    $supplyPhotos[] = [
                        'path' => $path,
                        'url' => Storage::url($path),
                    ];
                }
                foreach ($ra->realisasiItems as $ri) {
                    $supplyItems[] = [
                        'id' => $ri->id,
                        'uraian' => $ri->uraian,
                        'vol' => (int) $ri->volume,
                        'satuan' => $ri->satuan,
                        'hargaSatuan' => (float) $ri->harga_satuan,
                    ];
                }
            }
        }

        $asetList = [];
        if ($r->realisasiAsets->isNotEmpty()) {
            $asetList = $r->realisasiAsets->map(function ($ra) use ($r) {
                $pa = null;
                if ($r->permintaan) {
                    if ($ra->aset_id) {
                        $pa = $r->permintaan->permintaanAsets->where('aset_id', $ra->aset_id)->first();
                    } else {
                        $pa = $r->permintaan->permintaanAsets->firstWhere('nama_barang', $ra->nama_aset);
                    }
                }

                $rabItems = [];
                if ($pa) {
                    foreach ($pa->rabItems as $rab) {
                        $rabItems[] = [
                            'uraian' => $rab->uraian,
                            'vol' => (int) $rab->volume,
                            'satuan' => $rab->satuan,
                            'hargaSatuan' => (float) $rab->harga_satuan,
                            'total' => (float) $rab->jumlah,
                        ];
                    }
                }

                if (empty($rabItems) && $ra->realisasiItems->isNotEmpty()) {
                    foreach ($ra->realisasiItems as $ri) {
                        $rabItems[] = [
                            'uraian' => $ri->uraian,
                            'vol' => (int) $ri->volume,
                            'satuan' => $ri->satuan,
                            'hargaSatuan' => (float) $ri->harga_satuan,
                            'total' => (float) $ri->jumlah,
                        ];
                    }
                }

                $photos = [];
                foreach ($ra->foto_list as $path) {
                    $photos[] = [
                        'path' => $path,
                        'url' => Storage::url($path),
                    ];
                }

                return [
                    'id' => $ra->id,
                    'nama' => $ra->nama_aset ?? ($ra->aset ? $ra->aset->nama_barang : 'Aset Non-Register'),
                    'photos' => $photos,
                    'rabItems' => $rabItems,
                    'items' => $ra->realisasiItems->map(function ($ri) {
                        return [
                            'id' => $ri->id,
                            'uraian' => $ri->uraian,
                            'vol' => (int) $ri->volume,
                            'satuan' => $ri->satuan,
                            'hargaSatuan' => (float) $ri->harga_satuan,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray();
        } elseif ($r->permintaan && $r->permintaan->permintaanAsets->isNotEmpty()) {
            $asetList = $r->permintaan->permintaanAsets->map(function ($pa) {
                $rabItems = [];
                foreach ($pa->rabItems as $rab) {
                    $rabItems[] = [
                        'uraian' => $rab->uraian,
                        'vol' => (int) $rab->volume,
                        'satuan' => $rab->satuan,
                        'hargaSatuan' => (float) $rab->harga_satuan,
                        'total' => (float) $rab->jumlah,
                    ];
                }

                return [
                    'id' => $pa->id,
                    'nama' => $pa->nama_barang ?? ($pa->aset ? $pa->aset->nama_barang : 'Aset Non-Register'),
                    'photos' => [],
                    'rabItems' => $rabItems,
                    'items' => $rabItems,
                ];
            })->values()->toArray();
        } elseif ($r->jenis !== 'Supply') {
            $asetList = [
                [
                    'id' => 1,
                    'nama' => 'Laptop Dell Latitude 5420',
                    'photos' => [],
                    'rabItems' => [
                        [
                            'uraian' => 'Penggantian Baterai Original',
                            'vol' => 1,
                            'satuan' => 'unit',
                            'hargaSatuan' => 1250000,
                            'total' => 1250000,
                        ],
                        [
                            'uraian' => 'Jasa Pemasangan',
                            'vol' => 1,
                            'satuan' => 'ls',
                            'hargaSatuan' => 150000,
                            'total' => 150000,
                        ],
                    ],
                    'items' => [
                        [
                            'id' => 1,
                            'uraian' => 'Penggantian Baterai Original',
                            'vol' => 1,
                            'satuan' => 'unit',
                            'hargaSatuan' => 1250000,
                        ],
                        [
                            'id' => 2,
                            'uraian' => 'Jasa Pemasangan',
                            'vol' => 1,
                            'satuan' => 'ls',
                            'hargaSatuan' => 150000,
                        ],
                    ],
                ],
            ];
        }

        if (empty($rabItemsPermintaan) && ! empty($asetList)) {
            foreach ($asetList as $a) {
                foreach ($a['rabItems'] ?? [] as $rab) {
                    $rabItemsPermintaan[] = [
                        'uraian' => $rab['uraian'],
                        'vol' => (int) ($rab['vol'] ?? 1),
                        'satuan' => $rab['satuan'] ?? 'unit',
                        'hargaSatuan' => (float) ($rab['hargaSatuan'] ?? 0),
                        'total' => (float) (($rab['vol'] ?? 1) * ($rab['hargaSatuan'] ?? 0)),
                    ];
                }
            }
        }

        $realisasi = [
            'id' => $r->id,
            'no_fpa' => $r->no_fpa,
            'pekerjaan' => $r->pekerjaan,
            'vendor' => $r->vendor,
            'jenis' => $r->jenis,
            'tanggal_mulai' => $r->tanggal_mulai ? $r->tanggal_mulai->format('Y-m-d') : '',
            'tanggal_selesai' => $r->tanggal_selesai ? $r->tanggal_selesai->format('Y-m-d') : '',
            'kak_file' => $r->kak_file ? Storage::url($r->kak_file) : null,
            'kuitansi_file' => $r->kuitansi_file ? Storage::url($r->kuitansi_file) : null,
            'rabItemsPermintaan' => $rabItemsPermintaan,
            'supplyItems' => $supplyItems,
            'supplyPhotos' => $supplyPhotos,
            'asetList' => $asetList,
        ];

        $supplies = Supply::orderBy('nama_barang')->get(['id', 'kode_barang', 'nama_barang', 'kategori', 'satuan', 'stok', 'harga_estimasi']);

        return view('realisasi.edit', compact('realisasi', 'supplies'));
    }

    public function update(Request $request, $id)
    {
        SatkerService::abortIfViewingOtherSatker('pengubahan data realisasi');

        $r = Realisasi::with('realisasiAsets.realisasiItems')->findOrFail($id);

        $request->validate([
            'no_fpa' => 'required|string|max:255',
            'pekerjaan' => 'required|string',
            'vendor' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
        ]);

        if ($r->jenis === 'Supply') {
            $supplyItemsRaw = $request->input('supplyItems') ?? $request->input('asetList');
            if (is_string($supplyItemsRaw)) {
                $supplyItems = json_decode($supplyItemsRaw, true) ?? [];
            } else {
                $supplyItems = $supplyItemsRaw ?? [];
            }

            if (isset($supplyItems[0]['items'])) {
                $supplyItems = $supplyItems[0]['items'];
            }

            if (empty($supplyItems)) {
                return back()->withInput()->with('error', 'Daftar rincian barang persediaan wajib diisi minimal 1 item.');
            }

            foreach ($supplyItems as $itemIdx => $it) {
                $num = $itemIdx + 1;
                if (empty(trim($it['uraian'] ?? ''))) {
                    return back()->withInput()->with('error', 'Nama barang persediaan pada baris ke-'.$num.' wajib diisi.');
                }
                if (! isset($it['vol']) || (int) $it['vol'] <= 0) {
                    return back()->withInput()->with('error', 'Volume barang persediaan pada baris ke-'.$num.' harus berupa angka lebih dari 0.');
                }
                if (empty(trim($it['satuan'] ?? ''))) {
                    return back()->withInput()->with('error', 'Satuan barang persediaan pada baris ke-'.$num.' wajib diisi.');
                }
                if (! isset($it['hargaSatuan']) || (float) $it['hargaSatuan'] <= 0) {
                    return back()->withInput()->with('error', 'Harga satuan barang persediaan pada baris ke-'.$num.' belum diisi atau masih 0.');
                }
            }

            DB::transaction(function () use ($r, $request, $supplyItems) {
                $updateData = [
                    'no_fpa' => $request->no_fpa,
                    'pekerjaan' => $request->pekerjaan,
                    'vendor' => $request->vendor,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                ];

                if ($request->hasFile('kak')) {
                    if ($r->kak_file && Storage::disk('public')->exists($r->kak_file)) {
                        Storage::disk('public')->delete($r->kak_file);
                    }
                    $path = $request->file('kak')->store('realisasi_docs', 'public');
                    $updateData['kak_file'] = $path;
                }

                $bastOrKuitansi = $request->file('bast') ?: $request->file('kuitansi');
                if ($bastOrKuitansi) {
                    if ($r->bast_file && Storage::disk('public')->exists($r->bast_file)) {
                        Storage::disk('public')->delete($r->bast_file);
                    }
                    if ($r->kuitansi_file && Storage::disk('public')->exists($r->kuitansi_file)) {
                        Storage::disk('public')->delete($r->kuitansi_file);
                    }
                    $path = $bastOrKuitansi->store('realisasi_docs', 'public');
                    $updateData['bast_file'] = $path;
                    $updateData['kuitansi_file'] = $path;
                }

                $r->update($updateData);

                $ra = $r->realisasiAsets()->first();
                if (! $ra) {
                    $ra = $r->realisasiAsets()->create([
                        'nama_aset' => 'Barang Persediaan',
                        'kode_inventaris' => 'Supply / Persediaan',
                        'subtotal' => 0,
                    ]);
                } else {
                    $ra->update([
                        'nama_aset' => 'Barang Persediaan',
                        'kode_inventaris' => 'Supply / Persediaan',
                    ]);
                }

                $extraAsets = $r->realisasiAsets()->where('id', '!=', $ra->id)->get();
                foreach ($extraAsets as $extra) {
                    $extra->realisasiItems()->delete();
                    $extra->delete();
                }

                $existingPhotos = $request->input('existing_photos') ?? ($request->input('asetList')[0]['existing_photos'] ?? null);
                $oldPhotos = $ra->foto_list;
                $photoPaths = [];

                if (is_array($existingPhotos)) {
                    $photoPaths = $existingPhotos;
                    $removedPhotos = array_diff($oldPhotos, $existingPhotos);
                    foreach ($removedPhotos as $removedPhoto) {
                        if (Storage::disk('public')->exists($removedPhoto)) {
                            Storage::disk('public')->delete($removedPhoto);
                        }
                    }
                } else {
                    $photoPaths = $oldPhotos;
                }

                $uploadedFiles = [];
                $possibleKeys = ['foto_supply', 'foto_dokumentasi', "foto_aset_{$ra->id}", 'foto_aset_0'];
                foreach ($possibleKeys as $key) {
                    if ($request->hasFile($key)) {
                        $f = $request->file($key);
                        $uploadedFiles = is_array($f) ? $f : [$f];
                        break;
                    }
                }

                if (! empty($uploadedFiles)) {
                    foreach ($uploadedFiles as $file) {
                        if ($file && $file->isValid()) {
                            $photoPaths[] = $file->store('realisasi_photos', 'public');
                        }
                    }
                }

                $ra->update(['foto_dokumentasi' => json_encode(array_values($photoPaths))]);

                $ra->realisasiItems()->delete();
                foreach ($supplyItems as $item) {
                    $uraian = trim($item['uraian'] ?? '');
                    if (empty($uraian)) {
                        continue;
                    }
                    $vol = (int) ($item['vol'] ?? 1);
                    $satuan = $item['satuan'] ?? 'buah';
                    $harga = (float) ($item['hargaSatuan'] ?? 0);
                    $jumlah = $vol * $harga;

                    $ra->realisasiItems()->create([
                        'uraian' => $uraian,
                        'volume' => $vol,
                        'satuan' => $satuan,
                        'harga_satuan' => $harga,
                        'jumlah' => $jumlah,
                    ]);
                }

                $ra->hitungSubtotal();
                $r->hitungTotalRealisasi();
                if ($r->status === 'Selesai') {
                    $r->syncStokPersediaanMasuk();
                }
            });

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Perubahan data realisasi supply berhasil disimpan.']);
            }

            return redirect()->route('realisasi.index')->with('success', 'Perubahan data realisasi supply berhasil disimpan.');
        }

        DB::transaction(function () use ($r, $request) {
            $updateData = [
                'no_fpa' => $request->no_fpa,
                'pekerjaan' => $request->pekerjaan,
                'vendor' => $request->vendor,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
            ];

            if ($request->hasFile('kak')) {
                // Delete old KAK file from storage
                if ($r->kak_file && Storage::disk('public')->exists($r->kak_file)) {
                    Storage::disk('public')->delete($r->kak_file);
                }
                $path = $request->file('kak')->store('realisasi_docs', 'public');
                $updateData['kak_file'] = $path;
            }

            if ($request->hasFile('kuitansi')) {
                // Delete old kuitansi file from storage
                if ($r->kuitansi_file && Storage::disk('public')->exists($r->kuitansi_file)) {
                    Storage::disk('public')->delete($r->kuitansi_file);
                }
                $path = $request->file('kuitansi')->store('realisasi_docs', 'public');
                $updateData['kuitansi_file'] = $path;
            }

            $r->update($updateData);

            $asetListRaw = $request->input('asetList');
            if (is_string($asetListRaw)) {
                $asetList = json_decode($asetListRaw, true) ?? [];
            } else {
                $asetList = $asetListRaw ?? [];
            }

            foreach ($asetList as $index => $asetData) {
                $raId = $asetData['id'] ?? null;
                $ra = $raId ? $r->realisasiAsets()->find($raId) : null;
                if (! $ra) {
                    $ra = $r->realisasiAsets()->skip($index)->first();
                    if (! $ra) {
                        $ra = $r->realisasiAsets()->create([
                            'nama_aset' => $asetData['nama'] ?? ('Aset '.($index + 1)),
                            'subtotal' => 0,
                        ]);
                    }
                }

                // Check for new uploaded photos
                $uploadedFiles = [];
                $possibleKeys = [
                    "foto_aset_{$ra->id}",
                    "foto_aset_{$index}",
                    'foto_aset_'.($asetData['id'] ?? ''),
                ];

                foreach ($possibleKeys as $key) {
                    if ($request->hasFile($key)) {
                        $f = $request->file($key);
                        $uploadedFiles = is_array($f) ? $f : [$f];
                        break;
                    }
                }

                // Determine which old photos to remove
                $existingPhotos = $asetData['existing_photos'] ?? null;
                $photoPaths = [];
                $oldPhotos = $ra->foto_list;

                if (is_array($existingPhotos)) {
                    $photoPaths = $existingPhotos;
                    // Delete photos that were removed by user
                    $removedPhotos = array_diff($oldPhotos, $existingPhotos);
                    foreach ($removedPhotos as $removedPhoto) {
                        if (Storage::disk('public')->exists($removedPhoto)) {
                            Storage::disk('public')->delete($removedPhoto);
                        }
                    }
                } else {
                    $photoPaths = $oldPhotos;
                }

                // Append newly uploaded photos
                if (! empty($uploadedFiles)) {
                    foreach ($uploadedFiles as $file) {
                        if ($file && $file->isValid()) {
                            $photoPaths[] = $file->store('realisasi_photos', 'public');
                        }
                    }
                }

                $ra->update(['foto_dokumentasi' => json_encode(array_values($photoPaths))]);

                // Update items
                $items = $asetData['items'] ?? [];
                if (! empty($items)) {
                    $ra->realisasiItems()->delete();
                    foreach ($items as $item) {
                        $uraian = $item['uraian'] ?? '';
                        if (empty(trim($uraian))) {
                            continue;
                        }
                        $vol = (int) ($item['vol'] ?? 1);
                        $satuan = $item['satuan'] ?? 'unit';
                        $harga = (float) ($item['hargaSatuan'] ?? 0);
                        $jumlah = $vol * $harga;

                        $ra->realisasiItems()->create([
                            'uraian' => $uraian,
                            'volume' => $vol,
                            'satuan' => $satuan,
                            'harga_satuan' => $harga,
                            'jumlah' => $jumlah,
                        ]);
                    }
                }

                $ra->hitungSubtotal();
            }

            $r->hitungTotalRealisasi();
            if ($r->status === 'Selesai') {
                $r->syncStokPersediaanMasuk();
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Perubahan data realisasi perbaikan berhasil disimpan.']);
        }

        return redirect()->route('realisasi.index')->with('success', 'Perubahan data realisasi perbaikan berhasil disimpan.');
    }
}
