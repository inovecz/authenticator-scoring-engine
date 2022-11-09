<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function success(string $message): JsonResponse
    {
        return $this->respond(['message' => $message]);
    }

    public function error(string $error): JsonResponse
    {
        return $this->respond(['error' => $error], 400);
    }

    public function respond(array $json, int $statusCode = 200): JsonResponse
    {
        return response()->json($json, $statusCode);
    }
}
