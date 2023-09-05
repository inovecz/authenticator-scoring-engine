<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use App\Http\Resources\LoginAttemptResource;

class LoginAttemptService
{
    public function formatDatatable(Collection $items, array $datatableOptions): array
    {
        $pageLength = $datatableOptions['page_length'] ?? 10;
        $page = $datatableOptions['page'] ?? 0;
        $total = $datatableOptions['total'];

        return array_merge($datatableOptions, [
            'data' => LoginAttemptResource::collection($items),
            'next_page' => $pageLength * $page < $total ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null,
        ]);
    }
}
