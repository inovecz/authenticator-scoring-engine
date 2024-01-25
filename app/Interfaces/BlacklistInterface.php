<?php

namespace App\Interfaces;

use App\Models\Blacklist;
use App\Enums\BlacklistTypeEnum;
use Illuminate\Support\Collection;
use App\Repositories\BlacklistRepository;
use Illuminate\Pagination\LengthAwarePaginator;

interface BlacklistInterface
{
    public function all(): LengthAwarePaginator;

    public function find(int $id): Blacklist;

    public function create(array $data): Blacklist;

    public function search(string $filter, ?string $search): BlacklistRepository;

    public function searchByType(BlacklistTypeEnum $type, string $filter, ?string $search): BlacklistRepository;

    public function orderBy(string $orderBy, bool $sortAsc): BlacklistRepository;

    public function paginate(int $perPage, int $page): BlacklistRepository;

    public function get(): Collection;
}
