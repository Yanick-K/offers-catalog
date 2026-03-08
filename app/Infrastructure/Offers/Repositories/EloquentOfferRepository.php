<?php

declare(strict_types=1);

namespace App\Infrastructure\Offers\Repositories;

use App\Domain\Offers\Entities\Offer;
use App\Domain\Offers\Query\OfferFilterCriteria;
use App\Domain\Offers\Repositories\OfferRepository;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Domain\Shared\ValueObjects\PaginatedResult;
use App\Infrastructure\Offers\Cache\PublicOfferCache;
use App\Infrastructure\Offers\Repositories\Mappers\OfferMapper;
use App\Models\Offer as OfferModel;
use App\Shared\Query\SortDirection;

class EloquentOfferRepository implements OfferRepository
{
    private const DEFAULT_SORT = 'created_at';

    private const DEFAULT_DIRECTION = 'desc';

    private const SORTABLE = ['name', 'slug', 'state', 'created_at'];

    public function __construct(private readonly OfferMapper $mapper) {}

    public function save(Offer $offer): Offer
    {
        if ($offer->id) {
            $model = OfferModel::query()->findOrFail($offer->id->value);
            $model->fill($this->mapper->toPersistence($offer));
            $model->save();
        } else {
            $model = OfferModel::query()->create($this->mapper->toPersistence($offer));
        }

        return $this->mapper->toDomain($model);
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
        $model = OfferModel::query()->with('products')->find($id->value);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function paginate(OfferFilterCriteria $filters, PageRequest $page): PaginatedResult
    {
        $query = OfferModel::query()->withCount('products')->with('products');

        if ($filters->state()) {
            $query->where('state', $filters->state()->value);
        }

        if ($filters->name()) {
            $query->where('name', 'like', '%' . $filters->name() . '%');
        }

        if ($filters->slug()) {
            $query->where('slug', 'like', '%' . $filters->slug() . '%');
        }

        $sort = $filters->sort() ?? self::DEFAULT_SORT;
        if (! in_array($sort, self::SORTABLE, true)) {
            $sort = self::DEFAULT_SORT;
        }

        $direction = $filters->direction() ?? SortDirection::DESC;
        $directionValue = $direction->value;
        if (! in_array($directionValue, [self::DEFAULT_DIRECTION, SortDirection::ASC->value], true)) {
            $directionValue = self::DEFAULT_DIRECTION;
        }

        $query->orderBy($sort, $directionValue);

        $paginator = $query->paginate($page->perPage, ['*'], 'page', $page->page);

        return $this->mapper->toPaginatedResult($paginator);
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

            return $this->mapper->toPaginatedResult($paginator);
        });
    }
}
