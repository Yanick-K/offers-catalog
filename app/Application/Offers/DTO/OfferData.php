<?php

namespace App\Application\Offers\DTO;

use App\Domain\Offers\ValueObjects\OfferState;
use Illuminate\Http\UploadedFile;

final readonly class OfferData
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description,
        public OfferState $state,
        public ?UploadedFile $image,
    ) {}

    /**
     * @param  array{name: string, slug: string, description?: string|null, state: string}  $data
     */
    public static function fromArray(array $data, ?UploadedFile $image): self
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
