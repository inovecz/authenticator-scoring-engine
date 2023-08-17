<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GenderEnum;
use App\Enums\BlacklistTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blacklist>
 */
class BlacklistFactory extends Factory
{
    public function definition(): array
    {
        $types = BlacklistTypeEnum::cases();
        $type = $types[array_rand($types)];
        $value = match ($type) {
            BlacklistTypeEnum::DOMAIN => fake()->unique()->domainName,
            BlacklistTypeEnum::EMAIL => fake()->email,
            BlacklistTypeEnum::IP => fake()->boolean(90) ? fake()->ipv4 : [fake()->ipv4, fake()->ipv4],
        };
        return [
            'type' => $type,
            'value' => $value,
            'reason' => fake()->boolean ? fake()->sentence() : null,
            'active' => fake()->boolean(90),
        ];
    }
}
