@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-20">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('supply.distribusi.index') }}" 
               class="p-2 text-slate-400 hover:text-slate-600 hover:bg-white rounded-lg border border-slate-200 shadow-2xs transition-all"
               title="Kembali ke Daftar Distribusi">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Berita Acara Serah Terima (BAST)</h1>
                    @if($distribusi->status === 'Selesai' || $distribusi->file_bast_ttd)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Selesai (Sudah TTD Manual)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Menunggu TTD Manual
                        </span>
                    @endif
                </div>
                <p class="text-sm font-mono text-[#094886] font-bold mt-0.5">{{ $distribusi->nomor_bast }}</p>
            </div>
        </div>

        <!-- Action Buttons: Edit, Print, PDF, Back -->
        <div class="flex items-center gap-2">
            @if(!\App\Services\SatkerService::isViewingOtherSatker())
            <!-- Tombol Sesuaikan / Edit BAST -->
            <a href="{{ route('supply.distribusi.edit', $distribusi->id) }}" 
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800 text-xs sm:text-sm font-bold rounded-lg shadow-2xs transition-all cursor-pointer"
               title="Sesuaikan nomor, tanggal, pihak penyerah/penerima, dan rincian BAST">
                <svg class="w-4 h-4 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span>Sesuaikan Data</span>
            </a>
            @endif

            <!-- Tombol Cetak Browser -->
            <a href="{{ route('supply.distribusi.print', $distribusi->id) }}" target="_blank" 
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-xs sm:text-sm font-bold rounded-lg shadow-2xs transition-all cursor-pointer">
                <svg class="w-4 h-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                <span>Cetak BAST</span>
            </a>

            <!-- Tombol Download PDF -->
            <a href="{{ route('supply.distribusi.pdf', [$distribusi->id, 'download' => 1]) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#094886] hover:bg-[#073868] text-white text-xs sm:text-sm font-bold rounded-lg shadow-sm transition-all cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Ekspor PDF</span>
            </a>
        </div>
    </div>

    <!-- PANEL UPLOAD BERKAS SCAN BAST YANG SUDAH DITANDATANGANI MANUAL -->
    <div class="bg-white rounded-2xl border-2 {{ ($distribusi->status === 'Selesai' || $distribusi->file_bast_ttd) ? 'border-emerald-300 bg-emerald-50/20' : 'border-amber-300 bg-amber-50/30' }} p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl {{ ($distribusi->status === 'Selesai' || $distribusi->file_bast_ttd) ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} flex items-center justify-center flex-shrink-0">
                    @if($distribusi->status === 'Selesai' || $distribusi->file_bast_ttd)
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    @else
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                    @endif
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">
                        {{ ($distribusi->status === 'Selesai' || $distribusi->file_bast_ttd) ? 'Dokumen BAST Tanda Tangan Manual Tersimpan' : 'Unggah Kembali BAST yang Telah Ditandatangani Manual' }}
                    </h3>
                    <p class="text-xs text-slate-600 mt-1 max-w-xl leading-relaxed">
                        @if($distribusi->status === 'Selesai' || $distribusi->file_bast_ttd)
                            Berkas scan BAST fisik yang telah ditandatangani basah oleh Pihak Pertama dan Pihak Kedua telah diunggah ke arsip.
                            @if($distribusi->uploaded_at)
                                <span class="block mt-0.5 text-slate-500 font-medium">Diunggah pada: {{ $distribusi->uploaded_at->translatedFormat('d F Y, H:i') }}</span>
                            @endif
                        @else
                            Silakan <strong>Cetak / Ekspor PDF</strong> dokumen BAST di bawah, lakukan penandatanganan manual secara basah oleh kedua pihak, lalu pindai/foto dan unggah hasilnya di bawah ini untuk menyelesaikan proses arsip BAST.
                        @endif
                    </p>
                </div>
            </div>

            @if($distribusi->file_bast_ttd && $distribusi->file_url)
                <div class="flex items-center gap-2 self-start sm:self-center">
                    <a href="{{ $distribusi->file_url }}" target="_blank" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-lg shadow-sm transition-all">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <span>Lihat Berkas Scan</span>
                    </a>

                    @if(!\App\Services\SatkerService::isViewingOtherSatker())
                    <form action="{{ route('supply.distribusi.delete-ttd', $distribusi->id) }}" method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas scan tanda tangan ini? Status akan kembali menjadi Menunggu TTD.');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-100/60 rounded-lg transition-colors cursor-pointer" title="Hapus Berkas Scan">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </form>
                    @endif
                </div>
            @endif
        </div>

        @if(!\App\Services\SatkerService::isViewingOtherSatker() && (!$distribusi->file_bast_ttd || request()->has('ganti')))
        <form action="{{ route('supply.distribusi.upload-ttd', $distribusi->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 pt-4 border-t border-slate-200/80">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <div class="md:col-span-8">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Pilih File Scan BAST (PDF, JPG, PNG - Maks. 10MB) <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="file_bast_ttd" accept=".pdf,.jpg,.jpeg,.png" required
                           class="w-full text-xs text-slate-700 bg-white border border-slate-300 rounded-lg p-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#094886] hover:file:bg-blue-100 cursor-pointer">
                    @error('file_bast_ttd')
                        <span class="text-[11px] text-rose-600 font-semibold block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="md:col-span-4 flex items-center gap-2">
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-lg shadow-sm transition-all cursor-pointer">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        <span>Unggah Scan BAST</span>
                    </button>
                </div>
            </div>
        </form>
        @endif
    </div>

    <!-- TAMPILAN RESMI DOKUMEN BAST (SESUAI FORMAT FISIK BPS GORONTALO) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 sm:p-10 space-y-6">

        <!-- KOP BPS GORONTALO -->
        <div class="text-center pb-4 border-b-2 border-slate-900">
            <div class="flex items-center justify-center gap-4">
                <img src="{{ asset('logo_bps.png') }}" alt="Logo BPS" class="w-14 h-auto" onerror="this.style.display='none'">
                <div>
                    <div class="text-xs font-semibold text-slate-700 uppercase tracking-widest">Badan Pusat Statistik</div>
                    <div class="text-base sm:text-lg font-extrabold text-slate-900 uppercase">Provinsi Gorontalo</div>
                    <div class="text-[11px] text-slate-500">Jl. Durian No. 40, Kel. Tomulobutao, Kec. Dungingi, Kota Gorontalo, Gorontalo 96136</div>
                </div>
            </div>
        </div>

        <!-- JUDUL & NOMOR SURAT -->
        <div class="text-center space-y-1">
            <h2 class="text-sm sm:text-base font-extrabold uppercase underline tracking-wider text-slate-900">
                Berita Acara Serah Terima Barang Persediaan
            </h2>
            <p class="text-xs font-bold text-slate-700">Nomor: {{ $distribusi->nomor_bast }}</p>
        </div>

        <!-- PEMBUKA SURAT -->
        <div class="text-xs sm:text-sm text-slate-800 leading-relaxed text-justify">
            Pada hari ini, <strong>{{ $distribusi->tanggal_bast ? $distribusi->tanggal_bast->translatedFormat('l') : '...' }}</strong>, 
            tanggal <strong>{{ $distribusi->tanggal_bast ? $distribusi->tanggal_bast->translatedFormat('d') : '...' }}</strong>, 
            bulan <strong>{{ $distribusi->tanggal_bast ? $distribusi->tanggal_bast->translatedFormat('F') : '...' }}</strong>, 
            tahun <strong>{{ $distribusi->tanggal_bast ? $distribusi->tanggal_bast->translatedFormat('Y') : '...' }}</strong>, 
            yang bertanda tangan di bawah ini:
        </div>

        <!-- IDENTITAS PIHAK PERTAMA & KEDUA -->
        <div class="space-y-4 text-xs sm:text-sm text-slate-900 pl-2">
            <!-- Pihak Pertama -->
            <div class="grid grid-cols-12 gap-2">
                <div class="col-span-1 font-bold">1.</div>
                <div class="col-span-3 font-semibold">Nama</div>
                <div class="col-span-8">: <strong>{{ $distribusi->pihak_pertama_nama }}</strong></div>

                <div class="col-span-1"></div>
                <div class="col-span-3 font-semibold">NIP</div>
                <div class="col-span-8">: {{ $distribusi->pihak_pertama_nip ?: '-' }}</div>

                <div class="col-span-1"></div>
                <div class="col-span-3 font-semibold">Jabatan</div>
                <div class="col-span-8">: {{ $distribusi->pihak_pertama_jabatan }}</div>

                <div class="col-span-1"></div>
                <div class="col-span-11 italic text-slate-600">
                    Selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong> (Yang Menyerahkan).
                </div>
            </div>

            <!-- Pihak Kedua -->
            <div class="grid grid-cols-12 gap-2 pt-2 border-t border-slate-100">
                <div class="col-span-1 font-bold">2.</div>
                <div class="col-span-3 font-semibold">Nama</div>
                <div class="col-span-8">: <strong>{{ $distribusi->pihak_kedua_nama }}</strong></div>

                <div class="col-span-1"></div>
                <div class="col-span-3 font-semibold">NIP</div>
                <div class="col-span-8">: {{ $distribusi->pihak_kedua_nip ?: '-' }}</div>

                <div class="col-span-1"></div>
                <div class="col-span-3 font-semibold">Unit Kerja / Tim</div>
                <div class="col-span-8">: {{ $distribusi->pihak_kedua_unit_kerja }}</div>

                <div class="col-span-1"></div>
                <div class="col-span-11 italic text-slate-600">
                    Selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong> (Yang Menerima).
                </div>
            </div>
        </div>

        <!-- KLAUSUL KESEPAKATAN -->
        <div class="text-xs sm:text-sm text-slate-800 leading-relaxed text-justify">
            PIHAK PERTAMA menyerahkan barang persediaan kepada PIHAK KEDUA, dan PIHAK KEDUA menyatakan telah menerima barang persediaan tersebut dalam keadaan baik, cukup, dan lengkap untuk keperluan: 
            <strong>{{ $distribusi->kegiatan_keperluan ?: 'Operasional Kantor' }}</strong>, dengan rincian barang sebagai berikut:
        </div>

        <!-- TABEL RINCIAN BARANG -->
        <div class="border border-slate-800 overflow-hidden">
            <table class="w-full text-left text-xs sm:text-sm text-slate-900">
                <thead class="bg-slate-100 border-b border-slate-800 font-bold text-center">
                    <tr>
                        <th class="px-3 py-2 w-10 border-r border-slate-800">NO</th>
                        <th class="px-3 py-2 border-r border-slate-800 text-left">NAMA BARANG PERSEDIAAN</th>
                        <th class="px-3 py-2 border-r border-slate-800 text-left">SPESIFIKASI</th>
                        <th class="px-3 py-2 border-r border-slate-800 w-24">JUMLAH</th>
                        <th class="px-3 py-2 border-r border-slate-800 w-24">SATUAN</th>
                        <th class="px-3 py-2 text-left">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($distribusi->items as $idx => $bItem)
                    <tr>
                        <td class="px-3 py-2 text-center border-r border-slate-800 font-medium">{{ $idx + 1 }}</td>
                        <td class="px-3 py-2 border-r border-slate-800 font-bold">{{ $bItem->nama_barang }}</td>
                        <td class="px-3 py-2 border-r border-slate-800 text-slate-600 text-xs">{{ $bItem->spesifikasi ?: '-' }}</td>
                        <td class="px-3 py-2 border-r border-slate-800 text-center font-bold">{{ $bItem->jumlah }}</td>
                        <td class="px-3 py-2 border-r border-slate-800 text-center">{{ $bItem->satuan }}</td>
                        <td class="px-3 py-2 text-xs text-slate-600">{{ $bItem->keterangan ?: 'Baik & Lengkap' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- PENUTUP -->
        <div class="text-xs sm:text-sm text-slate-800 leading-relaxed text-justify">
            Demikian Berita Acara Serah Terima barang persediaan ini dibuat dengan sebenarnya dalam rangkap secukupnya untuk dapat dipergunakan sebagaimana mestinya.
        </div>

        <!-- KOLOM TANDA TANGAN (DUA PIHAK) -->
        <div class="grid grid-cols-2 gap-8 pt-8 text-center text-xs sm:text-sm text-slate-900">
            <div>
                <div class="font-semibold">Yang Menerima,</div>
                <div class="font-bold text-slate-700">PIHAK KEDUA</div>
                <div class="h-24"></div>
                <div class="font-bold underline uppercase">{{ $distribusi->pihak_kedua_nama }}</div>
                @if($distribusi->pihak_kedua_nip)
                    <div class="text-xs text-slate-600">NIP. {{ $distribusi->pihak_kedua_nip }}</div>
                @endif
                <div class="text-xs text-slate-500">{{ $distribusi->pihak_kedua_unit_kerja }}</div>
            </div>

            <div>
                <div class="font-semibold">Yang Menyerahkan,</div>
                <div class="font-bold text-slate-700">PIHAK PERTAMA</div>
                <div class="h-24"></div>
                <div class="font-bold underline uppercase">{{ $distribusi->pihak_pertama_nama }}</div>
                @if($distribusi->pihak_pertama_nip)
                    <div class="text-xs text-slate-600">NIP. {{ $distribusi->pihak_pertama_nip }}</div>
                @endif
                <div class="text-xs text-slate-500">{{ $distribusi->pihak_pertama_jabatan }}</div>
            </div>
        </div>

    </div>

    <!-- Bottom Actions -->
    <div class="flex items-center justify-between pt-4 border-t border-slate-200">
        <a href="{{ route('supply.distribusi.index') }}" 
           class="px-5 py-2.5 bg-white border border-slate-300 hover:border-slate-400 text-slate-700 text-sm font-semibold rounded-lg shadow-2xs transition-colors">
            ← Kembali ke Daftar Distribusi
        </a>

        <div class="flex items-center gap-2">
            @if(!\App\Services\SatkerService::isViewingOtherSatker())
            <a href="{{ route('supply.distribusi.edit', $distribusi->id) }}" 
               class="px-4 py-2.5 bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
               title="Sesuaikan nomor, tanggal, pihak penyerah/penerima, dan rincian BAST">
                Sesuaikan Data
            </a>
            <form action="{{ route('supply.distribusi.destroy', $distribusi->id) }}" method="POST" 
                  onsubmit="return confirm('Apakah Anda yakin ingin membatalkan distribusi ini? Stok fisik barang akan dikembalikan ke gudang.');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 text-sm font-semibold rounded-lg transition-colors cursor-pointer">
                    Batalkan Distribusi
                </button>
            </form>
            @endif
        </div>
    </div>

</div>
@endsection
