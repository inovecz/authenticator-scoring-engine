<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use App\Services\LoginService;
use Illuminate\Http\JsonResponse;
use App\Services\BlacklistService;
use App\Services\ScoreEntityService;
use App\Http\Controllers\Controller;
use App\Services\ScorePasswordService;
use App\Http\Requests\ConfirmLoginAttemptRequest;

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
        $userAgent = $request->input('user_agent');
        $clientIp = $request->input('ip');
        $clientIp = $clientIp === '127.0.0.1' ? '46.135.97.82' : $clientIp;

        $timer = $request->input('timer');
        $mouseDynamics = [
            'mouse_max_speed' => $request->input('mouse_max_speed'),
            'mouse_avg_speed' => $request->input('mouse_avg_speed'),
            'mouse_max_accel' => $request->input('mouse_max_accel'),
            'mouse_avg_accel' => $request->input('mouse_avg_accel'),
            'mouse_movement' => $request->input('mouse_movement'),
            'mouse_clicks' => $request->input('mouse_clicks'),
            'mouse_selections' => $request->input('mouse_selections'),
            'mouse_scrolls' => $request->input('mouse_scrolls'),
        ];

        if (!$entity || !$password) {
            return $this->error('Unsufficient data provided');
        }

        $blacklistService = new BlacklistService();
        [$blacklisted, $blacklistType, $value, $blaclistId] = $blacklistService->isBlacklisted($email, $clientIp, $userAgent);

        $loginService = new LoginService();
        $loginData = $loginService->getLoginAttemptData($entity, $clientIp, $userAgent);

        $loginData = array_merge($loginData, ['timer' => $timer], $mouseDynamics);

        if ($blacklisted) {
            $loginAttempt = LoginAttempt::create($loginData);
            return $this->error([
                'error' => 'Entity is blacklisted',
                'value' => $value,
                'blacklist_type' => $blacklistType,
                'blacklist_id' => $blaclistId,
                'login_attempt_id' => $loginAttempt->getId(),
                'success' => false,
            ]);
        }

        try {
            $maxPasswordScore = $this->scorePasswordService->getMaxScore();
            $scorePassword = $this->scorePasswordService->scorePassword($password);

            $maxEntityScore = $this->scoreEntityService->getMaxScore();
            $scoreEntity = $this->scoreEntityService->scoreEntity($entity, $loginData, $email, $phone);

            // Convert real score range to 0 - 100
            $loginScore = ($scorePassword['score'] + $scoreEntity['score']) / ($maxPasswordScore + $maxEntityScore) * 100;

            $loginData['response'] = [
                'score' => $loginScore,
                'password' => $scorePassword,
                'entity' => $scoreEntity,
            ];
            $loginAttempt = LoginAttempt::create($loginData);

            return response()->json(['score' => $loginScore, 'password' => $scorePassword, 'entity' => $scoreEntity, 'login_attempt_id' => $loginAttempt->getId()]);
        } catch (\Throwable $throwable) {
            return response()->json(['error' => $throwable->getMessage(), 'code' => $throwable->getCode(), 'file' => $throwable->getFile(), 'line' => $throwable->getLine(), 'trace' => $throwable->getTrace()], 400);
        }
    }

    public function confirmLoginAttempt(ConfirmLoginAttemptRequest $request): JsonResponse
    {
        $loginAttempt = LoginAttempt::where('id', $request->input('id'))->first();
        $loginAttempt->successful = true;
        $confirmed = $loginAttempt->save();
        return $confirmed ? $this->success('Login attempt confirmed as successful') : $this->error('Login attempt not confirmed');
    }
}
