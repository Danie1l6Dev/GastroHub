<?php

namespace App\Http\Requests;

use App\Enums\TableAccountMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TableAccountModeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'account_mode' => ['required', Rule::enum(TableAccountMode::class)],
        ];
    }
}
