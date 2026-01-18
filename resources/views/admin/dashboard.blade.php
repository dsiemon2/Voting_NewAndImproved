@extends('layouts.app')

@section('content')
<div>
    <!-- Welcome Header -->
    <h1 class="page-title">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </h1>
    <p style="color: #6b7280; margin-bottom: 20px;">Welcome, {{ $user->first_name }}! Here's an overview of your voting system.</p>

    <!-- Stats Cards -->
    <div class="grid grid-3" style="margin-bottom: 30px;">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-value">{{ $activeEvents->count() }}</div>
            <div class="stat-label">Active Events</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #10b981;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value">{{ $totalUsers }}</div>
            <div class="stat-label">Total Users</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #ff6600;">
                <i class="fas fa-vote-yea"></i>
            </div>
            <div class="stat-value">{{ $upcomingEvents->count() }}</div>
            <div class="stat-label">Upcoming Events</div>
        </div>
    </div>

    <div class="grid grid-2">
        <!-- Active Events -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar-check"></i> Active Events
            </div>
            @if($activeEvents->isEmpty())
                <p style="color: #6b7280; text-align: center; padding: 20px;">No active events</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Template</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeEvents->take(5) as $event)
                            <tr>
                                <td><strong>{{ $event->name }}</strong></td>
                                <td>{{ $event->template->name ?? 'General' }}</td>
                                <td>
                                    @if($event->event_date)
                                        {{ $event->event_date->format('M j, Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('voting.index', $event) }}" class="btn btn-sm btn-primary" title="Vote">
                                        <i class="fas fa-vote-yea"></i>
                                    </a>
                                    <a href="{{ route('results.index', $event) }}" class="btn btn-sm btn-success" title="Results">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                    <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-secondary" title="Manage">
                                        <i class="fas fa-cog"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            <div style="padding: 15px; border-top: 1px solid #e5e7eb;">
                <a href="{{ route('admin.events.index') }}" style="color: #2563eb; text-decoration: none;">
                    View all events <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt"></i> Quick Actions
            </div>
            <div class="grid grid-2" style="padding: 10px;">
                <a href="{{ route('admin.events.create') }}" class="stat-card" style="text-decoration: none; color: inherit;">
                    <div class="stat-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="stat-label" style="font-weight: bold;">New Event</div>
                </a>

                <a href="{{ route('admin.templates.index') }}" class="stat-card" style="text-decoration: none; color: inherit;">
                    <div class="stat-icon" style="color: #ff6600;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-label" style="font-weight: bold;">Templates</div>
                </a>

                <a href="{{ route('admin.voting-types.index') }}" class="stat-card" style="text-decoration: none; color: inherit;">
                    <div class="stat-icon" style="color: #10b981;">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <div class="stat-label" style="font-weight: bold;">Voting Types</div>
                </a>

                @if($user->isAdmin())
                    <a href="{{ route('admin.users.index') }}" class="stat-card" style="text-decoration: none; color: inherit;">
                        <div class="stat-icon" style="color: #8b5cf6;">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div class="stat-label" style="font-weight: bold;">Users</div>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
