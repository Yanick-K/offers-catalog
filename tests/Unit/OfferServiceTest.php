<?php

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

    public function test_it_creates_offer_and_stores_image(): void
    {
        Storage::fake('public');
        $service = $this->app->make(OfferService::class);

        $data = OfferData::fromArray(
            [
                'name' => 'Offre Test',
                'slug' => 'offre-test',
                'description' => 'Description test',
                'state' => OfferState::Draft->value,
            ],
            UploadedFile::fake()->image('offer.jpg')
        );

        $offer = $service->create($data);

        $this->assertDatabaseHas('offers', ['id' => $offer->id->value, 'name' => 'Offre Test']);
        Storage::disk('public')->assertExists($offer->image);
    }

    public function test_it_updates_offer_and_replaces_image(): void
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
                'name' => 'Offre Maj',
                'slug' => 'offre-maj',
                'description' => 'Nouvelle description',
                'state' => OfferState::Published->value,
            ],
            UploadedFile::fake()->image('new.jpg')
        );

        $service->update(new OfferId($offer->id), $data);

        $offer->refresh();

        Storage::disk('public')->assertMissing('offers/old.jpg');
        Storage::disk('public')->assertExists($offer->image);
        $this->assertSame('offre-maj', $offer->slug);
    }

    public function test_it_deletes_offer_and_associated_images(): void
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
