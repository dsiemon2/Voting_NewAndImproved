<?php

namespace App\Repositories\Eloquent;

use App\Models\Event;
use App\Models\EventModule;
use App\Models\EventTemplate;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    public function __construct(Event $model)
    {
        parent::__construct($model);
    }

    public function getActiveEvents(): Collection
    {
        return $this->model
            ->with(['template', 'votingType', 'state'])
            ->where('is_active', true)
            ->orderBy('event_date', 'desc')
            ->get();
    }

    public function getEventsByTemplate(int $templateId): Collection
    {
        return $this->model
            ->with(['votingType', 'state'])
            ->where('event_template_id', $templateId)
            ->orderBy('event_date', 'desc')
            ->get();
    }

    public function getUpcomingEvents(int $limit = 10): Collection
    {
        return $this->model
            ->with(['template', 'votingType'])
            ->where('is_active', true)
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')
            ->limit($limit)
            ->get();
    }

    public function getEventWithModules(int $eventId): ?Event
    {
        return $this->model
            ->with([
                'template.modules',
                'moduleOverrides.module',
                'votingType.placeConfigs',
                'votingConfig',
                'divisions',
                'state',
            ])
            ->find($eventId);
    }

    public function createFromTemplate(int $templateId, array $eventData): Event
    {
        return DB::transaction(function () use ($templateId, $eventData) {
            $template = EventTemplate::with('modules')->findOrFail($templateId);

            $event = $this->model->create([
                ...$eventData,
                'event_template_id' => $templateId,
            ]);

            // Copy template modules as event module overrides
            foreach ($template->modules as $module) {
                EventModule::create([
                    'event_id' => $event->id,
                    'module_id' => $module->id,
                    'is_enabled' => $module->pivot->is_enabled_by_default,
                    'custom_label' => $module->pivot->custom_label,
                    'configuration' => $module->pivot->configuration,
                ]);
            }

            return $event->fresh(['template', 'moduleOverrides']);
        });
    }

    public function updateModules(int $eventId, array $modules): void
    {
        DB::transaction(function () use ($eventId, $modules) {
            foreach ($modules as $moduleId => $settings) {
                EventModule::updateOrCreate(
                    [
                        'event_id' => $eventId,
                        'module_id' => $moduleId,
                    ],
                    [
                        'is_enabled' => $settings['is_enabled'] ?? true,
                        'custom_label' => $settings['custom_label'] ?? null,
                        'configuration' => $settings['configuration'] ?? null,
                    ]
                );
            }
        });
    }

    public function getEventsByUser(int $userId): Collection
    {
        return $this->model
            ->with(['template', 'votingType'])
            ->where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
