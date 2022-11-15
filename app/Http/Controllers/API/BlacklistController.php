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
        $types = explode(',', $request->input('type', ['IP,DOMAIN,EMAIL']));
        $blacklisted = Blacklist::whereIn('type', $types)->get();
        return BlacklistResource::collection($blacklisted);
    }

    public function getDatatable(Request $request, BlacklistTypeEnum $type): JsonResponse
    {
        $pageLength = (int) $request->input('pageLength', 10);
        $page = (int) $request->input('page', 1);
        $search = $request->input('search', '');
        $filter = $request->input('filter', 'all');
        $orderBy = $request->input('orderBy', 'id');
        $sortAsc = (bool) $request->input('sortAsc', false);

        $items = $this->blacklistRepository
            ->searchByType($type, $filter, $search)
            ->orderBy($orderBy, $sortAsc)
            ->paginate($pageLength, page: $page)
            ->get();
        $total = $this->blacklistRepository
            ->searchByType($type, $filter, $search)
            ->count();

        $blacklistService = new BlacklistService();
        $datatable = $blacklistService->formatDatatable($items, compact('pageLength', 'page', 'total', 'search', 'filter', 'orderBy', 'sortAsc'));
        return $this->success($datatable);
    }

    public function updateOrCreate(BlacklistSaveRequest $request): JsonResponse|BlacklistResource
    {
        if (Blacklist::whereType($request->input('type'))->whereValue($request->input('value'))->exists()) {
            return $this->error('blacklist.type_value_combination_exists');
        }
        $blacklist = Blacklist::updateOrCreate($request->all('id'), $request->all('type', 'value', 'reason', 'active'));
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
