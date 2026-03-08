<?php

declare(strict_types=1);

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

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public static function fromString(string $value): self
    {
        if (! ctype_digit($value)) {
            throw new InvalidArgumentException('Product id must be a positive integer.');
        }

        return new self((int) $value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
