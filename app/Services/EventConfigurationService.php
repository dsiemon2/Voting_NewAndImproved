<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\Module;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\EventTemplateRepositoryInterface;
use Illuminate\Support\Collection;

class EventConfigurationService
{
    public function __construct(
        private EventRepositoryInterface $eventRepository,
        private EventTemplateRepositoryInterface $templateRepository,
    ) {}

    /**
     * Get all enabled modules for an event
     */
    public function getEventModules(Event $event): Collection
    {
        // Get template modules as base
        $templateModules = $event->template->modules()
            ->wherePivot('is_enabled_by_default', true)
            ->get()
            ->keyBy('id');

        // Apply event-level overrides
        $eventOverrides = $event->moduleOverrides()->with('module')->get()->keyBy('module_id');

        return $templateModules->map(function ($module) use ($eventOverrides, $event) {
            $override = $eventOverrides->get($module->id);

            return (object) [
                'id' => $module->id,
                'code' => $module->code,
                'name' => $override?->custom_label ?? $module->pivot->custom_label ?? $module->name,
                'icon' => $module->icon,
                'route' => $this->buildModuleRoute($event, $module),
                'route_prefix' => $module->route_prefix,
                'is_enabled' => $override?->is_enabled ?? true,
                'is_core' => $module->is_core,
                'configuration' => array_merge(
                    json_decode($module->pivot->configuration ?? '{}', true) ?: [],
                    json_decode($override?->configuration ?? '{}', true) ?: []
                ),
            ];
        })->filter(fn($m) => $m->is_enabled)->values();
    }

    /**
     * Build navigation menu for an event
     */
    public function buildEventMenu(Event $event): array
    {
        $modules = $this->getEventModules($event);

        return $modules->map(fn($module) => [
            'label' => $module->name,
            'icon' => $module->icon,
            'route' => $module->route,
            'route_prefix' => $module->route_prefix,
            'is_core' => $module->is_core,
            'active' => request()->routeIs("admin.events.{$module->route_prefix}.*"),
        ])->values()->toArray();
    }

    /**
     * Get available templates
     */
    public function getAvailableTemplates(): Collection
    {
        return $this->templateRepository->getActive();
    }

    /**
     * Get template with modules
     */
    public function getTemplateWithModules(int $templateId): ?EventTemplate
    {
        return $this->templateRepository->getWithModules($templateId);
    }

    /**
     * Create event from template
     */
    public function createEventFromTemplate(int $templateId, array $eventData): Event
    {
        return $this->eventRepository->createFromTemplate($templateId, $eventData);
    }

    /**
     * Check if event has a specific module enabled
     */
    public function hasModule(Event $event, string $moduleCode): bool
    {
        return $this->getEventModules($event)
            ->contains('code', $moduleCode);
    }

    /**
     * Get label for a module type
     */
    public function getModuleLabel(Event $event, string $moduleCode): string
    {
        $module = $this->getEventModules($event)->firstWhere('code', $moduleCode);
        return $module?->name ?? ucfirst($moduleCode);
    }

    /**
     * Get all available modules
     */
    public function getAllModules(): Collection
    {
        return Module::orderBy('display_order')->get();
    }

    /**
     * Update event modules
     */
    public function updateEventModules(Event $event, array $modules): void
    {
        $this->eventRepository->updateModules($event->id, $modules);
    }

    /**
     * Build module route
     */
    private function buildModuleRoute(Event $event, Module $module): string
    {
        $routeName = "admin.events.{$module->route_prefix}.index";

        if (\Route::has($routeName)) {
            return route($routeName, $event);
        }

        return '#';
    }

    /**
     * Get participant label for event
     */
    public function getParticipantLabel(Event $event): string
    {
        return $event->template?->participant_label ?? 'Participant';
    }

    /**
     * Get entry label for event
     */
    public function getEntryLabel(Event $event): string
    {
        return $event->template?->entry_label ?? 'Entry';
    }
}
