<?php

declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Application\Offers\Queries\OfferSort;
use App\Domain\Offers\ValueObjects\OfferState;
use App\Shared\Query\SortDirection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfferIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('admin');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'state' => ['nullable', Rule::enum(OfferState::class)],
            'name' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', Rule::enum(OfferSort::class)],
            'direction' => ['nullable', Rule::enum(SortDirection::class)],
        ];
    }
}
