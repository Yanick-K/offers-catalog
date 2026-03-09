<?php

declare(strict_types=1);

namespace App\Application\Offers\Queries;

use App\Domain\Offers\Query\OfferFilterCriteria;
use App\Domain\Offers\ValueObjects\OfferState;

final readonly class OfferFilters extends OfferFilterCriteria
{
    /**
     * @param array{state?: string|null, name?: string|null, slug?: string|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            state: isset($data['state']) ? OfferState::tryFrom($data['state']) : null,
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
        );
    }
}
