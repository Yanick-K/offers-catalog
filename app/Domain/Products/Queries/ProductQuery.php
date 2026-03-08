<?php

declare(strict_types=1);

namespace App\Domain\Products\Queries;

use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\Entities\Product;
use App\Domain\Products\Repositories\ProductRepository;
use App\Domain\Products\ValueObjects\ProductId;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Domain\Shared\ValueObjects\PaginatedResult;

class ProductQuery
{
    public function __construct(private readonly ProductRepository $products) {}

    public function listForOffer(OfferId $offerId, PageRequest $page): PaginatedResult
    {
        return $this->products->paginateForOffer($offerId, $page);
    }

    public function getForOffer(OfferId $offerId, ProductId $productId): ?Product
    {
        return $this->products->findForOffer($offerId, $productId);
    }
}
