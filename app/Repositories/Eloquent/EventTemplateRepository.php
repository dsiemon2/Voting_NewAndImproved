<?php

namespace App\Repositories\Eloquent;

use App\Models\EventTemplate;
use App\Repositories\Contracts\EventTemplateRepositoryInterface;
use Illuminate\Support\Collection;

class EventTemplateRepository extends BaseRepository implements EventTemplateRepositoryInterface
{
    public function __construct(EventTemplate $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->model
            ->with('modules')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getWithModules(int $id)
    {
        return $this->model
            ->with('modules')
            ->find($id);
    }
}
