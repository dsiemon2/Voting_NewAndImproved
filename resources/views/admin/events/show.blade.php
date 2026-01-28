@extends('layouts.app')

@section('content')
<div>
    <!-- Header -->
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <a href="{{ route('admin.events.index') }}" style="color: #6b7280; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Events
            </a>
            <h1 class="page-title" style="margin-top: 10px;">
                <i class="fas {{ $event->template->icon ?? 'fa-calendar' }}"></i>
                {{ $event->name }}
            </h1>
            <div style="margin-top: 10px;">
                <span class="badge badge-info">{{ $event->template->name ?? 'N/A' }}</span>
                @if($event->is_active)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
                @if($event->is_public)
                    <span class="badge badge-warning">Public</span>
                @endif
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('voting.index', $event) }}" class="btn btn-success">
                <i class="fas fa-vote-yea"></i> Vote
            </a>
            <a href="{{ route('results.index', $event) }}" class="btn btn-primary">
                <i class="fas fa-chart-bar"></i> Results
            </a>
            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-4 mb-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-value">{{ $event->entries_count ?? $event->entries->count() }}</div>
            <div class="stat-label">{{ $event->template->entry_label ?? 'Entries' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: #10b981;"><i class="fas fa-vote-yea"></i></div>
            <div class="stat-value" style="color: #10b981;">{{ $event->votes_count ?? $event->votes->count() }}</div>
            <div class="stat-label">Votes Cast</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="color: #8b5cf6;"><i class="fas fa-layer-group"></i></div>
            <div class="stat-value" style="color: #8b5cf6;">{{ $event->divisions_count ?? $event->divisions->count() }}</div>
            <div class="stat-label">Divisions</div>
        </div>
        @if($event->hasModule('judging'))
        <div class="stat-card">
            <div class="stat-icon" style="color: #db2777;"><i class="fas fa-gavel"></i></div>
            <div class="stat-value" style="color: #db2777;">{{ $event->judges_count ?? $event->judges->count() }}</div>
            <div class="stat-label">Judges</div>
        </div>
        @else
        <div class="stat-card">
            <div class="stat-icon" style="color: #f59e0b;"><i class="fas fa-users"></i></div>
            <div class="stat-value" style="color: #f59e0b;">{{ $event->participants_count ?? $event->participants->count() }}</div>
            <div class="stat-label">{{ $event->template->participant_label ?? 'Participants' }}</div>
        </div>
        @endif
    </div>

    <!-- Main Content Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Left Column -->
        <div>
            <!-- Event Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Event Details
                </div>
                <div style="padding: 20px;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 10px 0; color: #6b7280; width: 150px;"><i class="fas fa-calendar"></i> Event Date</td>
                            <td style="padding: 10px 0; font-weight: bold;">{{ $event->event_date ? $event->event_date->format('F j, Y') : 'Not set' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; color: #6b7280;"><i class="fas fa-map-marker-alt"></i> Location</td>
                            <td style="padding: 10px 0; font-weight: bold;">
                                {{ $event->location ?? 'Not set' }}
                                @if($event->state), {{ $event->state->name }}@endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; color: #6b7280;"><i class="fas fa-poll"></i> Voting Type</td>
                            <td style="padding: 10px 0; font-weight: bold;">{{ $event->votingType->name ?? 'Not configured' }}</td>
                        </tr>
                        @if($event->description)
                        <tr>
                            <td style="padding: 10px 0; color: #6b7280; vertical-align: top;"><i class="fas fa-align-left"></i> Description</td>
                            <td style="padding: 10px 0;">{{ $event->description }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Manage Event - Module Links -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-cogs"></i> Manage Event
                </div>
                <div style="padding: 20px;">
                    <div class="template-grid">
                        @if($event->hasModule('divisions'))
                            <a href="{{ route('admin.events.divisions.index', $event) }}" class="module-link-card">
                                <div class="module-link-icon" style="background: #dbeafe; color: #0d7a3e;">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <div class="module-link-info">
                                    <strong>Divisions</strong>
                                    <span>{{ $event->divisions->count() }} total</span>
                                </div>
                            </a>
                        @endif

                        @if($event->hasModule('participants'))
                            <a href="{{ route('admin.events.participants.index', $event) }}" class="module-link-card">
                                <div class="module-link-icon" style="background: #d1fae5; color: #10b981;">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="module-link-info">
                                    <strong>{{ $event->template->participant_label ?? 'Participants' }}</strong>
                                    <span>{{ $event->participants->count() }} total</span>
                                </div>
                            </a>
                        @endif

                        @if($event->hasModule('entries'))
                            <a href="{{ route('admin.events.entries.index', $event) }}" class="module-link-card">
                                <div class="module-link-icon" style="background: #fef3c7; color: #d97706;">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="module-link-info">
                                    <strong>{{ $event->template->entry_label ?? 'Entries' }}</strong>
                                    <span>{{ $event->entries->count() }} total</span>
                                </div>
                            </a>
                        @endif

                        @if($event->hasModule('categories'))
                            <a href="{{ route('admin.events.categories.index', $event) }}" class="module-link-card">
                                <div class="module-link-icon" style="background: #f3e8ff; color: #9333ea;">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <div class="module-link-info">
                                    <strong>Categories</strong>
                                    <span>{{ $event->categories->count() ?? 0 }} total</span>
                                </div>
                            </a>
                        @endif

                        @if($event->hasModule('import'))
                            <a href="{{ route('admin.events.import', $event) }}" class="module-link-card">
                                <div class="module-link-icon" style="background: #e0e7ff; color: #4f46e5;">
                                    <i class="fas fa-file-import"></i>
                                </div>
                                <div class="module-link-info">
                                    <strong>Import Data</strong>
                                    <span>Bulk import</span>
                                </div>
                            </a>
                        @endif

                        @if($event->hasModule('pdf'))
                            <a href="{{ route('admin.events.ballots', $event) }}" class="module-link-card">
                                <div class="module-link-icon" style="background: #fee2e2; color: #dc2626;">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div class="module-link-info">
                                    <strong>Print Ballots</strong>
                                    <span>PDF export</span>
                                </div>
                            </a>
                        @endif

                        @if($event->hasModule('judging'))
                            <a href="{{ route('admin.events.judges.index', $event) }}" class="module-link-card">
                                <div class="module-link-icon" style="background: #fdf2f8; color: #db2777;">
                                    <i class="fas fa-gavel"></i>
                                </div>
                                <div class="module-link-info">
                                    <strong>Judging Panel</strong>
                                    <span>{{ $event->judges->count() ?? 0 }} judges</span>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div>
            <!-- Voting Configuration -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-sliders-h"></i> Voting Configuration
                </div>
                <div style="padding: 20px;">
                    @if($event->votingType)
                        <h4 style="margin-bottom: 10px;">{{ $event->votingType->name }}</h4>
                        <p style="color: #6b7280; font-size: 13px; margin-bottom: 15px;">{{ $event->votingType->description }}</p>

                        <strong style="font-size: 12px; color: #374151;">Point Distribution:</strong>
                        <div style="margin-top: 10px;">
                            @foreach($event->votingType->placeConfigs->sortBy('place') as $config)
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                                    <span>{{ ordinal($config->place) }} Place</span>
                                    <span class="badge badge-info">{{ $config->points }} pts</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="color: #6b7280;">No voting type configured</p>
                        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-cog"></i> Configure
                        </a>
                    @endif
                </div>
            </div>

            <!-- Enabled Modules -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-puzzle-piece"></i> Enabled Modules
                </div>
                <div style="padding: 20px;">
                    @forelse($event->template->modules as $module)
                        <div style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                            <i class="fas {{ $module->icon ?? 'fa-check' }}" style="color: #10b981; margin-right: 10px; width: 20px;"></i>
                            <span>{{ $module->name }}</span>
                        </div>
                    @empty
                        <p style="color: #6b7280;">No modules enabled</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Votes -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history"></i> Recent Activity
                </div>
                <div style="padding: 20px;">
                    @php
                        $recentVotes = $event->votes()->with('user')->latest()->take(5)->get();
                    @endphp
                    @forelse($recentVotes as $vote)
                        <div style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                            <strong>{{ $vote->user->name ?? 'Anonymous' }}</strong>
                            <span style="display: block; font-size: 12px; color: #6b7280;">
                                {{ $vote->created_at->diffForHumans() }}
                            </span>
                        </div>
                    @empty
                        <p style="color: #6b7280; text-align: center; padding: 20px 0;">
                            <i class="fas fa-vote-yea" style="font-size: 32px; display: block; margin-bottom: 10px; color: #d1d5db;"></i>
                            No votes yet
                        </p>
                    @endforelse

                    @if($recentVotes->count() > 0)
                        <div style="margin-top: 15px; text-align: center;">
                            <a href="{{ route('results.index', $event) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-chart-bar"></i> View All Results
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

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
@endsection
