<?php

namespace App\Services\AI\Wizards;

use App\Models\Entry;
use App\Models\Participant;
use App\Models\Division;

class UpdateEntryWizard extends BaseWizard
{
    public function getPromptForStep(string $step, array $data): string
    {
        $label = $this->getEntryLabel();

        return match($step) {
            'select_entry' => $this->buildSelectEntryPrompt($label),
            'field' => $this->buildFieldSelectionPrompt($data, $label),
            'value' => $this->buildValuePrompt($data, $label),
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

    protected function getParticipantLabel(): string
    {
        if ($this->event && $this->event->template) {
            return $this->event->template->participant_label ?? 'Participant';
        }
        return 'Participant';
    }

    protected function buildSelectEntryPrompt(string $label): string
    {
        if (!$this->event) {
            return "Please select an event first to update an {$label}.";
        }

        $entries = Entry::with(['participant', 'division'])
            ->where('event_id', $this->eventId)
            ->take(10)
            ->get();

        if ($entries->isEmpty()) {
            return "No {$label}s found in this event. Would you like to add one?";
        }

        $entryList = $entries->map(function($e, $index) {
            $participant = $e->participant?->name ?? 'Unknown';
            $division = $e->division?->name ?? '';
            $divisionText = $division ? " [{$division}]" : '';
            return "**" . ($index + 1) . ".** {$e->name} by {$participant}{$divisionText}";
        })->join("\n");

        return "Which {$label} would you like to update?\n\n{$entryList}\n\n*Enter the number, name, or ID*";
    }

    protected function buildFieldSelectionPrompt(array $data, string $label): string
    {
        $entry = Entry::find($data['select_entry']);
        $entryName = $entry ? $entry->name : "the {$label}";

        return "What would you like to update for **{$entryName}**?\n\n" .
               "**1.** Name\n" .
               "**2.** Description\n" .
               "**3.** Division\n" .
               "**4.** Entry Number\n\n" .
               "*Enter the number or field name*";
    }

    protected function buildValuePrompt(array $data, string $label): string
    {
        $entry = Entry::with(['participant', 'division'])->find($data['select_entry']);
        $field = $data['field'];

        return match($field) {
            'name' => "Current name: **{$entry->name}**\n\nEnter the new name:",
            'description' => "Current description: " . ($entry->description ?: '*No description*') . "\n\nEnter the new description (or type **skip** to clear):",
            'division_id' => $this->buildDivisionPrompt($entry),
            'entry_number' => "Current entry number: **{$entry->entry_number}**\n\nEnter the new entry number:",
            default => "Enter the new value:"
        };
    }

    protected function buildDivisionPrompt(Entry $entry): string
    {
        $currentDivision = $entry->division?->name ?? 'No division';

        if (!$this->event) {
            return "Current division: **{$currentDivision}**\n\nEnter the new division:";
        }

        $divisions = $this->event->divisions;

        if ($divisions->isEmpty()) {
            return "No divisions available for this event. Type **skip** to continue.";
        }

        $divisionList = $divisions->map(function($d, $index) {
            return "**" . ($index + 1) . ".** {$d->name} ({$d->code})";
        })->join("\n");

        return "Current division: **{$currentDivision}**\n\n" .
               "Select a new division:\n\n{$divisionList}\n\n" .
               "*Enter the number, code, or name (or type **skip** to clear)*";
    }

    protected function buildConfirmPrompt(array $data, string $label): string
    {
        $entry = Entry::find($data['select_entry']);
        $field = $data['field'];
        $newValue = $data['value'];

        $fieldLabel = match($field) {
            'name' => 'Name',
            'description' => 'Description',
            'division_id' => 'Division',
            'entry_number' => 'Entry Number',
            default => ucfirst(str_replace('_', ' ', $field))
        };

        $displayValue = match($field) {
            'division_id' => $newValue ? (Division::find($newValue)?->name ?? $newValue) : '*Cleared*',
            'description' => $newValue ?: '*Cleared*',
            default => $newValue
        };

        return "**Confirm Update:**\n\n" .
               "- **{$label}:** {$entry->name}\n" .
               "- **Field:** {$fieldLabel}\n" .
               "- **New Value:** {$displayValue}\n\n" .
               "Type **yes** to confirm or **no** to cancel.";
    }

    public function validateStep(string $step, $input, array $data): array
    {
        return match($step) {
            'select_entry' => $this->validateEntrySelection($input),
            'field' => $this->validateFieldSelection($input),
            'value' => $this->validateValue($input, $data),
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

    protected function validateFieldSelection($input): array
    {
        $input = strtolower(trim($input));

        $fieldMap = [
            '1' => 'name',
            '2' => 'description',
            '3' => 'division_id',
            '4' => 'entry_number',
            'name' => 'name',
            'description' => 'description',
            'division' => 'division_id',
            'division_id' => 'division_id',
            'entry_number' => 'entry_number',
            'number' => 'entry_number',
        ];

        if (isset($fieldMap[$input])) {
            return $this->validationSuccess($fieldMap[$input]);
        }

        return $this->validationError("Please select a valid field (1-4) or enter the field name.");
    }

    protected function validateValue($input, array $data): array
    {
        $field = $data['field'];

        return match($field) {
            'name' => $this->validateName($input),
            'description' => $this->validateDescription($input),
            'division_id' => $this->validateDivision($input),
            'entry_number' => $this->validateEntryNumber($input, $data),
            default => $this->validationSuccess($input)
        };
    }

    protected function validateName($input): array
    {
        if (empty($input) || strlen(trim($input)) < 2) {
            return $this->validationError("Please enter a valid name (at least 2 characters).");
        }
        return $this->validationSuccess(trim($input));
    }

    protected function validateDescription($input): array
    {
        if (empty($input) || strtolower(trim($input)) === 'skip') {
            return $this->validationSuccess(null);
        }
        return $this->validationSuccess(trim($input));
    }

    protected function validateDivision($input): array
    {
        if (empty($input) || strtolower(trim($input)) === 'skip') {
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

    protected function validateEntryNumber($input, array $data): array
    {
        if (!is_numeric($input)) {
            return $this->validationError("Please enter a valid number.");
        }

        $entryNumber = (int)$input;
        $currentEntryId = $data['select_entry'];

        // Check if number is already in use by another entry
        $existing = Entry::where('event_id', $this->eventId)
            ->where('entry_number', $entryNumber)
            ->where('id', '!=', $currentEntryId)
            ->first();

        if ($existing) {
            return $this->validationError("Entry number {$entryNumber} is already in use by '{$existing->name}'.");
        }

        return $this->validationSuccess($entryNumber);
    }

    protected function validateConfirm($input): array
    {
        $input = strtolower(trim($input));

        if (in_array($input, ['yes', 'y', 'confirm', 'update'])) {
            return $this->validationSuccess(true);
        }

        if (in_array($input, ['no', 'n', 'cancel'])) {
            return $this->validationError("Update cancelled.");
        }

        return $this->validationError("Please type **yes** to confirm or **no** to cancel.");
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

        if ($step === 'field') {
            return [
                ['label' => 'Name', 'value' => '1'],
                ['label' => 'Description', 'value' => '2'],
                ['label' => 'Division', 'value' => '3'],
                ['label' => 'Entry Number', 'value' => '4'],
            ];
        }

        if ($step === 'value' && isset($data['field']) && $data['field'] === 'division_id' && $this->event) {
            return $this->event->divisions->map(fn($d) => [
                'label' => "{$d->name} ({$d->code})",
                'value' => $d->id,
            ])->toArray();
        }

        if ($step === 'confirm') {
            return [
                ['label' => 'Yes, update', 'value' => 'yes'],
                ['label' => 'No, cancel', 'value' => 'no'],
            ];
        }

        return [];
    }

    public function canSkipStep(string $step): bool
    {
        return $step === 'value';
    }

    public function execute(array $data): array
    {
        $label = $this->getEntryLabel();
        $entry = Entry::find($data['select_entry']);
        $field = $data['field'];
        $value = $data['value'];

        $entry->$field = $value;
        $entry->save();

        $fieldLabel = match($field) {
            'name' => 'Name',
            'description' => 'Description',
            'division_id' => 'Division',
            'entry_number' => 'Entry Number',
            default => ucfirst(str_replace('_', ' ', $field))
        };

        $displayValue = match($field) {
            'division_id' => $value ? (Division::find($value)?->name ?? $value) : '*Cleared*',
            'description' => $value ?: '*Cleared*',
            default => $value
        };

        return [
            'message' => "**{$label} updated successfully!**\n\n" .
                        "- **{$label}:** {$entry->name}\n" .
                        "- **{$fieldLabel}:** {$displayValue}\n\n" .
                        "What would you like to do next?",
            'data' => $entry->toArray(),
            'suggestedActions' => [
                ['label' => "Update another {$label}", 'prompt' => 'update entry'],
                ['label' => "View all {$label}s", 'prompt' => 'show entries'],
                ['label' => 'Show results', 'prompt' => 'show results'],
            ],
        ];
    }
}
