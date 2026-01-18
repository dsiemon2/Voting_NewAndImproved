<?php

namespace App\Repositories\Contracts;

use App\Models\Event;
use Illuminate\Support\Collection;

interface EventRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveEvents(): Collection;

    public function getEventsByTemplate(int $templateId): Collection;

    public function getUpcomingEvents(int $limit = 10): Collection;

    public function getEventWithModules(int $eventId): ?Event;

    public function createFromTemplate(int $templateId, array $eventData): Event;

    public function updateModules(int $eventId, array $modules): void;

    public function getEventsByUser(int $userId): Collection;
}
