<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Link;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'short_link' => $this->faker->url(),
            'full_link' => $this->faker->url(),
            'user_id' => $this->faker->numberBetween(1, 10),
            'views' => 1,
        ];
    }
}
