<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Shared\Contracts\ImageUploader;
use App\Infrastructure\Offers\Cache\PublicOfferCache;
use App\Models\AuditLog;
use App\Models\Offer;

class OfferObserver
{
    public function __construct(private readonly ImageUploader $images) {}

    public function created(Offer $offer): void
    {
        AuditLog::record($offer, 'created', $offer->getAttributes());
        PublicOfferCache::bumpVersion();
    }

    public function updated(Offer $offer): void
    {
        if ($offer->wasChanged('image')) {
            $this->images->delete($offer->getOriginal('image'));
        }

        AuditLog::record($offer, 'updated', $offer->getChanges());
        PublicOfferCache::bumpVersion();
    }

    public function deleting(Offer $offer): void
    {
        $this->images->delete($offer->image);

        $productImages = $offer->products()->pluck('image');
        foreach ($productImages as $path) {
            $this->images->delete($path);
        }
    }

    public function deleted(Offer $offer): void
    {
        AuditLog::record($offer, 'deleted', $offer->getOriginal());
        PublicOfferCache::bumpVersion();
    }
}
