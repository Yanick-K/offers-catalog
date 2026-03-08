<?php

namespace App\Domain\Products\ValueObjects;

use InvalidArgumentException;

final readonly class ProductId
{
    public function __construct(public int $value)
    {
        if ($this->value < 1) {
            throw new InvalidArgumentException('Product id must be >= 1.');
        }
    }
}
