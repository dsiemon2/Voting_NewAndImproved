<?php

namespace App\Services\AI\Wizards;

use App\Models\Entry;
use App\Models\Vote;
use App\Models\DeletionHistory;
use Illuminate\Support\Facades\Auth;

class DeleteEntryWizard extends BaseWizard
{
    public function getPromptForStep(string $step, array $data): string
    {
        $label = $this->getEntryLabel();

        return match($step) {
            'select_entry' => $this->buildSelectEntryPrompt($label),
            'confirm' => $this->buildConfirmPrompt($data, $label),
            default => "Please continue..."
        };
    }

    protected function getEntryLabel(): string
    {
        if ($this->event && $this->event->template) {
            return $this->event->template->entry_label ?? 'Entry';
        }
        return 'Entry';
    }

    protected function buildSelectEntryPrompt(string $label): string
    {
        if (!$this->event) {
            return "Please select an event first to delete an {$label}.";
        }

        $entries = Entry::with(['participant', 'division'])
            ->where('event_id', $this->eventId)
            ->take(10)
            ->get();

        if ($entries->isEmpty()) {
            return "No {$label}s found in this event.";
        }

        $entryList = $entries->map(function($e, $index) {
            $participant = $e->participant?->name ?? 'Unknown';
            $voteCount = Vote::where('entry_id', $e->id)->count();
            $voteText = $voteCount > 0 ? " ({$voteCount} votes)" : '';
            return "**" . ($index + 1) . ".** {$e->name} by {$participant}{$voteText}";
        })->join("\n");

        return "⚠️ **Delete {$label}**\n\nWhich {$label} would you like to remove?\n\n{$entryList}\n\n*Enter the number, name, or ID*";
    }

    protected function buildConfirmPrompt(array $data, string $label): string
    {
        $entry = Entry::with(['participant', 'division'])->find($data['select_entry']);
        $voteCount = Vote::where('entry_id', $entry->id)->count();
        $participant = $entry->participant?->name ?? 'Unknown';
        $division = $entry->division?->name ?? null;

        $participantLabel = $this->event && $this->event->template
            ? ($this->event->template->participant_label ?? 'Participant')
            : 'Participant';

        $impactWarning = '';
        if ($voteCount > 0) {
            $impactWarning = "\n\n**This will also archive:**\n" .
                            "• {$voteCount} vote" . ($voteCount > 1 ? 's' : '') . "\n";
        }

        $divisionText = $division ? "\n- **Division:** {$division}" : '';

        return "⚠️ **Are you sure you want to delete {$label} \"{$entry->name}\"?**\n\n" .
               "- **{$label}:** {$entry->name}\n" .
               "- **{$participantLabel}:** {$participant}{$divisionText}\n" .
               "- **Event:** {$this->event->name}" .
               $impactWarning . "\n\n" .
               "Type **yes** to confirm deletion or **no** to cancel.\n\n" .
               "*Note: This is a soft delete - data can be recovered if needed.*";
    }

    public function validateStep(string $step, $input, array $data): array
    {
        return match($step) {
            'select_entry' => $this->validateEntrySelection($input),
            'confirm' => $this->validateConfirm($input),
            default => $this->validationError("Unknown step: {$step}")
        };
    }

    protected function validateEntrySelection($input): array
    {
        if (!$this->event) {
            return $this->validationError("Please select an event first.");
        }

        $entries = Entry::where('event_id', $this->eventId)->take(10)->get();

        // Check if numeric selection
        if (is_numeric($input)) {
            $index = (int)$input - 1;
            if ($index >= 0 && $index < $entries->count()) {
                return $this->validationSuccess($entries[$index]->id);
            }

            // Also check by ID directly
            $entry = Entry::where('event_id', $this->eventId)->where('id', (int)$input)->first();
            if ($entry) {
                return $this->validationSuccess($entry->id);
            }
        }

        // Check by name
        $entry = Entry::where('event_id', $this->eventId)
            ->where('name', 'like', "%{$input}%")
            ->first();
        if ($entry) {
            return $this->validationSuccess($entry->id);
        }

        return $this->validationError("Entry not found. Please enter a valid number, name, or ID.");
    }

    protected function validateConfirm($input): array
    {
        $input = strtolower(trim($input));

        if (in_array($input, ['yes', 'y', 'confirm', 'delete'])) {
            return $this->validationSuccess(true);
        }

        if (in_array($input, ['no', 'n', 'cancel'])) {
            return $this->validationError("Deletion cancelled. No changes were made.");
        }

        return $this->validationError("Please type **yes** to confirm deletion or **no** to cancel.");
    }

    public function getOptionsForStep(string $step, array $data): array
    {
        if ($step === 'select_entry' && $this->event) {
            return Entry::with('participant')
                ->where('event_id', $this->eventId)
                ->take(10)
                ->get()
                ->map(fn($e) => [
                    'label' => "{$e->name} by " . ($e->participant?->name ?? 'Unknown'),
                    'value' => $e->id,
                ])->toArray();
        }

        if ($step === 'confirm') {
            return [
                ['label' => 'Yes, delete', 'value' => 'yes'],
                ['label' => 'No, cancel', 'value' => 'no'],
            ];
        }

        return [];
    }

    public function canSkipStep(string $step): bool
    {
        return false;
    }

    public function execute(array $data): array
    {
        $label = $this->getEntryLabel();
        $entry = Entry::with('participant')->find($data['select_entry']);
        $entryName = $entry->name;
        $participantName = $entry->participant?->name ?? 'Unknown';
        $userId = Auth::id();

        // Count related votes before deletion
        $voteCount = Vote::where('entry_id', $entry->id)->count();

        // Build related deletions info
        $relatedDeletions = [];
        if ($voteCount > 0) {
            $relatedDeletions['vote_count'] = $voteCount;
        }

        // Record deletion in history
        DeletionHistory::recordDeletion(
            model: $entry,
            itemName: $entryName,
            itemType: $label,
            eventId: $this->eventId,
            deletedBy: $userId,
            reason: "Deleted via AI assistant",
            relatedDeletions: $relatedDeletions ?: null
        );

        // Soft delete votes
        Vote::where('entry_id', $entry->id)->update([
            'deleted_by' => $userId,
            'deleted_reason' => "Parent {$label} '{$entryName}' was deleted",
        ]);
        Vote::where('entry_id', $entry->id)->delete();

        // Soft delete the entry
        $entry->update([
            'deleted_by' => $userId,
            'deleted_reason' => "Deleted via AI assistant",
        ]);
        $entry->delete();

        $votesText = $voteCount > 0 ? "\n- **Votes archived:** {$voteCount}" : '';

        return [
            'message' => "**{$label} deleted successfully!**\n\n" .
                        "- **Deleted:** {$entryName} by {$participantName}{$votesText}\n\n" .
                        "*This was a soft delete - data has been archived and can be recovered if needed.*\n\n" .
                        "What would you like to do next?",
            'data' => ['deleted' => $entryName, 'votes_archived' => $voteCount],
            'suggestedActions' => [
                ['label' => "Add a new {$label}", 'prompt' => 'add an entry'],
                ['label' => "View all {$label}s", 'prompt' => 'show entries'],
                ['label' => 'Show results', 'prompt' => 'show results'],
            ],
        ];
    }
}
