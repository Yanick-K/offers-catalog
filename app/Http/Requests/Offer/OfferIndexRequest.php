<?php

declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Domain\Offers\ValueObjects\OfferState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfferIndexRequest extends FormRequest
{
    private const SORTABLE = ['name', 'slug', 'state', 'created_at'];

    private const DIRECTIONS = ['asc', 'desc'];

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
            'state' => ['nullable', Rule::in(OfferState::values())],
            'name' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', Rule::in(self::SORTABLE)],
            'direction' => ['nullable', Rule::in(self::DIRECTIONS)],
        ];
    }
}
