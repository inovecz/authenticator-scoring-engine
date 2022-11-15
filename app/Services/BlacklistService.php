<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Http\Resources\BlacklistResource;

class BlacklistService
{
    public function formatDatatable(Collection $items, array $datatableOptions): array
    {
        $pageLength = $datatableOptions['pageLength'] ?? 10;
        $page = $datatableOptions['page'] ?? 0;
        $total = $datatableOptions['total'];

        return array_merge($datatableOptions, [
            'data' => BlacklistResource::collection($items),
            'next_page' => $pageLength * $page < $total ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null,
        ]);
    }
}
