<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Models\LoginAttemp;
use Illuminate\Http\Request;
use App\Services\LoginService;
use Illuminate\Http\JsonResponse;
use App\Services\BlacklistService;
use App\Services\ScoreEntityService;
use App\Http\Controllers\Controller;
use App\Services\ScorePasswordService;

class ScoringEngineController extends Controller
{
    public function __construct(
        protected ScorePasswordService $scorePasswordService,
        protected ScoreEntityService $scoreEntityService
    ) {
    }

    public function scoreLogin(Request $request): JsonResponse
    {
        $password = $request->input('password');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $entity = $request->input('hash');
        $userAgent = $request->input('user-agent');
        $clientIp = $request->input('ip');
        $clientIp = $clientIp === '127.0.0.1' ? '46.135.97.82' : $clientIp;

        if (!$entity || !$password) {
            return $this->error('Unsufficient data provided');
        }

        $blacklistService = new BlacklistService();
        [$blacklisted, $blacklistType, $value, $blaclistId] = $blacklistService->isBlacklisted($email, $clientIp);
        if ($blacklisted) {
            return $this->error([
                'error' => 'Entity is blacklisted',
                'value' => $value,
                'blacklist_type' => $blacklistType,
                'blacklist_id' => $blaclistId,
            ]);
        }

        try {
            $loginService = new LoginService();
            $loginData = $loginService->getLoginAttempData($entity, $clientIp, $userAgent);

            $maxPasswordScore = $this->scorePasswordService->getMaxScore();
            $scorePassword = $this->scorePasswordService->scorePassword($password);

            $maxEntityScore = $this->scoreEntityService->getMaxScore();
            $scoreEntity = $this->scoreEntityService->scoreEntity($entity, $loginData, $email, $phone);

            // Convert real score range to 0 - 100
            $loginScore = ($scorePassword['score'] + $scoreEntity['score']) / ($maxPasswordScore + $maxEntityScore) * 100;

            LoginAttemp::create($loginData);

            return response()->json(['score' => $loginScore, 'password' => $scorePassword, 'entity' => $scoreEntity]);
        } catch (\Throwable $throwable) {
            return response()->json(['error' => $throwable->getMessage(), 'code' => $throwable->getCode(), 'file' => $throwable->getFile(), 'line' => $throwable->getLine()], 400);
        }
    }
}
