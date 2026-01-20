<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ballot - {{ $event->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
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
        .header p {
            color: #666;
            font-size: 14px;
        }
        .voting-section {
            margin-bottom: 25px;
        }
        .voting-section h2 {
            font-size: 16px;
            color: #1e40af;
            padding: 8px 12px;
            background: #f0f4ff;
            border-left: 4px solid #1e40af;
            margin-bottom: 15px;
        }
        .voting-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
        }
        .voting-box h3 {
            font-size: 14px;
            margin-bottom: 12px;
            color: #34495e;
        }
        .place-row {
            display: flex;
            margin-bottom: 10px;
            align-items: center;
        }
        .place-label {
            width: 120px;
            font-weight: bold;
        }
        .place-input {
            flex: 1;
            border: 1px solid #ccc;
            padding: 8px;
            min-height: 30px;
            background: #f9f9f9;
        }
        .place-points {
            width: 80px;
            text-align: right;
            color: #666;
            font-size: 11px;
        }
        .entries-list {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #ccc;
        }
        .entries-list h4 {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }
        .entry-item {
            display: inline-block;
            padding: 3px 8px;
            margin: 2px;
            background: #f0f0f0;
            border-radius: 3px;
            font-size: 11px;
        }
        .instructions {
            margin-top: 20px;
            padding: 12px;
            background: #fffbeb;
            border: 1px solid #f59e0b;
            font-size: 11px;
        }
        .instructions strong {
            color: #b45309;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #999;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $event->name }}</h1>
        <p>Official Voting Ballot</p>
        @if($event->event_date)
            <p>{{ $event->event_date->format('F j, Y') }}</p>
        @endif
    </div>

    @if($votingType)
        <div class="instructions">
            <strong>Voting Instructions:</strong>
            Enter the entry number for each place. {{ $votingType->name }} voting system.
        </div>
    @endif

    @forelse($divisionsByType as $type => $divisions)
        <div class="voting-section">
            <h2>{{ $type ?? 'General' }}</h2>

            @foreach($divisions as $division)
                <div class="voting-box">
                    <h3>{{ $division->name }} ({{ $division->code }})</h3>

                    @foreach($places as $place => $config)
                        <div class="place-row">
                            <span class="place-label">{{ $config['label'] ?? ordinal($place) . ' Place' }}:</span>
                            <div class="place-input"></div>
                            <span class="place-points">({{ $config['points'] }} pts)</span>
                        </div>
                    @endforeach

                    @if($division->entries->count() > 0)
                        <div class="entries-list">
                            <h4>Available Entries:</h4>
                            @foreach($division->entries as $entry)
                                <span class="entry-item">
                                    #{{ $entry->entry_number }} - {{ $entry->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @empty
        <p>No divisions found for this event.</p>
    @endforelse

    <div class="footer">
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>Voting Application - {{ config('app.name') }}</p>
    </div>
</body>
</html>

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
@endphp
