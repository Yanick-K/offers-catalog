<?php

declare(strict_types=1);

namespace App\Application\Products\DTO;

use App\Domain\Products\ValueObjects\ProductState;
use App\Domain\Shared\Contracts\ImageUpload;
use App\Domain\Shared\ValueObjects\Money;

final readonly class ProductData
{
    public function __construct(
        public string $name,
        public string $sku,
        public int $priceInCents,
        public ProductState $state,
        public ?ImageUpload $image,
    ) {}

    /**
     * @param array{name: string, sku: string, price: string|int|float, state: string} $data
     */
    public static function fromArray(array $data, ?ImageUpload $image): self
    {
        return new self(
            name: $data['name'],
            sku: $data['sku'],
            priceInCents: Money::fromInput($data['price'])->cents,
            state: ProductState::from($data['state']),
            image: $image,
        );
    }
}
