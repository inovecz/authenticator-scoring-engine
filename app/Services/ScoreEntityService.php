<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LeakedEmail;
use App\Models\LeakedPhone;
use App\Models\DisposableEmail;
use Illuminate\Support\Facades\DB;

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

        $geoDataScore = $this->scoreGeoData($entity, $currentLoginData);
        $entityScore += $geoDataScore;

        $deviceScore = $this->scoreDevice($entity, $currentLoginData);
        $entityScore += $deviceScore;

        $blacklistScore = $this->scoreBlacklist($email, $currentLoginData['ip']);
        $entityScore += $blacklistScore;

        return [
            'geo' => $geoDataScore,
            'device' => $deviceScore,
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
     * @maxMethodScore 20
     * @settings scoring.entity.geodata
     * @return int scoreGeoData (Same as usual = 0, Totally different = 20)
     */
    private function scoreGeoData(string $entity, array $currentLoginData): int
    {
        if (!setting('scoring.entity.geodata')) {
            return 0;
        }

        $maxGeoDataScore = $this->getMethodMaxScore(__FUNCTION__);
        //$columns = ['country_code', 'country', 'city', 'region', 'longitude', 'latitude', 'ip'];
        $columns = ['ip'];
        $loginData = array_filter($currentLoginData, static fn($value, $key) => in_array($key, $columns, true), ARRAY_FILTER_USE_BOTH);
        $mostFrequentDataObject = $this->getMostFrequentGeoData($entity, $columns);
        if (!$mostFrequentDataObject) {
            return $maxGeoDataScore;
        }
        //$loginData['longitude'] = $loginData['longitude'] ? round($loginData['longitude'], 2) : 0.0;
        //$loginData['latitude'] = $loginData['latitude'] ? round($loginData['latitude'], 2) : 0.0;
        return (int) ($maxGeoDataScore / count($columns) * count(array_diff((array) $mostFrequentDataObject, $loginData)));
    }

    /**
     * @maxMethodScore 20
     * @settings scoring.entity.device
     * @return int scoreDevice (Same as usual = 0, Totally different = 20)
     */
    private function scoreDevice(string $entity, array $currentLoginData): int
    {
        if (!setting('scoring.entity.device')) {
            return 0;
        }

        $maxDeviceScore = $this->getMethodMaxScore(__FUNCTION__);
        $columns = ['device', 'os', 'browser'];
        $loginData = array_filter($currentLoginData, static fn($value, $key) => in_array($key, $columns, true), ARRAY_FILTER_USE_BOTH);
        $mostFrequentDataObject = $this->getMostFrequentData($entity, $columns);
        if (!$mostFrequentDataObject) {
            return $maxDeviceScore;
        }
        return (int) ($maxDeviceScore / count($columns) * count(array_diff((array) $mostFrequentDataObject, $loginData)));
    }

    private function getMostFrequentData(string $entity, array $columns): ?\stdClass
    {
        $mostFrequentDataObject = DB::table('login_attempts');
        foreach ($columns as $column) {
            $mostFrequentDataObject = $mostFrequentDataObject->selectSub(DB::table('login_attempts')
                ->select($column)
                ->where('entity', $entity)
                ->where('successful', true)
                ->groupBy($column)
                ->orderByRaw('COUNT(*) DESC')
                ->limit(1), $column);
        }
        return $mostFrequentDataObject->distinct()->first();
    }

    private function getMostFrequentGeoData(string $entity, array $columns): ?\stdClass
    {
        $mostFrequentDataObject = DB::table('login_attempts');
        foreach ($columns as $column) {
            $mostFrequentDataObject = $mostFrequentDataObject->selectSub(DB::table('login_attempts')
                ->select($column)
                ->where('entity', $entity)
                ->where('successful', true)
                ->groupBy($column)
                ->orderByRaw('COUNT(*) DESC')
                ->limit(1), $column);
        }
        return $mostFrequentDataObject->distinct()->first();
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
    private function scoreBlacklist(string $email, string $ip): int
    {
        if (!setting('scoring.entity.blacklist')) {
            return 0;
        }

        $maxBlacklistScore = $this->getMethodMaxScore(__FUNCTION__);
        $blacklistService = new BlacklistService();
        return $blacklistService->isBlacklisted($email, $ip) ? $maxBlacklistScore : 0;
    }
}
