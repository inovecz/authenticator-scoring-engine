<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        setting([
            'scoring.password.leaks' => true,
            'scoring.password.length' => true,
            'scoring.password.complexity.numbers' => true,
            'scoring.password.complexity.letters' => true,
            'scoring.password.complexity.mixed_case' => true,
            'scoring.password.complexity.symbols' => true,
            'scoring.entity.leaks.email' => true,
            'scoring.entity.leaks.phone' => true,
            'scoring.entity.disposable_email' => true,
            'scoring.entity.geodata' => true,
            'scoring.entity.device' => true,
        ])->save();
    }
}
