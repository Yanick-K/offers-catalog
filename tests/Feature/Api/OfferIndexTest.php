<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Offers\ValueObjects\OfferState;
use App\Domain\Products\ValueObjects\ProductState;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferIndexTest extends TestCase
{
    use RefreshDatabase;

    public function testItReturnsOnlyPublishedOffersAndProducts(): void
    {
        $publishedOffer = Offer::factory()->create(['state' => OfferState::Published->value]);
        Offer::factory()->create(['state' => OfferState::Draft->value]);

        $publishedProduct = Product::factory()->create([
            'offer_id' => $publishedOffer->id,
            'state' => ProductState::Published->value,
        ]);

        Product::factory()->create([
            'offer_id' => $publishedOffer->id,
            'state' => ProductState::Draft->value,
        ]);

        $response = $this->getJson('/api/v1/offers?per_page=50');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertSame($publishedOffer->id, $data[0]['id']);
        $this->assertCount(1, $data[0]['products']);
        $this->assertSame($publishedProduct->id, $data[0]['products'][0]['id']);
    }
}
