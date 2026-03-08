<?php

declare(strict_types=1);

namespace App\Domain\Products\Repositories;

use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\Entities\Product;
use App\Domain\Products\ValueObjects\ProductId;
use App\Domain\Shared\ValueObjects\PageRequest;
use App\Domain\Shared\ValueObjects\PaginatedResult;

interface ProductRepository
{
    public function save(Product $product): Product;

    public function delete(ProductId $id): void;

    public function find(ProductId $id): ?Product;

    public function findForOffer(OfferId $offerId, ProductId $productId): ?Product;

    public function paginateForOffer(OfferId $offerId, PageRequest $page): PaginatedResult;
}
