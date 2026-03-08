<?php

namespace App\Observers;

use App\Infrastructure\Cache\PublicOfferCache;
use App\Models\AuditLog;
use App\Models\Product;
use App\Shared\Files\ImageUploader;

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
