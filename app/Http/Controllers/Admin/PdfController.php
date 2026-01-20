<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PdfController extends Controller
{
    /**
     * Generate ballot PDF for an event.
     */
    public function ballot(Event $event)
    {
        $event->load(['template', 'divisions.entries', 'votingConfig.votingType.placeConfigs']);

        $votingType = $event->votingConfig?->votingType;
        $placeConfigs = $votingType?->getPlaceConfigs() ?? [];

        // Convert place configs to key-value array
        $places = [];
        foreach ($placeConfigs as $config) {
            $places[$config['place']] = $config;
        }

        // Group divisions by type
        $divisionsByType = $event->divisions->groupBy('type');

        $pdf = Pdf::loadView('pdf.ballot', [
            'event' => $event,
            'votingType' => $votingType,
            'places' => $places,
            'divisionsByType' => $divisionsByType,
        ]);

        $filename = 'ballot-' . str_replace(' ', '-', strtolower($event->name)) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate printable ballot sheets (multiple per page).
     */
    public function ballotSheets(Event $event, int $perPage = 4)
    {
        $event->load(['template', 'divisions.entries', 'votingConfig.votingType.placeConfigs']);

        $votingType = $event->votingConfig?->votingType;
        $placeConfigs = $votingType?->getPlaceConfigs() ?? [];

        $places = [];
        foreach ($placeConfigs as $config) {
            $places[$config['place']] = $config;
        }

        $divisionsByType = $event->divisions->groupBy('type');

        $pdf = Pdf::loadView('pdf.ballot-sheets', [
            'event' => $event,
            'votingType' => $votingType,
            'places' => $places,
            'divisionsByType' => $divisionsByType,
            'perPage' => $perPage,
        ]);

        $filename = 'ballot-sheets-' . str_replace(' ', '-', strtolower($event->name)) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate results PDF for an event.
     */
    public function results(Event $event)
    {
        $event->load([
            'template',
            'divisions',
            'entries.participant',
            'entries.division',
            'votingConfig.votingType',
        ]);

        // Get results data
        $results = DB::table('votes')
            ->select([
                'entries.id as entry_id',
                'entries.name as entry_name',
                'entries.entry_number',
                'divisions.id as division_id',
                'divisions.name as division_name',
                'divisions.code as division_code',
                'divisions.type as division_type',
                'participants.name as participant_name',
                DB::raw('SUM(votes.final_points) as total_points'),
                DB::raw('COUNT(votes.id) as vote_count'),
                DB::raw('SUM(CASE WHEN votes.place = 1 THEN 1 ELSE 0 END) as first_place_count'),
                DB::raw('SUM(CASE WHEN votes.place = 2 THEN 1 ELSE 0 END) as second_place_count'),
                DB::raw('SUM(CASE WHEN votes.place = 3 THEN 1 ELSE 0 END) as third_place_count'),
            ])
            ->join('entries', 'votes.entry_id', '=', 'entries.id')
            ->leftJoin('divisions', 'entries.division_id', '=', 'divisions.id')
            ->leftJoin('participants', 'entries.participant_id', '=', 'participants.id')
            ->where('votes.event_id', $event->id)
            ->groupBy([
                'entries.id',
                'entries.name',
                'entries.entry_number',
                'divisions.id',
                'divisions.name',
                'divisions.code',
                'divisions.type',
                'participants.name',
            ])
            ->orderByDesc('total_points')
            ->get();

        // Group results by division type and sort each group by points descending
        $resultsByType = $results->groupBy('division_type')->map(function ($group) {
            return $group->sortByDesc('total_points')->values();
        });

        $pdf = Pdf::loadView('pdf.results', [
            'event' => $event,
            'resultsByType' => $resultsByType,
            'generatedAt' => now(),
        ]);

        $filename = 'results-' . str_replace(' ', '-', strtolower($event->name)) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate winner certificate PDF.
     */
    public function certificate(Event $event, int $place = 1, ?int $divisionId = null)
    {
        $event->load(['template']);

        // Get winner for the specified place
        $query = DB::table('votes')
            ->select([
                'entries.name as entry_name',
                'participants.name as participant_name',
                'divisions.name as division_name',
                'divisions.type as division_type',
                DB::raw('SUM(votes.final_points) as total_points'),
            ])
            ->join('entries', 'votes.entry_id', '=', 'entries.id')
            ->leftJoin('divisions', 'entries.division_id', '=', 'divisions.id')
            ->leftJoin('participants', 'entries.participant_id', '=', 'participants.id')
            ->where('votes.event_id', $event->id);

        if ($divisionId) {
            $query->where('entries.division_id', $divisionId);
        }

        $winner = $query->groupBy([
                'entries.id',
                'entries.name',
                'participants.name',
                'divisions.name',
                'divisions.type',
            ])
            ->orderByDesc('total_points')
            ->skip($place - 1)
            ->first();

        if (!$winner) {
            return back()->with('error', 'No results found for this place.');
        }

        $placeLabels = [
            1 => '1st Place',
            2 => '2nd Place',
            3 => '3rd Place',
        ];

        $pdf = Pdf::loadView('pdf.certificate', [
            'event' => $event,
            'winner' => $winner,
            'place' => $place,
            'placeLabel' => $placeLabels[$place] ?? "{$place}th Place",
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('letter', 'landscape');

        $filename = 'certificate-' . $place . '-' . str_replace(' ', '-', strtolower($event->name)) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate entries list PDF.
     */
    public function entriesList(Event $event)
    {
        $event->load([
            'template',
            'divisions',
            'entries.participant',
            'entries.division',
        ]);

        // Group entries by division
        $entriesByDivision = $event->entries->groupBy('division_id');

        $pdf = Pdf::loadView('pdf.entries-list', [
            'event' => $event,
            'entriesByDivision' => $entriesByDivision,
            'generatedAt' => now(),
        ]);

        $filename = 'entries-' . str_replace(' ', '-', strtolower($event->name)) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate event summary report PDF.
     */
    public function summary(Event $event)
    {
        $event->load([
            'template',
            'divisions',
            'entries.participant',
            'votingConfig.votingType',
        ]);

        // Get statistics
        $stats = [
            'total_divisions' => $event->divisions->count(),
            'total_entries' => $event->entries->count(),
            'total_participants' => $event->entries->pluck('participant_id')->unique()->count(),
            'total_votes' => DB::table('votes')->where('event_id', $event->id)->count(),
            'unique_voters' => DB::table('votes')->where('event_id', $event->id)->distinct('user_id')->count('user_id'),
        ];

        // Get top 3 per division type
        $topResults = DB::table('votes')
            ->select([
                'entries.name as entry_name',
                'divisions.type as division_type',
                'participants.name as participant_name',
                DB::raw('SUM(votes.final_points) as total_points'),
            ])
            ->join('entries', 'votes.entry_id', '=', 'entries.id')
            ->leftJoin('divisions', 'entries.division_id', '=', 'divisions.id')
            ->leftJoin('participants', 'entries.participant_id', '=', 'participants.id')
            ->where('votes.event_id', $event->id)
            ->groupBy(['entries.id', 'entries.name', 'divisions.type', 'participants.name'])
            ->orderByDesc('total_points')
            ->get()
            ->groupBy('division_type');

        $pdf = Pdf::loadView('pdf.summary', [
            'event' => $event,
            'stats' => $stats,
            'topResults' => $topResults,
            'generatedAt' => now(),
        ]);

        $filename = 'summary-' . str_replace(' ', '-', strtolower($event->name)) . '.pdf';

        return $pdf->download($filename);
    }
}
