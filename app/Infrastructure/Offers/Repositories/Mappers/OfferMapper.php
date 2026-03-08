<?php

declare(strict_types=1);

namespace App\Infrastructure\Offers\Repositories\Mappers;

use App\Domain\Offers\Entities\Offer;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Infrastructure\Products\Repositories\Mappers\ProductMapper;
use App\Models\Offer as OfferModel;
use Illuminate\Pagination\LengthAwarePaginator;

class OfferMapper
{
    public function __construct(private readonly ProductMapper $productMapper) {}

    public function toDomain(OfferModel $model): Offer
    {
        $model->loadMissing('products');

        $offer = new Offer(
            id: new OfferId($model->id),
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            state: $model->state,
            image: $model->image,
        );

        $products = $model->products->map(
            fn ($product) => $this->productMapper->toDomain($product)
        )->all();

        return $offer->withProducts($products);
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
    public function toPaginatedResult(LengthAwarePaginator $paginator): PaginatedResult
    {
        $items = array_map(
            fn (OfferModel $model) => $this->toDomain($model),
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
