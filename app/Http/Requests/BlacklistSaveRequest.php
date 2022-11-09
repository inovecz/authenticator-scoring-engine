<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\BlacklistTypeEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class BlacklistSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'sometimes|integer|exists:blacklists,id',
            'type' => ['required', new Enum(BlacklistTypeEnum::class)],
            'value' => 'required|string',
            'reason' => 'sometimes|nullable|string',
            'active' => 'sometimes|required|boolean',
        ];
    }

    public function filters(): array
    {
        return [
            'id' => 'digit',
            'type' => 'escape|trim',
            'value' => 'escape|trim',
            'reason' => 'escape|trim',
            'active' => 'escape|trim|cast:boolean',
        ];
    }
}
