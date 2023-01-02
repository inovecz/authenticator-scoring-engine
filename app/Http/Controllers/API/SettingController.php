<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingSaveRequest;
use anlutro\LaravelSettings\Facades\Setting;

class SettingController extends Controller
{
    public function getAll(): JsonResponse
    {
        return $this->success(Setting::all());
    }

    public function getByKey(Request $request, string $key): JsonResponse
    {
        $value = setting($key);
        return isset($value) ? $this->success(compact('key', 'value')) : $this->error('Setting key .  '.$key.' . doesn\'t exists');
    }

    public function storeSetting(SettingSaveRequest $request): JsonResponse
    {
        $key = $request->input('key');
        setting([$key => $request->input('value')])->save();
        return $this->success([$key => setting($key)]);
    }
}
