<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmLoginAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|int|exists:login_attempts,id',
        ];
    }

    public function filters(): array
    {
        return [
            'id' => 'trim|escape|digit',
        ];
    }
}
