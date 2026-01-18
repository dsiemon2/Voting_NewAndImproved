<?php

namespace App\Repositories\Eloquent;

use App\Models\Participant;
use App\Repositories\Contracts\ParticipantRepositoryInterface;
use Illuminate\Support\Collection;

class ParticipantRepository extends BaseRepository implements ParticipantRepositoryInterface
{
    public function __construct(Participant $model)
    {
        parent::__construct($model);
    }

    public function getByEvent(int $eventId): Collection
    {
        return $this->model
            ->with('division')
            ->where('event_id', $eventId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getByDivision(int $divisionId): Collection
    {
        return $this->model
            ->where('division_id', $divisionId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
