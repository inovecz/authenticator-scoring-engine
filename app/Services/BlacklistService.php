<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Blacklist;
use Illuminate\Support\Str;
use App\Enums\BlacklistTypeEnum;
use Illuminate\Support\Collection;
use App\Http\Resources\BlacklistResource;

class BlacklistService
{
    public function formatDatatable(Collection $items, array $datatableOptions): array
    {
        $pageLength = $datatableOptions['page_length'] ?? 10;
        $page = $datatableOptions['page'] ?? 0;
        $total = $datatableOptions['total'];

        return array_merge($datatableOptions, [
            'data' => BlacklistResource::collection($items),
            'next_page' => $pageLength * $page < $total ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null,
        ]);
    }

    public function isBlacklisted(string $email, string $ip, string $userAgent, $forceCheck = false): array
    {
        $domain = get_email_domain($email);

        $blacklistId = $this->checkInBlacklist($ip, BlacklistTypeEnum::IP);
        if ($blacklistId && ($forceCheck || setting('deny_login.blacklist.ip') === true)) {
            return [true, BlacklistTypeEnum::IP, $ip, $blacklistId];
        }

        $blacklistId = $this->checkInBlacklist($domain, BlacklistTypeEnum::DOMAIN);
        if ($blacklistId && ($forceCheck || setting('deny_login.blacklist.domain') === true)) {
            return [true, BlacklistTypeEnum::DOMAIN, $domain, $blacklistId];
        }

        $blacklistId = $this->checkInBlacklist($email, BlacklistTypeEnum::EMAIL);
        if ($blacklistId && ($forceCheck || setting('deny_login.blacklist.email') === true)) {
            return [true, BlacklistTypeEnum::EMAIL, $email, $blacklistId];
        }

        if ($forceCheck || setting('deny_login.blacklist.os') === true) {
            /** @see https://resources.infosecinstitute.com/topics/penetration-testing/top-10-linux-distro-ethical-hacking-penetration-testing/ */
            $blacklistedOSs = ['kali', 'parrot', 'blackarch', 'backbox', 'samurai', 'pentoo', 'deft', 'caine', 'network security toolkit', 'bugtraq'];
            foreach ($blacklistedOSs as $blacklistedOS) {
                if (Str::of($userAgent)->lower()->contains($blacklistedOS)) {
                    return [true, BlacklistTypeEnum::OS, $userAgent, $blacklistedOS];
                }
            }
        }

        return [false, null, null, null];
    }

    private function checkInBlacklist(string $value, BlacklistTypeEnum $blacklistType): ?int
    {
        if (!in_array($blacklistType, [BlacklistTypeEnum::IP, BlacklistTypeEnum::OS], true) && $blacklist = Blacklist::whereActive(true)->whereType($blacklistType)->whereValue(json_encode($value))->first()) {
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
