<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Entries List - {{ $event->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2c3e50;
        }
        .header h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 14px;
            color: #666;
        }
        .division-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .division-section h2 {
            font-size: 14px;
            color: #fff;
            padding: 8px 12px;
            background: #34495e;
            margin-bottom: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f3f4f6;
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .entry-number {
            font-weight: bold;
            color: #0d6e38;
            font-size: 13px;
        }
        .entry-name {
            font-weight: 500;
        }
        .participant-name {
            color: #666;
        }
        .summary {
            margin-top: 30px;
            padding: 15px;
            background: #f0f4ff;
            border: 1px solid #0d6e38;
        }
        .summary h3 {
            font-size: 14px;
            color: #0d6e38;
            margin-bottom: 10px;
        }
        .summary-grid {
            display: flex;
            flex-wrap: wrap;
        }
        .summary-item {
            width: 33%;
            padding: 5px 0;
        }
        .summary-item strong {
            color: #2c3e50;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #999;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $event->name }}</h1>
        <p class="subtitle">Complete Entries List</p>
        @if($event->event_date)
            <p class="date">{{ $event->event_date->format('F j, Y') }}</p>
        @endif
    </div>

    @php
        $totalEntries = 0;
        $participantLabel = $event->template->participant_label ?? 'Participant';
        $entryLabel = $event->template->entry_label ?? 'Entry';
    @endphp

    @forelse($event->divisions as $division)
        @php
            $divisionEntries = $entriesByDivision->get($division->id, collect());
            $totalEntries += $divisionEntries->count();
        @endphp

        <div class="division-section">
            <h2>{{ $division->name }} ({{ $division->code }}) - {{ $divisionEntries->count() }} Entries</h2>

            @if($divisionEntries->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>{{ $entryLabel }} Name</th>
                            <th>{{ $participantLabel }}</th>
                            <th style="width: 100px;">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($divisionEntries->sortBy('entry_number') as $entry)
                            <tr>
                                <td class="entry-number">{{ $entry->entry_number }}</td>
                                <td class="entry-name">{{ $entry->name }}</td>
                                <td class="participant-name">{{ $entry->participant->name ?? '-' }}</td>
                                <td>{{ $entry->created_at->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="padding: 15px; color: #666; font-style: italic;">No entries in this division.</p>
            @endif
        </div>
    @empty
        <p>No divisions found for this event.</p>
    @endforelse

    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <strong>Total Divisions:</strong> {{ $event->divisions->count() }}
            </div>
            <div class="summary-item">
                <strong>Total Entries:</strong> {{ $totalEntries }}
            </div>
            <div class="summary-item">
                <strong>Unique {{ $participantLabel }}s:</strong> {{ $event->entries->pluck('participant_id')->unique()->count() }}
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
