<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Shared\ValueObjects\Money;
use InvalidArgumentException;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    public function testFromInputAcceptsIntAsUnits(): void
    {
        $money = Money::fromInput(12);

        $this->assertSame(1200, $money->cents);
    }

    public function testFromInputAcceptsFloat(): void
    {
        $money = Money::fromInput(12.99);

        $this->assertSame(1299, $money->cents);
    }

    public function testFromDecimalStringAcceptsComma(): void
    {
        $money = Money::fromDecimalString('12,50');

        $this->assertSame(1250, $money->cents);
    }

    public function testToDecimalStringFormatsAmount(): void
    {
        $money = Money::fromCents(1299);

        $this->assertSame('12.99', $money->toDecimalString());
    }

    public function testToFloatReturnsAmount(): void
    {
        $money = Money::fromCents(150);

        $this->assertSame(1.5, $money->toFloat());
    }

    public function testEqualsComparesCents(): void
    {
        $first = Money::fromCents(1000);
        $second = Money::fromInput('10.00');

        $this->assertTrue($first->equals($second));
    }

    public function testRejectsNegativeCents(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Money(-1);
    }

    public function testRejectsInvalidDecimalString(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::fromDecimalString('12.999');
    }
}
