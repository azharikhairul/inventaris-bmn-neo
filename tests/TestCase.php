<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    protected ?User $testUser = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUser = User::firstOrCreate(
            ['username' => 'admin_test'],
            [
                'name' => 'Admin Test',
                'email' => 'admin_test@bps.go.id',
                'role' => 'Admin',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]
        );

        $this->actingAs($this->testUser);
    }
}
