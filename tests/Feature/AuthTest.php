<?php

namespace Tests\Feature;

use App\Models\Satker;
use App\Models\User;
use App\Services\BpsImapAuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_screen_can_be_rendered(): void
    {
        Auth::logout();

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Sistem');
        $response->assertSee('Username BPS');
        $response->assertSee('mail.bps.go.id');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        Auth::logout();

        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_unregistered_user_cannot_login(): void
    {
        Auth::logout();

        $response = $this->post('/login', [
            'username' => 'unregistered.user',
            'password' => 'secret123',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_registered_user_can_login_with_local_password_hash(): void
    {
        Auth::logout();

        $user = User::create([
            'name' => 'Budi Santoso',
            'username' => 'budi.santoso',
            'email' => 'budi.santoso@bps.go.id',
            'role' => 'Staff',
            'status' => 'active',
            'password' => Hash::make('mypassword123'),
        ]);

        $response = $this->post('/login', [
            'username' => 'budi.santoso',
            'password' => 'mypassword123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_first_time_login_uses_imap_and_caches_hash_to_database(): void
    {
        Auth::logout();

        // User registered by admin without local password
        $user = User::create([
            'name' => 'Siti Rahma',
            'username' => 'siti.rahma',
            'email' => 'siti.rahma@bps.go.id',
            'role' => 'Staff',
            'status' => 'active',
            'password' => null,
        ]);

        $this->assertNull($user->password);

        // Mock IMAP Service returning success
        $mockImap = Mockery::mock(BpsImapAuthService::class);
        $mockImap->shouldReceive('authenticate')
            ->once()
            ->with('siti.rahma', 'bpsMailPassword2026')
            ->andReturn([
                'success' => true,
                'server' => 'mail.bps.go.id',
                'message' => 'Autentikasi BPS berhasil.',
                'connection_error' => false,
            ]);

        $this->app->instance(BpsImapAuthService::class, $mockImap);

        $response = $this->post('/login', [
            'username' => 'siti.rahma',
            'password' => 'bpsMailPassword2026',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Reload user from database and verify hash is now stored
        $user->refresh();
        $this->assertNotNull($user->password);
        $this->assertTrue(Hash::check('bpsMailPassword2026', $user->password));
    }

    public function test_user_can_logout(): void
    {
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_admin_can_view_user_management_and_register_new_bps_user(): void
    {
        $response = $this->get('/users');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');
        $response->assertSee('Tambah Pengguna BPS');

        $postResponse = $this->post('/users', [
            'name' => 'Dewi Lestari',
            'username' => 'dewi.lestari',
            'role' => 'Staff',
            'status' => 'active',
        ]);

        $postResponse->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'username' => 'dewi.lestari',
            'email' => 'dewi.lestari@bps.go.id',
            'role' => 'Staff',
        ]);
    }

    public function test_login_reads_user_satker_and_sets_active_satker_in_session(): void
    {
        Auth::logout();

        $satkerBoalemo = Satker::firstOrCreate(
            ['kode_satker' => '054013200636901000KD'],
            [
                'nama_satker' => 'BPS Kabupaten Boalemo',
                'singkatan' => 'BPS Kab. Boalemo',
                'tingkat' => 'Kabupaten/Kota',
                'kabupaten_kota' => 'Kab. Boalemo',
                'is_active' => true,
            ]
        );

        $user = User::create([
            'name' => 'Operator Boalemo',
            'username' => 'op.boalemo',
            'email' => 'op.boalemo@bps.go.id',
            'role' => 'Staff',
            'status' => 'active',
            'satker_id' => $satkerBoalemo->id,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'username' => 'op.boalemo',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('active_satker_id', $satkerBoalemo->id);
        $response->assertSessionHas('success');
        $this->assertStringContainsString('BPS Kabupaten Boalemo', session('success'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_email_identifies_user_satker_correctly(): void
    {
        Auth::logout();

        $satkerPohuwato = Satker::firstOrCreate(
            ['kode_satker' => '054013400636903000KD'],
            [
                'nama_satker' => 'BPS Kabupaten Pohuwato',
                'singkatan' => 'BPS Kab. Pohuwato',
                'tingkat' => 'Kabupaten/Kota',
                'kabupaten_kota' => 'Kab. Pohuwato',
                'is_active' => true,
            ]
        );

        $user = User::create([
            'name' => 'Operator Pohuwato',
            'username' => 'op.pohuwato',
            'email' => 'op.pohuwato@bps.go.id',
            'role' => 'Staff',
            'status' => 'active',
            'satker_id' => $satkerPohuwato->id,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'username' => 'op.pohuwato@bps.go.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('active_satker_id', $satkerPohuwato->id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_clears_active_satker_session(): void
    {
        $satker = Satker::first();

        $user = User::create([
            'name' => 'Staff Logout Test',
            'username' => 'staff.logout',
            'email' => 'staff.logout@bps.go.id',
            'role' => 'Staff',
            'status' => 'active',
            'satker_id' => $satker?->id,
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($user)->withSession(['active_satker_id' => $satker?->id]);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
        $this->assertFalse(session()->has('active_satker_id'));
    }
}
