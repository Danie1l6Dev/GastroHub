<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TableItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'delta' => ['required', 'integer', 'in:-1,1'],
        ];
    }
}
