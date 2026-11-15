<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BpsImapAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected BpsImapAuthService $imapAuthService;

    public function __construct(BpsImapAuthService $imapAuthService)
    {
        $this->imapAuthService = $imapAuthService;
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username atau Email BPS wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $usernameInput = trim($request->input('username'));
        $passwordInput = $request->input('password');

        // 1. Check if user is registered in the database
        $user = User::findByUsernameOrEmail($usernameInput);

        if (! $user) {
            return back()
                ->withInput($request->only('username'))
                ->with('error', 'Akun Anda belum terdaftar di sistem Inventaris BMN. Silakan hubungi Administrator untuk mendaftarkan akun Anda terlebih dahulu.');
        }

        // 2. Check if user account is active
        if ($user->status && strtolower($user->status) !== 'active') {
            return back()
                ->withInput($request->only('username'))
                ->with('error', 'Akun Anda saat ini dinonaktifkan oleh Administrator. Silakan hubungi admin untuk aktivasi kembali.');
        }

        // 3. Resolve which satker this username is registered in
        $satker = $user->resolveSatker();
        $satkerName = $satker?->nama_satker ?? 'Satker BPS';

        // 4. Step A: Check local password hash first (fast & works offline / when mail server is down)
        if (! empty($user->password) && Hash::check($passwordInput, $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            // Set active satker context to user's registered satker
            if ($satker) {
                $request->session()->put('active_satker_id', $satker->id);
            }

            // Ensure username column is populated if previously null
            if (empty($user->username)) {
                $user->username = str_contains($usernameInput, '@') ? explode('@', $usernameInput)[0] : $usernameInput;
                $user->save();
            }

            Log::info("User {$user->username} logged in via local password hash into satker: {$satkerName} (ID: {$satker?->id})");

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, '.($user->name ?? $user->username)."! Anda berhasil masuk ke lingkup {$satkerName}.");
        }

        // 5. Step B: Local password is null or mismatched, try BPS IMAP (mail.bps.go.id / mailhost.bps.go.id)
        $imapResult = $this->imapAuthService->authenticate($usernameInput, $passwordInput);

        if ($imapResult['success']) {
            // Success! Cache password hash locally so subsequent logins do not need remote mail queries
            $user->password = Hash::make($passwordInput);
            if (empty($user->username)) {
                $user->username = str_contains($usernameInput, '@') ? explode('@', $usernameInput)[0] : $usernameInput;
            }
            $user->save();

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            // Set active satker context to user's registered satker
            if ($satker) {
                $request->session()->put('active_satker_id', $satker->id);
            }

            Log::info("User {$user->username} authenticated via BPS IMAP ({$imapResult['server']}) into satker: {$satkerName} (ID: {$satker?->id})");

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Login berhasil menggunakan akun BPS Mail ('.$imapResult['server']."). Anda berhasil masuk ke lingkup {$satkerName}.");
        }

        // 6. Handle IMAP connection failure vs wrong credentials
        if (! empty($imapResult['connection_error'])) {
            return back()
                ->withInput($request->only('username'))
                ->with('error', 'Tidak dapat terhubung ke server Mail BPS (mail.bps.go.id / mailhost.bps.go.id) dan sandi lokal belum tersedia/cocok. Silakan periksa koneksi jaringan Anda atau hubungi Administrator.');
        }

        return back()
            ->withInput($request->only('username'))
            ->with('error', 'Username atau password akun BPS yang Anda masukkan salah. Pastikan password sesuai dengan akun SSO/Mail BPS.');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->forget('active_satker_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem Inventaris BMN.');
    }
}
