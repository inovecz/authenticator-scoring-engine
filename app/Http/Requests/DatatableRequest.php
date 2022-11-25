<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DatatableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_length' => 'sometimes|nullable|int|min:1',
            'page' => 'sometimes|nullable|int|min:1',
            'search' => 'sometimes|nullable|string',
            'filter' => 'sometimes|nullable|string',
            'order_by' => 'sometimes|nullable|string',
            'sort_asc' => 'sometimes|nullable|bool',
        ];
    }

    public function filters(): array
    {
        return [
            'page_length' => 'digit',
            'page' => 'digit',
            'search' => 'escape|trim',
            'filter' => 'escape|trim',
            'order_by' => 'escape|trim',
            'sort_asc' => 'escape|trim|cast:bool',
        ];
    }
}
