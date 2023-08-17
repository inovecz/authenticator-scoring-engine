<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Location;
use Jenssegers\Agent\Agent;
use Adrianorosa\GeoLocation\GeoLocation;
use Adrianorosa\GeoLocation\GeoLocationException;

class LoginService
{
    public function getLoginAttemptData(string $entity, string $clientIp, string $userAgent): array
    {
        $agent = new Agent();
        $agent->setUserAgent($userAgent);

        $device = match (true) {
            $agent->isDesktop() => 'desktop',
            $agent->isTablet() => 'tablet',
            $agent->isMobile() => 'mobile',
            default => 'unknown'
        };
        return array_merge(
            [
                'entity' => $entity,
                'ip' => $clientIp,
            ],
            $this->getGeoData($clientIp),
            [
                'device' => $device,
                'os' => $agent->platform() ?: 'unknown',
                'browser' => $agent->browser() ?: 'unknown',
            ]
        );
    }

    private function getGeoData(string $ip): array
    {
        if ($location = Location::where('ip', $ip)->first()) {
            return $location->getToArray();
        }
        try {
            $geoData = GeoLocation::lookup($ip)->toArray();
        } catch (GeoLocationException $exception) {
            $geoData = [];
        }
        if (isset($geoData['countryCode'])) {
            $geoData['country_code'] = $geoData['countryCode'];
            unset($geoData['countryCode']);
        }
        Location::create(['ip' => $ip, ...$geoData]);
        return $geoData ?: [];
    }
}
