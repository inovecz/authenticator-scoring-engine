<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Jenssegers\Agent\Agent;
use App\Models\LoginAttemp;
use Illuminate\Http\Request;
use App\Services\LoginService;
use Illuminate\Http\JsonResponse;
use App\Services\ScoreEntityService;
use App\Services\ScorePasswordService;
use Adrianorosa\GeoLocation\GeoLocation;

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

            $scorePassword = $this->scorePassword($password);
            $loginScore += $scorePassword['score'];

            $scoreEntity = $this->scoreEntity($entity, $loginData);
            $loginScore += $scoreEntity['score'];

            $loginAttemp = LoginAttemp::create($loginData);

            return response()->json(['score' => $loginScore, 'password' => $scorePassword, 'entity' => $scoreEntity]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'code' => $e->getCode(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        }
    }

    private function scorePassword(string $password): array
    {
        $passwordScore = 0;

        $uncompromisedScore = $this->scorePasswordService->scoreUncompromised($password);
        $passwordScore += $uncompromisedScore;

        $lengthScore = $this->scorePasswordService->scoreLength($password);
        $passwordScore += $lengthScore;

        $complexityScore = $this->scorePasswordService->scoreComplexity($password);
        $passwordScore += $complexityScore;

        return [
            'uncompromised' => $uncompromisedScore,
            'length' =>  $lengthScore,
            'complexity' => $complexityScore,
            'score' => $passwordScore,
        ];
    }

    private function scoreEntity(string $entity, array $currentLoginData): array
    {
        $entityScore = 0;

        $historicalScore = $this->scoreEntityService->scoreHistorical($entity, $currentLoginData);
        $entityScore += $historicalScore;

        return [
            'historical' => $historicalScore,
            'score' => $entityScore,
        ];
    }
}
