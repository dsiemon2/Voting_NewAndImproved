@extends('layouts.app')

@section('content')
<style>
    .live-results-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .live-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding: 20px;
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        color: white;
        border-radius: 10px;
    }

    .live-header h1 {
        margin: 0;
        font-size: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .live-indicator {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .live-dot {
        width: 12px;
        height: 12px;
        background: #10b981;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    .live-dot.updating {
        background: #f59e0b;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
    }

    .status-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 20px;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e2e8f0;
    }

    .status-info {
        display: flex;
        gap: 25px;
        flex-wrap: wrap;
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        font-size: 14px;
    }

    .status-item .count {
        font-weight: bold;
        color: #1e3a8a;
    }

    .status-actions {
        display: flex;
        gap: 10px;
    }

    .btn-refresh {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        border: 1px solid #ddd;
        background: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-refresh:hover {
        background: #f5f5f5;
    }

    .btn-refresh.rotating svg {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 20px;
    }

    .results-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .results-card-header {
        background: #2c3e50;
        color: white;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .results-card-header h3 {
        margin: 0;
        font-size: 18px;
    }

    .entry-count {
        background: rgba(255,255,255,0.2);
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
    }

    .results-table {
        width: 100%;
        border-collapse: collapse;
    }

    .results-table th {
        background: #f1f5f9;
        padding: 12px 15px;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 600;
    }

    .results-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e2e8f0;
    }

    .results-table tr:last-child td {
        border-bottom: none;
    }

    .results-table tr.top-3 {
        background: #fef9c3;
    }

    .results-table tr.updated {
        animation: highlight 1s ease-out;
    }

    @keyframes highlight {
        from { background: #bbf7d0; }
        to { background: transparent; }
    }

    .rank {
        font-weight: bold;
        font-size: 16px;
        width: 35px;
        text-align: center;
    }

    .rank-1 { color: #ca8a04; }
    .rank-2 { color: #6b7280; }
    .rank-3 { color: #b45309; }

    .entry-info .name {
        font-weight: 600;
        color: #1e293b;
    }

    .entry-info .participant {
        font-size: 12px;
        color: #64748b;
    }

    .points {
        font-weight: bold;
        font-size: 16px;
        color: #1e40af;
    }

    .vote-counts {
        display: flex;
        gap: 8px;
    }

    .vote-badge {
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
    }

    .vote-badge.gold { background: #fef3c7; color: #92400e; }
    .vote-badge.silver { background: #e5e7eb; color: #374151; }
    .vote-badge.bronze { background: #fde68a; color: #78350f; }

    .no-results {
        text-align: center;
        padding: 40px;
        color: #6b7280;
    }

    .connection-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        padding: 10px 15px;
        border-radius: 6px;
        margin-bottom: 15px;
        display: none;
    }

    .connection-error.show {
        display: block;
    }

    @media (max-width: 768px) {
        .live-header {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }

        .status-bar {
            flex-direction: column;
            gap: 15px;
        }

        .results-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="live-results-container">
    <!-- Live Header -->
    <div class="live-header">
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
            </svg>
            {{ $event->name }} - Live Results
        </h1>
        <div class="live-indicator">
            <span class="live-dot" id="liveDot"></span>
            <span>LIVE</span>
        </div>
    </div>

    <!-- Connection Error -->
    <div class="connection-error" id="connectionError">
        <strong>Connection issue:</strong> Unable to fetch latest results. Retrying...
    </div>

    <!-- Status Bar -->
    <div class="status-bar">
        <div class="status-info">
            <div class="status-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span id="voteCount"><span class="count">{{ $event->votes()->count() }}</span> votes</span>
            </div>
            <div class="status-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span>Last update: <span id="lastUpdate">{{ now()->format('g:i:s A') }}</span></span>
            </div>
            <div class="status-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                </svg>
                <span>Auto-refresh: <span id="refreshInterval">10s</span></span>
            </div>
        </div>
        <div class="status-actions">
            <button class="btn-refresh" id="refreshBtn" onclick="manualRefresh()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                </svg>
                Refresh Now
            </button>
            <a href="{{ route('results.index', $event) }}" class="btn-refresh">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="7" height="7" x="3" y="3" rx="1"/>
                    <rect width="7" height="7" x="14" y="3" rx="1"/>
                    <rect width="7" height="7" x="14" y="14" rx="1"/>
                    <rect width="7" height="7" x="3" y="14" rx="1"/>
                </svg>
                Full View
            </a>
        </div>
    </div>

    <!-- Results Grid -->
    <div class="results-grid" id="resultsGrid">
        @forelse($divisions->groupBy('type') as $typeName => $typeDivisions)
            @php
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

            <div class="results-card" data-type="{{ $typeName }}">
                <div class="results-card-header">
                    <h3>{{ $typeName ?? 'Results' }}</h3>
                    <span class="entry-count">{{ $typeResults->count() }} entries</span>
                </div>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>{{ $entryLabel }}</th>
                            <th>Points</th>
                            <th>Votes</th>
                        </tr>
                    </thead>
                    <tbody id="results-{{ Str::slug($typeName) }}">
                        @forelse($typeResults as $index => $result)
                            <tr class="{{ $index < 3 ? 'top-3' : '' }}" data-entry-id="{{ $result->entry_id }}">
                                <td class="rank rank-{{ $index + 1 }}">{{ $index + 1 }}</td>
                                <td class="entry-info">
                                    <div class="name">{{ $result->entry_name }}</div>
                                    <div class="participant">{{ $result->participant_name ?? '' }}</div>
                                </td>
                                <td class="points">{{ number_format($result->total_points ?? 0) }}</td>
                                <td>
                                    <div class="vote-counts">
                                        @if(($result->first_place_count ?? 0) > 0)
                                            <span class="vote-badge gold">{{ $result->first_place_count }}</span>
                                        @endif
                                        @if(($result->second_place_count ?? 0) > 0)
                                            <span class="vote-badge silver">{{ $result->second_place_count }}</span>
                                        @endif
                                        @if(($result->third_place_count ?? 0) > 0)
                                            <span class="vote-badge bronze">{{ $result->third_place_count }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="no-results-row">
                                <td colspan="4" class="no-results">No votes yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @empty
            <div class="results-card" style="grid-column: 1 / -1;">
                <div class="results-card-header">
                    <h3>All Results</h3>
                </div>
                <div class="no-results">
                    <p>No results available yet. Vote now to see live updates!</p>
                    <a href="{{ route('voting.index', $event) }}" class="btn-refresh" style="display: inline-flex; margin-top: 15px;">
                        Cast Your Vote
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
const POLL_INTERVAL = 10000; // 10 seconds
const POLL_URL = '{{ route('results.poll', $event) }}';
let lastVoteCount = {{ $event->votes()->count() }};
let pollTimer = null;
let consecutiveErrors = 0;

function updateResults() {
    const liveDot = document.getElementById('liveDot');
    const refreshBtn = document.getElementById('refreshBtn');
    const connectionError = document.getElementById('connectionError');

    liveDot.classList.add('updating');
    refreshBtn.classList.add('rotating');

    fetch(POLL_URL, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        consecutiveErrors = 0;
        connectionError.classList.remove('show');

        // Update vote count
        document.getElementById('voteCount').innerHTML =
            '<span class="count">' + data.vote_count + '</span> votes';

        // Update last update time
        document.getElementById('lastUpdate').textContent =
            new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit' });

        // Check if there are new votes
        if (data.vote_count !== lastVoteCount) {
            lastVoteCount = data.vote_count;
            updateResultsTables(data.results);
        }

        liveDot.classList.remove('updating');
        refreshBtn.classList.remove('rotating');
    })
    .catch(error => {
        console.error('Error fetching results:', error);
        consecutiveErrors++;

        liveDot.classList.remove('updating');
        refreshBtn.classList.remove('rotating');

        if (consecutiveErrors >= 3) {
            connectionError.classList.add('show');
        }
    });
}

function updateResultsTables(results) {
    // Group results by division type
    const groupedResults = {};
    results.forEach(result => {
        const type = result.division_type || 'Other';
        if (!groupedResults[type]) {
            groupedResults[type] = [];
        }
        groupedResults[type].push(result);
    });

    // Sort each group by points and update tables
    Object.keys(groupedResults).forEach(type => {
        const sorted = groupedResults[type].sort((a, b) => b.total_points - a.total_points);
        const tableId = 'results-' + type.toLowerCase().replace(/\s+/g, '-');
        const tbody = document.getElementById(tableId);

        if (tbody) {
            updateTableBody(tbody, sorted);
        }
    });
}

function updateTableBody(tbody, results) {
    // Build new HTML
    let html = '';
    results.forEach((result, index) => {
        const isTop3 = index < 3 ? 'top-3' : '';
        const rankClass = index < 3 ? 'rank-' + (index + 1) : '';

        let voteBadges = '';
        if (result.first_place_count > 0) {
            voteBadges += '<span class="vote-badge gold">' + result.first_place_count + '</span>';
        }
        if (result.second_place_count > 0) {
            voteBadges += '<span class="vote-badge silver">' + result.second_place_count + '</span>';
        }
        if (result.third_place_count > 0) {
            voteBadges += '<span class="vote-badge bronze">' + result.third_place_count + '</span>';
        }

        html += `
            <tr class="${isTop3} updated" data-entry-id="${result.entry_id}">
                <td class="rank ${rankClass}">${index + 1}</td>
                <td class="entry-info">
                    <div class="name">${result.entry_name}</div>
                    <div class="participant">${result.participant_name || ''}</div>
                </td>
                <td class="points">${result.total_points.toLocaleString()}</td>
                <td>
                    <div class="vote-counts">${voteBadges}</div>
                </td>
            </tr>
        `;
    });

    if (results.length === 0) {
        html = '<tr class="no-results-row"><td colspan="4" class="no-results">No votes yet</td></tr>';
    }

    tbody.innerHTML = html;

    // Remove animation class after animation completes
    setTimeout(() => {
        const rows = tbody.querySelectorAll('.updated');
        rows.forEach(row => row.classList.remove('updated'));
    }, 1000);
}

function manualRefresh() {
    updateResults();
}

// Start polling
function startPolling() {
    pollTimer = setInterval(updateResults, POLL_INTERVAL);
}

// Stop polling when page is hidden
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        clearInterval(pollTimer);
    } else {
        updateResults();
        startPolling();
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    startPolling();
});
</script>
@endsection
