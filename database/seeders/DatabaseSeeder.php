<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /** Seed the application's database  */
    public function run(): void
    {
        // User::factory(1)->create();

        User::factory()->create([
            'name' => 'test',
            'email' => 'test@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

        $this->call(
            [
                ProductSeeder::class,
                StoreSeeder::class,
                ProductStoreSeeder::class,
            ]
        );
    }
}
