<?php
declare(strict_types=1);
namespace App\Services;

use Illuminate\Support\Facades\DB;

class ScoreEntityService
{
    public int $maxScore = 20;

    /** @return int scoreHistorical (Same as usual = 0, Totally different = 20) */
    public function scoreHistorical(string $entity, array $currentLoginData): int
    {
        $maxHistoricalScore = 20;
        $table = 'login_attemps';
        $columns = ['country_code', 'country', 'city', 'region', 'longitude', 'latitude', 'ip', 'device', 'os', 'browser'];

        $mostFrequentDataObject = DB::table('login_attemps');
        foreach ($columns as $column) {
            $mostFrequentDataObject = $mostFrequentDataObject->selectSub(DB::table($table)
                ->select($column)
                ->where('entity', $entity)
                ->groupBy($column)
                ->orderByRaw('COUNT(*) DESC')
                ->limit(1), $column);
        }
        $mostFrequentDataObject = $mostFrequentDataObject->distinct()->first();

        if ($mostFrequentDataObject) {
            unset($currentLoginData['entity']);
            $currentLoginData['longitude'] = $currentLoginData['longitude'] ? round($currentLoginData['longitude'], 2) : 0.0;
            $currentLoginData['latitude'] = $currentLoginData['latitude'] ? round($currentLoginData['latitude'], 2) : 0.0;
            $mostFrequentData = (array)$mostFrequentDataObject;
            return (int)($maxHistoricalScore / count($columns) * count(array_diff($mostFrequentData, $currentLoginData)));
        }

        return 20;
    }
}
