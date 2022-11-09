<?php
declare(strict_types=1);
namespace App\Http\Controllers\API;

use App\Models\LoginAttemp;
use Illuminate\Http\Request;
use App\Services\LoginService;
use Illuminate\Http\JsonResponse;
use App\Services\ScoreEntityService;
use App\Http\Controllers\Controller;
use App\Services\ScorePasswordService;

class ScoringEngineController extends Controller
{
    public function __construct(
        protected ScorePasswordService $scorePasswordService,
        protected ScoreEntityService $scoreEntityService
    ) {}

    public function scoreLogin(Request $request): JsonResponse
    {
        $password = $request->input('password');
        $entity = $request->input('hash');
        $userAgent = $request->input('user-agent');
        $clientIp = $request->input('ip');
        $clientIp = $clientIp === '127.0.0.1' ? '46.135.97.82' : $clientIp;

        if (!$entity || !$password) {
            return response()->json(['error' => 'Unsufficient data provided']);
        }

        try {
            $loginService = new LoginService();
            $loginData = $loginService->getLoginAttempData($entity, $clientIp, $userAgent);
            $loginScore = 0;

            // $maxPasswordScore = $this->scorePasswordService->getMaxScore();
            $scorePassword = $this->scorePasswordService->scorePassword($password);
            $loginScore += $scorePassword['score'];

            // $maxEntityScore = $this->scoreEntityService->getMaxScore();
            $scoreEntity = $this->scoreEntityService->scoreEntity($entity, $loginData);
            $loginScore += $scoreEntity['score'];

            $loginAttemp = LoginAttemp::create($loginData);

            return response()->json(['score' => $loginScore, 'password' => $scorePassword, 'entity' => $scoreEntity]);
        } catch (\Throwable $throwable) {
            return response()->json(['error' => $throwable->getMessage(), 'code' => $throwable->getCode(), 'file' => $throwable->getFile(), 'line' => $throwable->getLine()], 400);
        }
    }
}
