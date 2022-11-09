<?php
declare(strict_types=1);
namespace App\Services;

use Illuminate\Support\Facades\DB;

class ScoreEntityService extends ScoreService
{
    public function scoreEntity(string $entity, array $currentLoginData): array
    {
        $entityScore = 0;

        $geoDataScore = $this->scoreGeoData($entity, $currentLoginData);
        $entityScore += $geoDataScore;

        $deviceScore = $this->scoreDevice($entity, $currentLoginData);
        $entityScore += $deviceScore;

        return [
            'geo' => $geoDataScore,
            'device' => $deviceScore,
            'score' => $entityScore,
        ];
    }

    /**
     * @maxMethodScore 20
     * @return int scoreGeoData (Same as usual = 0, Totally different = 20)
     */
    private function scoreGeoData(string $entity, array $currentLoginData): int
    {
        $maxGeoDataScore = $this->getMethodMaxScore(__FUNCTION__);
        $columns = ['country_code', 'country', 'city', 'region', 'longitude', 'latitude', 'ip'];
        $loginData = array_filter($currentLoginData, static fn ($value, $key) => in_array($key, $columns, true), ARRAY_FILTER_USE_BOTH);
        $mostFrequentDataObject = $this->getMostFrequentData($entity, $columns);
        $loginData['longitude'] = $loginData['longitude'] ? round($loginData['longitude'], 2) : 0.0;
        $loginData['latitude'] = $loginData['latitude'] ? round($loginData['latitude'], 2) : 0.0;
        return (int) ($maxGeoDataScore / count($columns) * count(array_diff((array)$mostFrequentDataObject, $loginData)));
    }

    /**
     * @maxMethodScore 20
     * @return int scoreDevice (Same as usual = 0, Totally different = 20)
     */
    private function scoreDevice(string $entity, array $currentLoginData): int
    {
        $maxDeviceScore = $this->getMethodMaxScore(__FUNCTION__);
        $columns = ['device', 'os', 'browser'];
        $loginData = array_filter($currentLoginData, static fn ($value, $key) => in_array($key, $columns, true), ARRAY_FILTER_USE_BOTH);
        $mostFrequentDataObject = $this->getMostFrequentData($entity, $columns);
        return (int) ($maxDeviceScore / count($columns) * count(array_diff((array)$mostFrequentDataObject, $loginData)));
    }


    private function getMostFrequentData(string $entity, array $columns): \stdClass
    {
        $mostFrequentDataObject = DB::table('login_attemps');
        foreach ($columns as $column) {
            $mostFrequentDataObject = $mostFrequentDataObject->selectSub(DB::table('login_attemps')
                ->select($column)
                ->where('entity', $entity)
                ->groupBy($column)
                ->orderByRaw('COUNT(*) DESC')
                ->limit(1), $column);
        }
        return $mostFrequentDataObject->distinct()->first();
    }
}
