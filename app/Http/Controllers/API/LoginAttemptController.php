<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\LoginAttemptService;
use App\Http\Requests\DatatableRequest;
use App\Repositories\LoginAttemptRepository;

class LoginAttemptController extends Controller
{
    public function __construct(protected LoginAttemptRepository $loginAttemptRepository) { }

    public function getDatatable(DatatableRequest $request): JsonResponse
    {
        $pageLength = (int) $request->input('page_length', 10);
        $page = (int) $request->input('page', 1);
        $search = $request->input('search', '');
        $filter = $request->input('filter', 'all');
        $orderBy = $request->input('order_by', 'id');
        $sortAsc = (bool) $request->input('sort_asc', false);

        $items = $this->loginAttemptRepository
            ->search($filter, $search)
            ->orderBy($orderBy, $sortAsc)
            ->paginate($pageLength, page: $page)
            ->get();
        $total = $this->loginAttemptRepository
            ->search($filter, $search)
            ->count();

        $loginAttemptService = new LoginAttemptService();
        $datatable = $loginAttemptService->formatDatatable($items, [
            'page_length' => $pageLength,
            'page' => $page,
            'total' => $total,
            'search' => $search,
            'filter' => $filter,
            'order_by' => $orderBy,
            'sort_asc' => $sortAsc,
        ]);
        return $this->success($datatable);
    }
}
