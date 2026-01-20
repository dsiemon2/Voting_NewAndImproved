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
    $placeCount = count($placeConfigs);

    // Group divisions by type
    $divisionsByType = $divisions->groupBy(function($division) {
        return $division->type ?? 'Other';
    });
@endphp

<style>
    .voting-container {
        max-width: 1700px;
        margin: 0 auto;
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

    .voting-section {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .division-box {
        flex: 1;
        min-width: 300px;
        background: #34495e;
        color: white;
        border-radius: 10px;
        overflow: hidden;
    }

    .division-box-header {
        background: #2c3e50;
        padding: 15px 20px;
        text-align: center;
    }

    .division-box-header h2 {
        margin: 0;
        font-size: 22px;
    }

    .division-box-header small {
        opacity: 0.8;
        font-size: 12px;
    }

    .division-box-body {
        padding: 20px;
    }

    .vote-input-group {
        margin-bottom: 15px;
    }

    .vote-input-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #fef3c7;
        font-size: 14px;
    }

    .vote-input-group .place-badge {
        display: inline-block;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        text-align: center;
        line-height: 25px;
        margin-right: 8px;
        font-size: 12px;
        font-weight: bold;
    }

    .place-badge.first { background: #ffd700; color: #000; }
    .place-badge.second { background: #c0c0c0; color: #000; }
    .place-badge.third { background: #cd7f32; color: #fff; }
    .place-badge.other { background: #6b7280; color: #fff; }

    .vote-input-group input {
        width: 100%;
        padding: 12px 15px;
        font-size: 18px;
        text-align: center;
        border: 2px solid #4b5563;
        border-radius: 6px;
        background: #374151;
        color: white;
    }

    .vote-input-group input:focus {
        outline: none;
        border-color: #ff6600;
        background: #3f4a5a;
    }

    .vote-input-group input::placeholder {
        color: #9ca3af;
    }

    .points-label {
        float: right;
        background: #ff6600;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
    }

    .submit-container {
        text-align: center;
        margin: 30px 0;
    }

    .submit-btn {
        background: #1e40af;
        color: white;
        border: none;
        padding: 15px 50px;
        font-size: 18px;
        font-weight: bold;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .submit-btn:hover {
        background: #1e3a8a;
    }

    .results-header {
        margin: 0 0 20px;
        color: #1e3a8a;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .results-section {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .results-box {
        flex: 1;
        min-width: 300px;
        background: #ecf0f1;
        color: #2c3e50;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .results-box-header {
        background: #2c3e50;
        color: white;
        padding: 15px 20px;
        text-align: center;
    }

    .results-box-header h3 {
        margin: 0;
        font-size: 18px;
    }

    .results-table {
        width: 100%;
        border-collapse: collapse;
    }

    .results-table th {
        background: #2c3e50;
        color: white;
        padding: 10px;
        text-align: left;
        font-size: 12px;
        border: 1px solid #bdc3c7;
    }

    .results-table td {
        padding: 10px;
        border: 1px solid #bdc3c7;
        font-size: 13px;
    }

    .results-table tr:hover {
        background: #dfe6e9;
    }

    .rank-badge {
        display: inline-block;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        text-align: center;
        line-height: 24px;
        font-weight: bold;
        font-size: 12px;
    }

    .rank-1 { background: #ffd700; color: #000; }
    .rank-2 { background: #c0c0c0; color: #000; }
    .rank-3 { background: #cd7f32; color: #fff; }

    .error-message {
        background: #fee2e2;
        color: #dc2626;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .success-message {
        background: #d1fae5;
        color: #10b981;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .info-bar {
        background: #dbeafe;
        color: #1e40af;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .results-container {
        margin-top: 30px;
    }

    /* Wrapper for centering the event header */
    .event-header-wrapper {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
    }

    @media (max-width: 768px) {
        .voting-section, .results-section {
           flex-direction: column;
        }

        .division-box, .results-box {
            min-width: 100%;
        }
    }
</style>

<div class="voting-container">
    <!-- Event Header -->
    <div class="event-header-wrapper">
        <div class="event-header">
            <h1><i class="fas {{ $event->template->icon ?? 'fa-calendar' }}"></i> {{ $event->name }}</h1>
            <p>{{ $event->event_date ? $event->event_date->format('F j, Y') : '' }} {{ $event->location ? '- ' . $event->location : '' }}</p>
        </div>
    </div>

    <!-- Info Bar -->
    <div class="info-bar">
        <div>
            <strong><i class="fas fa-info-circle"></i> Voting System:</strong>
            {{ $event->votingConfig?->votingType?->name ?? 'Standard Ranked' }}
            @if($placeCount > 0)
                ({{ $placeCount }} places:
                @foreach($placeConfigs as $place => $points)
                    {{ ordinal($place) }}={{ $points }}pts{{ !$loop->last ? ', ' : '' }}
                @endforeach)
            @endif
        </div>
        <div>
            <a href="{{ route('results.index', $event) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-chart-bar"></i> View Full Results
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    @if(session('success'))
        <div class="success-message">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Voting Form -->
    <form action="{{ route('voting.store', $event) }}" method="POST" id="votingForm">
        @csrf
        <input type="hidden" name="event_id" value="{{ $event->id }}">

        <div class="voting-section">
            @if($divisionsByType->isNotEmpty())
                @foreach($divisionsByType as $typeName => $typeDivisions)
                    @php
                        // Get the type code from template for this type name
                        $typeCode = 'P';
                        foreach($divisionTypes as $dt) {
                            if($dt['name'] === $typeName) {
                                $typeCode = $dt['code'];
                                break;
                            }
                        }
                    @endphp
                    <div class="division-box">
                        <div class="division-box-header">
                            <h2>{{ $typeName }}</h2>
                            <small>Enter division number for each place (e.g., 1, 2, 3)</small>
                        </div>
                        <div class="division-box-body">
                            @foreach($placeConfigs as $place => $points)
                                <div class="vote-input-group">
                                    <label>
                                        <span class="place-badge {{ $place == 1 ? 'first' : ($place == 2 ? 'second' : ($place == 3 ? 'third' : 'other')) }}">
                                            {{ $place }}
                                        </span>
                                        {{ ordinal($place) }} Place
                                        <span class="points-label">{{ $points }} pts</span>
                                    </label>
                                    <input type="text"
                                           name="votes[{{ $typeCode }}][{{ $place }}]"
                                           id="{{ strtolower(str_replace(' ', '-', $typeName)) }}-{{ ordinal($place) }}"
                                           placeholder="Enter Number"
                                           autocomplete="off"
                                           value="{{ old('votes.' . $typeCode . '.' . $place) }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <!-- No division types - single voting box -->
                <div class="division-box" style="flex: 100%;">
                    <div class="division-box-header">
                        <h2>Vote</h2>
                        <small>Enter {{ strtolower($entryLabel) }} number for each place</small>
                    </div>
                    <div class="division-box-body">
                        @foreach($placeConfigs as $place => $points)
                            <div class="vote-input-group">
                                <label>
                                    <span class="place-badge {{ $place == 1 ? 'first' : ($place == 2 ? 'second' : ($place == 3 ? 'third' : 'other')) }}">
                                        {{ $place }}
                                    </span>
                                    {{ ordinal($place) }} Place
                                    <span class="points-label">{{ $points }} pts</span>
                                </label>
                                <input type="text"
                                       name="votes[0][{{ $place }}]"
                                       placeholder="Enter Number"
                                       autocomplete="off">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="submit-container">
            <button type="submit" class="submit-btn">
                <i class="fas fa-vote-yea"></i> Submit Vote
            </button>
        </div>
    </form>

    <!-- Results Section -->
    <div class="results-container">
        <h2 class="results-header"><i class="fas fa-trophy"></i> Current Results</h2>
        <div class="results-section">
        @if($divisionsByType->isNotEmpty())
            @foreach($divisionsByType as $typeName => $typeDivisions)
                @php
                    // Collect all results for this division type
                    $typeResults = collect();
                    foreach($typeDivisions as $division) {
                        if(isset($resultsByDivision[$division->id])) {
                            foreach($resultsByDivision[$division->id] as $result) {
                                $typeResults->push($result);
                            }
                        }
                    }
                    $typeResults = $typeResults->sortByDesc('total_points')->values();
                @endphp
                <div class="results-box">
                    <div class="results-box-header">
                        <h3>{{ $typeName }} Results</h3>
                    </div>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ $entryLabel }}</th>
                                <th>{{ $participantLabel }}</th>
                                <th>Division</th>
                                <th>Points</th>
                                @foreach($placeConfigs as $place => $points)
                                    <th>{{ ordinal($place) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($typeResults as $index => $result)
                                <tr>
                                    <td>
                                        <span class="rank-badge {{ $index < 3 ? 'rank-' . ($index + 1) : '' }}">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td><strong>{{ $result->entry_name ?? '-' }}</strong></td>
                                    <td>{{ $result->participant_name ?? '-' }}</td>
                                    <td>{{ $result->division_code ?? '-' }}</td>
                                    <td><strong>{{ $result->total_points ?? 0 }}</strong></td>
                                    @foreach($placeConfigs as $place => $points)
                                        @php $countField = "place_{$place}_count"; @endphp
                                        <td>{{ $result->$countField ?? 0 }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 5 + count($placeConfigs) }}" style="text-align: center; padding: 30px; color: #6b7280;">
                                        No votes yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        @else
            <!-- No division types - single results table -->
            @php
                // Get all results for events without divisions
                $allResults = collect();
                foreach($resultsByDivision as $divId => $divResults) {
                    foreach($divResults as $result) {
                        $allResults->push($result);
                    }
                }
                $allResults = $allResults->sortByDesc('total_points')->values();
            @endphp
            <div class="results-box" style="flex: 100%;">
                <div class="results-box-header">
                    <h3>Results</h3>
                </div>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ $entryLabel }}</th>
                            <th>Points</th>
                            @foreach($placeConfigs as $place => $points)
                                <th>{{ ordinal($place) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allResults as $index => $result)
                            <tr>
                                <td>
                                    <span class="rank-badge {{ $index < 3 ? 'rank-' . ($index + 1) : '' }}">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td><strong>{{ $result->entry_name ?? '-' }}</strong></td>
                                <td><strong>{{ $result->total_points ?? 0 }}</strong></td>
                                @foreach($placeConfigs as $place => $points)
                                    @php $countField = "place_{$place}_count"; @endphp
                                    <td>{{ $result->$countField ?? 0 }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 3 + count($placeConfigs) }}" style="text-align: center; padding: 30px; color: #6b7280;">
                                    No votes yet
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
        </div>
    </div>
</div>

<script>
    // Auto-focus on first input
    document.addEventListener('DOMContentLoaded', function() {
        const firstInput = document.querySelector('.vote-input-group input');
        if (firstInput) {
            firstInput.focus();
        }
    });

    // Move to next input on Enter
    document.querySelectorAll('.vote-input-group input').forEach((input, index, inputs) => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (index + 1 < inputs.length) {
                    inputs[index + 1].focus();
                } else {
                    document.getElementById('votingForm').submit();
                }
            }
        });
    });
</script>
@endsection
