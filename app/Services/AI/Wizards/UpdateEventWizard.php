<?php

namespace App\Services\AI\Wizards;

use App\Models\Event;

class UpdateEventWizard extends BaseWizard
{
    public function getPromptForStep(string $step, array $data): string
    {
        return match($step) {
            'select_event' => $this->buildSelectEventPrompt(),
            'field' => $this->buildFieldSelectionPrompt($data),
            'value' => $this->buildValuePrompt($data),
            'confirm' => $this->buildConfirmPrompt($data),
            default => "Please continue..."
        };
    }

    protected function buildSelectEventPrompt(): string
    {
        if ($this->event) {
            return "Do you want to update **{$this->event->name}**?\n\n" .
                   "Type **yes** to continue or enter a different event name/ID.";
        }

        $events = Event::with('template')->take(10)->get();

        if ($events->isEmpty()) {
            return "No events found. Would you like to create one instead?";
        }

        $eventList = $events->map(function($e, $index) {
            $status = $e->is_active ? '✅ Active' : '📝 Draft';
            return "**" . ($index + 1) . ".** {$e->name} ({$status})";
        })->join("\n");

        return "Which event would you like to update?\n\n{$eventList}\n\n*Enter the number, name, or ID*";
    }

    protected function buildFieldSelectionPrompt(array $data): string
    {
        $event = Event::find($data['select_event']);
        $eventName = $event ? $event->name : 'the event';

        return "What would you like to update for **{$eventName}**?\n\n" .
               "**1.** Name\n" .
               "**2.** Description\n" .
               "**3.** Event Date\n" .
               "**4.** Status (Activate/Deactivate)\n\n" .
               "*Enter the number or field name*";
    }

    protected function buildValuePrompt(array $data): string
    {
        $event = Event::find($data['select_event']);
        $field = $data['field'];

        return match($field) {
            'name' => "Current name: **{$event->name}**\n\nEnter the new name:",
            'description' => "Current description: " . ($event->description ?: '*No description*') . "\n\nEnter the new description (or type **skip** to clear):",
            'event_date' => "Current date: " . ($event->event_date ? $event->event_date->format('M j, Y') : '*Not set*') . "\n\nEnter the new date (e.g., Jan 15, 2026):",
            'is_active' => "Current status: " . ($event->is_active ? '✅ Active' : '📝 Draft') . "\n\nType **activate** or **deactivate**:",
            default => "Enter the new value:"
        };
    }

    protected function buildConfirmPrompt(array $data): string
    {
        $event = Event::find($data['select_event']);
        $field = $data['field'];
        $newValue = $data['value'];

        $fieldLabel = match($field) {
            'name' => 'Name',
            'description' => 'Description',
            'event_date' => 'Event Date',
            'is_active' => 'Status',
            default => ucfirst($field)
        };

        $displayValue = match($field) {
            'is_active' => $newValue ? '✅ Active' : '📝 Draft',
            'event_date' => $newValue ? date('M j, Y', strtotime($newValue)) : 'Not set',
            'description' => $newValue ?: '*Cleared*',
            default => $newValue
        };

        return "**Confirm Update:**\n\n" .
               "- **Event:** {$event->name}\n" .
               "- **Field:** {$fieldLabel}\n" .
               "- **New Value:** {$displayValue}\n\n" .
               "Type **yes** to confirm or **no** to cancel.";
    }

    public function validateStep(string $step, $input, array $data): array
    {
        return match($step) {
            'select_event' => $this->validateEventSelection($input),
            'field' => $this->validateFieldSelection($input),
            'value' => $this->validateValue($input, $data),
            'confirm' => $this->validateConfirm($input),
            default => $this->validationError("Unknown step: {$step}")
        };
    }

    protected function validateEventSelection($input): array
    {
        // If we have a current event and user says yes
        if ($this->event && in_array(strtolower(trim($input)), ['yes', 'y'])) {
            return $this->validationSuccess($this->event->id);
        }

        $events = Event::take(10)->get();

        // Check if numeric selection
        if (is_numeric($input)) {
            $index = (int)$input - 1;
            if ($index >= 0 && $index < $events->count()) {
                return $this->validationSuccess($events[$index]->id);
            }

            // Also check by ID directly
            $event = Event::find((int)$input);
            if ($event) {
                return $this->validationSuccess($event->id);
            }
        }

        // Check by name
        $event = Event::where('name', 'like', "%{$input}%")->first();
        if ($event) {
            return $this->validationSuccess($event->id);
        }

        return $this->validationError("Event not found. Please enter a valid number, name, or ID.");
    }

    protected function validateFieldSelection($input): array
    {
        $input = strtolower(trim($input));

        $fieldMap = [
            '1' => 'name',
            '2' => 'description',
            '3' => 'event_date',
            '4' => 'is_active',
            'name' => 'name',
            'description' => 'description',
            'date' => 'event_date',
            'event_date' => 'event_date',
            'status' => 'is_active',
            'active' => 'is_active',
            'activate' => 'is_active',
            'deactivate' => 'is_active',
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
            'event_date' => $this->validateDate($input),
            'is_active' => $this->validateStatus($input),
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

    protected function validateDate($input): array
    {
        if (empty($input) || strtolower(trim($input)) === 'skip') {
            return $this->validationSuccess(null);
        }

        $timestamp = strtotime($input);
        if (!$timestamp) {
            return $this->validationError("Please enter a valid date (e.g., Jan 15, 2026).");
        }

        return $this->validationSuccess(date('Y-m-d', $timestamp));
    }

    protected function validateStatus($input): array
    {
        $input = strtolower(trim($input));

        if (in_array($input, ['activate', 'active', 'yes', 'on', '1', 'true'])) {
            return $this->validationSuccess(true);
        }

        if (in_array($input, ['deactivate', 'inactive', 'no', 'off', '0', 'false', 'draft'])) {
            return $this->validationSuccess(false);
        }

        return $this->validationError("Please type **activate** or **deactivate**.");
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
        if ($step === 'field') {
            return [
                ['label' => 'Name', 'value' => '1'],
                ['label' => 'Description', 'value' => '2'],
                ['label' => 'Event Date', 'value' => '3'],
                ['label' => 'Status', 'value' => '4'],
            ];
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
        return false;
    }

    public function execute(array $data): array
    {
        $event = Event::find($data['select_event']);
        $field = $data['field'];
        $value = $data['value'];

        $oldValue = $event->$field;
        $event->$field = $value;
        $event->save();

        $fieldLabel = match($field) {
            'name' => 'Name',
            'description' => 'Description',
            'event_date' => 'Event Date',
            'is_active' => 'Status',
            default => ucfirst($field)
        };

        $displayValue = match($field) {
            'is_active' => $value ? '✅ Active' : '📝 Draft',
            'event_date' => $value ? date('M j, Y', strtotime($value)) : 'Not set',
            default => $value ?: '*Cleared*'
        };

        return [
            'message' => "**Event updated successfully!**\n\n" .
                        "- **Event:** {$event->name}\n" .
                        "- **{$fieldLabel}:** {$displayValue}\n\n" .
                        "What would you like to do next?",
            'data' => $event->toArray(),
            'suggestedActions' => [
                ['label' => 'Update another field', 'prompt' => 'update event'],
                ['label' => 'View event details', 'prompt' => 'show event details'],
                ['label' => 'Add a participant', 'prompt' => 'add a participant'],
            ],
        ];
    }
}
