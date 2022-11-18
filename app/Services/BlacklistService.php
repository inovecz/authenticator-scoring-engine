<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Blacklist;
use App\Enums\BlacklistTypeEnum;
use Illuminate\Support\Collection;
use App\Http\Resources\BlacklistResource;

class BlacklistService
{
    public function formatDatatable(Collection $items, array $datatableOptions): array
    {
        $pageLength = $datatableOptions['pageLength'] ?? 10;
        $page = $datatableOptions['page'] ?? 0;
        $total = $datatableOptions['total'];

        return array_merge($datatableOptions, [
            'data' => BlacklistResource::collection($items),
            'next_page' => $pageLength * $page < $total ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null,
        ]);
    }

    public function isBlacklisted(string $email, string $ip): array
    {
        $explodedEmail = explode('@', $email);
        $domain = array_pop($explodedEmail);

        if ($blacklistId = $this->checkInBlacklist($ip, BlacklistTypeEnum::IP)) {
            return [true, BlacklistTypeEnum::IP, $ip, $blacklistId];
        }

        if ($blacklistId = $this->checkInBlacklist($domain, BlacklistTypeEnum::DOMAIN)) {
            return [true, BlacklistTypeEnum::DOMAIN, $domain, $blacklistId];
        }

        if ($blacklistId = $this->checkInBlacklist($email, BlacklistTypeEnum::EMAIL)) {
            return [true, BlacklistTypeEnum::EMAIL, $email, $blacklistId];
        }

        return [false, null, null, null];
    }

    private function checkInBlacklist(string $value, BlacklistTypeEnum $blacklistType): ?int
    {
        if ($blacklistType !== BlacklistTypeEnum::IP && $blacklist = Blacklist::whereActive(true)->whereType($blacklistType)->whereValue(json_encode($value))->first()) {
            return $blacklist->getId();
        }

        if ($blacklistType === BlacklistTypeEnum::IP) {
            if ($blacklist = Blacklist::whereActive(true)->whereType($blacklistType)->where('value', 'LIKE', '%'.$value.'%')->first()) {
                return $blacklist->getId();
            }

            $longIP = ip2long($value);
            $blacklistsWithRanges = Blacklist::whereActive(true)->whereType($blacklistType)->where('value', 'LIKE', '[%')->get();
            /** @var Blacklist $blacklist */
            foreach ($blacklistsWithRanges as $blacklist) {
                $range = array_map(static fn($ip) => ip2long($ip), $blacklist->getValue());
                $rangeMin = min($range);
                $rangeMax = max($range);
                if ($longIP >= $rangeMin && $longIP <= $rangeMax) {
                    return $blacklist->getId();
                }
            }
        }
        return null;
    }
}
