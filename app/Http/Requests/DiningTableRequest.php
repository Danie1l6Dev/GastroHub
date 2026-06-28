<?php

namespace App\Http\Requests;

use App\Enums\TableStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiningTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('dining_tables', 'code')->ignore($this->table),
            ],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:30'],
            'is_active' => ['sometimes', 'boolean'],
            'current_status' => ['required', Rule::enum(TableStatus::class)],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $data['is_active'] = $this->boolean('is_active');

        return $data;
    }
}
