<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;
    protected array $with = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->model->with($this->with)->get($columns);
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->with($this->with)->find($id, $columns);
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->model->with($this->with)->findOrFail($id, $columns);
    }

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->with($this->with)->where($field, $value)->first($columns);
    }

    public function findWhere(array $conditions, array $columns = ['*']): Collection
    {
        $query = $this->model->with($this->with);

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->get($columns);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->with($this->with)->paginate($perPage, $columns);
    }

    public function with(array $relations): self
    {
        $this->with = $relations;
        return $this;
    }

    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Reset eager loading
     */
    protected function resetWith(): void
    {
        $this->with = [];
    }
}
