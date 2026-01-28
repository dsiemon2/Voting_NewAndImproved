<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ballot Sheets - {{ $event->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            margin: 0.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #333;
        }
        .page {
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: avoid;
        }
        .ballot-grid {
            display: flex;
            flex-wrap: wrap;
        }
        .ballot {
            width: 48%;
            margin: 1%;
            padding: 12px;
            border: 2px solid #333;
            page-break-inside: avoid;
        }
        .ballot-header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #999;
        }
        .ballot-header h2 {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        .ballot-header p {
            font-size: 10px;
            color: #666;
        }
        .voting-section {
            margin-bottom: 10px;
        }
        .voting-section h3 {
            font-size: 10px;
            color: #0d6e38;
            background: #f0f4ff;
            padding: 4px 6px;
            margin-bottom: 6px;
        }
        .place-row {
            display: flex;
            margin-bottom: 6px;
            align-items: center;
        }
        .place-label {
            width: 70px;
            font-size: 9px;
            font-weight: bold;
        }
        .place-input {
            flex: 1;
            border: 1px solid #999;
            height: 18px;
            background: #f9f9f9;
        }
        .place-points {
            width: 40px;
            text-align: right;
            font-size: 8px;
            color: #666;
        }
        .entries-ref {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px dashed #ccc;
            font-size: 8px;
            color: #666;
        }
        .entry-code {
            display: inline-block;
            padding: 1px 4px;
            margin: 1px;
            background: #f0f0f0;
            font-size: 7px;
        }
        .ballot-footer {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px solid #999;
            text-align: center;
            font-size: 7px;
            color: #999;
        }
        .cut-line {
            border-top: 1px dashed #ccc;
            margin: 5px 0;
            position: relative;
        }
        .cut-line::before {
            content: 'CUT HERE';
            position: absolute;
            top: -6px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 0 5px;
            font-size: 6px;
            color: #999;
        }
    </style>
</head>
<body>
    @php
        $ballotsPerPage = $perPage ?? 4;
        $ballotCount = 0;
    @endphp

    <div class="page">
        <div class="ballot-grid">
            @for($i = 0; $i < $ballotsPerPage; $i++)
                @if($i > 0 && $i % 2 == 0)
                    <div class="cut-line" style="width: 100%;"></div>
                @endif
                <div class="ballot">
                    <div class="ballot-header">
                        <h2>{{ $event->name }}</h2>
                        <p>Official Ballot #{{ $i + 1 }}</p>
                    </div>

                    @forelse($divisionsByType as $type => $divisions)
                        <div class="voting-section">
                            <h3>{{ $type ?? 'Vote' }}</h3>

                            @foreach($divisions as $division)
                                <div style="margin-bottom: 8px;">
                                    <strong style="font-size: 9px;">{{ $division->code }}:</strong>

                                    @foreach($places as $place => $config)
                                        <div class="place-row">
                                            <span class="place-label">{{ $config['label'] ?? ordinal($place) }}:</span>
                                            <div class="place-input"></div>
                                            <span class="place-points">({{ $config['points'] }}pts)</span>
                                        </div>
                                    @endforeach

                                    @if($division->entries->count() > 0)
                                        <div class="entries-ref">
                                            @foreach($division->entries->take(10) as $entry)
                                                <span class="entry-code">#{{ $entry->entry_number }}</span>
                                            @endforeach
                                            @if($division->entries->count() > 10)
                                                <span class="entry-code">...</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <p style="text-align: center; color: #666;">No divisions</p>
                    @endforelse

                    <div class="ballot-footer">
                        {{ $event->event_date?->format('M j, Y') ?? '' }}
                    </div>
                </div>
            @endfor
        </div>
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
