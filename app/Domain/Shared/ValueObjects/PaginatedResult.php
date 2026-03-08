<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

final readonly class PaginatedResult
{
    /**
     * @param list<mixed> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
        public int $lastPage,
    ) {}
}
