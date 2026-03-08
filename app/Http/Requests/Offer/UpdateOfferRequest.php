<?php

declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Domain\Offers\ValueObjects\OfferState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfferRequest extends FormRequest
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
        $offerId = $this->route('offerId');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('offers', 'slug')->ignore($offerId)],
            'image' => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string', 'max:255'],
            'state' => ['required', Rule::in(OfferState::values())],
        ];
    }
}
