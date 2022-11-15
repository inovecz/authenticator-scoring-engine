<?php

namespace App\Repositories;

use App\Models\Blacklist;
use Illuminate\Support\Str;
use App\Enums\BlacklistTypeEnum;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class BlacklistRepository implements \App\Interfaces\BlacklistInterface
{
    private Blacklist $blacklist;
    private ?Builder $builder;

    public function __construct()
    {
        $this->builder = Blacklist::query();
    }

    public function all(): LengthAwarePaginator
    {
        return Blacklist::paginate();
    }

    public function find(int $id): Blacklist
    {
        return Blacklist::findOrFail($id);
    }

    public function create(array $data): Blacklist
    {
        return Blacklist::create($data);
    }

    public function search(string $filter = 'all', ?string $search = ''): BlacklistRepository
    {
        $search = in_array($search, ['', null], true) ? null : '%'.$search.'%';

        $this->builder = $this->builder
            ->when($search, function (Builder $query) use ($filter, $search) {
                $query->when($filter === 'all', function (Builder $searchQuery) use ($search) {
                    $searchQuery->where(function ($q) use ($search) {
                        $q->where('value', 'LIKE', $search)
                            ->orWhere('reason', 'LIKE', $search);
                    });
                });
                $query->when($filter === 'value', function (Builder $searchQuery) use ($search) {
                    $searchQuery->where('value', 'LIKE', '%'.$search.'%');
                });
                $query->when($filter === 'reason', function (Builder $searchQuery) use ($search) {
                    $searchQuery->where('reason', 'LIKE', '%'.$search.'%');
                });
            });

        return $this;
    }

    public function searchByType(BlacklistTypeEnum $type, string $filter = 'all', ?string $search = ''): BlacklistRepository
    {
        $this->builder = $this->builder->whereType($type);
        return $this->search($filter, $search);
    }

    public function orderBy(string $orderBy, bool $sortAsc = true): BlacklistRepository
    {
        $this->builder = $this->builder->orderBy($orderBy, $sortAsc ? 'ASC' : 'DESC');
        return $this;
    }

    public function paginate(int $perPage = 10, int $page = 1): BlacklistRepository
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
        $sql = Str::replaceArray('?', $this->builder->getBindings(), $this->builder->toSql());
        $result = $this->builder->get();
        $this->builder = Blacklist::query();
        return $result;
    }

    public function count(): int
    {
        $result = $this->builder->count();
        $this->builder = Blacklist::query();
        return $result;
    }
}
