<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Blacklist;
use Illuminate\Database\Seeder;

class BlacklistTestSeeder extends Seeder
{
    protected array $datetimes = [];

    public function __construct()
    {
        $this->datetimes = ['created_at' => now(), 'updated_at' => now()];
    }

    public function run(): void
    {
        Blacklist::insert([
            $this->addDatetimes(['type' => 'DOMAIN', 'value' => json_encode('blacklisted.cz'), 'active' => true]),
            $this->addDatetimes(['type' => 'DOMAIN', 'value' => json_encode('blacklisted.com'), 'active' => true]),
            $this->addDatetimes(['type' => 'DOMAIN', 'value' => json_encode('inactive.net'), 'active' => false]),
            $this->addDatetimes(['type' => 'DOMAIN', 'value' => json_encode('inactive.org'), 'active' => false]),
            $this->addDatetimes(['type' => 'IP', 'value' => json_encode('50.100.150.200'), 'active' => true]),
            $this->addDatetimes(['type' => 'IP', 'value' => json_encode(['100.100.100.0', '100.100.100.255']), 'active' => true]),
            $this->addDatetimes(['type' => 'IP', 'value' => json_encode('255.255.255.255'), 'active' => false]),
            $this->addDatetimes(['type' => 'IP', 'value' => json_encode(['200.200.0.0', '200.200.255.255']), 'active' => false]),
            $this->addDatetimes(['type' => 'EMAIL', 'value' => json_encode('blacklisted@active.cz'), 'active' => true]),
            $this->addDatetimes(['type' => 'EMAIL', 'value' => json_encode('blacklisted@active.com'), 'active' => true]),
            $this->addDatetimes(['type' => 'EMAIL', 'value' => json_encode('blacklisted@inactive.net'), 'active' => false]),
            $this->addDatetimes(['type' => 'EMAIL', 'value' => json_encode('blacklisted@inactive.org'), 'active' => false]),
        ]);
    }

    private function addDatetimes(array $array): array
    {
        return array_merge($array, $this->datetimes);
    }
}
