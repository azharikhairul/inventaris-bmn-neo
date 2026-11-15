<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function index()
    {
        $user = Auth::user();

        return view('profile.index', compact('user'));
    }

    /**
     * Update the authenticated user's profile information and password.
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'username.required' => 'Username BPS wajib diisi.',
            'username.unique' => 'Username BPS ini sudah digunakan oleh pengguna lain.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sesuai.',
        ]);

        $cleanUsername = strtolower(trim(str_replace(' ', '', $validated['username'])));
        if (str_contains($cleanUsername, '@')) {
            $cleanUsername = explode('@', $cleanUsername)[0];
        }

        $user->name = trim($validated['name']);
        $user->username = $cleanUsername;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile.index')
            ->with('success', 'Profil dan pengaturan akun Anda berhasil diperbarui.');
    }
}
