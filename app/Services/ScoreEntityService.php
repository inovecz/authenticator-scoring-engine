<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Location;
use App\Models\IpAddress;
use App\Models\LeakedEmail;
use App\Models\LeakedPhone;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\MlTimerSuccess;
use App\Models\MlMouseSuccess;
use App\Models\DisposableEmail;
use Illuminate\Support\Collection;

class ScoreEntityService extends ScoreService
{
    public function scoreEntity(string $entity, array $currentLoginData, string $email, string $phone = null): array
    {
        $entityScore = 0;

        $leakedEmailScore = $this->scoreLeakedEmail($email);
        $entityScore += $leakedEmailScore;

        $disposableEmailScore = $this->scoreDisposableEmail($email);
        $entityScore += $disposableEmailScore;

        $leakedPhoneScore = !$phone ? 0 : $this->scoreLeakedPhone($phone);
        $entityScore += $leakedPhoneScore;

        $mlScore = $this->scoreEntityBehavior($entity, $currentLoginData);
        $entityScore += $mlScore;

        $blacklistScore = $this->scoreBlacklist($email, $currentLoginData['ip'], $currentLoginData['user_agent']);
        $entityScore += $blacklistScore;

        return [
            'ml_score' => $mlScore,
            'leaks' => [
                'email' => $leakedEmailScore,
                'phone' => $leakedPhoneScore,
            ],
            'disposable_email' => $disposableEmailScore,
            'blacklist' => $blacklistScore,
            'score' => $entityScore,
        ];
    }

    /**
     * @maxMethodScore 100
     * @settings scoring.entity.behavior
     * @return int scoreGeoData (Same as usual = 0, Totally different = 20)
     */
    private function scoreEntityBehavior(string $entity, array $currentLoginData): int
    {
        $maxEntityBehaviorScore = $this->getMethodMaxScore(__FUNCTION__);
        if (!setting('scoring.entity.behavior')) {
            return 0;
        }

        /** GlobalScore */
        $ipGlobal = IpAddress::where('ip', $currentLoginData['ip'])->first()->getMlSuccessRate();
        $locationGlobal = Location::where([
            'country' => $currentLoginData['country'],
            'region' => $currentLoginData['region'],
            'city' => $currentLoginData['city'],
        ])->first()->getMlSuccessRate();
        $mouseGlobal = MlMouseSuccess::getMlSuccessRateByValue($currentLoginData['mouse_avg_accel']);
        $timerGlobal = MlTimerSuccess::getMlSuccessRateByValue($currentLoginData['timer']);
        $globalScore = weighted_average([$ipGlobal, $locationGlobal, $mouseGlobal, $timerGlobal], [25, 25, 25, 25]);

        $loginAttempts = LoginAttempt::with('location')->where('entity', $entity)->get();
        /** @var LoginAttempt $attempt */
        foreach ($loginAttempts as $attempt) {
            $attempt->locationComposite = $attempt->location->getCountry().$attempt->location->getRegion().$attempt->location->getCity();
            $attempt->userAgentComposite = $attempt->getDevice().$attempt->getOS().$attempt->getBrowser();
        }

        /** IP */
        $sameIpAttempts = $loginAttempts->filter(fn(LoginAttempt $attempt) => $attempt->getIP() === $currentLoginData['ip']);
        $ipEntity = $this->calculateByML($sameIpAttempts);
        /** Location */
        $sameLocationAttempts = $loginAttempts->filter(fn(LoginAttempt $attempt) => $attempt->locationComposite === $currentLoginData['country'].$currentLoginData['region'].$currentLoginData['city']);
        $locationEntity = $this->calculateByML($sameLocationAttempts);
        /** Mouse */
        $avgAccel = $currentLoginData['mouse_avg_accel'];
        $rangeMin = floor_to($avgAccel, 100);
        $rangeMax = ceil_to($avgAccel, 100);
        $sameMouseAttempts = $loginAttempts->filter(fn(LoginAttempt $attempt) => $attempt->getMouseAvgAccel() >= $rangeMin && $attempt->getMouseAvgAccel() < $rangeMax);
        $mouseEntity = $this->calculateByML($sameMouseAttempts);
        /** Timer */
        $timer = $currentLoginData['timer'];
        $rangeMin = floor_to($timer, 2);
        $rangeMax = ceil_to($timer, 2);
        $sameMouseAttempts = $loginAttempts->filter(fn(LoginAttempt $attempt) => $attempt->getTimer() >= $rangeMin && $attempt->getTimer() < $rangeMax);
        $timerEntity = $this->calculateByML($sameMouseAttempts);
        /** User agent */
        $sameUserAgentAttempts = $loginAttempts->filter(fn(LoginAttempt $attempt) => $attempt->userAgentComposite === $currentLoginData['device'].$currentLoginData['os'].$currentLoginData['browser']);
        $userAgentLocal = $this->calculateByML($sameUserAgentAttempts);

        $entityScore = weighted_average([$ipEntity, $locationEntity, $mouseEntity, $timerEntity, $userAgentLocal], [18.75, 18.75, 18.75, 18.75, 25]);

        $totalScore = weighted_average([$globalScore, $entityScore], [40, 60]);

        $maxScore = $this->getMethodMaxScore(__FUNCTION__);
        return (int) ((1 - $totalScore) * $maxScore);
    }

