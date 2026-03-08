<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Offers\ValueObjects\OfferState;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offer>
 */
class OfferFactory extends Factory
{
    protected $model = Offer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->sentence(3);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'image' => 'seed/offers/placeholder.svg',
            'state' => fake()->randomElement(OfferState::values()),
        ];
    }

    public function published(): static
    {
        return $this->state([
            'state' => OfferState::Published->value,
        ]);
    }
}
