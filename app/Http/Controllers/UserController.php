<?php

namespace App\Http\Controllers;

use App\Models\Satker;
use App\Models\User;
use App\Services\SatkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Authorize admin access only.
     */
    protected function authorizeAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak. Halaman Manajemen Pengguna hanya dapat diakses oleh Administrator.');
        }
    }

    /**
     * Display a listing of registered users.
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $query = User::query();

        // Filter users by active satker scope
        SatkerService::filterQuery($query);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /** @var User $currentUser */
        $currentUser = Auth::user();
        if ($currentUser->canAccessAllSatkers()) {
            $satkers = SatkerService::getAllSatkers();
        } else {
            $satkers = Satker::where('id', $currentUser->satker_id)->get();
        }

        $users = $query->with('satker')->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('users.index', compact('users', 'satkers'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Regency admins cannot add users to other satkers
        if (! $currentUser->canAccessAllSatkers() && SatkerService::isViewingOtherSatker()) {
            SatkerService::abortIfViewingOtherSatker('penambahan pengguna');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'role' => ['required', 'in:Admin,Staff'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'username.required' => 'Username BPS wajib diisi.',
            'username.unique' => 'Username BPS ini sudah terdaftar.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $cleanUsername = strtolower(trim(str_replace(' ', '', $validated['username'])));
        if (str_contains($cleanUsername, '@')) {
            $cleanUsername = explode('@', $cleanUsername)[0];
        }

        $satkerId = $currentUser->canAccessAllSatkers()
            ? ($request->filled('satker_id') ? (int) $request->satker_id : (SatkerService::getActiveSatkerId() ?? SatkerService::getIndukSatkerId()))
            : (int) $currentUser->satker_id;

        $user = new User;
        $user->name = trim($validated['name']);
        $user->username = $cleanUsername;
        $user->email = $cleanUsername.'@bps.go.id';
        $user->role = $validated['role'];
        $user->satker_id = $satkerId;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        } else {
            // Null password means it will be fetched and cached from BPS Mail IMAP on first login
            $user->password = null;
        }

        $user->save();

        Cache::forget('sidebar_user_count_'.($satkerId ?? 'all'));
        Cache::forget('sidebar_user_count_all');

        return redirect()->route('users.index')
            ->with('success', "Pengguna {$user->name} ({$user->username}) berhasil didaftarkan. Pengguna kini dapat login menggunakan akun BPS Mail.");
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($id);

        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Regency admin can only edit users in their own satker
        if (! $currentUser->canAccessAllSatkers()) {
            if (SatkerService::isViewingOtherSatker()) {
                SatkerService::abortIfViewingOtherSatker('perubahan data pengguna');
            }
            if ((int) $user->satker_id !== (int) $currentUser->satker_id) {
                abort(403, 'Akses ditolak. Anda hanya dapat mengelola pengguna di satker Anda sendiri.');
            }
        }

        if ($user->isSuperAdmin()) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
                'password' => ['nullable', 'string', 'min:6'],
            ]);

            // Role dan status Administrator dilindungi (selalu Admin dan Aktif)
            $userRole = 'Admin';
            $userStatus = 'active';
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
                'role' => ['required', 'in:Admin,Staff'],
                'status' => ['required', 'in:active,inactive'],
                'password' => ['nullable', 'string', 'min:6'],
            ]);

            $userRole = $validated['role'];
            $userStatus = $validated['status'];
        }

        $cleanUsername = strtolower(trim(str_replace(' ', '', $validated['username'])));
        if (str_contains($cleanUsername, '@')) {
            $cleanUsername = explode('@', $cleanUsername)[0];
        }

        $user->name = trim($validated['name']);
        $user->username = $cleanUsername;
        $user->email = null;
        $user->role = $userRole;
        $user->status = $userStatus;
        if ($currentUser->canAccessAllSatkers() && $request->has('satker_id')) {
            $user->satker_id = $request->filled('satker_id') ? (int) $request->satker_id : null;
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        Cache::forget('sidebar_user_count_'.($user->satker_id ?? 'all'));
        Cache::forget('sidebar_user_count_all');

        return redirect()->route('users.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($id);

        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Regency admin can only delete users in their own satker
        if (! $currentUser->canAccessAllSatkers()) {
            if (SatkerService::isViewingOtherSatker()) {
                SatkerService::abortIfViewingOtherSatker('penghapusan pengguna');
            }
            if ((int) $user->satker_id !== (int) $currentUser->satker_id) {
                abort(403, 'Akses ditolak. Anda hanya dapat mengelola pengguna di satker Anda sendiri.');
            }
        }

        if ($user->isSuperAdmin()) {
            return redirect()->route('users.index')
                ->with('error', 'Akun Administrator utama dilindungi dan tidak dapat dihapus dari sistem.');
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        $satkerId = $user->satker_id;
        $name = $user->name;
        $user->delete();

        Cache::forget('sidebar_user_count_'.($satkerId ?? 'all'));
        Cache::forget('sidebar_user_count_all');

        return redirect()->route('users.index')
            ->with('success', "Pengguna {$name} berhasil dihapus dari sistem.");
    }
}
