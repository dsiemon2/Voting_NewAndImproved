<?php

namespace App\Services\AI\Wizards;

use App\Models\Participant;
use App\Models\Division;

class AddParticipantWizard extends BaseWizard
{
    public function getPromptForStep(string $step, array $data): string
    {
        $label = $this->getParticipantLabel();

        return match($step) {
            'name' => "Let's add a new {$label}!\n\nWhat is the {$label}'s full name?",
            'email' => "What is {$data['name']}'s email address? (or type **skip**)",
            'division' => $this->buildDivisionPrompt($data),
            'confirm' => $this->buildConfirmPrompt($data),
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

    protected function buildDivisionPrompt(array $data): string
    {
        if (!$this->event) {
            return "Which division should {$data['name']} be assigned to?\n\n*(Enter division code or name, or type **skip**)*";
        }

        $divisions = $this->event->divisions;

        if ($divisions->isEmpty()) {
            return "No divisions set up for this event. Type **skip** to continue without a division.";
        }

        $divisionList = $divisions->map(function($d, $index) {
            return "**" . ($index + 1) . ".** {$d->name} ({$d->code})";
        })->join("\n");

        return "Which division should {$data['name']} be in?\n\n" .
               $divisionList . "\n\n" .
               "*Enter the number, code, or name (or type **skip**)*";
    }

    protected function buildConfirmPrompt(array $data): string
    {
        $label = $this->getParticipantLabel();
        $divisionText = 'Not assigned';

        if (!empty($data['division'])) {
            $division = Division::find($data['division']);
            $divisionText = $division ? $division->name : 'Unknown';
        }

        return "**Review new {$label}:**\n\n" .
               "- **Name:** {$data['name']}\n" .
               "- **Email:** " . ($data['email'] ?: 'Not provided') . "\n" .
               "- **Division:** {$divisionText}\n\n" .
               "Type **yes** to add or **no** to cancel.";
    }

    public function validateStep(string $step, $input, array $data): array
    {
        return match($step) {
            'name' => $this->validateName($input),
            'email' => $this->validateEmail($input),
            'division' => $this->validateDivision($input),
            'confirm' => $this->validateConfirm($input),
            default => $this->validationError("Unknown step: {$step}")
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
        if (empty($input)) {
            return $this->validationSuccess(null);
        }

        if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return $this->validationError("Please enter a valid email address or type **skip**.");
        }

        return $this->validationSuccess(trim($input));
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

    protected function validateConfirm($input): array
    {
        $input = strtolower(trim($input));

        if (in_array($input, ['yes', 'y', 'confirm', 'add'])) {
            return $this->validationSuccess(true);
        }

        if (in_array($input, ['no', 'n', 'cancel'])) {
            return $this->validationError("Cancelled. No participant was added.");
        }

        return $this->validationError("Please type **yes** to confirm or **no** to cancel.");
    }

    public function getOptionsForStep(string $step, array $data): array
    {
        if ($step === 'division' && $this->event) {
            return $this->event->divisions->map(fn($d) => [
                'label' => "{$d->name} ({$d->code})",
                'value' => $d->id,
            ])->toArray();
        }

        if ($step === 'confirm') {
            return [
                ['label' => 'Yes, add participant', 'value' => 'yes'],
                ['label' => 'No, cancel', 'value' => 'no'],
            ];
        }

        return [];
    }

    public function canSkipStep(string $step): bool
    {
        return in_array($step, ['email', 'division']);
    }

    public function execute(array $data): array
    {
        $label = $this->getParticipantLabel();

        $participant = Participant::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'division_id' => $data['division'],
            'event_id' => $this->eventId,
        ]);

        $divisionName = '';
        if ($data['division']) {
            $division = Division::find($data['division']);
            $divisionName = $division ? " ({$division->name})" : '';
        }

        return [
            'message' => "**{$label} added successfully!**\n\n" .
                        "- **Name:** {$participant->name}\n" .
                        "- **ID:** {$participant->id}{$divisionName}\n\n" .
                        "Would you like to add an entry for this {$label}?",
            'data' => $participant->toArray(),
            'suggestedActions' => [
                ['label' => 'Add an entry', 'prompt' => "add an entry for {$participant->name}"],
                ['label' => "Add another {$label}", 'prompt' => 'add a participant'],
                ['label' => 'View all participants', 'prompt' => 'show participants'],
            ],
        ];
    }
}
