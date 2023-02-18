<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\CRUDRepositoryInterface;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class EloquentModelRepository implements CRUDRepositoryInterface
{
    /** Returns the model class which can be used to call static methods of the model */
    abstract protected function modelClass(): string|Model;

    public function all(): Collection
    {
        return $this->modelClass()::all();
    }

    public function find(int $id): Eloquent|Builder|null
    {
        return $this->modelClass()::find($id);
    }

    public function save(Eloquent $model): bool
    {
        return $model->save();
    }

    public function create(array $attributes): Eloquent
    {
        return $this->modelClass()::create($attributes);
    }

    public function update(int $id, array $attributes): bool|int
    {
        return $this->modelClass()::where('id', $id)->update($attributes);
    }

    public function delete(int $id): int
    {
        return $this->modelClass()::destroy($id);
    }
}
