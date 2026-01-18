@extends('layouts.app')

@section('content')
<div>
    <!-- Header -->
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <h1 class="page-title"><i class="fas fa-calendar-alt"></i> Events</h1>
        </div>
        <a href="{{ route('admin.events.create') }}" class="btn btn-warning">
            <i class="fas fa-plus"></i> New Event
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <form action="{{ route('admin.events.index') }}" method="GET">
            <div class="d-flex gap-2" style="flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search events..."
                           class="form-control">
                </div>
                <div>
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div>
                    <select name="template" class="form-control" onchange="this.form.submit()">
                        <option value="">All Templates</option>
                        @foreach(\App\Models\EventTemplate::all() as $template)
                            <option value="{{ $template->id }}" {{ request('template') == $template->id ? 'selected' : '' }}>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>

    <!-- Events Table -->
    <div class="card">
        <table class="table responsive-table">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Template</th>
                    <th class="hide-tablet">Date</th>
                    <th class="hide-tablet">Location</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                    <tr>
                        <td data-label="Event">
                            <strong>{{ $event->name }}</strong>
                            @if($event->description)
                                <span class="mobile-subtitle">{{ \Illuminate\Support\Str::limit($event->description, 50) }}</span>
                            @endif
                            @if($event->event_date)
                                <span class="mobile-subtitle"><i class="fas fa-calendar"></i> {{ $event->event_date->format('M j, Y') }}</span>
                            @endif
                        </td>
                        <td data-label="Template">
                            <span class="badge badge-info">
                                <i class="fas {{ $event->template->icon ?? 'fa-calendar' }}"></i>
                                {{ $event->template->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td data-label="Date" class="hide-tablet">
                            @if($event->event_date)
                                {{ $event->event_date->format('M j, Y') }}
                            @else
                                <span style="color: #9ca3af;">Not set</span>
                            @endif
                        </td>
                        <td data-label="Location" class="hide-tablet">
                            @if($event->location)
                                {{ $event->location }}
                                @if($event->state)
                                    , {{ $event->state->code }}
                                @endif
                            @else
                                <span style="color: #9ca3af;">Not set</span>
                            @endif
                        </td>
                        <td data-label="Status">
                            @if($event->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                            @if($event->is_public)
                                <span class="badge badge-warning">Public</span>
                            @endif
                        </td>
                        <td data-label="Actions" class="text-center">
                            <div class="action-buttons">
                                <a href="{{ route('voting.index', $event) }}" class="action-btn action-btn-vote" title="Vote">
                                    <i class="fas fa-vote-yea"></i>
                                </a>
                                <a href="{{ route('results.index', $event) }}" class="action-btn action-btn-view" title="View Results">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                <a href="{{ route('admin.events.show', $event) }}" class="action-btn" style="background: #e5e7eb; color: #374151;" title="Manage Event">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.events.edit', $event) }}" class="action-btn action-btn-edit" title="Edit Event">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.events.clear-data', $event) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('⚠️ WARNING: This will permanently delete ALL data for this event including:\n\n• Divisions\n• {{ $event->template->participant_label ?? 'Participants' }}\n• {{ $event->template->entry_label ?? 'Entries' }}\n• All Votes\n\nThe event settings will be preserved.\n\nAre you sure you want to continue?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn" style="background: #fef3c7; color: #b45309;" title="Clear All Event Data (Divisions, {{ $event->template->participant_label ?? 'Participants' }}, {{ $event->template->entry_label ?? 'Entries' }}, Votes)">
                                        <i class="fas fa-eraser"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.events.destroy', $event) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('Are you sure you want to DELETE this entire event?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-delete" title="Delete Event">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="6" class="text-center" style="padding: 40px; color: #6b7280;">
                            <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            <p>No events found</p>
                            <a href="{{ route('admin.events.create') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Create your first event
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($events->hasPages())
            <div style="padding: 15px; border-top: 1px solid #e5e7eb;">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>
<style>
    /* Mobile subtitle - hidden on desktop */
    .mobile-subtitle {
        display: none;
    }

    /* Tablet: Hide less important columns */
    @media screen and (max-width: 1023px) {
        .hide-tablet {
            display: none;
        }
    }

    /* Mobile: Card Layout */
    @media screen and (max-width: 768px) {
        .responsive-table {
            border: 0;
        }

        .responsive-table thead {
            display: none;
        }

        .responsive-table tbody {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .responsive-table tbody tr {
            display: block;
            margin-bottom: 15px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 0;
            width: 100%;
            max-width: 400px;
        }

        .responsive-table tbody tr.empty-row {
            display: table-row;
            max-width: none;
        }

        .responsive-table tbody tr.empty-row td {
            display: table-cell;
        }

        .responsive-table tbody tr td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border: none;
            border-bottom: 1px solid #f3f4f6;
            text-align: right;
        }

        .responsive-table tbody tr td:last-child {
            border-bottom: none;
        }

        .responsive-table tbody tr td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #374151;
            text-align: left;
            flex-shrink: 0;
            margin-right: 15px;
        }

        .responsive-table tbody tr td[data-label="Event"] {
            background: #f8fafc;
            border-radius: 8px 8px 0 0;
            flex-direction: column;
            align-items: flex-start;
        }

        .responsive-table tbody tr td[data-label="Event"]::before {
            display: none;
        }

        .mobile-subtitle {
            display: block;
            font-size: 12px;
            color: #6b7280;
            font-weight: normal;
            margin-top: 2px;
        }

        .responsive-table tbody tr td[data-label="Actions"] {
            justify-content: center;
            background: #f9fafb;
            border-radius: 0 0 8px 8px;
            padding: 10px 15px;
            flex-wrap: wrap;
        }

        .responsive-table tbody tr td[data-label="Actions"]::before {
            display: none;
        }

        .action-buttons {
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }

        .d-flex.gap-2 {
            flex-direction: column;
        }

        .d-flex.gap-2 > div,
        .d-flex.gap-2 > button {
            width: 100%;
        }
    }

    @media screen and (max-width: 480px) {
        .d-flex.justify-between {
            flex-direction: column;
            gap: 15px;
        }

        .d-flex.justify-between .btn {
            width: 100%;
        }
    }

</style>
@endsection
