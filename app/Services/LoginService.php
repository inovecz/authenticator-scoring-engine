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
                'user_agent' => $userAgent,
                'device' => $device,
                'os' => $agent->platform() ?: 'unknown',
                'browser' => $agent->browser() ?: 'unknown',
            ]
        );
    }

    private function getGeoData(string $ip): array
    {
        try {
            $geoData = GeoLocation::lookup($ip)->toArray();
        } catch (GeoLocationException $exception) {
            $geoData = [];
        }
        if (array_key_exists('countryCode', $geoData)) {
            $geoData['country_code'] = $geoData['countryCode'];
            unset($geoData['countryCode']);
        }
        $location = Location::updateOrCreate($geoData);
        $location->refresh();
        return $location->getToArray();
    }
}
