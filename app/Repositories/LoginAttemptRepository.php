<?php

namespace App\Repositories;

use App\Models\LoginAttempt;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class LoginAttemptRepository implements \App\Interfaces\LoginAttemptInterface
{
    private ?Builder $builder;

    public function __construct()
    {
        $this->builder = LoginAttempt::query();
    }

    public function all(): LengthAwarePaginator
    {
        return LoginAttempt::paginate();
    }

    public function find(int $id): LoginAttempt
    {
        return LoginAttempt::findOrFail($id);
    }

    public function create(array $data): LoginAttempt
    {
        return LoginAttempt::create($data);
    }

    public function search(string $filter = 'all', ?string $search = ''): LoginAttemptRepository
    {
        $search = in_array($search, ['', null], true) ? null : '%'.$search.'%';

        $this->builder = $this->builder
            ->when($search, function (Builder $query) use ($filter, $search) {
                $query->when($filter === 'all', function (Builder $searchQuery) use ($search) {
                    $searchQuery->where(function ($q) use ($search) {
                        $q->where('ip', 'LIKE', $search)
                            ->orWhere('entity', 'LIKE', $search)
                            ->orWhere('user_agent', 'LIKE', $search)
                            ->orWhere('device', 'LIKE', $search)
                            ->orWhere('browser', 'LIKE', $search)
                            ->orWhere('os', 'LIKE', $search);
                    });
                });
                $query->when($filter === 'ip', function (Builder $searchQuery) use ($search) {
                    $searchQuery->where('ip', 'LIKE', '%'.$search.'%');
                });
                $query->when($filter === 'device', function (Builder $searchQuery) use ($search) {
                    $searchQuery->where('device', 'LIKE', '%'.$search.'%');
                });
                $query->when($filter === 'os', function (Builder $searchQuery) use ($search) {
                    $searchQuery->where('os', 'LIKE', '%'.$search.'%');
                });
                $query->when($filter === 'browser', function (Builder $searchQuery) use ($search) {
                    $searchQuery->where('browser', 'LIKE', '%'.$search.'%');
                });
                $query->when($filter === 'user_agent', function (Builder $searchQuery) use ($search) {
                    $searchQuery->where('user_agent', 'LIKE', '%'.$search.'%');
                });
                $query->when($filter === 'entity', function (Builder $searchQuery) use ($search) {
                    $searchQuery->where('entity', 'LIKE', '%'.$search.'%');
                });
            });

        return $this;
    }

    public function orderBy(string $orderBy, bool $sortAsc = true): LoginAttemptRepository
    {
        $this->builder = $this->builder->orderBy($orderBy, $sortAsc ? 'ASC' : 'DESC');
        return $this;
    }

    public function paginate(int $perPage = 10, int $page = 1): LoginAttemptRepository
    {
        $this->builder = $this->builder->skip(($page - 1) * $perPage)->take($perPage);
        return $this;
    }

    public function query(): Builder
    {
        return $this->builder;
    }

    public function get(): Collection
    {
        $result = $this->builder->get();
        $this->builder = LoginAttempt::query();
        return $result;
    }

    public function count(): int
    {
        $result = $this->builder->count();
        $this->builder = LoginAttempt::query();
        return $result;
    }
}
