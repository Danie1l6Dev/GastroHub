<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TableAliasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'alias' => ['required', 'string', 'min:2', 'max:80'],
        ];
    }

    public function messages(): array
    {
        return [
            'alias.required' => 'Escribe tu nombre o alias para continuar.',
            'alias.min' => 'El alias debe tener al menos 2 caracteres.',
        ];
    }
}
