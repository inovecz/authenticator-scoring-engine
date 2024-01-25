<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DisposableEmail;
use Illuminate\Database\Seeder;

class DisposableEmailSeeder extends Seeder
{
    public function run()
    {
        DisposableEmail::factory(100)->create();
    }
}
