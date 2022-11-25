<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Models\Blacklist;
use Illuminate\Http\Request;
use App\Enums\BlacklistTypeEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\BlacklistService;
use App\Http\Controllers\Controller;
use App\Http\Requests\DatatableRequest;
use App\Http\Resources\BlacklistResource;
use App\Repositories\BlacklistRepository;
use App\Http\Requests\BlacklistSaveRequest;
use App\Http\Requests\BlacklistDeleteRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlacklistController extends Controller
{
    public function __construct(protected BlacklistRepository $blacklistRepository)
    {
    }

    public function getAll(): AnonymousResourceCollection
    {
        return BlacklistResource::collection(Blacklist::all());
    }

    public function getCount(): JsonResponse
    {
        $result = DB::table('blacklists')->select('type', DB::raw('count(*) as total'))->groupBy('type')->get();
        return response()->json($result->flatMap(fn($typeResult) => [$typeResult->type => $typeResult->total])->toArray());
    }

    public function getByType(Request $request): AnonymousResourceCollection
    {
        $types = $request->input('type', ['IP,DOMAIN,EMAIL']);
        $types = is_array($types) ? $types : explode(',', $types);
        $blacklisted = Blacklist::whereIn('type', $types)->get();
        return BlacklistResource::collection($blacklisted);
    }

    public function getDatatable(DatatableRequest $request, BlacklistTypeEnum $type): JsonResponse
    {
        $pageLength = (int) $request->input('page_length', 10);
        $page = (int) $request->input('page', 1);
        $search = $request->input('search', '');
        $filter = $request->input('filter', 'all');
        $orderBy = $request->input('order_by', 'id');
        $sortAsc = (bool) $request->input('sort_asc', false);

        $items = $this->blacklistRepository
            ->searchByType($type, $filter, $search)
            ->orderBy($orderBy, $sortAsc)
            ->paginate($pageLength, page: $page)
            ->get();
        $total = $this->blacklistRepository
            ->searchByType($type, $filter, $search)
            ->count();

        $blacklistService = new BlacklistService();
        $datatable = $blacklistService->formatDatatable($items, [
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

    public function updateOrCreate(BlacklistSaveRequest $request): JsonResponse|BlacklistResource
    {
        if ($request->has('id')) {
            $blacklist = Blacklist::where('id', $request->input('id'))->first();
            $blacklist->update($request->only('type', 'value', 'reason', 'active'));
        } else {
            $type = $request->input('type');
            $value = json_encode($request->input('value'), JSON_THROW_ON_ERROR);
            if (Blacklist::where(compact('type', 'value'))->exists()) {
                return $this->error('blacklist.type_value_combination_exists');
            }
            $blacklist = Blacklist::create($request->only('type', 'value', 'reason', 'active'));
        }
        return new BlacklistResource($blacklist);
    }

    public function destroy(BlacklistDeleteRequest $request): JsonResponse
    {
        $deleted = Blacklist::where('id', $request->input('id'))->delete();
        return $deleted ? $this->success('blacklist.deleted') : $this->error('blacklist.delete_failed');
    }

    public function toggleActive(Request $request, Blacklist $blacklist): BlacklistResource
    {
        $blacklist->update(['active' => !$blacklist->isActive()]);
        return new BlacklistResource($blacklist);
    }
}
