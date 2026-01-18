<?php

namespace App\Services\AI\Wizards;

use App\Models\Entry;
use App\Models\Participant;
use App\Models\Division;

class AddEntryWizard extends BaseWizard
{
    public function getPromptForStep(string $step, array $data): string
    {
        $entryLabel = $this->getEntryLabel();
        $participantLabel = $this->getParticipantLabel();

        return match($step) {
            'participant' => $this->buildParticipantPrompt(),
            'division' => $this->buildDivisionPrompt($data),
            'name' => "What is the name of this {$entryLabel}?\n\n*Example: \"Grandmother's Secret Recipe\"*",
            'confirm' => $this->buildConfirmPrompt($data),
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

    protected function getParticipantLabel(): string
    {
        if ($this->event && $this->event->template) {
            return $this->event->template->participant_label ?? 'Participant';
        }
        return 'Participant';
    }

    protected function buildParticipantPrompt(): string
    {
        $label = $this->getParticipantLabel();
        $entryLabel = $this->getEntryLabel();

        if (!$this->event) {
            return "Who is submitting this {$entryLabel}?\n\n*Enter the {$label}'s name or ID*";
        }

        $participants = $this->event->participants()->take(10)->get();

        if ($participants->isEmpty()) {
            return "No {$label}s registered yet. Would you like to add one first?\n\n*Type the {$label}'s name to create a new one, or type 'cancel'*";
        }

        $list = $participants->map(function($p, $index) {
            return "**" . ($index + 1) . ".** {$p->name}";
        })->join("\n");

        $moreText = $this->event->participants()->count() > 10 ? "\n\n*... and more. Type a name to search.*" : "";

        return "Who is submitting this {$entryLabel}?\n\n" .
               $list . $moreText . "\n\n" .
               "*Enter the number or name*";
    }

    protected function buildDivisionPrompt(array $data): string
    {
        if (!$this->event) {
            return "Which division is this entry for?\n\n*(Enter division code or name)*";
        }

        $divisions = $this->event->divisions;

        if ($divisions->isEmpty()) {
            return "No divisions set up. Type **skip** to continue.";
        }

        $divisionList = $divisions->map(function($d, $index) {
            return "**" . ($index + 1) . ".** {$d->name} ({$d->code})";
        })->join("\n");

        return "Which division is this entry for?\n\n" .
               $divisionList . "\n\n" .
               "*Enter the number, code, or name*";
    }

    protected function buildConfirmPrompt(array $data): string
    {
        $entryLabel = $this->getEntryLabel();
        $participantLabel = $this->getParticipantLabel();

        $participant = Participant::find($data['participant']);
        $participantName = $participant ? $participant->name : 'Unknown';

        $divisionText = 'Not assigned';
        if (!empty($data['division'])) {
            $division = Division::find($data['division']);
            $divisionText = $division ? "{$division->name} ({$division->code})" : 'Unknown';
        }

        return "**Review new {$entryLabel}:**\n\n" .
               "- **Name:** {$data['name']}\n" .
               "- **{$participantLabel}:** {$participantName}\n" .
               "- **Division:** {$divisionText}\n\n" .
               "Type **yes** to add or **no** to cancel.";
    }

    public function validateStep(string $step, $input, array $data): array
    {
        return match($step) {
            'participant' => $this->validateParticipant($input),
            'division' => $this->validateDivision($input),
            'name' => $this->validateName($input),
            'confirm' => $this->validateConfirm($input),
            default => $this->validationError("Unknown step: {$step}")
        };
    }

    protected function validateParticipant($input): array
    {
        if (empty($input)) {
            return $this->validationError("Please select or enter a participant.");
        }

        if (!$this->event) {
            return $this->validationError("No event context. Please select an event first.");
        }

        $participants = $this->event->participants;

        // Check if numeric selection
        if (is_numeric($input)) {
            $index = (int)$input - 1;
            $limitedList = $participants->take(10);
            if ($index >= 0 && $index < $limitedList->count()) {
                return $this->validationSuccess($limitedList[$index]->id);
            }
        }

        // Check by name (partial match)
        $participant = $participants->first(function($p) use ($input) {
            return stripos($p->name, $input) !== false;
        });

        if ($participant) {
            return $this->validationSuccess($participant->id);
        }

        return $this->validationError("Participant not found. Please enter a valid number or name.");
    }

    protected function validateDivision($input): array
    {
        if (empty($input)) {
            return $this->validationSuccess(null);
        }

        if (!$this->event) {
            return $this->validationSuccess(null);
        }

        $divisions = $this->event->divisions;

        if ($divisions->isEmpty()) {
            return $this->validationSuccess(null);
        }

        // Check if numeric selection
        if (is_numeric($input)) {
            $index = (int)$input - 1;
            if ($index >= 0 && $index < $divisions->count()) {
                return $this->validationSuccess($divisions[$index]->id);
            }
        }

        // Check by code or name
        $division = $divisions->first(function($d) use ($input) {
            return strtolower($d->code) === strtolower($input) ||
                   stripos($d->name, $input) !== false;
        });

        if ($division) {
            return $this->validationSuccess($division->id);
        }

        return $this->validationError("Division not found. Please enter a valid number, code, or name.");
    }

    protected function validateName($input): array
    {
        if (empty($input) || strlen(trim($input)) < 2) {
            return $this->validationError("Please enter a valid entry name (at least 2 characters).");
        }

        return $this->validationSuccess(trim($input));
    }

    protected function validateConfirm($input): array
    {
        $input = strtolower(trim($input));

        if (in_array($input, ['yes', 'y', 'confirm', 'add'])) {
            return $this->validationSuccess(true);
        }

        if (in_array($input, ['no', 'n', 'cancel'])) {
            return $this->validationError("Cancelled. No entry was added.");
        }

        return $this->validationError("Please type **yes** to confirm or **no** to cancel.");
    }

    public function getOptionsForStep(string $step, array $data): array
    {
        if ($step === 'participant' && $this->event) {
            return $this->event->participants->take(10)->map(fn($p) => [
                'label' => $p->name,
                'value' => $p->id,
            ])->toArray();
        }

        if ($step === 'division' && $this->event) {
            return $this->event->divisions->map(fn($d) => [
                'label' => "{$d->name} ({$d->code})",
                'value' => $d->id,
            ])->toArray();
        }

        if ($step === 'confirm') {
            return [
                ['label' => 'Yes, add entry', 'value' => 'yes'],
                ['label' => 'No, cancel', 'value' => 'no'],
            ];
        }

        return [];
    }

    public function canSkipStep(string $step): bool
    {
        return $step === 'division';
    }

    public function execute(array $data): array
    {
        $entryLabel = $this->getEntryLabel();

        // Generate entry number
        $division = Division::find($data['division']);
        $entryNumber = $this->generateEntryNumber($division);

        $entry = Entry::create([
            'name' => $data['name'],
            'participant_id' => $data['participant'],
            'division_id' => $data['division'],
            'event_id' => $this->eventId,
            'entry_number' => $entryNumber,
        ]);

        $participant = Participant::find($data['participant']);
        $divisionText = $division ? " in {$division->name}" : '';

        return [
            'message' => "**{$entryLabel} added successfully!**\n\n" .
                        "- **Name:** {$entry->name}\n" .
                        "- **Entry #:** {$entryNumber}\n" .
                        "- **By:** {$participant->name}{$divisionText}\n\n" .
                        "Would you like to add another entry?",
            'data' => $entry->toArray(),
            'suggestedActions' => [
                ['label' => 'Add another entry', 'prompt' => 'add an entry'],
                ['label' => 'View all entries', 'prompt' => 'show entries'],
                ['label' => 'View results', 'prompt' => 'show results'],
            ],
        ];
    }

    protected function generateEntryNumber(?Division $division): int
    {
        if (!$this->event) {
            return 1;
        }

        // Get max entry number for this event
        $maxNumber = Entry::where('event_id', $this->eventId)->max('entry_number') ?? 0;

        // If division has a type, use prefix ranges (P=1-99, A=101-199, etc.)
        if ($division && $division->type) {
            $typePrefix = match(strtolower(substr($division->type, 0, 1))) {
                'p' => 0,     // Professional: 1-99
                'a' => 100,   // Amateur: 101-199
                'n' => 200,   // Nature: 201-299
                'v' => 300,   // Vocal: 301-399
                default => 0
            };

            $maxInRange = Entry::where('event_id', $this->eventId)
                ->where('division_id', $division->id)
                ->max('entry_number') ?? $typePrefix;

            return max($maxInRange + 1, $typePrefix + 1);
        }

        return $maxNumber + 1;
    }
}
