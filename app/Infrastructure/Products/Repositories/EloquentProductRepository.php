<?php

declare(strict_types=1);

namespace App\Infrastructure\Products\Repositories;

use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\Entities\Product;
use App\Domain\Products\Repositories\ProductRepository;
use App\Domain\Products\ValueObjects\ProductId;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Infrastructure\Products\Repositories\Mappers\ProductMapper;
use App\Models\Product as ProductModel;

class EloquentProductRepository implements ProductRepository
{
    public function __construct(private readonly ProductMapper $mapper) {}

    public function save(Product $product): Product
    {
        if ($product->id) {
            $model = ProductModel::query()->findOrFail($product->id->value);
            $model->fill($this->mapper->toPersistence($product));
            $model->save();
        } else {
            $model = ProductModel::query()->create($this->mapper->toPersistence($product));
        }

        return $this->mapper->toDomain($model);
    }

    public function delete(ProductId $id): void
    {
        $model = ProductModel::query()->find($id->value);
        if ($model) {
            $model->delete();
        }
    }

    public function find(ProductId $id): ?Product
    {
        $model = ProductModel::query()->find($id->value);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function findForOffer(OfferId $offerId, ProductId $productId): ?Product
    {
        $model = ProductModel::query()
            ->where('offer_id', $offerId->value)
            ->whereKey($productId->value)
            ->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function paginateForOffer(OfferId $offerId, PageRequest $page): PaginatedResult
    {
        $paginator = ProductModel::query()
            ->where('offer_id', $offerId->value)
            ->latest()
            ->paginate($page->perPage, ['*'], 'page', $page->page);

        return $this->mapper->toPaginatedResult($paginator);
    }
}
