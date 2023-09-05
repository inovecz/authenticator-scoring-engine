<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\IpAddress;
use App\Models\LoginAttempt;
use App\Models\MlTimerSuccess;
use App\Models\MlMouseSuccess;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use App\Services\MachineLearningService;

class CalculateGlobalMlSuccess extends Command
{
    protected $signature = 'app:calculate-global-ml-success';
    protected $description = 'Command description';

    protected MachineLearningService $machineLearningService;
    protected Collection $attempts;

    public function __construct()
    {
        parent::__construct();
        $this->machineLearningService = new MachineLearningService();
    }

    public function handle(): int
    {
        $this->info('Fetching data...');
        $this->attempts = LoginAttempt::with('location')->get();

        $this->info('Transforming data...');
        $bar = $this->output->createProgressBar($this->attempts->count());
        $bar->setBarWidth(50);
        $bar->setFormat('%current%/%max% [%bar%] Processed %elapsed%/%estimated%, %percent%% done. Memory usage: %memory%');
        $bar->start();

        /** @var LoginAttempt $attempt */
        foreach ($this->attempts as $attempt) {
            $bar->advance();
            $attempt->locationComposite = $attempt->location->getCountry().'%%'.$attempt->location->getRegion().'%%'.$attempt->location->getCity();
        }

        $bar->finish();

        $this->calculateSuccessForIps();
        $this->calculateSuccessForLocations();
        $this->calculateSuccessForMouse();
        $this->calculateSuccessForTime();

        return Command::SUCCESS;
    }

    private function calculateSuccessForIps(): void
    {
        $groups = $this->attempts->groupBy('ip');

        $this->newLine();
        $this->info('Calculating success for IPs...');
        $bar = $this->output->createProgressBar($groups->count());
        $bar->setBarWidth(50);
        $bar->setFormat('%current%/%max% [%bar%] Processed %elapsed%/%estimated%, %percent%% done. Memory usage: %memory%');
        $bar->start();

        foreach ($groups as $ip => $attempts) {
            $bar->advance();
            $inputs = $attempts->map(fn(LoginAttempt $attempt) => [1])->toArray();
            $outputs = $attempts->map(fn(LoginAttempt $attempt) => $attempt->isSuccessful() ? 'successful' : 'unsuccessful')->toArray();

            $this->machineLearningService->setDataset($inputs, $outputs);
            $predicted = $this->machineLearningService->predict();
            IpAddress::updateOrCreate(['ip' => $ip], ['ml_success_rate' => $predicted['successful']]);
        }

        $bar->finish();
    }

    private function calculateSuccessForLocations(): void
    {
        $groups = $this->attempts->groupBy('locationComposite');

        $this->newLine();
        $this->info('Calculating success for locations...');
        $bar = $this->output->createProgressBar($groups->count());
        $bar->setBarWidth(50);
        $bar->setFormat('%current%/%max% [%bar%] Processed %elapsed%/%estimated%, %percent%% done. Memory usage: %memory%');
        $bar->start();

        foreach ($groups as $location => $attempts) {
            $bar->advance();
            $inputs = $attempts->map(fn(LoginAttempt $attempt) => [1])->toArray();
            $outputs = $attempts->map(fn(LoginAttempt $attempt) => $attempt->isSuccessful() ? 'successful' : 'unsuccessful')->toArray();

            $this->machineLearningService->setDataset($inputs, $outputs);
            $predicted = $this->machineLearningService->predict();
            [$country, $region, $city] = explode('%%', $location);
            Location::where(['country' => $country, 'region' => $region, 'city' => $city])->update(['ml_success_rate' => $predicted['successful']]);
        }

        $bar->finish();
    }

    private function calculateSuccessForMouse(): void
    {
        $groups = $this->attempts->groupBy(function (LoginAttempt $attempt) {
            return determine_range($attempt->getMouseAvgAccel(), 0, 1000, 100);
        });

        $this->newLine();
        $this->info('Calculating success for mouse dynamics...');
        $bar = $this->output->createProgressBar($groups->count());
        $bar->setBarWidth(50);
        $bar->setFormat('%current%/%max% [%bar%] Processed %elapsed%/%estimated%, %percent%% done. Memory usage: %memory%');
        $bar->start();

        foreach ($groups as $range => $attempts) {
            $bar->advance();
            $inputs = $attempts->map(fn(LoginAttempt $attempt) => [1])->toArray();
            $outputs = $attempts->map(fn(LoginAttempt $attempt) => $attempt->isSuccessful() ? 'successful' : 'unsuccessful')->toArray();

            $this->machineLearningService->setDataset($inputs, $outputs);
            $predicted = $this->machineLearningService->predict();
            [$min, $max] = explode('-', $range);
            MlMouseSuccess::updateOrCreate(['from' => $min, 'to' => $max], ['ml_success_rate' => $predicted['successful']]);
        }

        $bar->finish();
    }

    private function calculateSuccessForTime(): void
    {
        $groups = $this->attempts->groupBy(function (LoginAttempt $attempt) {
            return determine_range($attempt->getTimer(), 0, 180, 2);
        });

        $this->newLine();
        $this->info('Calculating success for login timer...');
        $bar = $this->output->createProgressBar($groups->count());
        $bar->setBarWidth(50);
        $bar->setFormat('%current%/%max% [%bar%] Processed %elapsed%/%estimated%, %percent%% done. Memory usage: %memory%');
        $bar->start();

        foreach ($groups as $range => $attempts) {
            $bar->advance();
            $inputs = $attempts->map(fn(LoginAttempt $attempt) => [1])->toArray();
            $outputs = $attempts->map(fn(LoginAttempt $attempt) => $attempt->isSuccessful() ? 'successful' : 'unsuccessful')->toArray();

            $this->machineLearningService->setDataset($inputs, $outputs);
            $predicted = $this->machineLearningService->predict();
            [$min, $max] = explode('-', $range);
            MlTimerSuccess::updateOrCreate(['from' => $min, 'to' => $max], ['ml_success_rate' => $predicted['successful']]);
        }

        $bar->finish();
    }
}
