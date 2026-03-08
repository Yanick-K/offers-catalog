<?php

declare(strict_types=1);

namespace App\Application\Offers\Queries;

use App\Domain\Offers\Query\OfferFilterCriteria;
use App\Domain\Offers\ValueObjects\OfferState;
use App\Shared\Query\SortDirection;

final readonly class OfferFilters extends OfferFilterCriteria
{
    /**
     * @param array{state?: string|null, name?: string|null, slug?: string|null, sort?: string|null, direction?: string|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            state: isset($data['state']) ? OfferState::tryFrom($data['state']) : null,
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            sort: isset($data['sort']) ? OfferSort::tryFrom($data['sort'])?->value : null,
            direction: isset($data['direction']) ? SortDirection::tryFrom($data['direction']) : null,
        );
    }
}
