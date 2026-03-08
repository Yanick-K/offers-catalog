<?php

namespace App\Domain\Shared\ValueObjects;

final readonly class PaginatedResult
{
    /**
     * @param  array<int, mixed>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
        public int $lastPage,
    ) {}
}
