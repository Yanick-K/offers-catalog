<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\Entities\Product;
use App\Domain\Products\Repositories\ProductRepository;
use App\Domain\Products\ValueObjects\ProductId;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Models\Product as ProductModel;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepository
{
    public function save(Product $product): Product
    {
        if ($product->id) {
            $model = ProductModel::query()->findOrFail($product->id->value);
            $model->fill($this->toPersistence($product));
            $model->save();
        } else {
            $model = ProductModel::query()->create($this->toPersistence($product));
        }

        return $this->toDomain($model);
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

        return $model ? $this->toDomain($model) : null;
    }

    public function findForOffer(OfferId $offerId, ProductId $productId): ?Product
    {
        $model = ProductModel::query()
            ->where('offer_id', $offerId->value)
            ->whereKey($productId->value)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function paginateForOffer(OfferId $offerId, PageRequest $page): PaginatedResult
    {
        $paginator = ProductModel::query()
            ->where('offer_id', $offerId->value)
            ->latest()
            ->paginate($page->perPage, ['*'], 'page', $page->page);

        return $this->toPaginatedResult($paginator);
    }

    /**
     * @param  LengthAwarePaginator<ProductModel>  $paginator
     */
    private function toPaginatedResult(LengthAwarePaginator $paginator): PaginatedResult
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

    private function toDomain(ProductModel $model): Product
    {
        return new Product(
            id: new ProductId($model->id),
            offerId: new OfferId($model->offer_id),
            name: $model->name,
            sku: $model->sku,
            price: (string) $model->price,
            state: $model->state,
            image: $model->image,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function toPersistence(Product $product): array
    {
        return [
            'offer_id' => $product->offerId->value,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'state' => $product->state->value,
            'image' => $product->image,
        ];
    }
}
