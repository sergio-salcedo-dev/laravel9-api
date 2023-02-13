<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//        Store::factory()->count(5)->create();
        for ($i = 1; $i <= 5; $i++) {
            Store::create(['name' => "Store $i"]);
        }
    }
}
