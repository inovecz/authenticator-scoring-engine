<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\LoginAttempt;
use Illuminate\Console\Command;
use Adrianorosa\GeoLocation\GeoLocation;
use Adrianorosa\GeoLocation\GeoLocationException;

class FetchLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $locations = LoginAttempt::whereNull('location_id')->get();
        $this->info('Found '.$locations->count().' locations to fetch');
        $ipAddresses = $locations->pluck('ip')->unique();
        $this->info('Found '.$ipAddresses->count().' unique IP addresses');
        die();

        $bar = $this->output->createProgressBar($locations->count());
        $bar->setBarWidth(50);
        $bar->setFormat('%current%/%max% [%bar%] Processed %elapsed%/%estimated%, %percent%% done. Memory usage: %memory%');
        $bar->start();

        /** @var Location $location */
        foreach ($locations as $location) {
            $bar->advance();
            try {
                $geoData = GeoLocation::lookup($location->getIP())->toArray();
            } catch (GeoLocationException $exception) {
                $this->error($exception->getMessage());
                die;
            }
            if ($geoData && isset($geoData['countryCode'])) {
                $geoData['country_code'] = $geoData['countryCode'];
                unset($geoData['countryCode']);
                $location->update($geoData);
            }
        }

        $bar->finish();

        return Command::SUCCESS;
    }
}
