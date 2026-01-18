@extends('layouts.app')

@section('content')
<style>
    .thank-you-container {
        max-width: 600px;
        margin: 60px auto;
        text-align: center;
    }

    .thank-you-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        padding: 50px 40px;
        position: relative;
        overflow: hidden;
    }

    .thank-you-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #10b981, #059669);
    }

    .success-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    }

    .success-icon i {
        font-size: 50px;
        color: white;
    }

    .thank-you-card h1 {
        font-size: 32px;
        color: #1e293b;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .thank-you-card .event-name {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 600;
    }

    .thank-you-card p {
        color: #64748b;
        font-size: 18px;
        margin-bottom: 35px;
        line-height: 1.6;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-results {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        color: white;
        border: none;
        padding: 15px 30px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-results:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(30, 58, 138, 0.3);
        color: white;
    }

    .btn-vote-again {
        background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
        color: white;
        border: none;
        padding: 15px 30px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-vote-again:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 102, 0, 0.3);
        color: white;
    }

    .btn-dashboard {
        background: #f1f5f9;
        color: #475569;
        border: 2px solid #e2e8f0;
        padding: 13px 28px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-dashboard:hover {
        background: #e2e8f0;
        color: #1e293b;
        border-color: #cbd5e1;
    }

    .confetti {
        position: absolute;
        width: 10px;
        height: 10px;
        opacity: 0;
    }

    @keyframes confetti-fall {
        0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
        100% { transform: translateY(600px) rotate(720deg); opacity: 0; }
    }

    .event-info {
        background: #f8fafc;
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 30px;
        display: inline-block;
    }

    .event-info .label {
        color: #94a3b8;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 5px;
    }

    .event-info .value {
        color: #1e293b;
        font-size: 18px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .thank-you-container {
            margin: 20px auto;
            padding: 0 15px;
        }

        .thank-you-card {
            padding: 30px 20px;
        }

        .thank-you-card h1 {
            font-size: 24px;
        }

        .thank-you-card p {
            font-size: 16px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-results, .btn-vote-again, .btn-dashboard {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="thank-you-container">
    <div class="thank-you-card">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <h1>Thank You for Voting!</h1>

        <div class="event-info">
            <div class="label">Event</div>
            <div class="value">{{ $event->name }}</div>
        </div>

        <p>
            Your votes have been recorded successfully.<br>
            The results are being updated in real-time.
        </p>

        <div class="action-buttons">
            <a href="{{ route('results.index', $event) }}" class="btn-results">
                <i class="fas fa-trophy"></i>
                View Results
            </a>

            <a href="{{ route('voting.index', $event) }}" class="btn-vote-again">
                <i class="fas fa-vote-yea"></i>
                Vote Again
            </a>

            <a href="{{ route('dashboard') }}" class="btn-dashboard">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
        </div>
    </div>
</div>

<script>
    // Simple confetti effect
    document.addEventListener('DOMContentLoaded', function() {
        const colors = ['#ff6600', '#1e3a8a', '#10b981', '#ffd700', '#c0c0c0'];
        const container = document.querySelector('.thank-you-card');

        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animation = `confetti-fall ${2 + Math.random() * 2}s linear ${Math.random() * 2}s`;
            confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
            container.appendChild(confetti);
        }
    });
</script>
@endsection
