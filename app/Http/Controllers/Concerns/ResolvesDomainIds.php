<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Domain\Offers\ValueObjects\OfferId;
use App\Domain\Products\ValueObjects\ProductId;
use InvalidArgumentException;

trait ResolvesDomainIds
{
    private function offerIdFromRoute(string $offerId): OfferId
    {
        try {
            return OfferId::fromString($offerId);
        } catch (InvalidArgumentException $exception) {
            abort(404);
        }
    }

    private function productIdFromRoute(string $productId): ProductId
    {
        try {
            return ProductId::fromString($productId);
        } catch (InvalidArgumentException $exception) {
            abort(404);
        }
    }
}
