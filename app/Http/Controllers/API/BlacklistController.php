<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Models\Blacklist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\BlacklistResource;
use App\Http\Requests\BlacklistSaveRequest;
use App\Http\Requests\BlacklistDeleteRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlacklistController extends Controller
{
    public function getAll(): AnonymousResourceCollection
    {
        return BlacklistResource::collection(Blacklist::all());
    }

    public function getByType(Request $request): AnonymousResourceCollection
    {
        $types = explode(',', $request->query('type', ['IP,DOMAIN,EMAIL']));
        $blacklisted = Blacklist::whereIn('type', $types)->get();
        return BlacklistResource::collection($blacklisted);
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
}
