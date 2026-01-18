<?php

namespace App\Services\AI\Wizards;

use App\Models\Participant;
use App\Models\Entry;
use App\Models\Vote;
use App\Models\Division;
use App\Models\DeletionHistory;
use Illuminate\Support\Facades\Auth;

class DeleteParticipantWizard extends BaseWizard
{
    public function getPromptForStep(string $step, array $data): string
    {
        $label = $this->getParticipantLabel();

        return match($step) {
            'select_participant' => $this->buildSelectParticipantPrompt($label),
            'confirm' => $this->buildConfirmPrompt($data, $label),
            default => "Please continue..."
        };
    }

    protected function getParticipantLabel(): string
    {
        if ($this->event && $this->event->template) {
            return $this->event->template->participant_label ?? 'Participant';
        }
        return 'Participant';
    }

    protected function buildSelectParticipantPrompt(string $label): string
    {
        if (!$this->event) {
            return "Please select an event first to delete a {$label}.";
        }

        $participants = Participant::where('event_id', $this->eventId)->take(10)->get();

        if ($participants->isEmpty()) {
            return "No {$label}s found in this event.";
        }

        $participantList = $participants->map(function($p, $index) {
            $division = $p->division_id ? Division::find($p->division_id)?->name : 'No division';
            $entryCount = Entry::where('participant_id', $p->id)->count();
            return "**" . ($index + 1) . ".** {$p->name}" . ($entryCount > 0 ? " ({$entryCount} entries)" : '');
        })->join("\n");

        return "⚠️ **Delete {$label}**\n\nWhich {$label} would you like to remove?\n\n{$participantList}\n\n*Enter the number, name, or ID*";
    }

    protected function buildConfirmPrompt(array $data, string $label): string
    {
        $participant = Participant::find($data['select_participant']);
        $entryCount = Entry::where('participant_id', $participant->id)->count();
        $voteCount = Vote::whereIn('entry_id', Entry::where('participant_id', $participant->id)->pluck('id'))->count();

        $entryLabel = $this->event && $this->event->template
            ? ($this->event->template->entry_label ?? 'entry')
            : 'entry';

        $impactWarning = '';
        if ($entryCount > 0 || $voteCount > 0) {
            $impactWarning = "\n\n**This will also archive:**\n";
            if ($entryCount > 0) {
                $impactWarning .= "• {$entryCount} {$entryLabel}" . ($entryCount > 1 ? 's' : '') . "\n";
            }
            if ($voteCount > 0) {
                $impactWarning .= "• {$voteCount} vote" . ($voteCount > 1 ? 's' : '') . "\n";
            }
        }

        return "⚠️ **Are you sure you want to delete {$label} {$participant->name}?**\n\n" .
               "- **{$label}:** {$participant->name}\n" .
               "- **Email:** " . ($participant->email ?: 'Not set') . "\n" .
               "- **Event:** {$this->event->name}" .
               $impactWarning . "\n\n" .
               "Type **yes** to confirm deletion or **no** to cancel.\n\n" .
               "*Note: This is a soft delete - data can be recovered if needed.*";
    }

    public function validateStep(string $step, $input, array $data): array
    {
        return match($step) {
            'select_participant' => $this->validateParticipantSelection($input),
            'confirm' => $this->validateConfirm($input),
            default => $this->validationError("Unknown step: {$step}")
        };
    }

    protected function validateParticipantSelection($input): array
    {
        if (!$this->event) {
            return $this->validationError("Please select an event first.");
        }

        $participants = Participant::where('event_id', $this->eventId)->take(10)->get();

        // Check if numeric selection
        if (is_numeric($input)) {
            $index = (int)$input - 1;
            if ($index >= 0 && $index < $participants->count()) {
                return $this->validationSuccess($participants[$index]->id);
            }

            // Also check by ID directly
            $participant = Participant::where('event_id', $this->eventId)->where('id', (int)$input)->first();
            if ($participant) {
                return $this->validationSuccess($participant->id);
            }
        }

        // Check by name
        $participant = Participant::where('event_id', $this->eventId)
            ->where('name', 'like', "%{$input}%")
            ->first();
        if ($participant) {
            return $this->validationSuccess($participant->id);
        }

        return $this->validationError("Participant not found. Please enter a valid number, name, or ID.");
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
        if ($step === 'select_participant' && $this->event) {
            return Participant::where('event_id', $this->eventId)
                ->take(10)
                ->get()
                ->map(fn($p) => [
                    'label' => $p->name,
                    'value' => $p->id,
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
        $label = $this->getParticipantLabel();
        $participant = Participant::with('entries.votes')->find($data['select_participant']);
        $participantName = $participant->name;
        $userId = Auth::id();

        // Count related items before deletion
        $entries = Entry::where('participant_id', $participant->id)->get();
        $entryCount = $entries->count();
        $voteCount = Vote::whereIn('entry_id', $entries->pluck('id'))->count();

        // Build related deletions info
        $relatedDeletions = [];
        if ($entryCount > 0) {
            $relatedDeletions['entries'] = $entries->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->name,
            ])->toArray();
        }
        if ($voteCount > 0) {
            $relatedDeletions['vote_count'] = $voteCount;
        }

        // Record deletion in history
        DeletionHistory::recordDeletion(
            model: $participant,
            itemName: $participantName,
            itemType: $label,
            eventId: $this->eventId,
            deletedBy: $userId,
            reason: "Deleted via AI assistant",
            relatedDeletions: $relatedDeletions ?: null
        );

        // Soft delete votes for all entries
        foreach ($entries as $entry) {
            Vote::where('entry_id', $entry->id)->update([
                'deleted_by' => $userId,
                'deleted_reason' => "Parent {$label} '{$participantName}' was deleted",
            ]);
            Vote::where('entry_id', $entry->id)->delete();
        }

        // Soft delete entries
        Entry::where('participant_id', $participant->id)->update([
            'deleted_by' => $userId,
            'deleted_reason' => "Parent {$label} '{$participantName}' was deleted",
        ]);
        Entry::where('participant_id', $participant->id)->delete();

        // Soft delete the participant
        $participant->update([
            'deleted_by' => $userId,
            'deleted_reason' => "Deleted via AI assistant",
        ]);
        $participant->delete();

        $entriesText = $entryCount > 0 ? "\n- **Entries archived:** {$entryCount}" : '';
        $votesText = $voteCount > 0 ? "\n- **Votes archived:** {$voteCount}" : '';

        return [
            'message' => "**{$label} deleted successfully!**\n\n" .
                        "- **Deleted:** {$participantName}{$entriesText}{$votesText}\n\n" .
                        "*This was a soft delete - data has been archived and can be recovered if needed.*\n\n" .
                        "What would you like to do next?",
            'data' => ['deleted' => $participantName, 'entries_archived' => $entryCount, 'votes_archived' => $voteCount],
            'suggestedActions' => [
                ['label' => "Add a new {$label}", 'prompt' => 'add a participant'],
                ['label' => "View all {$label}s", 'prompt' => 'show participants'],
                ['label' => 'Show event stats', 'prompt' => 'show statistics'],
            ],
        ];
    }
}
