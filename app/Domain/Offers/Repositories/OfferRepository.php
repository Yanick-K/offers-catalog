<?php

declare(strict_types=1);

namespace App\Domain\Offers\Repositories;

use App\Domain\Offers\Entities\Offer;
use App\Domain\Offers\OfferFilters;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Domain\Shared\ValueObjects\PaginatedResult;

interface OfferRepository
{
    public function save(Offer $offer): Offer;

    public function delete(OfferId $id): void;

    public function find(OfferId $id): ?Offer;

    public function findWithProducts(OfferId $id): ?Offer;

    public function paginate(OfferFilters $filters, PageRequest $page): PaginatedResult;

    public function paginatePublished(PageRequest $page): PaginatedResult;
}
