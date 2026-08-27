<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * There's no Filament UserResource in this app (single-admin backend,
     * no multi-role management needed) — this seeder is the only way to get
     * a first login. Change this password immediately after logging in once
     * (Filament's passwordReset() is enabled on the panel).
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@angelinvestor.pk',
            'password' => bcrypt('change-me-immediately'),
        ]);
    }
}
