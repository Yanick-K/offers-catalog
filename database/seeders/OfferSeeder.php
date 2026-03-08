<?php

namespace Database\Seeders;

use App\Domain\Offers\ValueObjects\OfferState;
use App\Domain\Products\ValueObjects\ProductState;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory('seed/offers');
        $disk->makeDirectory('seed/products');

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
            $imagePath = $this->storeRemoteImage(
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
                $productImage = $this->storeRemoteImage(
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

    private function storeRemoteImage(string $directory, string $seed, string $label): string
    {
        $path = sprintf('%s/%s', trim($directory, '/'), $seed);
        $url = sprintf('https://picsum.photos/seed/%s/640/480', $seed);

        try {
            $response = Http::timeout(10)->retry(2, 200)->get($url);
        } catch (Throwable) {
            return $this->createSvg($path.'.svg', $label);
        }

        if (! $response->successful()) {
            return $this->createSvg($path.'.svg', $label);
        }

        $extension = $this->extensionFromContentType($response->header('Content-Type'));
        $imagePath = $path.'.'.$extension;

        Storage::disk('public')->put($imagePath, $response->body());

        return $imagePath;
    }

    private function createSvg(string $path, string $label): string
    {
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480"><rect width="640" height="480" fill="#f3f4f6"/><text x="50%%" y="50%%" font-family="Arial, sans-serif" font-size="24" fill="#111827" text-anchor="middle" dominant-baseline="middle">%s</text></svg>',
            e($label)
        );

        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    private function extensionFromContentType(?string $contentType): string
    {
        $type = strtolower((string) $contentType);

        if (str_contains($type, 'image/png')) {
            return 'png';
        }

        if (str_contains($type, 'image/webp')) {
            return 'webp';
        }

        if (str_contains($type, 'image/avif')) {
            return 'avif';
        }

        return 'jpg';
    }
}
