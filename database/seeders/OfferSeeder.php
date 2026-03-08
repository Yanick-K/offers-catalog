<?php

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
                'name' => 'Offre Starter',
                'slug' => 'offre-starter',
                'state' => OfferState::Published,
                'description' => 'Une offre simple pour demarrer rapidement.',
            ],
            [
                'name' => 'Offre Premium',
                'slug' => 'offre-premium',
                'state' => OfferState::Draft,
                'description' => 'Une offre complete avec options avancees.',
            ],
            [
                'name' => 'Offre Enterprise',
                'slug' => 'offre-enterprise',
                'state' => OfferState::Hidden,
                'description' => 'Une offre sur-mesure pour les grands comptes.',
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
                    'name' => 'Pack Base',
                    'sku' => sprintf('SKU-%s-BASE', Str::upper(Str::slug($offer->slug))),
                    'state' => ProductState::Published,
                    'price' => '29.90',
                ],
                [
                    'name' => 'Pack Plus',
                    'sku' => sprintf('SKU-%s-PLUS', Str::upper(Str::slug($offer->slug))),
                    'state' => ProductState::Draft,
                    'price' => '59.90',
                ],
                [
                    'name' => 'Pack Max',
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
