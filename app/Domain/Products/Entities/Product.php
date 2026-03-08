<?php

declare(strict_types=1);

namespace App\Domain\Products\Entities;

use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\ValueObjects\ProductId;
use App\Domain\Products\ValueObjects\ProductState;

final readonly class Product
{
    public function __construct(
        public ?ProductId $id,
        public OfferId $offerId,
        public string $name,
        public string $sku,
        public int $priceInCents,
        public ProductState $state,
        public ?string $image,
    ) {}
}
