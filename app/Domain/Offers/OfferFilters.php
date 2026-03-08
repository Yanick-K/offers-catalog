<?php

declare(strict_types=1);

namespace App\Domain\Offers;

use App\Domain\Offers\ValueObjects\OfferState;

final readonly class OfferFilters
{
    public function __construct(
        public ?OfferState $state,
        public ?string $name,
        public ?string $slug,
        public ?string $sort,
        public ?string $direction,
    ) {}

    /**
     * @param array{state?: string|null, name?: string|null, slug?: string|null, sort?: string|null, direction?: string|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            state: isset($data['state']) ? OfferState::tryFrom($data['state']) : null,
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            sort: $data['sort'] ?? null,
            direction: $data['direction'] ?? null,
        );
    }
}
