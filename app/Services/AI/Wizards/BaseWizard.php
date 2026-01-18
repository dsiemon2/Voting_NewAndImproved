<?php

namespace App\Services\AI\Wizards;

use App\Models\Event;

abstract class BaseWizard
{
    protected ?int $eventId = null;
    protected ?Event $event = null;

    /**
     * Set the event context for this wizard
     */
    public function setEventId(?int $eventId): void
    {
        $this->eventId = $eventId;
        if ($eventId) {
            $this->event = Event::with(['template', 'divisions', 'participants'])->find($eventId);
        }
    }

    /**
     * Get the prompt for a specific step
     */
    abstract public function getPromptForStep(string $step, array $data): string;

    /**
     * Validate input for a specific step
     */
    abstract public function validateStep(string $step, $input, array $data): array;

    /**
     * Execute the wizard and create the record
     */
    abstract public function execute(array $data): array;

    /**
     * Get available options for a step (for button-based selection)
     */
    public function getOptionsForStep(string $step, array $data): array
    {
        return [];
    }

    /**
     * Check if a step can be skipped
     */
    public function canSkipStep(string $step): bool
    {
        return false;
    }

    /**
     * Helper to format validation success
     */
    protected function validationSuccess($value): array
    {
        return ['valid' => true, 'value' => $value];
    }

    /**
     * Helper to format validation error
     */
    protected function validationError(string $error): array
    {
        return ['valid' => false, 'error' => $error];
    }
}
