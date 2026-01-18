<?php

namespace App\Repositories\Eloquent;

use App\Models\VotingType;
use App\Repositories\Contracts\VotingTypeRepositoryInterface;
use Illuminate\Support\Collection;

class VotingTypeRepository extends BaseRepository implements VotingTypeRepositoryInterface
{
    public function __construct(VotingType $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->model
            ->with('placeConfigs')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getByCategory(string $category): Collection
    {
        return $this->model
            ->with('placeConfigs')
            ->where('category', $category)
            ->where('is_active', true)
            ->get();
    }

    public function findByCode(string $code)
    {
        return $this->model
            ->with('placeConfigs')
            ->where('code', $code)
            ->first();
    }
}
