<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Database\Seeder;

class ProductStoreSeeder extends Seeder
{
    /** Run the database seeds. */
    public function run(): void
    {
        $stores = Store::paginate(2);
        $products = Product::paginate(2);

        foreach ($products as $product) {
            foreach ($stores as $store) {
                ProductStore::create([
                    'product_id' => $product->id,
                    'store_id' => $store->id,
                ]);
            }
        }
    }
}
