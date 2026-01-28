<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Results - {{ $event->name }}</title>
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
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2c3e50;
        }
        .header h1 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 16px;
            color: #0d6e38;
            font-weight: bold;
        }
        .header .date {
            color: #666;
            margin-top: 5px;
        }
        .results-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .results-section h2 {
            font-size: 16px;
            color: #fff;
            padding: 10px 15px;
            background: #2c3e50;
            margin-bottom: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #34495e;
            color: #fff;
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        th.center, td.center {
            text-align: center;
        }
        th.right, td.right {
            text-align: right;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        tr.winner-1 {
            background: #fef3c7;
        }
        tr.winner-2 {
            background: #e5e7eb;
        }
        tr.winner-3 {
            background: #fde68a;
        }
        .rank {
            font-weight: bold;
            font-size: 14px;
        }
        .rank-1 { color: #d97706; }
        .rank-2 { color: #6b7280; }
        .rank-3 { color: #b45309; }
        .points {
            font-weight: bold;
            font-size: 13px;
            color: #0d6e38;
        }
        .place-count {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin: 0 1px;
        }
        .place-1st { background: #fef3c7; color: #92400e; }
        .place-2nd { background: #e5e7eb; color: #374151; }
        .place-3rd { background: #fde68a; color: #92400e; }
        .no-results {
            padding: 30px;
            text-align: center;
            color: #666;
            font-style: italic;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
        }
        .footer .timestamp {
            color: #999;
            font-size: 10px;
        }
        .footer .official {
            margin-top: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $event->name }}</h1>
        <p class="subtitle">Official Results</p>
        @if($event->event_date)
            <p class="date">{{ $event->event_date->format('F j, Y') }}</p>
        @endif
    </div>

    @forelse($resultsByType as $type => $results)
        <div class="results-section">
            <h2>{{ $type ?? 'Results' }}</h2>

            @if($results->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="center">Rank</th>
                            <th>Entry</th>
                            <th>{{ $event->template->participant_label ?? 'Participant' }}</th>
                            <th style="width: 80px;" class="center">Division</th>
                            <th style="width: 70px;" class="center">Points</th>
                            <th style="width: 50px;" class="center">1st</th>
                            <th style="width: 50px;" class="center">2nd</th>
                            <th style="width: 50px;" class="center">3rd</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $index => $result)
                            @php $rank = $index + 1; @endphp
                            <tr class="{{ $rank <= 3 ? 'winner-' . $rank : '' }}">
                                <td class="center">
                                    <span class="rank {{ $rank <= 3 ? 'rank-' . $rank : '' }}">
                                        {{ $rank }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $result->entry_name }}</strong>
                                    <br><small>#{{ $result->entry_number }}</small>
                                </td>
                                <td>{{ $result->participant_name ?? '-' }}</td>
                                <td class="center">{{ $result->division_code ?? '-' }}</td>
                                <td class="center points">{{ number_format($result->total_points, 1) }}</td>
                                <td class="center">
                                    @if($result->first_place_count > 0)
                                        <span class="place-count place-1st">{{ $result->first_place_count }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="center">
                                    @if($result->second_place_count > 0)
                                        <span class="place-count place-2nd">{{ $result->second_place_count }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="center">
                                    @if($result->third_place_count > 0)
                                        <span class="place-count place-3rd">{{ $result->third_place_count }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-results">No votes recorded for this division.</div>
            @endif
        </div>
    @empty
        <div class="no-results">No results available for this event.</div>
    @endforelse

    <div class="footer">
        <p class="official">Official Results Document</p>
        <p class="timestamp">Generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
