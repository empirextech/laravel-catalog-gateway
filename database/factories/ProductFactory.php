<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'url' => fake()->url(),
            'price_minor' => fake()->numberBetween(199, 19900),
            'currency' => 'EUR',
            'stock' => fake()->numberBetween(0, 40),
            'brand' => 'EMPIREX',
            'gtin' => fake()->numerify('590##########'),
        ];
    }
}
