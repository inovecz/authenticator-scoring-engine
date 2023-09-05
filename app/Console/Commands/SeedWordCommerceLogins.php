<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\ServerUser;
use Jenssegers\Agent\Agent;
use App\Models\LoginAttempt;
use Illuminate\Console\Command;

class SeedWordCommerceLogins extends Command
{
    protected $signature = 'app:seed-word-commerce-logins';

    protected $description = 'Command description';

    public function handle()
    {
        $json = file_get_contents(storage_path('app/wp_wflogins.json'));
        $records = json_decode($json, true);

        $bar = $this->output->createProgressBar(count($records));
        $bar->setBarWidth(50);
        $bar->setFormat('%current%/%max% [%bar%] Processed %elapsed%/%estimated%, %percent%% done. Memory usage: %memory%');
        $bar->start();

        LoginAttempt::unguard();
        foreach ($records as $record) {
            $bar->advance();

            $ip = hex_to_ip($record['IP']);
            //$location = Location::where(['ip' => $ip])->first();
            $userHash = $this->getUserHash($record['username']);
            //if (!$location) {
            //    try {
            //        $geoData = GeoLocation::lookup($ip)->toArray();
            //    } catch (GeoLocationException $exception) {
            //        $geoData = [];
            //    }
            //    if (isset($geoData['countryCode'])) {
            //        $geoData['country_code'] = $geoData['countryCode'];
            //        unset($geoData['countryCode']);
            //    }
            //    Location::create(['ip' => $ip, ...$geoData]);
            //}
            if ($record['UA']) {
                $agent = new Agent();
                $agent->setUserAgent($record['UA']);
                $device = match (true) {
                    $agent->isDesktop() => 'desktop',
                    $agent->isTablet() => 'tablet',
                    $agent->isMobile() => 'mobile',
                    default => 'unknown'
                };
            }

            $ctime = Carbon::createFromTimestamp($record['ctime']);

            LoginAttempt::create([
                'ip' => $ip,
                'entity' => $userHash,
                'user_agent' => $record['UA'],
                'device' => $device ?? 'unknown',
                'os' => $agent?->platform() ?? 'unknown',
                'browser' => $agent?->browser() ?? 'unknown',
                'action' => $record['action'],
                'successful' => $record['fail'] === 0,
                'created_at' => $ctime,
                'updated_at' => $ctime,
            ]);
        }
        LoginAttempt::reguard();
        $bar->finish();
        return Command::SUCCESS;
    }

    private function getUserHash(mixed $username)
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = ServerUser::where('email', $username)->first();
            if (!$user) {
                $user = ServerUser::create([
                    'email' => $username,
                    'hash' => generate_hash(),
                ]);
            }
        } else {
            $user = ServerUser::where('name', $username)->first();
            if (!$user) {
                $user = ServerUser::create([
                    'name' => $username,
                    'hash' => generate_hash(),
                ]);
            }
        }
        return $user->getHash();
    }
}
