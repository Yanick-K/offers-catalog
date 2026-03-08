<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Products\Entities\Product;
use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id?->value,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => Money::fromCents($this->priceInCents)->toDecimalString(),
            'state' => $this->state->value,
            'image' => $this->image,
            'image_url' => $this->image ? Storage::disk('public')->url($this->image) : null,
        ];
    }
}