    private function calculateByML(Collection $loginAttempts): float
    {
        if ($loginAttempts->isEmpty()) {
            return 0.5;
        }
        $machineLearningService = new MachineLearningService();
        $inputs = $loginAttempts->map(fn(LoginAttempt $attempt) => [1])->toArray();
        $outputs = $loginAttempts->map(fn(LoginAttempt $attempt) => $attempt->isSuccessful() ? 'successful' : 'unsuccessful')->toArray();
        $machineLearningService->setDataset($inputs, $outputs);
        $predicted = $machineLearningService->predict();
        return $predicted['successful'];
    }

    /**
     * @maxMethodScore 20
     * @settings scoring.entity.leaks.email
     * @return int scoreLeakedEmail (Not leaked = 0, Leaked = 20)
     */
    private function scoreLeakedEmail(string $email): int
    {
        if (!setting('scoring.entity.leaks.email')) {
            return 0;
        }

        $maxLeakedEmailScore = $this->getMethodMaxScore(__FUNCTION__);
        return LeakedEmail::where('email', $email)->exists() ? $maxLeakedEmailScore : 0;
    }

    /**
     * @maxMethodScore 20
     * @settings scoring.entity.leaks.phone
     * @return int scoreLeakedPhone (Not leaked = 0, Leaked = 20)
     */
    private function scoreLeakedPhone(string $phone): int
    {
        if (!setting('scoring.entity.leaks.phone')) {
            return 0;
        }

        $maxLeakedPhoneScore = $this->getMethodMaxScore(__FUNCTION__);
        return LeakedPhone::where('phone', $phone)->exists() ? $maxLeakedPhoneScore : 0;
    }

    /**
     * @maxMethodScore 20
     * @settings scoring.entity.disposable_email
     * @return int scoreDisposableEmail (Not disposable = 0, Disposable = 20)
     */
    private function scoreDisposableEmail(string $email): int
    {
        if (!setting('scoring.entity.disposable_email')) {
            return 0;
        }

        $maxLeakedPhoneScore = $this->getMethodMaxScore(__FUNCTION__);
        $domain = get_email_domain($email);
        return DisposableEmail::where('domain', $domain)->exists() ? $maxLeakedPhoneScore : 0;
    }

    /**
     * @maxMethodScore 20
     * @settings scoring.entity.blacklist
     * @return int scoreBlacklist (Not blacklisted = 0, Blacklisted = 20)
     */
    private function scoreBlacklist(string $email, string $ip, string $userAgent): int
    {
        if (!setting('scoring.entity.blacklist')) {
            return 0;
        }

        $maxBlacklistScore = $this->getMethodMaxScore(__FUNCTION__);
        $blacklistService = new BlacklistService();
        return $blacklistService->isBlacklisted($email, $ip, $userAgent, true)[0] ? $maxBlacklistScore : 0;
    }

    /**
     * @maxMethodScore 20
     * @settings scoring.entity.operating_system
     * @return int scoreOperatingSystem (Not blacklisted = 0, Blacklisted = 20)
     */
    private function scoreOperatingSystem(string $userAgent): int
    {
        if (!setting('scoring.entity.operating_system')) {
            return 0;
        }

        $maxBlacklistScore = $this->getMethodMaxScore(__FUNCTION__);
        return Str::of($userAgent)->contains('Kali Linux') ? $maxBlacklistScore : 0;
    }
}
