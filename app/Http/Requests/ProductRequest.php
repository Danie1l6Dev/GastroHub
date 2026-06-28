<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:140'],
            'slug' => [
                'nullable',
                'string',
                'max:160',
                Rule::unique('products', 'slug')->ignore($this->product),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:100'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_available' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $data['is_available'] = $this->boolean('is_available');
        $data['is_featured'] = $this->boolean('is_featured');
        $data['position'] = $data['sort_order'];

        return $data;
    }

    public function messages(): array
    {
        return [
            'price.min' => 'El precio debe ser mayor o igual a $100.',
            'image.image' => 'La imagen del producto debe ser un archivo de imagen valido.',
        ];
    }
}
