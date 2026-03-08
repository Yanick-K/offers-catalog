<?php

declare(strict_types=1);

namespace App\Domain\Offers\Query;

use App\Domain\Offers\ValueObjects\OfferState;
use App\Shared\Query\SortDirection;

readonly class OfferFilterCriteria
{
    public function __construct(
        public ?OfferState $state,
        public ?string $name,
        public ?string $slug,
        public ?string $sort,
        public ?SortDirection $direction,
    ) {}

    public function state(): ?OfferState
    {
        return $this->state;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function slug(): ?string
    {
        return $this->slug;
    }

    public function sort(): ?string
    {
        return $this->sort;
    }

    public function direction(): ?SortDirection
    {
        return $this->direction;
    }
}
