<?php

declare(strict_types=1);

namespace App\Infrastructure\Products\Repositories\Mappers;

use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\Entities\Product;
use App\Domain\Products\ValueObjects\ProductId;
use App\Domain\Shared\ValueObjects\Money;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Models\Product as ProductModel;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductMapper
{
    public function toDomain(ProductModel $model): Product
    {
        return new Product(
            id: new ProductId($model->id),
            offerId: new OfferId($model->offer_id),
            name: $model->name,
            sku: $model->sku,
            priceInCents: Money::fromDecimalString((string) $model->price)->cents,
            state: $model->state,
            image: $model->image,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(Product $product): array
    {
        return [
            'offer_id' => $product->offerId->value,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => Money::fromCents($product->priceInCents)->toDecimalString(),
            'state' => $product->state->value,
            'image' => $product->image,
        ];
    }

    /**
     * @param LengthAwarePaginator<ProductModel> $paginator
     */
    public function toPaginatedResult(LengthAwarePaginator $paginator): PaginatedResult
    {
        $items = array_map(
            fn (ProductModel $model) => $this->toDomain($model),
            $paginator->items()
        );

        return new PaginatedResult(
            items: $items,
            total: $paginator->total(),
            page: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            lastPage: $paginator->lastPage(),
        );
    }
}
