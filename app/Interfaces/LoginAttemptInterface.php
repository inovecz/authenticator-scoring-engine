<?php

namespace App\Interfaces;

use App\Models\LoginAttempt;
use Illuminate\Support\Collection;
use App\Repositories\LoginAttemptRepository;
use Illuminate\Pagination\LengthAwarePaginator;

interface LoginAttemptInterface
{
    public function all(): LengthAwarePaginator;

    public function find(int $id): LoginAttempt;

    public function create(array $data): LoginAttempt;

    public function search(string $filter, ?string $search): LoginAttemptRepository;

    public function orderBy(string $orderBy, bool $sortAsc): LoginAttemptRepository;

    public function paginate(int $perPage, int $page): LoginAttemptRepository;

    public function get(): Collection;
}
