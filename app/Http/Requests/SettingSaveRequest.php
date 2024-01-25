<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string',
            'value' => 'required',
        ];
    }

    public function filters(): array
    {
        return [
            'key' => 'escape|trim',
            'value' => 'escape|trim',
        ];
    }

    protected function passedValidation(): void
    {
        if (in_array($this->value, ['true', 'false'], true)) {
            $this->merge(['value' => $this->value === 'true']);
        }
    }
}
