<?php

namespace App\Application\Products\DTO;

use App\Domain\Products\ValueObjects\ProductState;
use Illuminate\Http\UploadedFile;

final readonly class ProductData
{
    public function __construct(
        public string $name,
        public string $sku,
        public string $price,
        public ProductState $state,
        public ?UploadedFile $image,
    ) {}

    /**
     * @param  array{name: string, sku: string, price: string|int|float, state: string}  $data
     */
    public static function fromArray(array $data, ?UploadedFile $image): self
    {
        return new self(
            name: $data['name'],
            sku: $data['sku'],
            price: (string) $data['price'],
            state: ProductState::from($data['state']),
            image: $image,
        );
    }
}
