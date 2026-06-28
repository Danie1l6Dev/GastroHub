<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'slug' => [
                'nullable',
                'string',
                'max:140',
                Rule::unique('categories', 'slug')->ignore($this->category),
            ],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $data['is_active'] = $this->boolean('is_active');
        $data['position'] = $data['sort_order'];

        return $data;
    }
}
