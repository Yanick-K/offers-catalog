<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Offers\ValueObjects\OfferState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    /** @use HasFactory<\Database\Factories\OfferFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'state',
    ];

    protected $casts = [
        'state' => OfferState::class,
    ];

    /**
     * @param  Builder<Offer> $query
     * @return Builder<Offer>
     */
    public function scopeOfState(Builder $query, OfferState|string $state): Builder
    {
        $value = $state instanceof OfferState ? $state->value : $state;

        return $query->where('state', $value);
    }

    /**
     * @param  Builder<Offer> $query
     * @return Builder<Offer>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('state', OfferState::Published->value);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
