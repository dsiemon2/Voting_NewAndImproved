<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Display analytics dashboard for an event.
     */
    public function index(Event $event)
    {
        $event->load(['template', 'divisions', 'entries', 'votingConfig.votingType']);

        // Get basic stats
        $stats = $this->getBasicStats($event);

        // Get voting trends over time
        $votingTrends = $this->getVotingTrends($event);

        // Get division breakdown
        $divisionBreakdown = $this->getDivisionBreakdown($event);

        // Get top performers
        $topPerformers = $this->getTopPerformers($event);

        // Get voter engagement metrics
        $voterMetrics = $this->getVoterMetrics($event);

        // Get place distribution
        $placeDistribution = $this->getPlaceDistribution($event);

        return view('admin.analytics.index', [
            'event' => $event,
            'stats' => $stats,
            'votingTrends' => $votingTrends,
            'divisionBreakdown' => $divisionBreakdown,
            'topPerformers' => $topPerformers,
            'voterMetrics' => $voterMetrics,
            'placeDistribution' => $placeDistribution,
        ]);
    }

    /**
     * Get basic statistics for the event.
     */
    private function getBasicStats(Event $event): array
    {
        $totalVotes = Vote::where('event_id', $event->id)->count();
        $uniqueVoters = Vote::where('event_id', $event->id)->distinct('user_id')->count('user_id');
        $totalEntries = $event->entries->count();
        $totalDivisions = $event->divisions->count();

        // Calculate participation rate
        $entriesWithVotes = Vote::where('event_id', $event->id)
            ->distinct('entry_id')
            ->count('entry_id');
        $participationRate = $totalEntries > 0 ? round(($entriesWithVotes / $totalEntries) * 100, 1) : 0;

        // Average votes per entry
        $avgVotesPerEntry = $totalEntries > 0 ? round($totalVotes / $totalEntries, 1) : 0;

        // Total points distributed
        $totalPoints = Vote::where('event_id', $event->id)->sum('final_points');

        return [
            'total_votes' => $totalVotes,
            'unique_voters' => $uniqueVoters,
            'total_entries' => $totalEntries,
            'total_divisions' => $totalDivisions,
            'entries_with_votes' => $entriesWithVotes,
            'participation_rate' => $participationRate,
            'avg_votes_per_entry' => $avgVotesPerEntry,
            'total_points' => $totalPoints,
        ];
    }

    /**
     * Get voting trends over time.
     */
    private function getVotingTrends(Event $event): array
    {
        $trends = Vote::where('event_id', $event->id)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as vote_count'),
                DB::raw('COUNT(DISTINCT user_id) as voter_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $trends->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M j'))->toArray(),
            'votes' => $trends->pluck('vote_count')->toArray(),
            'voters' => $trends->pluck('voter_count')->toArray(),
        ];
    }

    /**
     * Get breakdown by division.
     */
    private function getDivisionBreakdown(Event $event): array
    {
        $breakdown = DB::table('votes')
            ->select(
                'divisions.name as division_name',
                'divisions.type as division_type',
                DB::raw('COUNT(votes.id) as vote_count'),
                DB::raw('SUM(votes.final_points) as total_points'),
                DB::raw('COUNT(DISTINCT votes.entry_id) as entries_voted')
            )
            ->join('entries', 'votes.entry_id', '=', 'entries.id')
            ->leftJoin('divisions', 'entries.division_id', '=', 'divisions.id')
            ->where('votes.event_id', $event->id)
            ->groupBy('divisions.id', 'divisions.name', 'divisions.type')
            ->get();

        return [
            'labels' => $breakdown->pluck('division_name')->toArray(),
            'votes' => $breakdown->pluck('vote_count')->toArray(),
            'points' => $breakdown->pluck('total_points')->toArray(),
            'data' => $breakdown->toArray(),
        ];
    }

    /**
     * Get top performers.
     */
    private function getTopPerformers(Event $event, int $limit = 10): array
    {
        return DB::table('votes')
            ->select(
                'entries.id',
                'entries.name as entry_name',
                'entries.entry_number',
                'divisions.name as division_name',
                'participants.name as participant_name',
                DB::raw('SUM(votes.final_points) as total_points'),
                DB::raw('COUNT(votes.id) as vote_count'),
                DB::raw('SUM(CASE WHEN votes.place = 1 THEN 1 ELSE 0 END) as first_count'),
                DB::raw('SUM(CASE WHEN votes.place = 2 THEN 1 ELSE 0 END) as second_count'),
                DB::raw('SUM(CASE WHEN votes.place = 3 THEN 1 ELSE 0 END) as third_count')
            )
            ->join('entries', 'votes.entry_id', '=', 'entries.id')
            ->leftJoin('divisions', 'entries.division_id', '=', 'divisions.id')
            ->leftJoin('participants', 'entries.participant_id', '=', 'participants.id')
            ->where('votes.event_id', $event->id)
            ->groupBy('entries.id', 'entries.name', 'entries.entry_number', 'divisions.name', 'participants.name')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get voter engagement metrics.
     */
    private function getVoterMetrics(Event $event): array
    {
        // Votes per voter distribution
        $votesPerVoter = DB::table('votes')
            ->select('user_id', DB::raw('COUNT(*) as vote_count'))
            ->where('event_id', $event->id)
            ->groupBy('user_id')
            ->get();

        $avgVotesPerVoter = $votesPerVoter->avg('vote_count') ?? 0;
        $maxVotesPerVoter = $votesPerVoter->max('vote_count') ?? 0;

        // Voter activity by hour
        $hourlyActivity = Vote::where('event_id', $event->id)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as vote_count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Most active hour
        $peakHour = $hourlyActivity->sortByDesc('vote_count')->first();

        return [
            'avg_votes_per_voter' => round($avgVotesPerVoter, 1),
            'max_votes_per_voter' => $maxVotesPerVoter,
            'peak_hour' => $peakHour ? $peakHour->hour : null,
            'peak_hour_votes' => $peakHour ? $peakHour->vote_count : 0,
            'hourly_labels' => $hourlyActivity->pluck('hour')->map(fn($h) => sprintf('%02d:00', $h))->toArray(),
            'hourly_votes' => $hourlyActivity->pluck('vote_count')->toArray(),
        ];
    }

    /**
     * Get place distribution (how many 1st, 2nd, 3rd place votes).
     */
    private function getPlaceDistribution(Event $event): array
    {
        $distribution = Vote::where('event_id', $event->id)
            ->select('place', DB::raw('COUNT(*) as count'))
            ->groupBy('place')
            ->orderBy('place')
            ->get();

        return [
            'labels' => $distribution->pluck('place')->map(fn($p) => ordinal($p) . ' Place')->toArray(),
            'counts' => $distribution->pluck('count')->toArray(),
        ];
    }

    /**
     * Get analytics data as JSON (for AJAX updates).
     */
    public function data(Event $event)
    {
        return response()->json([
            'stats' => $this->getBasicStats($event),
            'votingTrends' => $this->getVotingTrends($event),
            'divisionBreakdown' => $this->getDivisionBreakdown($event),
            'topPerformers' => $this->getTopPerformers($event),
            'voterMetrics' => $this->getVoterMetrics($event),
            'placeDistribution' => $this->getPlaceDistribution($event),
        ]);
    }
}

/**
 * Helper function to get ordinal suffix.
 */
function ordinal($number) {
    $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
    if (($number % 100) >= 11 && ($number % 100) <= 13) {
        return $number . 'th';
    }
    return $number . $ends[$number % 10];
}
