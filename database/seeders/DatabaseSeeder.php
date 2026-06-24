<?php

namespace Database\Seeders;

use App\Enums\UserRole;
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
        $this->call(ConsentVersionSeeder::class);
        $this->call(PilotLocationSeeder::class);

        // User::factory(10)->create();

        User::query()->updateOrCreate(
            ['email' => 'admin@example.test'],
            [
                'name' => 'مدیر نمایشی اکسپلوریا',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'demo@example.test'],
            [
                'name' => 'کاربر نمایشی اکسپلوریا',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => UserRole::Viewer,
            ],
        );
    }
}
