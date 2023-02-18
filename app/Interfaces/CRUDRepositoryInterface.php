<?php

declare(strict_types=1);

namespace App\Interfaces;


use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface CRUDRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): Eloquent|Builder|null;

    public function save(Eloquent $model): bool;

    public function create(array $attributes): Eloquent;

    public function update(int $id, array $attributes): bool|int;

    public function delete(int $id): int;
}
