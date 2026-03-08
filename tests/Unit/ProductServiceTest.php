<?php

namespace Tests\Unit;

use App\Application\Products\DTO\ProductData;
use App\Application\Products\Services\ProductService;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\ValueObjects\ProductId;
use App\Domain\Products\ValueObjects\ProductState;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_product_and_stores_image(): void
    {
        Storage::fake('public');
        $offer = Offer::factory()->create();
        $service = $this->app->make(ProductService::class);

        $data = ProductData::fromArray(
            [
                'name' => 'Produit Test',
                'sku' => 'SKU-TEST-1',
                'price' => '19.90',
                'state' => ProductState::Published->value,
            ],
            UploadedFile::fake()->image('product.jpg')
        );

        $product = $service->create(new OfferId($offer->id), $data);

        $this->assertDatabaseHas('products', ['id' => $product->id->value, 'sku' => 'SKU-TEST-1']);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_it_updates_product_and_replaces_image(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create([
            'image' => 'products/old.jpg',
            'state' => ProductState::Draft->value,
        ]);

        Storage::disk('public')->put('products/old.jpg', 'old');

        $service = $this->app->make(ProductService::class);

        $data = ProductData::fromArray(
            [
                'name' => 'Produit Maj',
                'sku' => $product->sku,
                'price' => '29.90',
                'state' => ProductState::Published->value,
            ],
            UploadedFile::fake()->image('new.jpg')
        );

        $service->update(new OfferId($product->offer_id), new ProductId($product->id), $data);

        $product->refresh();

        Storage::disk('public')->assertMissing('products/old.jpg');
        Storage::disk('public')->assertExists($product->image);
        $this->assertSame('Produit Maj', $product->name);
    }
}
