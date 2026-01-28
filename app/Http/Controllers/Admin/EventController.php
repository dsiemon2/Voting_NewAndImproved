<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventVotingConfig;
use App\Models\Webhook;
use App\Services\EventConfigurationService;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\EventTemplateRepositoryInterface;
use App\Repositories\Contracts\VotingTypeRepositoryInterface;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private EventRepositoryInterface $eventRepository,
        private EventTemplateRepositoryInterface $templateRepository,
        private VotingTypeRepositoryInterface $votingTypeRepository,
        private EventConfigurationService $configService,
    ) {}

    public function index()
    {
        $events = $this->eventRepository->with(['template', 'votingType', 'state'])->paginate(15);
        $templates = $this->templateRepository->getActive();
        $votingTypes = $this->votingTypeRepository->getActive();
        $states = \App\Models\State::all();
        $modules = \App\Models\Module::orderBy('is_core', 'desc')->orderBy('name')->get();

        return view('admin.events.index', [
            'events' => $events,
            'templates' => $templates,
            'votingTypes' => $votingTypes,
            'states' => $states,
            'modules' => $modules,
        ]);
    }

    public function create()
    {
        $templates = $this->templateRepository->getActive();
        $votingTypes = $this->votingTypeRepository->getActive();
        $states = \App\Models\State::all();
        $modules = \App\Models\Module::orderBy('is_core', 'desc')->orderBy('name')->get();

        return view('admin.events.create', [
            'templates' => $templates,
            'votingTypes' => $votingTypes,
            'states' => $states,
            'modules' => $modules,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_template_id' => 'required|exists:event_templates,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'state_id' => 'nullable|exists:states,id',
            'voting_type_id' => 'required|exists:voting_types,id',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_public'] = $request->boolean('is_public', false);

        $event = $this->configService->createEventFromTemplate(
            $validated['event_template_id'],
            $validated
        );

        // Create voting config
        EventVotingConfig::create([
            'event_id' => $event->id,
            'voting_type_id' => $validated['voting_type_id'],
        ]);

        // Dispatch webhook for event.created
        Webhook::dispatch('event.created', [
            'event_id' => $event->id,
            'name' => $event->name,
            'template' => $event->template?->name,
            'created_by' => auth()->id(),
            'created_at' => $event->created_at->toIso8601String(),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Event created successfully!', 'event_id' => $event->id]);
        }

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'Event created successfully!');
    }

    public function show(Event $event)
    {
        $event->load([
            'template',
            'votingConfig.votingType.placeConfigs',
            'divisions',
            'participants',
            'entries',
            'categories',
            'modules',
            'state',
            'votes',
        ]);

        return view('admin.events.show', [
            'event' => $event,
        ]);
    }

    public function edit(Event $event)
    {
        $event->load(['modules', 'votingConfig']);

        if (request()->ajax()) {
            return response()->json($event);
        }

        $templates = $this->templateRepository->getActive();
        $votingTypes = $this->votingTypeRepository->getActive();
        $states = \App\Models\State::all();
        $modules = \App\Models\Module::orderBy('is_core', 'desc')->orderBy('name')->get();

        return view('admin.events.edit', [
            'event' => $event,
            'templates' => $templates,
            'votingTypes' => $votingTypes,
            'states' => $states,
            'modules' => $modules,
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'state_id' => 'nullable|exists:states,id',
            'voting_type_id' => 'required|exists:voting_types,id',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_public'] = $request->boolean('is_public');

        $event->update($validated);

        // Update voting config
        $event->votingConfig?->update([
            'voting_type_id' => $validated['voting_type_id'],
        ]);

        // Dispatch webhook for event.updated
        Webhook::dispatch('event.updated', [
            'event_id' => $event->id,
            'name' => $event->name,
            'updated_by' => auth()->id(),
            'updated_at' => now()->toIso8601String(),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Event updated successfully!']);
        }

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        $eventId = $event->id;
        $eventName = $event->name;

        $event->delete();

        // Dispatch webhook for event.deleted
        Webhook::dispatch('event.deleted', [
            'event_id' => $eventId,
            'name' => $eventName,
            'deleted_by' => auth()->id(),
            'deleted_at' => now()->toIso8601String(),
        ]);

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event deleted successfully!');
    }

    /**
     * Show voting configuration
     */
    public function votingConfig(Event $event)
    {
        $event->load(['votingConfig.votingType.placeConfigs']);
        $votingTypes = $this->votingTypeRepository->getActive();

        return view('admin.events.voting-config', [
            'event' => $event,
            'votingTypes' => $votingTypes,
        ]);
    }

    /**
     * Update voting configuration
     */
    public function updateVotingConfig(Request $request, Event $event)
    {
        $validated = $request->validate([
            'voting_type_id' => 'required|exists:voting_types,id',
            'max_votes_per_user' => 'nullable|integer|min:1',
            'allow_self_voting' => 'boolean',
            'voting_starts_at' => 'nullable|date',
            'voting_ends_at' => 'nullable|date|after:voting_starts_at',
            'show_live_results' => 'boolean',
            'show_vote_counts' => 'boolean',
            'show_percentages' => 'boolean',
            'place_overrides' => 'nullable|array',
        ]);

        $event->votingConfig()->updateOrCreate(
            ['event_id' => $event->id],
            $validated
        );

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'Voting configuration updated!');
    }

    /**
     * Show module configuration
     */
    public function modules(Event $event)
    {
        $event->load(['template.modules', 'moduleOverrides']);
        $allModules = $this->configService->getAllModules();

        return view('admin.events.modules', [
            'event' => $event,
            'allModules' => $allModules,
        ]);
    }

    /**
     * Update module configuration
     */
    public function updateModules(Request $request, Event $event)
    {
        $validated = $request->validate([
            'modules' => 'required|array',
            'modules.*.is_enabled' => 'boolean',
            'modules.*.custom_label' => 'nullable|string|max:100',
        ]);

        $this->configService->updateEventModules($event, $validated['modules']);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'Module configuration updated!');
    }

    /**
     * Show categories for event
     */
    public function categories(Event $event)
    {
        $categories = $event->categories()->orderBy('display_order')->orderBy('name')->paginate(15);
        return view('admin.events.categories.index', compact('event', 'categories'));
    }

    /**
     * Store a new category
     */
    public function storeCategory(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['event_id'] = $event->id;
        $validated['is_active'] = $request->has('is_active');
        $validated['display_order'] = $validated['display_order'] ?? $event->categories()->max('display_order') + 1;

        $event->categories()->create($validated);

        return redirect()->route('admin.events.categories.index', $event)
            ->with('success', 'Category created successfully.');
    }

    /**
     * Update a category
     */
    public function updateCategory(Request $request, Event $event, \App\Models\Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $category->update($validated);

        return redirect()->route('admin.events.categories.index', $event)
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete a category
     */
    public function destroyCategory(Event $event, \App\Models\Category $category)
    {
        if ($category->entries()->count() > 0) {
            return redirect()->route('admin.events.categories.index', $event)
                ->with('error', 'Cannot delete category with existing entries.');
        }

        $category->delete();

        return redirect()->route('admin.events.categories.index', $event)
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Show import page
     */
    public function import(Event $event)
    {
        return view('admin.events.import', compact('event'));
    }

    /**
     * Process import
     */
    public function processImport(Request $request, Event $event)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
            'type' => 'required|in:combined,participants,entries,divisions',
        ]);

        try {
            $import = new \App\Imports\EventDataImport($event, $request->input('type'));
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

            $stats = $import->getStats();
            $message = 'Import completed: ';
            $parts = [];

            if ($stats['divisions'] > 0) {
                $parts[] = $stats['divisions'] . ' divisions';
            }
            if ($stats['participants'] > 0) {
                $parts[] = $stats['participants'] . ' participants';
            }
            if ($stats['entries'] > 0) {
                $parts[] = $stats['entries'] . ' entries';
            }

            $message .= implode(', ', $parts) ?: 'No data imported';

            if (!empty($stats['errors'])) {
                $message .= '. Errors: ' . implode('; ', array_slice($stats['errors'], 0, 3));
            }

            return redirect()->route('admin.events.show', $event)
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Show ballots/PDF page
     */
    public function ballots(Event $event)
    {
        $event->load(['entries.division', 'entries.participant', 'divisions', 'template']);
        return view('admin.events.ballots', compact('event'));
    }

    /**
     * Clear all event data (divisions, participants, entries, votes)
     * Keeps the event configuration itself
     */
    public function clearData(Event $event)
    {
        // Count what will be deleted for the message
        $votesCount = $event->votes()->count();
        $entriesCount = $event->entries()->count();
        $participantsCount = $event->participants()->count();
        $divisionsCount = $event->divisions()->count();

        // Delete in proper order (respecting foreign key constraints)
        // 1. Delete votes first (they reference entries)
        $event->votes()->delete();

        // 2. Delete vote summaries
        $event->voteSummaries()->delete();

        // 3. Delete entries (they reference participants and divisions)
        $event->entries()->forceDelete();

        // 4. Delete participants (they reference divisions)
        $event->participants()->forceDelete();

        // 5. Delete divisions
        $event->divisions()->forceDelete();

        // 6. Delete categories
        $event->categories()->forceDelete();

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', "Event data cleared successfully! Removed: {$divisionsCount} divisions, {$participantsCount} participants, {$entriesCount} entries, {$votesCount} votes.");
    }
}
