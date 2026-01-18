<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface ParticipantRepositoryInterface extends BaseRepositoryInterface
{
    public function getByEvent(int $eventId): Collection;

    public function getByDivision(int $divisionId): Collection;
}
