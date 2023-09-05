<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LoginAttempt;
use Phpml\Classification\SVC;
use Illuminate\Console\Command;
use Phpml\SupportVectorMachine\Kernel;

class MachineLearning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:machine-learning';

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
        $this->info('================');
        $this->info('Machine Learning');
        $this->info('================');

        $timer = microtime(true);
        $this->components->twoColumnDetail('Reading chronicles', '');

        //$attempts = LoginAttempt::where('entity', '99e70321f7c7405d9589c91466b9db94')->where('ip', '178.255.168.232')->get();
        $rawAttempts = LoginAttempt::all();
        $attempts = $rawAttempts->map(static function (LoginAttempt $attempt) {
            return [
                $attempt->getEntity(),
                $attempt->getIP(),
                //$attempt->getUserAgent(),
            ];
        })->toArray();
        $success = $rawAttempts->map(static function (LoginAttempt $attempt) {
            return $attempt->isSuccessful();
        })->toArray();

        $this->components->twoColumnDetail('', 'done in '.round(microtime(true) - $timer, 2).' seconds');
        $timer = microtime(true);
        $this->components->twoColumnDetail('Warming neurons', '');

        $classifier = new SVC(Kernel::LINEAR, $cost = 1000, probabilityEstimates: true);
        $classifier->train($attempts, $success);

        $this->components->twoColumnDetail('', 'done in '.round(microtime(true) - $timer, 2).' seconds');
        $timer = microtime(true);
        $this->components->twoColumnDetail('Calling Sybila', '');

        $newAttempt = [
            '99e7144d06ad4b498b4156983bd96781',
            '78.154.184.168',
            //'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)',
        ];
        $prediction = $classifier->predictProbability($newAttempt);

        $this->components->twoColumnDetail('', 'done in '.round(microtime(true) - $timer, 2).' seconds');
        $this->info('================');
        $this->info('Predicted results of successful login for entity '.implode(', ', $newAttempt));
        $this->components->twoColumnDetail('Value', 'Probability');
        foreach ($prediction as $key => $value) {
            $this->components->twoColumnDetail('<fg=magenta>'.$key.'</>', '<fg=blue>'.round($value * 100).'%</>');
        }

        return Command::SUCCESS;
    }
}
