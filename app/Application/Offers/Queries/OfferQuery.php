<?php

declare(strict_types=1);

namespace App\Application\Offers\Queries;

use App\Domain\Offers\Entities\Offer;
use App\Domain\Offers\Repositories\OfferRepository;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Domain\Shared\ValueObjects\PaginatedResult;

class OfferQuery
{
    public function __construct(private readonly OfferRepository $offers) {}

    public function list(OfferFilters $filters, PageRequest $page): PaginatedResult
    {
        return $this->offers->paginate($filters, $page);
    }

    public function find(OfferId $id): ?Offer
    {
        return $this->offers->find($id);
    }

    public function listPublished(PageRequest $page): PaginatedResult
    {
        return $this->offers->paginatePublished($page);
    }
}
