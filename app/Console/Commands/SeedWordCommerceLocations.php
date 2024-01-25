<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Location;
use Illuminate\Console\Command;

class SeedWordCommerceLocations extends Command
{
    protected $signature = 'app:seed-word-commerce-locations';

    protected $description = 'Command description';

    public function handle()
    {
        $json = file_get_contents(storage_path('app/wp_wflocs.json'));
        $data = json_decode($json, true);
        foreach ($data as $record) {
            $ip = inet_ntop(substr(hex2bin($record['IP']), -4));
            $failed = (bool) $record['failed'];
            Location::updateOrCreate([
                'ip' => $ip,
            ], [
                'attempts' => 1,
                'successful_attempts' => $failed ? 0 : 1,
                'success_rate' => $failed ? 0.0 : 1.0,
                'city' => $record['city'],
                'region' => $record['region'],
                'country' => $record['countryName'],
                'country_code' => $record['countryCode'],
                'longitude' => $record['lon'],
                'latitude' => $record['lat'],
            ]);
        }
        return Command::SUCCESS;
    }
}
