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
     */
    public function run(): void
    {
        $this->call(ConsentVersionSeeder::class);
        $this->call(PilotLocationSeeder::class);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'کاربر نمایشی اکسپلوریا',
            'email' => 'demo@example.test',
        ]);
    }
}
