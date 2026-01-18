<?php

namespace App\Services\AI\Wizards;

use App\Models\Division;

class AddDivisionWizard extends BaseWizard
{
    public function getPromptForStep(string $step, array $data): string
    {
        return match($step) {
            'type' => $this->buildTypePrompt(),
            'code' => "What code should this division have?\n\n*Examples: P, A, PRO, AMATEUR*\n*(2-5 characters)*",
            'name' => "What is the full name of this division?\n\n*Example: \"Professional Division\"*",
            'confirm' => $this->buildConfirmPrompt($data),
            default => "Please continue..."
        };
    }

    protected function buildTypePrompt(): string
    {
        $prompt = "Let's add a new division!\n\nWhat type of division is this?\n\n";

        if ($this->event && $this->event->template && $this->event->template->division_types) {
            $types = is_string($this->event->template->division_types)
                ? json_decode($this->event->template->division_types, true)
                : $this->event->template->division_types;

            if (!empty($types)) {
                $typeList = collect($types)->map(function($t, $index) {
                    $name = is_array($t) ? ($t['name'] ?? $t['type'] ?? 'Unknown') : $t;
                    return "**" . ($index + 1) . ".** {$name}";
                })->join("\n");

                $prompt .= "Template types:\n{$typeList}\n\n";
            }
        }

        $prompt .= "*Enter the type (e.g., Professional, Amateur, etc.)*";
        return $prompt;
    }

    protected function buildConfirmPrompt(array $data): string
    {
        return "**Review new division:**\n\n" .
               "- **Type:** {$data['type']}\n" .
               "- **Code:** {$data['code']}\n" .
               "- **Name:** {$data['name']}\n\n" .
               "Type **yes** to add or **no** to cancel.";
    }

    public function validateStep(string $step, $input, array $data): array
    {
        return match($step) {
            'type' => $this->validateType($input),
            'code' => $this->validateCode($input),
            'name' => $this->validateName($input),
            'confirm' => $this->validateConfirm($input),
            default => $this->validationError("Unknown step: {$step}")
        };
    }

    protected function validateType($input): array
    {
        if (empty($input) || strlen(trim($input)) < 2) {
            return $this->validationError("Please enter a valid division type.");
        }

        // Check if template has predefined types
        if ($this->event && $this->event->template && $this->event->template->division_types) {
            $types = is_string($this->event->template->division_types)
                ? json_decode($this->event->template->division_types, true)
                : $this->event->template->division_types;

            if (!empty($types) && is_numeric($input)) {
                $index = (int)$input - 1;
                if ($index >= 0 && $index < count($types)) {
                    $t = $types[$index];
                    $typeName = is_array($t) ? ($t['name'] ?? $t['type'] ?? 'Unknown') : $t;
                    return $this->validationSuccess($typeName);
                }
            }
        }

        return $this->validationSuccess(trim($input));
    }

    protected function validateCode($input): array
    {
        if (empty($input)) {
            return $this->validationError("Please enter a division code.");
        }

        $code = strtoupper(trim($input));

        if (strlen($code) < 1 || strlen($code) > 5) {
            return $this->validationError("Code must be 1-5 characters.");
        }

        // Check for duplicates
        if ($this->event) {
            $exists = Division::where('event_id', $this->eventId)
                ->where('code', $code)
                ->exists();

            if ($exists) {
                return $this->validationError("Code '{$code}' already exists. Please use a different code.");
            }
        }

        return $this->validationSuccess($code);
    }

    protected function validateName($input): array
    {
        if (empty($input) || strlen(trim($input)) < 2) {
            return $this->validationError("Please enter a valid division name (at least 2 characters).");
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
            return $this->validationError("Cancelled. No division was added.");
        }

        return $this->validationError("Please type **yes** to confirm or **no** to cancel.");
    }

    public function getOptionsForStep(string $step, array $data): array
    {
        if ($step === 'type' && $this->event && $this->event->template && $this->event->template->division_types) {
            $types = is_string($this->event->template->division_types)
                ? json_decode($this->event->template->division_types, true)
                : $this->event->template->division_types;

            return collect($types)->map(function($t) {
                $name = is_array($t) ? ($t['name'] ?? $t['type'] ?? 'Unknown') : $t;
                return ['label' => $name, 'value' => $name];
            })->toArray();
        }

        if ($step === 'confirm') {
            return [
                ['label' => 'Yes, add division', 'value' => 'yes'],
                ['label' => 'No, cancel', 'value' => 'no'],
            ];
        }

        return [];
    }

    public function execute(array $data): array
    {
        $division = Division::create([
            'type' => $data['type'],
            'code' => $data['code'],
            'name' => $data['name'],
            'event_id' => $this->eventId,
        ]);

        return [
            'message' => "**Division added successfully!**\n\n" .
                        "- **Name:** {$division->name}\n" .
                        "- **Code:** {$division->code}\n" .
                        "- **Type:** {$division->type}\n\n" .
                        "Would you like to add participants to this division?",
            'data' => $division->toArray(),
            'suggestedActions' => [
                ['label' => 'Add a participant', 'prompt' => 'add a participant'],
                ['label' => 'Add another division', 'prompt' => 'add a division'],
                ['label' => 'View all divisions', 'prompt' => 'show divisions'],
            ],
        ];
    }
}
