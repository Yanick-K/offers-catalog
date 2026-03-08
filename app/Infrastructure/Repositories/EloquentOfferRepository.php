<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Offers\Entities\Offer;
use App\Domain\Offers\OfferFilters;
use App\Domain\Offers\Repositories\OfferRepository;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\Entities\Product;
use App\Domain\Products\ValueObjects\ProductId;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Infrastructure\Cache\PublicOfferCache;
use App\Models\Offer as OfferModel;
use App\Models\Product as ProductModel;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentOfferRepository implements OfferRepository
{
    private const DEFAULT_SORT = 'created_at';

    private const DEFAULT_DIRECTION = 'desc';

    private const SORTABLE = ['name', 'slug', 'state', 'created_at'];

    public function save(Offer $offer): Offer
    {
        if ($offer->id) {
            $model = OfferModel::query()->findOrFail($offer->id->value);
            $model->fill($this->toPersistence($offer));
            $model->save();
        } else {
            $model = OfferModel::query()->create($this->toPersistence($offer));
        }

        return $this->toDomain($model);
    }

    public function delete(OfferId $id): void
    {
        $model = OfferModel::query()->find($id->value);
        if ($model) {
            $model->delete();
        }
    }

    public function find(OfferId $id): ?Offer
    {
        $model = OfferModel::query()->find($id->value);

        return $model ? $this->toDomain($model) : null;
    }

    public function findWithProducts(OfferId $id): ?Offer
    {
        $model = OfferModel::query()->with('products')->find($id->value);

        return $model ? $this->toDomain($model, true) : null;
    }

    public function paginate(OfferFilters $filters, PageRequest $page): PaginatedResult
    {
        $query = OfferModel::query()->withCount('products');

        if ($filters->state) {
            $query->where('state', $filters->state->value);
        }

        if ($filters->name) {
            $query->where('name', 'like', '%'.$filters->name.'%');
        }

        if ($filters->slug) {
            $query->where('slug', 'like', '%'.$filters->slug.'%');
        }

        $sort = $filters->sort ?? self::DEFAULT_SORT;
        if (! in_array($sort, self::SORTABLE, true)) {
            $sort = self::DEFAULT_SORT;
        }

        $direction = $filters->direction ?? self::DEFAULT_DIRECTION;
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = self::DEFAULT_DIRECTION;
        }

        $query->orderBy($sort, $direction);

        $paginator = $query->paginate($page->perPage, ['*'], 'page', $page->page);

        return $this->toPaginatedResult($paginator);
    }

    public function paginatePublished(PageRequest $page): PaginatedResult
    {
        return PublicOfferCache::remember($page->page, $page->perPage, function () use ($page) {
            $paginator = OfferModel::query()
                ->published()
                ->with([
                    'products' => static function ($query) {
                        $query->published();
                    },
                ])
                ->orderByDesc('id')
                ->paginate($page->perPage, ['*'], 'page', $page->page);

            return $this->toPaginatedResult($paginator, true);
        });
    }

    /**
     * @param  LengthAwarePaginator<OfferModel>  $paginator
     */
    private function toPaginatedResult(LengthAwarePaginator $paginator, bool $withProducts = false): PaginatedResult
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

    private function toDomain(OfferModel $model, bool $withProducts = false): Offer
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

    private function toDomainProduct(ProductModel $model): Product
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
    private function toPersistence(Offer $offer): array
    {
        return [
            'name' => $offer->name,
            'slug' => $offer->slug,
            'description' => $offer->description,
            'state' => $offer->state->value,
            'image' => $offer->image,
        ];
    }
}
