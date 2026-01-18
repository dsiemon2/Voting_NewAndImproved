<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface EntryRepositoryInterface extends BaseRepositoryInterface
{
    public function getByEvent(int $eventId): Collection;

    public function getByDivision(int $eventId, int $divisionId): Collection;

    public function findByEntryNumber(int $eventId, string $entryNumber);
}
