<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TableReadyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_ready' => ['required', 'boolean'],
        ];
    }
}
