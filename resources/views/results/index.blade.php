@extends('layouts.app')

@section('content')
@php
    if (!function_exists('ordinal')) {
        function ordinal($number) {
            $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
            if (($number % 100) >= 11 && ($number % 100) <= 13) {
                return $number . 'th';
            }
            return $number . $ends[$number % 10];
        }
    }
    $divisionTypes = $event->template->getDivisionTypes();
@endphp

<style>
    .results-container {
        max-width: 1700px;
        margin: 0 auto;
    }

    .event-header-wrapper {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
    }

    .event-header {
        text-align: center;
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        max-width: 600px;
        width: 100%;
    }

    .event-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
    }

    .event-header p {
        margin: 0;
        opacity: 0.9;
    }

    .action-bar {
        background: #f8fafc;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        border: 1px solid #e2e8f0;
    }

    .action-bar .info {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .action-bar .info-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        font-size: 14px;
    }

    .action-bar .info-item i {
        color: #1e3a8a;
    }

    .action-bar .actions {
        display: flex;
        gap: 10px;
    }

    .btn-export {
        background: #ff6600;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-export:hover {
        background: #e55a00;
        color: white;
    }

    .btn-vote {
        background: #10b981;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .btn-vote:hover {
        background: #059669;
        color: white;
    }

    .results-section {
        display: flex;
        gap: 25px;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }

    .results-box {
        flex: 1;
        min-width: 300px;
        background: #f8fafc;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .results-box-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        padding: 18px 20px;
        text-align: center;
    }

    .results-box-header h2 {
        margin: 0;
        font-size: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .results-box-header small {
        opacity: 0.8;
        font-size: 12px;
        display: block;
        margin-top: 5px;
    }

    .results-table {
        width: 100%;
        border-collapse: collapse;
    }

    .results-table th {
        background: #1e3a8a;
        color: white;
        padding: 12px 10px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .results-table th.center {
        text-align: center;
    }

    .results-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
    }

    .results-table tr:hover {
        background: #e2e8f0;
    }

    .results-table tr.winner {
        background: #fef3c7;
    }

    .results-table tr.winner:hover {
        background: #fde68a;
    }

    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        font-weight: bold;
        font-size: 13px;
    }

    .rank-1 { background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%); color: #000; box-shadow: 0 2px 4px rgba(255,215,0,0.4); }
    .rank-2 { background: linear-gradient(135deg, #c0c0c0 0%, #e0e0e0 100%); color: #000; box-shadow: 0 2px 4px rgba(192,192,192,0.4); }
    .rank-3 { background: linear-gradient(135deg, #cd7f32 0%, #e89b52 100%); color: #fff; box-shadow: 0 2px 4px rgba(205,127,50,0.4); }

    .entry-name {
        font-weight: 600;
        color: #1e293b;
    }

    .participant-name {
        color: #64748b;
        font-size: 13px;
    }

    .division-code {
        background: #e2e8f0;
        color: #475569;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .points-badge {
        background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 15px;
        font-weight: bold;
        font-size: 14px;
    }

    .vote-count {
        text-align: center;
        color: #64748b;
        font-size: 13px;
    }

    .no-results {
        text-align: center;
        padding: 40px;
        color: #6b7280;
    }

    .no-results i {
        font-size: 48px;
        margin-bottom: 15px;
        display: block;
        color: #d1d5db;
    }

    .results-grid-wrapper {
        margin-top: 20px;
    }

    @media (max-width: 768px) {
        .results-section {
            flex-direction: column;
        }

        .results-box {
            min-width: 100%;
        }

        .action-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .action-bar .info {
            flex-direction: column;
            gap: 10px;
        }

        .action-bar .actions {
            justify-content: center;
        }

        .results-table {
            display: block;
            overflow-x: auto;
        }
    }

    @media print {
        .action-bar {
            display: none;
        }
        .results-box {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>

<div class="results-container">
    <!-- Event Header -->
    <div class="event-header-wrapper">
        <div class="event-header">
            <h1><i class="fas fa-trophy"></i> {{ $event->name }} - Results</h1>
            <p>{{ $event->event_date ? $event->event_date->format('F j, Y') : '' }} {{ $event->location ? '- ' . $event->location : '' }}</p>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <div class="info">
            <div class="info-item">
                <i class="fas fa-list"></i>
                <span>{{ $divisions->count() }} Divisions</span>
            </div>
            <div class="info-item">
                <i class="fas fa-poll"></i>
                <span>{{ $event->votingConfig?->votingType?->name ?? 'Standard Ranked' }} Voting</span>
            </div>
            @if(count($placeConfigs) > 0)
            <div class="info-item">
                <i class="fas fa-medal"></i>
                <span>
                    @foreach($placeConfigs as $place => $points)
                        {{ ordinal($place) }}={{ $points }}pts{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </span>
            </div>
            @endif
        </div>
        <div class="actions">
            <a href="{{ route('voting.index', $event) }}" class="btn-vote">
                <i class="fas fa-vote-yea"></i> Vote Now
            </a>
            <a href="{{ route('admin.events.ballots', $event) }}" class="btn-export">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="error-message" style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i>
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    @if(session('success'))
        <div class="success-message" style="background: #d1fae5; color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Results by Division Type -->
    <div class="results-grid-wrapper">
        <div class="results-section">
        @forelse($divisionsByType as $typeName => $typeDivisions)
            @php
                // Collect all results for this type
                $typeResults = collect();
                foreach($typeDivisions as $division) {
                    if(isset($resultsByDivision[$division->id])) {
                        foreach($resultsByDivision[$division->id] as $result) {
                            $result->division_type = $typeName;
                            $typeResults->push($result);
                        }
                    }
                }
                $typeResults = $typeResults->sortByDesc('total_points')->values();
            @endphp
            <div class="results-box">
                <div class="results-box-header">
                    <h2>
                        <i class="fas fa-medal"></i>
                        {{ $typeName }} Results
                    </h2>
                    <small>{{ $typeDivisions->count() }} division(s) - {{ $typeResults->count() }} {{ Str::plural($entryLabel, $typeResults->count()) }}</small>
                </div>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>{{ $entryLabel }}</th>
                            <th>{{ $participantLabel }}</th>
                            <th>Division</th>
                            <th class="center">Points</th>
                            @if($showVoteCounts && count($placeConfigs) > 0)
                                @foreach($placeConfigs as $place => $points)
                                    <th class="center" title="{{ ordinal($place) }} Place Votes">{{ ordinal($place) }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($typeResults as $index => $result)
                            <tr class="{{ $index < 3 ? 'winner' : '' }}">
                                <td>
                                    <span class="rank-badge {{ $index < 3 ? 'rank-' . ($index + 1) : '' }}">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="entry-name">{{ $result->entry_name }}</span>
                                    @if($result->entry_number)
                                        <br><small style="color: #9ca3af;">#{{ $result->entry_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="participant-name">{{ $result->participant_name ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="division-code">{{ $result->division_code ?? '-' }}</span>
                                </td>
                                <td class="center">
                                    <span class="points-badge">{{ number_format($result->total_points ?? 0) }}</span>
                                </td>
                                @if($showVoteCounts && count($placeConfigs) > 0)
                                    @foreach($placeConfigs as $place => $points)
                                        @php $countField = "place_{$place}_count"; @endphp
                                        <td class="vote-count">{{ $result->$countField ?? 0 }}</td>
                                    @endforeach
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + ($showVoteCounts ? count($placeConfigs) : 0) }}" class="no-results">
                                    <i class="fas fa-inbox"></i>
                                    <p>No votes yet for {{ $typeName }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @empty
            <!-- No division types defined - show all results -->
            <div class="results-box" style="flex: 100%;">
                <div class="results-box-header">
                    <h2>
                        <i class="fas fa-medal"></i>
                        All Results
                    </h2>
                </div>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>{{ $entryLabel }}</th>
                            <th>{{ $participantLabel }}</th>
                            <th>Division</th>
                            <th class="center">Points</th>
                            @if($showVoteCounts && count($placeConfigs) > 0)
                                @foreach($placeConfigs as $place => $points)
                                    <th class="center">{{ ordinal($place) }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $allResults = collect($resultsByDivision)->flatten(1)->sortByDesc('total_points')->values();
                        @endphp
                        @forelse($allResults as $index => $result)
                            <tr class="{{ $index < 3 ? 'winner' : '' }}">
                                <td>
                                    <span class="rank-badge {{ $index < 3 ? 'rank-' . ($index + 1) : '' }}">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="entry-name">{{ $result->entry_name }}</span>
                                </td>
                                <td>
                                    <span class="participant-name">{{ $result->participant_name ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="division-code">{{ $result->division_code ?? '-' }}</span>
                                </td>
                                <td class="center">
                                    <span class="points-badge">{{ number_format($result->total_points ?? 0) }}</span>
                                </td>
                                @if($showVoteCounts && count($placeConfigs) > 0)
                                    @foreach($placeConfigs as $place => $points)
                                        @php $countField = "place_{$place}_count"; @endphp
                                        <td class="vote-count">{{ $result->$countField ?? 0 }}</td>
                                    @endforeach
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + ($showVoteCounts ? count($placeConfigs) : 0) }}" class="no-results">
                                    <i class="fas fa-inbox"></i>
                                    <p>No votes recorded yet</p>
                                    <a href="{{ route('voting.index', $event) }}" class="btn-vote" style="display: inline-flex; margin-top: 15px;">
                                        <i class="fas fa-vote-yea"></i> Cast Your Vote
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforelse
        </div>
    </div>
</div>

<script>
    // Auto-refresh results every 30 seconds
    @if($event->votingConfig?->show_live_results)
    setTimeout(function() {
        location.reload();
    }, 30000);
    @endif
</script>
@endsection
