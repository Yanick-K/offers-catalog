<?php

declare(strict_types=1);

namespace App\Application\Offers\Services;

use App\Application\Offers\DTO\OfferData;
use App\Domain\Offers\Entities\Offer;
use App\Domain\Offers\Repositories\OfferRepository;
use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Shared\Contracts\ImageUploader;

class OfferCommandService
{
    public function __construct(
        private readonly OfferRepository $offers,
        private readonly ImageUploader $imageUploader,
    ) {}

    public function create(OfferData $data): Offer
    {
        $imagePath = null;
        if ($data->image) {
            $imagePath = $this->imageUploader->upload($data->image, 'offers');
        }

        $offer = new Offer(
            id: null,
            name: $data->name,
            slug: $data->slug,
            description: $data->description,
            state: $data->state,
            image: $imagePath,
        );

        return $this->offers->save($offer);
    }

    public function update(OfferId $id, OfferData $data): ?Offer
    {
        $existing = $this->offers->find($id);
        if (! $existing) {
            return null;
        }

        $imagePath = $existing->image;
        if ($data->image) {
            // Old image cleanup is handled by the OfferObserver.
            $imagePath = $this->imageUploader->upload($data->image, 'offers');
        }

        $offer = new Offer(
            id: $id,
            name: $data->name,
            slug: $data->slug,
            description: $data->description,
            state: $data->state,
            image: $imagePath,
            products: $existing->products,
        );

        return $this->offers->save($offer);
    }

    public function delete(OfferId $id): void
    {
        $this->offers->delete($id);
    }
}
