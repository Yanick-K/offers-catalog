<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

final readonly class PageRequest
{
    public function __construct(
        public int $page,
        public int $perPage,
    ) {
        if ($this->page < 1) {
            throw new InvalidArgumentException('Page must be >= 1.');
        }

        if ($this->perPage < 1) {
            throw new InvalidArgumentException('Per page must be >= 1.');
        }
    }
}
