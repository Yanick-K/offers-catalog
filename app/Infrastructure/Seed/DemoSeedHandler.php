<?php

declare(strict_types=1);

namespace App\Infrastructure\Seed;

use App\Infrastructure\Files\LocalImageStore;
use App\Infrastructure\Files\RemoteImageStore;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

class DemoSeedHandler
{
    public function __construct(
        private readonly RemoteImageStore $remoteImages,
        private readonly LocalImageStore $localImages,
    ) {}

    public function handle(int $offers, int $products, bool $useRemote = false): void
    {
        $this->ensureAdminUser();
        $images = $useRemote ? $this->remoteImages : $this->localImages;

        for ($i = 0; $i < $offers; $i++) {
            $offerImage = $images->store('seed/offers', 'offer-' . Str::uuid());
            $offer = Offer::factory()->create([
                'image' => $offerImage,
            ]);

            for ($j = 0; $j < $products; $j++) {
                $productImage = $images->store(
                    'seed/products',
                    'product-' . $offer->id . '-' . Str::uuid()
                );

                Product::factory()
                    ->for($offer)
                    ->create([
                        'image' => $productImage,
                    ]);
            }
        }
    }

    private function ensureAdminUser(): void
    {
        if (User::query()->where('email', 'test@example.com')->exists()) {
            return;
        }

        User::factory()->admin()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
