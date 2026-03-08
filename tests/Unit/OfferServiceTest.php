<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\Offers\DTO\OfferData;
use App\Application\Offers\Services\OfferService;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Offers\ValueObjects\OfferState;
use App\Domain\Products\ValueObjects\ProductState;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfferServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesOfferAndStoresImage(): void
    {
        Storage::fake('public');
        $service = $this->app->make(OfferService::class);

        $data = OfferData::fromArray(
            [
                'name' => 'Test Offer',
                'slug' => 'test-offer',
                'description' => 'Test description',
                'state' => OfferState::Draft->value,
            ],
            UploadedFile::fake()->image('offer.jpg')
        );

        $offer = $service->create($data);

        $this->assertDatabaseHas('offers', ['id' => $offer->id->value, 'name' => 'Test Offer']);
        Storage::disk('public')->assertExists($offer->image);
    }

    public function testItUpdatesOfferAndReplacesImage(): void
    {
        Storage::fake('public');

        $offer = Offer::factory()->create([
            'state' => OfferState::Draft->value,
            'image' => 'offers/old.jpg',
        ]);

        Storage::disk('public')->put('offers/old.jpg', 'old');

        $service = $this->app->make(OfferService::class);

        $data = OfferData::fromArray(
            [
                'name' => 'Updated Offer',
                'slug' => 'updated-offer',
                'description' => 'Updated description',
                'state' => OfferState::Published->value,
            ],
            UploadedFile::fake()->image('new.jpg')
        );

        $service->update(new OfferId($offer->id), $data);

        $offer->refresh();

        Storage::disk('public')->assertMissing('offers/old.jpg');
        Storage::disk('public')->assertExists($offer->image);
        $this->assertSame('updated-offer', $offer->slug);
    }

    public function testItDeletesOfferAndAssociatedImages(): void
    {
        Storage::fake('public');

        $offer = Offer::factory()->create([
            'state' => OfferState::Draft->value,
            'image' => 'offers/offer.jpg',
        ]);

        $product = Product::factory()->create([
            'offer_id' => $offer->id,
            'state' => ProductState::Published->value,
            'image' => 'products/product.jpg',
        ]);

        Storage::disk('public')->put('offers/offer.jpg', 'offer');
        Storage::disk('public')->put('products/product.jpg', 'product');

        $service = $this->app->make(OfferService::class);
        $service->delete(new OfferId($offer->id));

        $this->assertDatabaseMissing('offers', ['id' => $offer->id]);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing('offers/offer.jpg');
        Storage::disk('public')->assertMissing('products/product.jpg');
    }
}
