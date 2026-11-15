<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator BMN',
                'email' => 'admin@bps.go.id',
                'password' => Hash::make('admin123'),
                'role' => 'Admin',
                'status' => 'active',
            ]
        );
    }
}
