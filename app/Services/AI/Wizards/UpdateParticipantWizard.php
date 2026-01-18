<?php

namespace App\Services\AI\Wizards;

use App\Models\Participant;
use App\Models\Division;

class UpdateParticipantWizard extends BaseWizard
{
    public function getPromptForStep(string $step, array $data): string
    {
        $label = $this->getParticipantLabel();

        return match($step) {
            'select_participant' => $this->buildSelectParticipantPrompt($label),
            'field' => $this->buildFieldSelectionPrompt($data, $label),
            'value' => $this->buildValuePrompt($data, $label),
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
            return "Please select an event first to update a {$label}.";
        }

        $participants = Participant::where('event_id', $this->eventId)->take(10)->get();

        if ($participants->isEmpty()) {
            return "No {$label}s found in this event. Would you like to add one?";
        }

        $participantList = $participants->map(function($p, $index) {
            $division = $p->division_id ? Division::find($p->division_id)?->name : 'No division';
            return "**" . ($index + 1) . ".** {$p->name}" . ($p->email ? " ({$p->email})" : '') . " - {$division}";
        })->join("\n");

        return "Which {$label} would you like to update?\n\n{$participantList}\n\n*Enter the number, name, or ID*";
    }

    protected function buildFieldSelectionPrompt(array $data, string $label): string
    {
        $participant = Participant::find($data['select_participant']);
        $participantName = $participant ? $participant->name : "the {$label}";

        return "What would you like to update for **{$participantName}**?\n\n" .
               "**1.** Name\n" .
               "**2.** Email\n" .
               "**3.** Division\n\n" .
               "*Enter the number or field name*";
    }

    protected function buildValuePrompt(array $data, string $label): string
    {
        $participant = Participant::find($data['select_participant']);
        $field = $data['field'];

        return match($field) {
            'name' => "Current name: **{$participant->name}**\n\nEnter the new name:",
            'email' => "Current email: " . ($participant->email ?: '*Not set*') . "\n\nEnter the new email (or type **skip** to clear):",
            'division_id' => $this->buildDivisionPrompt($participant),
            default => "Enter the new value:"
        };
    }

    protected function buildDivisionPrompt(Participant $participant): string
    {
        $currentDivision = $participant->division_id
            ? Division::find($participant->division_id)?->name
            : 'No division';

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
        $participant = Participant::find($data['select_participant']);
        $field = $data['field'];
        $newValue = $data['value'];

        $fieldLabel = match($field) {
            'name' => 'Name',
            'email' => 'Email',
            'division_id' => 'Division',
            default => ucfirst(str_replace('_', ' ', $field))
        };

        $displayValue = match($field) {
            'division_id' => $newValue ? (Division::find($newValue)?->name ?? $newValue) : '*Cleared*',
            'email' => $newValue ?: '*Cleared*',
            default => $newValue
        };

        return "**Confirm Update:**\n\n" .
               "- **{$label}:** {$participant->name}\n" .
               "- **Field:** {$fieldLabel}\n" .
               "- **New Value:** {$displayValue}\n\n" .
               "Type **yes** to confirm or **no** to cancel.";
    }

    public function validateStep(string $step, $input, array $data): array
    {
        return match($step) {
            'select_participant' => $this->validateParticipantSelection($input),
            'field' => $this->validateFieldSelection($input),
            'value' => $this->validateValue($input, $data),
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

    protected function validateFieldSelection($input): array
    {
        $input = strtolower(trim($input));

        $fieldMap = [
            '1' => 'name',
            '2' => 'email',
            '3' => 'division_id',
            'name' => 'name',
            'email' => 'email',
            'division' => 'division_id',
            'division_id' => 'division_id',
        ];

        if (isset($fieldMap[$input])) {
            return $this->validationSuccess($fieldMap[$input]);
        }

        return $this->validationError("Please select a valid field (1-3) or enter the field name.");
    }

    protected function validateValue($input, array $data): array
    {
        $field = $data['field'];

        return match($field) {
            'name' => $this->validateName($input),
            'email' => $this->validateEmail($input),
            'division_id' => $this->validateDivision($input),
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

    protected function validateEmail($input): array
    {
        if (empty($input) || strtolower(trim($input)) === 'skip') {
            return $this->validationSuccess(null);
        }

        if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return $this->validationError("Please enter a valid email address or type **skip** to clear.");
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
        if ($step === 'select_participant' && $this->event) {
            return Participant::where('event_id', $this->eventId)
                ->take(10)
                ->get()
                ->map(fn($p) => [
                    'label' => $p->name,
                    'value' => $p->id,
                ])->toArray();
        }

        if ($step === 'field') {
            return [
                ['label' => 'Name', 'value' => '1'],
                ['label' => 'Email', 'value' => '2'],
                ['label' => 'Division', 'value' => '3'],
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
        $label = $this->getParticipantLabel();
        $participant = Participant::find($data['select_participant']);
        $field = $data['field'];
        $value = $data['value'];

        $participant->$field = $value;
        $participant->save();

        $fieldLabel = match($field) {
            'name' => 'Name',
            'email' => 'Email',
            'division_id' => 'Division',
            default => ucfirst(str_replace('_', ' ', $field))
        };

        $displayValue = match($field) {
            'division_id' => $value ? (Division::find($value)?->name ?? $value) : '*Cleared*',
            'email' => $value ?: '*Cleared*',
            default => $value
        };

        return [
            'message' => "**{$label} updated successfully!**\n\n" .
                        "- **{$label}:** {$participant->name}\n" .
                        "- **{$fieldLabel}:** {$displayValue}\n\n" .
                        "What would you like to do next?",
            'data' => $participant->toArray(),
            'suggestedActions' => [
                ['label' => "Update another {$label}", 'prompt' => 'update participant'],
                ['label' => "View all {$label}s", 'prompt' => 'show participants'],
                ['label' => 'Add an entry', 'prompt' => 'add an entry'],
            ],
        ];
    }
}
