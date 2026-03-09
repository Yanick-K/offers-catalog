<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(public int $cents)
    {
        if ($this->cents < 0) {
            throw new InvalidArgumentException('Money cents must be >= 0.');
        }
    }

    public static function fromInput(int|float|string $value): self
    {
        if (is_int($value)) {
            return new self($value * 100);
        }

        if (is_float($value)) {
            return self::fromDecimalString(number_format($value, 2, '.', ''));
        }

        return self::fromDecimalString($value);
    }

    public static function fromDecimalString(string $value): self
    {
        $normalized = str_replace(',', '.', trim($value));

        if ($normalized === '') {
            throw new InvalidArgumentException('Money amount must not be empty.');
        }

        if (! preg_match('/^\\d+(\\.\\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException('Money amount must have at most 2 decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '0');
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

        $cents = ((int) $whole) * 100 + (int) $fraction;

        return new self($cents);
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public function toDecimalString(): string
    {
        return number_format($this->cents / 100, 2, '.', '');
    }

    public function toFloat(): float
    {
        return $this->cents / 100;
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }
}
