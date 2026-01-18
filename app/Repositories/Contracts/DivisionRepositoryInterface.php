<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DivisionRepositoryInterface extends BaseRepositoryInterface
{
    public function getByEvent(int $eventId): Collection;

    public function getActiveByEvent(int $eventId): Collection;

    public function findByCode(int $eventId, string $code);
}
