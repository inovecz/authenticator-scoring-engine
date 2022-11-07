<?php
declare(strict_types=1);
namespace App\Services;

use Jenssegers\Agent\Agent;
use Adrianorosa\GeoLocation\GeoLocation;

class LoginService
{
    public function getLoginAttempData(string $entity, string $clientIp, string $userAgent): array
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
                'ip' => $clientIp
            ],
            $this->getGeoData($clientIp),
            [
                'device' => $device,
                'os' => $agent->platform() ?: 'unknown',
                'browser' => $agent->browser() ?: 'unknown'
            ]
        );
    }

    private function getGeoData(string $ip): array
    {
        $geoData = GeoLocation::lookup($ip)->toArray();
        if (isset($geoData['countryCode'])) {
            $geoData['country_code'] = $geoData['countryCode'];
            unset($geoData['countryCode']);
        }
        return $geoData ?: [];
    }
}
