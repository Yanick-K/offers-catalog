<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\Products\DTO\ProductData;
use App\Application\Products\Services\ProductCommandService;
use App\Application\Shared\Files\UploadedImage;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\ValueObjects\ProductId;
use App\Domain\Products\ValueObjects\ProductState;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ProductCommandServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesProductAndStoresImage(): void
    {
        Storage::fake('public');
        $offer = Offer::factory()->create();
        $service = $this->app->make(ProductCommandService::class);

        $data = ProductData::fromArray(
            [
                'name' => 'Test Product',
                'sku' => 'SKU-TEST-1',
                'price' => '19.90',
                'state' => ProductState::Published->value,
            ],
            $this->imageUpload('product.jpg')
        );

        $product = $service->create(new OfferId($offer->id), $data);

        $this->assertDatabaseHas('products', ['id' => $product->id->value, 'sku' => 'SKU-TEST-1']);
        Storage::disk('public')->assertExists($product->image);
    }

    public function testItUpdatesProductAndReplacesImage(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create([
            'image' => 'products/old.jpg',
            'state' => ProductState::Draft->value,
        ]);

        Storage::disk('public')->put('products/old.jpg', 'old');

        $service = $this->app->make(ProductCommandService::class);

        $data = ProductData::fromArray(
            [
                'name' => 'Updated Product',
                'sku' => $product->sku,
                'price' => '29.90',
                'state' => ProductState::Published->value,
            ],
            $this->imageUpload('new.jpg')
        );

        $service->update(new OfferId($product->offer_id), new ProductId($product->id), $data);

        $product->refresh();

        Storage::disk('public')->assertMissing('products/old.jpg');
        Storage::disk('public')->assertExists($product->image);
        $this->assertSame('Updated Product', $product->name);
    }

    private function imageUpload(string $name): UploadedImage
    {
        $path = tempnam(sys_get_temp_dir(), 'upload_');
        if ($path === false) {
            throw new RuntimeException('Failed to create a temporary image.');
        }

        file_put_contents($path, 'image');

        $extension = pathinfo($name, PATHINFO_EXTENSION);
        if ($extension === '') {
            $extension = 'jpg';
        }

        return new UploadedImage($path, $extension);
    }
}
