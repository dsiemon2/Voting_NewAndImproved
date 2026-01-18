<?php

namespace App\Imports;

use App\Models\Event;
use App\Models\Division;
use App\Models\Participant;
use App\Models\Entry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EventDataImport implements ToCollection, WithHeadingRow
{
    protected Event $event;
    protected string $importType;
    protected array $stats = [
        'divisions' => 0,
        'participants' => 0,
        'entries' => 0,
        'errors' => [],
    ];

    public function __construct(Event $event, string $importType = 'combined')
    {
        $this->event = $event;
        $this->importType = $importType;
    }

    public function collection(Collection $rows)
    {
        $divisionTypes = $this->event->template->getDivisionTypes();

        foreach ($rows as $index => $row) {
            try {
                $rowNum = $index + 2; // +2 for header row and 0-indexing

                // Get division code from first column
                $divisionCode = trim($row['division'] ?? $row[0] ?? '');
                $participantName = trim($row['participant'] ?? $row['chef'] ?? $row['name'] ?? $row[1] ?? '');

                // Get entry names from remaining columns
                $entryNames = [];
                foreach ($row as $key => $value) {
                    if (!in_array($key, ['division', 'participant', 'chef', 'name', 0, 1]) && !empty(trim($value ?? ''))) {
                        $entryNames[] = trim($value);
                    }
                }

                if (empty($divisionCode) && empty($participantName)) {
                    continue; // Skip empty rows
                }

                // Find or create division
                $division = null;
                if (!empty($divisionCode)) {
                    $division = Division::where('event_id', $this->event->id)
                        ->where('code', $divisionCode)
                        ->first();

                    if (!$division) {
                        // Determine division type from code prefix
                        $typePrefix = substr($divisionCode, 0, 1);
                        $typeName = 'Other';
                        foreach ($divisionTypes as $type) {
                            if ($type['code'] === $typePrefix) {
                                $typeName = $type['name'];
                                break;
                            }
                        }

                        $division = Division::create([
                            'event_id' => $this->event->id,
                            'name' => $typeName . ' ' . substr($divisionCode, 1),
                            'type' => $typeName,
                            'code' => $divisionCode,
                            'is_active' => true,
                        ]);
                        $this->stats['divisions']++;
                    }
                }

                // Create participant if name provided
                $participant = null;
                if (!empty($participantName) && $this->importType !== 'divisions') {
                    $participant = Participant::create([
                        'event_id' => $this->event->id,
                        'division_id' => $division?->id,
                        'name' => $participantName,
                        'is_active' => true,
                    ]);
                    $this->stats['participants']++;
                }

                // Create entries
                if ($this->importType !== 'participants' && $this->importType !== 'divisions') {
                    foreach ($entryNames as $entryName) {
                        if (empty($entryName)) continue;

                        // Get next entry number with retry for race conditions
                        $maxTries = 5;
                        $created = false;

                        for ($try = 0; $try < $maxTries && !$created; $try++) {
                            try {
                                $maxNumber = Entry::where('event_id', $this->event->id)
                                    ->max('entry_number') ?? 0;

                                Entry::create([
                                    'event_id' => $this->event->id,
                                    'division_id' => $division?->id,
                                    'participant_id' => $participant?->id,
                                    'entry_number' => $maxNumber + 1 + $try,
                                    'name' => $entryName,
                                    'is_active' => true,
                                ]);
                                $this->stats['entries']++;
                                $created = true;
                            } catch (\Illuminate\Database\QueryException $e) {
                                if ($try === $maxTries - 1) {
                                    throw $e;
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->stats['errors'][] = "Row {$rowNum}: " . $e->getMessage();
            }
        }
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}
