<?php

declare(strict_types=1);

namespace App\Application\Offers\DTO;

use App\Domain\Offers\ValueObjects\OfferState;
use App\Domain\Shared\Contracts\ImageUpload;

final readonly class OfferData
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description,
        public OfferState $state,
        public ?ImageUpload $image,
    ) {}

    /**
     * @param array{name: string, slug: string, description?: string|null, state: string} $data
     */
    public static function fromArray(array $data, ?ImageUpload $image): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            state: OfferState::from($data['state']),
            image: $image,
        );
    }
}
