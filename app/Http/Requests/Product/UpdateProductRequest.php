<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Domain\Products\ValueObjects\ProductState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('productId');

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($productId)],
            'image' => ['nullable', 'image', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'state' => ['required', Rule::in(ProductState::values())],
        ];
    }
}
