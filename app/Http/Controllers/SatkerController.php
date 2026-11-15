<?php

namespace App\Http\Controllers;

use App\Models\Satker;
use App\Models\User;
use App\Services\SatkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SatkerController extends Controller
{
    /**
     * Switch active satker scope (for Super Admin / Admin Provinsi).
     */
    public function switchSatker(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user || ! $user->canAccessAllSatkers()) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk berpindah satker.');
        }

        $satkerId = $request->filled('satker_id') && $request->input('satker_id') !== 'all'
            ? (int) $request->input('satker_id')
            : null;

        SatkerService::setActiveSatker($satkerId);

        $namaSatker = $satkerId
            ? (Satker::find($satkerId)?->nama_satker ?? 'Satker Terpilih')
            : 'Semua Satker (Gabungan)';

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))
                ->with('success', 'Lingkup aktif dialihkan ke: '.$namaSatker);
        }

        return back()->with('success', 'Lingkup aktif dialihkan ke: '.$namaSatker);
    }

    /**
     * Display list of Satker and branch statistics.
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user || ! $user->canAccessAllSatkers()) {
            abort(403, 'Akses manajemen satker terbatas untuk Administrator Provinsi.');
        }

        $satkers = Satker::withCount(['users', 'permintaans', 'realisasis'])
            ->with(['asets' => function ($q) {
                $q->select('id', 'kode_satker');
            }])
            ->orderByRaw("CASE WHEN tingkat = 'Provinsi' THEN 0 ELSE 1 END")
            ->orderBy('nama_satker')
            ->get();

        return view('satker.index', compact('satkers'));
    }

    /**
     * Store new Satker.
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang dapat menambahkan Satker baru.');
        }

        $validated = $request->validate([
            'kode_satker' => 'required|string|max:50|unique:satkers,kode_satker',
            'nama_satker' => 'required|string|max:255',
            'singkatan' => 'nullable|string|max:100',
            'tingkat' => 'required|in:Provinsi,Kabupaten/Kota',
            'kabupaten_kota' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
        ]);

        if ($validated['tingkat'] === 'Provinsi' && empty($validated['kabupaten_kota'])) {
            $validated['kabupaten_kota'] = 'Provinsi Gorontalo';
        }

        Satker::create($validated);

        return redirect()->route('satker.index')->with('success', 'Satker baru berhasil ditambahkan.');
    }

    /**
     * Update existing Satker.
     */
    public function update(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang dapat mengubah data Satker.');
        }

        $satker = Satker::findOrFail($id);

        $validated = $request->validate([
            'kode_satker' => 'required|string|max:50|unique:satkers,kode_satker,'.$satker->id,
            'nama_satker' => 'required|string|max:255',
            'singkatan' => 'nullable|string|max:100',
            'tingkat' => 'required|in:Provinsi,Kabupaten/Kota',
            'kabupaten_kota' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
        ]);

        if ($validated['tingkat'] === 'Provinsi' && empty($validated['kabupaten_kota'])) {
            $validated['kabupaten_kota'] = 'Provinsi Gorontalo';
        }

        $satker->update($validated);

        return redirect()->route('satker.index')->with('success', 'Data Satker berhasil diperbarui.');
    }
}
