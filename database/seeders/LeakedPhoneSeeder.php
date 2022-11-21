<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LeakedPhone;
use Illuminate\Database\Seeder;

class LeakedPhoneSeeder extends Seeder
{
    public function run()
    {
        LeakedPhone::factory(100)->create();
    }
}
