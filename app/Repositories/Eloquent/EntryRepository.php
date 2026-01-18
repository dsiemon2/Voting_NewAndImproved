<?php

namespace App\Repositories\Eloquent;

use App\Models\Entry;
use App\Repositories\Contracts\EntryRepositoryInterface;
use Illuminate\Support\Collection;

class EntryRepository extends BaseRepository implements EntryRepositoryInterface
{
    public function __construct(Entry $model)
    {
        parent::__construct($model);
    }

    public function getByEvent(int $eventId): Collection
    {
        return $this->model
            ->with(['division', 'participant', 'category'])
            ->where('event_id', $eventId)
            ->where('is_active', true)
            ->orderBy('entry_number')
            ->get();
    }

    public function getByDivision(int $eventId, int $divisionId): Collection
    {
        return $this->model
            ->with(['participant', 'category'])
            ->where('event_id', $eventId)
            ->where('division_id', $divisionId)
            ->where('is_active', true)
            ->orderBy('entry_number')
            ->get();
    }

    public function findByEntryNumber(int $eventId, string $entryNumber)
    {
        return $this->model
            ->with(['division', 'participant'])
            ->where('event_id', $eventId)
            ->where('entry_number', $entryNumber)
            ->first();
    }
}
