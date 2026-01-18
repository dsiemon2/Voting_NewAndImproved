<?php

namespace App\Repositories\Eloquent;

use App\Models\Division;
use App\Repositories\Contracts\DivisionRepositoryInterface;
use Illuminate\Support\Collection;

class DivisionRepository extends BaseRepository implements DivisionRepositoryInterface
{
    public function __construct(Division $model)
    {
        parent::__construct($model);
    }

    public function getByEvent(int $eventId): Collection
    {
        return $this->model
            ->where('event_id', $eventId)
            ->orderBy('display_order')
            ->get();
    }

    public function getActiveByEvent(int $eventId): Collection
    {
        return $this->model
            ->where('event_id', $eventId)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
    }

    public function findByCode(int $eventId, string $code)
    {
        return $this->model
            ->where('event_id', $eventId)
            ->where('code', $code)
            ->first();
    }
}
