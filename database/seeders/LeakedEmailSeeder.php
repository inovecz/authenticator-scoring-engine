<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LeakedEmail;
use Illuminate\Database\Seeder;

class LeakedEmailSeeder extends Seeder
{
    public function run()
    {
        LeakedEmail::factory(100)->create();
    }
}
