<?php

namespace App\Domain\Offers\ValueObjects;

use InvalidArgumentException;

final readonly class OfferId
{
    public function __construct(public int $value)
    {
        if ($this->value < 1) {
            throw new InvalidArgumentException('Offer id must be >= 1.');
        }
    }
}
