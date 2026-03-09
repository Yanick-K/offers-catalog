<?php

declare(strict_types=1);

namespace App\Observers;

use App\Domain\Shared\Contracts\ImageUploader;
use App\Infrastructure\Offers\Cache\PublicOfferCache;
use App\Models\AuditLog;
use App\Models\Product;

class ProductObserver
{
    public function __construct(private readonly ImageUploader $images) {}

    public function created(Product $product): void
    {
        AuditLog::record($product, 'created', $product->getAttributes());
        PublicOfferCache::bumpVersion();
    }

    public function updated(Product $product): void
    {
        if ($product->wasChanged('image')) {
            $this->images->delete($product->getOriginal('image'));
        }

        AuditLog::record($product, 'updated', $product->getChanges());
        PublicOfferCache::bumpVersion();
    }

    public function deleting(Product $product): void
    {
        $this->images->delete($product->image);
    }

    public function deleted(Product $product): void
    {
        AuditLog::record($product, 'deleted', $product->getOriginal());
        PublicOfferCache::bumpVersion();
    }
}
