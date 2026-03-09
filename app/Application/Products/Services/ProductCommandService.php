<?php

declare(strict_types=1);

namespace App\Application\Products\Services;

use App\Application\Products\DTO\ProductData;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\Entities\Product;
use App\Domain\Products\Repositories\ProductRepository;
use App\Domain\Products\ValueObjects\ProductId;
use App\Domain\Shared\Contracts\ImageUploader;

class ProductCommandService
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly ImageUploader $imageUploader,
    ) {}

    public function create(OfferId $offerId, ProductData $data): Product
    {
        $imagePath = null;
        if ($data->image) {
            $imagePath = $this->imageUploader->upload($data->image, 'products');
        }

        $product = new Product(
            id: null,
            offerId: $offerId,
            name: $data->name,
            sku: $data->sku,
            priceInCents: $data->priceInCents,
            state: $data->state,
            image: $imagePath,
        );

        return $this->products->save($product);
    }

    public function update(OfferId $offerId, ProductId $productId, ProductData $data): ?Product
    {
        $existing = $this->products->findForOffer($offerId, $productId);
        if (! $existing) {
            return null;
        }

        $imagePath = $existing->image;
        if ($data->image) {
            // Old image cleanup is handled by the ProductObserver.
            $imagePath = $this->imageUploader->upload($data->image, 'products');
        }

        $product = new Product(
            id: $productId,
            offerId: $offerId,
            name: $data->name,
            sku: $data->sku,
            priceInCents: $data->priceInCents,
            state: $data->state,
            image: $imagePath,
        );

        return $this->products->save($product);
    }

    public function delete(OfferId $offerId, ProductId $productId): bool
    {
        $existing = $this->products->findForOffer($offerId, $productId);
        if (! $existing) {
            return false;
        }

        $this->products->delete($productId);

        return true;
    }
}
