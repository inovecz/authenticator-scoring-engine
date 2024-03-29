<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(BlacklistSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(LeakedEmailSeeder::class);
        $this->call(LeakedPhoneSeeder::class);
        $this->call(DisposableEmailSeeder::class);
    }
}
