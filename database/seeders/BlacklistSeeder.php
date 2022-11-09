<?php
declare(strict_types=1);
namespace Database\Seeders;

use App\Models\Blacklist;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlacklistSeeder extends Seeder
{
    public function run(): void
    {
        Blacklist::factory(100)->create();
    }
}
