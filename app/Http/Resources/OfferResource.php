<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Offers\Entities\Offer;
use App\Domain\Products\Entities\Product;
use App\Domain\Products\ValueObjects\ProductState;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Offer
 */
class OfferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $publishedProducts = array_values(array_filter(
            $this->products,
            static fn (Product $product): bool => $product->state === ProductState::Published
        ));

        return [
            'id' => $this->id?->value,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'state' => $this->state->value,
            'image' => $this->image,
            'image_url' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'products' => ProductResource::collection($publishedProducts),
        ];
    }
}
