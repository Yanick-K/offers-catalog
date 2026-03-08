<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories\Mappers;

use App\Domain\Offers\Entities\Offer;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\Entities\Product;
use App\Domain\Products\ValueObjects\ProductId;
use App\Domain\Shared\ValueObjects\Money;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Models\Offer as OfferModel;
use App\Models\Product as ProductModel;
use Illuminate\Pagination\LengthAwarePaginator;

class OfferMapper
{
    public function toDomain(OfferModel $model, bool $withProducts = false): Offer
    {
        $offer = new Offer(
            id: new OfferId($model->id),
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            state: $model->state,
            image: $model->image,
        );

        if (! $withProducts) {
            return $offer;
        }

        $products = $model->products->map(
            fn (ProductModel $product) => $this->toDomainProduct($product)
        )->all();

        return $offer->withProducts($products);
    }

    public function toDomainProduct(ProductModel $model): Product
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
    public function toPersistence(Offer $offer): array
    {
        return [
            'name' => $offer->name,
            'slug' => $offer->slug,
            'description' => $offer->description,
            'state' => $offer->state->value,
            'image' => $offer->image,
        ];
    }

    /**
     * @param LengthAwarePaginator<OfferModel> $paginator
     */
    public function toPaginatedResult(LengthAwarePaginator $paginator, bool $withProducts = false): PaginatedResult
    {
        $items = array_map(
            fn (OfferModel $model) => $this->toDomain($model, $withProducts),
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
