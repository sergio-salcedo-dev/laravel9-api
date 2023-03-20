<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Link;
use Illuminate\Database\Seeder;

class LinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Link::factory()->create([
            'short_link' => 'google.com',
            'full_link' => 'https://google.com',
            'user_id' => 1,
            'views' => 1,
        ]);
    }
}
