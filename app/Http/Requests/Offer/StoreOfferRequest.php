<?php

declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Domain\Offers\ValueObjects\OfferState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOfferRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('offers', 'slug')],
            'image' => ['required', 'image', 'max:2048'],
            'description' => ['nullable', 'string', 'max:255'],
            'state' => ['required', Rule::enum(OfferState::class)],
        ];
    }
}
