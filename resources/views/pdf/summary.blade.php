<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Event Summary - {{ $event->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2c3e50;
        }
        .header h1 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 16px;
            color: #1e40af;
            font-weight: bold;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h2 {
            font-size: 16px;
            color: #2c3e50;
            padding: 10px 15px;
            background: #f0f4ff;
            border-left: 4px solid #1e40af;
            margin-bottom: 15px;
        }
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            margin: -5px;
        }
        .stat-box {
            width: calc(33.333% - 10px);
            margin: 5px;
            padding: 20px;
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        .stat-box .value {
            font-size: 32px;
            font-weight: bold;
            color: #1e40af;
            display: block;
        }
        .stat-box .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .event-details {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
        }
        .event-details p {
            margin-bottom: 8px;
        }
        .event-details strong {
            color: #2c3e50;
            display: inline-block;
            width: 140px;
        }
        .top-results {
            margin-top: 15px;
        }
        .division-results {
            margin-bottom: 20px;
        }
        .division-results h3 {
            font-size: 14px;
            color: #34495e;
            padding: 8px 12px;
            background: #e5e7eb;
            margin-bottom: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .rank {
            font-weight: bold;
            color: #1e40af;
        }
        .rank-1 { color: #d97706; }
        .rank-2 { color: #6b7280; }
        .rank-3 { color: #b45309; }
        .points {
            font-weight: bold;
            color: #1e40af;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
        }
        .footer .timestamp {
            color: #999;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $event->name }}</h1>
        <p class="subtitle">Event Summary Report</p>
    </div>

    <div class="section">
        <h2>Event Details</h2>
        <div class="event-details">
            <p><strong>Event Name:</strong> {{ $event->name }}</p>
            <p><strong>Template:</strong> {{ $event->template->name ?? 'N/A' }}</p>
            <p><strong>Voting Type:</strong> {{ $event->votingConfig->votingType->name ?? 'N/A' }}</p>
            @if($event->event_date)
                <p><strong>Event Date:</strong> {{ $event->event_date->format('F j, Y') }}</p>
            @endif
            @if($event->location)
                <p><strong>Location:</strong> {{ $event->location }}</p>
            @endif
            <p><strong>Status:</strong> {{ $event->is_active ? 'Active' : 'Inactive' }}</p>
        </div>
    </div>

    <div class="section">
        <h2>Statistics</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <span class="value">{{ $stats['total_divisions'] }}</span>
                <span class="label">Divisions</span>
            </div>
            <div class="stat-box">
                <span class="value">{{ $stats['total_entries'] }}</span>
                <span class="label">Entries</span>
            </div>
            <div class="stat-box">
                <span class="value">{{ $stats['total_participants'] }}</span>
                <span class="label">{{ $event->template->participant_label ?? 'Participants' }}</span>
            </div>
            <div class="stat-box">
                <span class="value">{{ $stats['total_votes'] }}</span>
                <span class="label">Total Votes</span>
            </div>
            <div class="stat-box">
                <span class="value">{{ $stats['unique_voters'] }}</span>
                <span class="label">Unique Voters</span>
            </div>
            <div class="stat-box">
                <span class="value">{{ $stats['total_entries'] > 0 ? round($stats['total_votes'] / $stats['total_entries'], 1) : 0 }}</span>
                <span class="label">Avg Votes/Entry</span>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Top Results by Division</h2>
        <div class="top-results">
            @forelse($topResults as $type => $results)
                <div class="division-results">
                    <h3>{{ $type ?? 'General' }}</h3>
                    @if($results->count() > 0)
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Rank</th>
                                    <th>Entry</th>
                                    <th>{{ $event->template->participant_label ?? 'Participant' }}</th>
                                    <th style="width: 80px;">Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results->take(5) as $index => $result)
                                    <tr>
                                        <td>
                                            <span class="rank {{ $index < 3 ? 'rank-' . ($index + 1) : '' }}">
                                                {{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td>{{ $result->entry_name }}</td>
                                        <td>{{ $result->participant_name ?? '-' }}</td>
                                        <td class="points">{{ number_format($result->total_points, 1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p style="padding: 10px; color: #666; font-style: italic;">No results yet.</p>
                    @endif
                </div>
            @empty
                <p style="padding: 15px; color: #666; font-style: italic;">No voting results available.</p>
            @endforelse
        </div>
    </div>

    <div class="footer">
        <p class="timestamp">Report generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
