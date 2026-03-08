<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Offers\ValueObjects\OfferState;
use App\Domain\Products\ValueObjects\ProductState;
use App\Infrastructure\Files\LocalImageStore;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfferSeeder extends Seeder
{
    public function __construct(private readonly LocalImageStore $images) {}

    public function run(): void
    {

        $offers = [
            [
                'name' => 'Starter Offer',
                'slug' => 'starter-offer',
                'state' => OfferState::Published,
                'description' => 'A simple offer to get started quickly.',
            ],
            [
                'name' => 'Premium Offer',
                'slug' => 'premium-offer',
                'state' => OfferState::Draft,
                'description' => 'A full offer with advanced options.',
            ],
            [
                'name' => 'Enterprise Offer',
                'slug' => 'enterprise-offer',
                'state' => OfferState::Hidden,
                'description' => 'A tailored offer for enterprise customers.',
            ],
        ];

        foreach ($offers as $index => $data) {
            $imagePath = $this->images->store(
                'seed/offers',
                sprintf('offer-%s', $data['slug']),
                $data['name']
            );

            $offer = Offer::factory()->create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'state' => $data['state']->value,
                'image' => $imagePath,
            ]);

            $products = [
                [
                    'name' => 'Base Pack',
                    'sku' => sprintf('SKU-%s-BASE', Str::upper(Str::slug($offer->slug))),
                    'state' => ProductState::Published,
                    'price' => '29.90',
                ],
                [
                    'name' => 'Plus Pack',
                    'sku' => sprintf('SKU-%s-PLUS', Str::upper(Str::slug($offer->slug))),
                    'state' => ProductState::Draft,
                    'price' => '59.90',
                ],
                [
                    'name' => 'Max Pack',
                    'sku' => sprintf('SKU-%s-MAX', Str::upper(Str::slug($offer->slug))),
                    'state' => ProductState::Invisible,
                    'price' => '89.90',
                ],
            ];

            foreach ($products as $productIndex => $productData) {
                $productImage = $this->images->store(
                    'seed/products',
                    sprintf('product-%s-%s', $offer->id, $productIndex + 1),
                    $productData['name']
                );

                Product::factory()->create([
                    'offer_id' => $offer->id,
                    'name' => $productData['name'],
                    'sku' => $productData['sku'],
                    'state' => $productData['state']->value,
                    'price' => $productData['price'],
                    'image' => $productImage,
                ]);
            }
        }
    }
}
