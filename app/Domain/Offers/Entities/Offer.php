<?php

namespace App\Domain\Offers\Entities;

use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Offers\ValueObjects\OfferState;
use App\Domain\Products\Entities\Product;

final readonly class Offer
{
    /**
     * @param  array<int, Product>  $products
     */
    public function __construct(
        public ?OfferId $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public OfferState $state,
        public ?string $image,
        public array $products = [],
    ) {}

    /**
     * @param  array<int, Product>  $products
     */
    public function withProducts(array $products): self
    {
        return new self(
            $this->id,
            $this->name,
            $this->slug,
            $this->description,
            $this->state,
            $this->image,
            $products
        );
    }
}
