<?php

namespace Database\Seeders;

use App\Enums\RecordStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserAccessScope;
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
                'role' => UserRole::Visitor,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'viewer@example.test'],
            [
                'name' => 'مشاهده‌گر نمایشی اکسپلوریا',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => UserRole::Viewer,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'regional@example.test'],
            [
                'name' => 'ادمین استانی / منطقه‌ای اکسپلوریا',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => UserRole::RegionalAdmin,
            ],
        );

        $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
        $regionalAdmin = User::query()->where('email', 'regional@example.test')->firstOrFail();

        UserAccessScope::query()->updateOrCreate(
            ['user_id' => $admin->id, 'role_key' => 'super_admin', 'scope_type' => 'global', 'scope_id' => null],
            ['status' => RecordStatus::Active, 'metadata' => ['source' => 'database_seed']],
        );

        UserAccessScope::query()->updateOrCreate(
            ['user_id' => $regionalAdmin->id, 'role_key' => 'regional_admin', 'scope_type' => 'region', 'scope_id' => 'تهران'],
            ['status' => RecordStatus::Active, 'metadata' => ['source' => 'database_seed']],
        );
    }
}
