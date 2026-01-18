<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Session;

class WizardStateMachine
{
    const WIZARDS = [
        // Create wizards
        'add_event' => [
            'steps' => ['template', 'name', 'description', 'dates', 'voting_type', 'confirm'],
            'handler' => \App\Services\AI\Wizards\AddEventWizard::class,
        ],
        'add_participant' => [
            'steps' => ['name', 'email', 'division', 'confirm'],
            'handler' => \App\Services\AI\Wizards\AddParticipantWizard::class,
        ],
        'add_entry' => [
            'steps' => ['participant', 'division', 'name', 'confirm'],
            'handler' => \App\Services\AI\Wizards\AddEntryWizard::class,
        ],
        'add_division' => [
            'steps' => ['type', 'code', 'name', 'confirm'],
            'handler' => \App\Services\AI\Wizards\AddDivisionWizard::class,
        ],
        // Update wizards
        'update_event' => [
            'steps' => ['select_event', 'field', 'value', 'confirm'],
            'handler' => \App\Services\AI\Wizards\UpdateEventWizard::class,
        ],
        'update_participant' => [
            'steps' => ['select_participant', 'field', 'value', 'confirm'],
            'handler' => \App\Services\AI\Wizards\UpdateParticipantWizard::class,
        ],
        'update_entry' => [
            'steps' => ['select_entry', 'field', 'value', 'confirm'],
            'handler' => \App\Services\AI\Wizards\UpdateEntryWizard::class,
        ],
        // Delete wizards
        'delete_participant' => [
            'steps' => ['select_participant', 'confirm'],
            'handler' => \App\Services\AI\Wizards\DeleteParticipantWizard::class,
        ],
        'delete_entry' => [
            'steps' => ['select_entry', 'confirm'],
            'handler' => \App\Services\AI\Wizards\DeleteEntryWizard::class,
        ],
    ];

    protected ?string $wizardType = null;
    protected int $currentStep = 0;
    protected array $collectedData = [];
    protected ?int $eventId = null;

    public function __construct()
    {
        $this->loadState();
    }

    /**
     * Check if a wizard is currently active
     */
    public function isActive(): bool
    {
        return $this->wizardType !== null;
    }

    /**
     * Get current wizard type
     */
    public function getWizardType(): ?string
    {
        return $this->wizardType;
    }

    /**
     * Start a new wizard
     */
    public function start(string $wizardType, ?int $eventId = null): array
    {
        if (!isset(self::WIZARDS[$wizardType])) {
            return [
                'success' => false,
                'message' => "Unknown wizard type: {$wizardType}",
            ];
        }

        $this->wizardType = $wizardType;
        $this->currentStep = 0;
        $this->collectedData = [];
        $this->eventId = $eventId;
        $this->saveState();

        return [
            'success' => true,
            'message' => $this->promptForCurrentStep(),
            'wizardState' => $this->getWizardState(),
        ];
    }

    /**
     * Process user input for current step
     */
    public function processInput(string $input): array
    {
        if (!$this->isActive()) {
            return [
                'success' => false,
                'message' => 'No active wizard',
            ];
        }

        // Handle cancel
        if (strtolower(trim($input)) === 'cancel') {
            $this->cancel();
            return [
                'success' => true,
                'complete' => true,
                'message' => "Wizard cancelled. How else can I help you?",
            ];
        }

        // Handle skip for optional fields
        if (strtolower(trim($input)) === 'skip') {
            $input = null;
        }

        $wizard = self::WIZARDS[$this->wizardType];
        $currentStepName = $wizard['steps'][$this->currentStep];

        // Get handler and validate input
        $handler = app($wizard['handler']);
        $handler->setEventId($this->eventId);

        $validation = $handler->validateStep($currentStepName, $input, $this->collectedData);

        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['error'],
                'wizardState' => $this->getWizardState(),
            ];
        }

        // Store validated value
        $this->collectedData[$currentStepName] = $validation['value'];
        $this->currentStep++;
        $this->saveState();

        // Check if wizard is complete
        if ($this->currentStep >= count($wizard['steps'])) {
            $result = $handler->execute($this->collectedData);
            $this->cancel(); // Clear wizard state

            return [
                'success' => true,
                'complete' => true,
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
                'suggestedActions' => $result['suggestedActions'] ?? [],
            ];
        }

        return [
            'success' => true,
            'complete' => false,
            'message' => $this->promptForCurrentStep(),
            'wizardState' => $this->getWizardState(),
        ];
    }

    /**
     * Cancel the current wizard
     */
    public function cancel(): void
    {
        $this->wizardType = null;
        $this->currentStep = 0;
        $this->collectedData = [];
        $this->eventId = null;
        Session::forget('ai_wizard_state');
        Session::save();
    }

    /**
     * Get prompt for current step
     */
    protected function promptForCurrentStep(): string
    {
        $wizard = self::WIZARDS[$this->wizardType];
        $handler = app($wizard['handler']);
        $handler->setEventId($this->eventId);
        $stepName = $wizard['steps'][$this->currentStep];

        return $handler->getPromptForStep($stepName, $this->collectedData);
    }

    /**
     * Get current wizard state for frontend
     */
    public function getWizardState(): array
    {
        if (!$this->isActive()) {
            return [];
        }

        $wizard = self::WIZARDS[$this->wizardType];
        $handler = app($wizard['handler']);
        $handler->setEventId($this->eventId);
        $stepName = $wizard['steps'][$this->currentStep];

        return [
            'type' => $this->wizardType,
            'currentStep' => $this->currentStep,
            'totalSteps' => count($wizard['steps']),
            'stepName' => $stepName,
            'collectedData' => $this->collectedData,
            'options' => $handler->getOptionsForStep($stepName, $this->collectedData),
            'canSkip' => $handler->canSkipStep($stepName),
        ];
    }

    /**
     * Load wizard state from session
     */
    protected function loadState(): void
    {
        $state = Session::get('ai_wizard_state', []);

        $this->wizardType = $state['wizardType'] ?? null;
        $this->currentStep = $state['currentStep'] ?? 0;
        $this->collectedData = $state['collectedData'] ?? [];
        $this->eventId = $state['eventId'] ?? null;
    }

    /**
     * Save wizard state to session
     */
    protected function saveState(): void
    {
        Session::put('ai_wizard_state', [
            'wizardType' => $this->wizardType,
            'currentStep' => $this->currentStep,
            'collectedData' => $this->collectedData,
            'eventId' => $this->eventId,
        ]);
        Session::save();
    }
}
