<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Shared\ValueObjects\Money;
use InvalidArgumentException;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    public function test_from_input_accepts_int_as_units(): void
    {
        $money = Money::fromInput(12);

        $this->assertSame(1200, $money->cents);
    }

    public function test_from_input_accepts_float(): void
    {
        $money = Money::fromInput(12.99);

        $this->assertSame(1299, $money->cents);
    }

    public function test_from_decimal_string_accepts_comma(): void
    {
        $money = Money::fromDecimalString('12,50');

        $this->assertSame(1250, $money->cents);
    }

    public function test_to_decimal_string_formats_amount(): void
    {
        $money = Money::fromCents(1299);

        $this->assertSame('12.99', $money->toDecimalString());
    }

    public function test_to_float_returns_amount(): void
    {
        $money = Money::fromCents(150);

        $this->assertSame(1.5, $money->toFloat());
    }

    public function test_equals_compares_cents(): void
    {
        $first = Money::fromCents(1000);
        $second = Money::fromInput('10.00');

        $this->assertTrue($first->equals($second));
    }

    public function test_rejects_negative_cents(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Money(-1);
    }

    public function test_rejects_invalid_decimal_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromDecimalString('12.999');
    }
}
