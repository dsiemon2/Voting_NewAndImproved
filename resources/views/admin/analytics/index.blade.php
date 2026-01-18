@extends('layouts.app')

@section('title', 'Analytics - ' . $event->name)

@section('content')
<div class="analytics-page">
    <div class="page-header">
        <div>
            <h1>Analytics Dashboard</h1>
            <p class="subtitle">{{ $event->name }}</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.events.pdf.summary', $event) }}" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export PDF
            </a>
            <a href="{{ route('admin.events.show', $event) }}" class="btn btn-outline">Back to Event</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_votes']) }}</div>
            <div class="stat-label">Total Votes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['unique_voters']) }}</div>
            <div class="stat-label">Unique Voters</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['participation_rate'] }}%</div>
            <div class="stat-label">Participation Rate</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_points'], 1) }}</div>
            <div class="stat-label">Total Points</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['avg_votes_per_entry'] }}</div>
            <div class="stat-label">Avg Votes/Entry</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $voterMetrics['avg_votes_per_voter'] }}</div>
            <div class="stat-label">Avg Votes/Voter</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <div class="chart-card">
            <h3>Voting Trends Over Time</h3>
            <canvas id="votingTrendsChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Votes by Division</h3>
            <canvas id="divisionChart"></canvas>
        </div>
    </div>

    <div class="charts-row">
        <div class="chart-card">
            <h3>Place Distribution</h3>
            <canvas id="placeChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Hourly Voting Activity</h3>
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>

    <!-- Top Performers Table -->
    <div class="data-card">
        <h3>Top Performers</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Entry</th>
                        <th>{{ $event->template->participant_label ?? 'Participant' }}</th>
                        <th>Division</th>
                        <th class="text-center">1st</th>
                        <th class="text-center">2nd</th>
                        <th class="text-center">3rd</th>
                        <th class="text-center">Total Votes</th>
                        <th class="text-right">Points</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topPerformers as $index => $performer)
                        <tr class="{{ $index < 3 ? 'highlight-' . ($index + 1) : '' }}">
                            <td class="rank rank-{{ $index + 1 }}">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $performer->entry_name }}</strong>
                                <span class="entry-number">#{{ $performer->entry_number }}</span>
                            </td>
                            <td>{{ $performer->participant_name ?? '-' }}</td>
                            <td>{{ $performer->division_name ?? '-' }}</td>
                            <td class="text-center">
                                @if($performer->first_count > 0)
                                    <span class="place-badge place-1">{{ $performer->first_count }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($performer->second_count > 0)
                                    <span class="place-badge place-2">{{ $performer->second_count }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($performer->third_count > 0)
                                    <span class="place-badge place-3">{{ $performer->third_count }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">{{ $performer->vote_count }}</td>
                            <td class="text-right points">{{ number_format($performer->total_points, 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Division Breakdown -->
    <div class="data-card">
        <h3>Division Breakdown</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Division</th>
                        <th>Type</th>
                        <th class="text-center">Entries Voted</th>
                        <th class="text-center">Total Votes</th>
                        <th class="text-right">Total Points</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($divisionBreakdown['data'] as $division)
                        <tr>
                            <td><strong>{{ $division->division_name ?? 'No Division' }}</strong></td>
                            <td>{{ $division->division_type ?? '-' }}</td>
                            <td class="text-center">{{ $division->entries_voted }}</td>
                            <td class="text-center">{{ $division->vote_count }}</td>
                            <td class="text-right points">{{ number_format($division->total_points, 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.analytics-page {
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 28px;
    color: #2c3e50;
    margin-bottom: 5px;
}

.page-header .subtitle {
    color: #666;
    font-size: 16px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 15px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #1e40af;
}

.stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 5px;
}

.charts-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.chart-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chart-card h3 {
    font-size: 16px;
    color: #2c3e50;
    margin-bottom: 15px;
}

.data-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.data-card h3 {
    font-size: 18px;
    color: #2c3e50;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f3f4f6;
    padding: 12px;
    text-align: left;
    font-size: 11px;
    text-transform: uppercase;
    color: #666;
    border-bottom: 2px solid #e5e7eb;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.data-table .text-center {
    text-align: center;
}

.data-table .text-right {
    text-align: right;
}

.rank {
    font-weight: bold;
    font-size: 16px;
}

.rank-1 { color: #d97706; }
.rank-2 { color: #6b7280; }
.rank-3 { color: #b45309; }

.highlight-1 { background: #fef3c7; }
.highlight-2 { background: #f3f4f6; }
.highlight-3 { background: #fde68a; }

.entry-number {
    color: #999;
    font-size: 12px;
    margin-left: 5px;
}

.place-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.place-1 { background: #fef3c7; color: #92400e; }
.place-2 { background: #e5e7eb; color: #374151; }
.place-3 { background: #fde68a; color: #92400e; }

.points {
    font-weight: bold;
    color: #1e40af;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    border: none;
}

.btn-secondary {
    background: #34495e;
    color: #fff;
}

.btn-secondary:hover {
    background: #2c3e50;
}

.btn-outline {
    background: transparent;
    border: 1px solid #ddd;
    color: #666;
}

.btn-outline:hover {
    background: #f5f5f5;
}

@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .charts-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Voting Trends Chart
    const trendsCtx = document.getElementById('votingTrendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: @json($votingTrends['labels']),
            datasets: [{
                label: 'Votes',
                data: @json($votingTrends['votes']),
                borderColor: '#1e40af',
                backgroundColor: 'rgba(30, 64, 175, 0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Unique Voters',
                data: @json($votingTrends['voters']),
                borderColor: '#d97706',
                backgroundColor: 'rgba(217, 119, 6, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Division Chart
    const divisionCtx = document.getElementById('divisionChart').getContext('2d');
    new Chart(divisionCtx, {
        type: 'doughnut',
        data: {
            labels: @json($divisionBreakdown['labels']),
            datasets: [{
                data: @json($divisionBreakdown['votes']),
                backgroundColor: [
                    '#1e40af',
                    '#d97706',
                    '#059669',
                    '#7c3aed',
                    '#dc2626',
                    '#0891b2'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Place Distribution Chart
    const placeCtx = document.getElementById('placeChart').getContext('2d');
    new Chart(placeCtx, {
        type: 'bar',
        data: {
            labels: @json($placeDistribution['labels']),
            datasets: [{
                label: 'Votes',
                data: @json($placeDistribution['counts']),
                backgroundColor: ['#fbbf24', '#9ca3af', '#b45309', '#6366f1', '#10b981']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Hourly Activity Chart
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: @json($voterMetrics['hourly_labels']),
            datasets: [{
                label: 'Votes',
                data: @json($voterMetrics['hourly_votes']),
                backgroundColor: '#1e40af'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection
