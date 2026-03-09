<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Products\ValueObjects\ProductState;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'offer_id' => Offer::factory(),
            'name' => fake()->words(2, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'image' => 'seed/products/placeholder.svg',
            'price' => fake()->randomFloat(2, 1, 999),
            'state' => fake()->randomElement(ProductState::values()),
        ];
    }

    public function published(): static
    {
        return $this->state([
            'state' => ProductState::Published->value,
        ]);
    }
}
