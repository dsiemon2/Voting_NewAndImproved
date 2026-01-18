<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate - {{ $event->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            background: #fff;
        }
        .certificate {
            width: 100%;
            height: 100%;
            padding: 40px;
            border: 8px double #2c3e50;
            position: relative;
        }
        .certificate::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid #d4af37;
            pointer-events: none;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header .award-title {
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 8px;
            color: #666;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 48px;
            color: #2c3e50;
            font-weight: normal;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 16px;
            color: #666;
            font-style: italic;
        }
        .content {
            text-align: center;
            margin: 30px 0;
        }
        .presented-to {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .winner-name {
            font-size: 36px;
            color: #1e40af;
            font-weight: bold;
            margin-bottom: 15px;
            padding: 10px 0;
            border-bottom: 2px solid #d4af37;
            border-top: 2px solid #d4af37;
        }
        .entry-name {
            font-size: 20px;
            color: #34495e;
            margin-bottom: 10px;
            font-style: italic;
        }
        .division {
            font-size: 14px;
            color: #666;
        }
        .place-badge {
            display: inline-block;
            margin: 25px 0;
            padding: 15px 40px;
            font-size: 28px;
            font-weight: bold;
            border-radius: 50px;
        }
        .place-1 {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border: 3px solid #d97706;
        }
        .place-2 {
            background: linear-gradient(135deg, #f3f4f6 0%, #d1d5db 100%);
            color: #374151;
            border: 3px solid #6b7280;
        }
        .place-3 {
            background: linear-gradient(135deg, #fde68a 0%, #fbbf24 100%);
            color: #78350f;
            border: 3px solid #b45309;
        }
        .event-info {
            margin-top: 30px;
            text-align: center;
        }
        .event-name {
            font-size: 22px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .event-date {
            font-size: 14px;
            color: #666;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            padding: 0 60px;
        }
        .signature-line {
            width: 200px;
            text-align: center;
        }
        .signature-line .line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            height: 40px;
        }
        .signature-line .label {
            font-size: 12px;
            color: #666;
        }
        .seal {
            position: absolute;
            bottom: 80px;
            right: 80px;
            width: 100px;
            height: 100px;
            border: 3px solid #d4af37;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 10px;
            color: #d4af37;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="header">
            <p class="award-title">Certificate of Achievement</p>
            <h1>Award</h1>
            <p class="subtitle">In Recognition of Excellence</p>
        </div>

        <div class="content">
            <p class="presented-to">This certificate is presented to</p>
            <div class="winner-name">{{ $winner->participant_name ?? 'Winner' }}</div>

            @if($winner->entry_name)
                <p class="entry-name">"{{ $winner->entry_name }}"</p>
            @endif

            @if($winner->division_name)
                <p class="division">{{ $winner->division_type }} Division - {{ $winner->division_name }}</p>
            @endif

            <div class="place-badge place-{{ $place }}">
                {{ $placeLabel }}
            </div>
        </div>

        <div class="event-info">
            <p class="event-name">{{ $event->name }}</p>
            @if($event->event_date)
                <p class="event-date">{{ $event->event_date->format('F j, Y') }}</p>
            @endif
        </div>

        <div class="footer">
            <div class="signature-line">
                <div class="line"></div>
                <p class="label">Event Organizer</p>
            </div>
            <div class="signature-line">
                <div class="line"></div>
                <p class="label">Date</p>
            </div>
        </div>

        <div class="seal">
            Official<br>Award
        </div>
    </div>
</body>
</html>
