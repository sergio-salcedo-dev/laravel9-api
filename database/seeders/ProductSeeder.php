<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
//        Product::factory()->count(5)->create();

        for ($i = 1; $i <= 5; $i++) {
            Product::create(['name' => "Product $i"]);
        }
    }
}
