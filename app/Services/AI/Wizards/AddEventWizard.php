<?php

namespace App\Services\AI\Wizards;

use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\VotingType;
use App\Models\EventVotingConfig;
use Carbon\Carbon;

class AddEventWizard extends BaseWizard
{
    public function getPromptForStep(string $step, array $data): string
    {
        return match($step) {
            'template' => $this->buildTemplatePrompt(),
            'name' => "What would you like to name this event?\n\n*Example: \"2026 Annual Soup Cookoff\"*",
            'description' => "Add a description for this event (or type **skip**):",
            'dates' => "When should this event run?\n\n" .
                      "Enter a date (or **skip** for no date):\n" .
                      "*Format: MM/DD/YYYY*\n" .
                      "*Example: 01/15/2026*",
            'voting_type' => $this->buildVotingTypePrompt(),
            'confirm' => $this->buildConfirmPrompt($data),
            default => "Please continue..."
        };
    }

    protected function buildTemplatePrompt(): string
    {
        $templates = EventTemplate::all();

        $templateList = $templates->map(function($t, $index) {
            return "**" . ($index + 1) . ".** {$t->name}";
        })->join("\n");

        return "Let's create a new event!\n\n" .
               "Which template would you like to use?\n\n" .
               $templateList . "\n\n" .
               "*Enter the number or name of the template*";
    }

    protected function buildVotingTypePrompt(): string
    {
        $types = VotingType::with('placeConfigs')->get();

        $typeList = $types->map(function($t, $index) {
            $points = $t->placeConfigs->pluck('points')->join('-');
            return "**" . ($index + 1) . ".** {$t->name}" . ($points ? " ({$points})" : '');
        })->join("\n");

        return "Which voting system should this event use?\n\n" .
               $typeList . "\n\n" .
               "*Enter the number or name*";
    }

    protected function buildConfirmPrompt(array $data): string
    {
        $template = EventTemplate::find($data['template']);
        $votingType = VotingType::find($data['voting_type']);

        $summary = "**Review your new event:**\n\n" .
                   "- **Name:** {$data['name']}\n" .
                   "- **Template:** {$template->name}\n" .
                   "- **Description:** " . ($data['description'] ?: 'None') . "\n" .
                   "- **Date:** " . ($data['dates'] ?: 'Not set') . "\n" .
                   "- **Voting Type:** {$votingType->name}\n\n" .
                   "Type **yes** to create this event or **no** to cancel.";

        return $summary;
    }

    public function validateStep(string $step, $input, array $data): array
    {
        return match($step) {
            'template' => $this->validateTemplate($input),
            'name' => $this->validateName($input),
            'description' => $this->validationSuccess($input),
            'dates' => $this->validateDates($input),
            'voting_type' => $this->validateVotingType($input),
            'confirm' => $this->validateConfirm($input),
            default => $this->validationError("Unknown step: {$step}")
        };
    }

    protected function validateTemplate($input): array
    {
        if (empty($input)) {
            return $this->validationError("Please select a template.");
        }

        $templates = EventTemplate::all();

        // Check if numeric selection
        if (is_numeric($input)) {
            $index = (int)$input - 1;
            if ($index >= 0 && $index < $templates->count()) {
                return $this->validationSuccess($templates[$index]->id);
            }
        }

        // Check by name
        $template = $templates->first(function($t) use ($input) {
            return stripos($t->name, $input) !== false;
        });

        if ($template) {
            return $this->validationSuccess($template->id);
        }

        return $this->validationError("Template not found. Please enter a valid number or name.");
    }

    protected function validateName($input): array
    {
        if (empty($input) || strlen(trim($input)) < 3) {
            return $this->validationError("Please enter a valid event name (at least 3 characters).");
        }

        return $this->validationSuccess(trim($input));
    }

    protected function validateDates($input): array
    {
        if (empty($input)) {
            return $this->validationSuccess(null);
        }

        try {
            $date = Carbon::createFromFormat('m/d/Y', trim($input));
            return $this->validationSuccess($date->format('Y-m-d'));
        } catch (\Exception $e) {
            return $this->validationError("Invalid date format. Please use MM/DD/YYYY (e.g., 01/15/2026).");
        }
    }

    protected function validateVotingType($input): array
    {
        if (empty($input)) {
            return $this->validationError("Please select a voting type.");
        }

        $types = VotingType::all();

        // Check if numeric selection
        if (is_numeric($input)) {
            $index = (int)$input - 1;
            if ($index >= 0 && $index < $types->count()) {
                return $this->validationSuccess($types[$index]->id);
            }
        }

        // Check by name
        $type = $types->first(function($t) use ($input) {
            return stripos($t->name, $input) !== false;
        });

        if ($type) {
            return $this->validationSuccess($type->id);
        }

        return $this->validationError("Voting type not found. Please enter a valid number or name.");
    }

    protected function validateConfirm($input): array
    {
        $input = strtolower(trim($input));

        if (in_array($input, ['yes', 'y', 'confirm', 'create'])) {
            return $this->validationSuccess(true);
        }

        if (in_array($input, ['no', 'n', 'cancel'])) {
            return $this->validationError("Event creation cancelled.");
        }

        return $this->validationError("Please type **yes** to confirm or **no** to cancel.");
    }

    public function getOptionsForStep(string $step, array $data): array
    {
        return match($step) {
            'template' => EventTemplate::all()->map(fn($t) => [
                'label' => $t->name,
                'value' => $t->id,
            ])->toArray(),
            'voting_type' => VotingType::all()->map(fn($t) => [
                'label' => $t->name,
                'value' => $t->id,
            ])->toArray(),
            'confirm' => [
                ['label' => 'Yes, create event', 'value' => 'yes'],
                ['label' => 'No, cancel', 'value' => 'no'],
            ],
            default => []
        };
    }

    public function canSkipStep(string $step): bool
    {
        return in_array($step, ['description', 'dates']);
    }

    public function execute(array $data): array
    {
        $template = EventTemplate::find($data['template']);

        // Create the event
        $event = Event::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'event_date' => $data['dates'],
            'event_template_id' => $data['template'],
            'is_active' => false, // Start as draft
        ]);

        // Create voting config
        EventVotingConfig::create([
            'event_id' => $event->id,
            'voting_type_id' => $data['voting_type'],
        ]);

        // Copy default divisions from template if any
        if ($template->division_types) {
            $divisionTypes = is_string($template->division_types)
                ? json_decode($template->division_types, true)
                : $template->division_types;

            foreach ($divisionTypes as $index => $type) {
                $event->divisions()->create([
                    'name' => $type['name'] ?? $type,
                    'code' => $type['code'] ?? substr($type['name'] ?? $type, 0, 1) . ($index + 1),
                    'type' => $type['type'] ?? ($type['name'] ?? $type),
                ]);
            }
        }

        return [
            'message' => "**Event created successfully!**\n\n" .
                        "- **{$event->name}** (ID: {$event->id})\n" .
                        "- Template: {$template->name}\n" .
                        "- Status: Draft\n\n" .
                        "**Next steps:**\n" .
                        "1. Add participants\n" .
                        "2. Add entries\n" .
                        "3. Set event to 'active' when ready\n\n" .
                        "*Would you like me to help with any of these?*",
            'data' => $event->toArray(),
            'suggestedActions' => [
                ['label' => 'Add a participant', 'prompt' => 'add a participant'],
                ['label' => 'View this event', 'prompt' => 'show event details'],
                ['label' => 'Activate event', 'prompt' => 'activate the event'],
            ],
        ];
    }
}
