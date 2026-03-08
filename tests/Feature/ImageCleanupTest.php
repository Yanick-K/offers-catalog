<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function testOfferOldImageIsDeletedOnUpdate(): void
    {
        Storage::fake('public');

        $offer = Offer::factory()->create(['image' => 'offers/old.jpg']);
        Storage::disk('public')->put('offers/old.jpg', 'old');

        $offer->image = 'offers/new.jpg';
        $offer->save();

        Storage::disk('public')->assertMissing('offers/old.jpg');
    }

    public function testProductOldImageIsDeletedOnUpdate(): void
    {
        Storage::fake('public');

        $offer = Offer::factory()->create();
        $product = Product::factory()
            ->for($offer)
            ->create(['image' => 'products/old.jpg']);

        Storage::disk('public')->put('products/old.jpg', 'old');

        $product->image = 'products/new.jpg';
        $product->save();

        Storage::disk('public')->assertMissing('products/old.jpg');
    }
}
