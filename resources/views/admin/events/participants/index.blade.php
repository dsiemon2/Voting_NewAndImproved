@extends('layouts.app')

@section('content')
@php
    $participantLabel = $event->template->participant_label ?? 'Participants';
    $participantLabelSingular = \Illuminate\Support\Str::singular($participantLabel);
@endphp

<div>
    <!-- Header -->
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <a href="{{ route('admin.events.show', $event) }}" style="color: #6b7280; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to {{ $event->name }}
            </a>
            <h1 class="page-title" style="margin-top: 10px;">
                <i class="fas fa-users"></i> {{ $participantLabel }} List
            </h1>
        </div>
        <a href="{{ route('admin.events.participants.create', $event) }}" class="btn btn-warning">
            <i class="fas fa-plus"></i> Add New
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <form action="{{ route('admin.events.participants.index', $event) }}" method="GET" class="filter-form">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search {{ strtolower($participantLabel) }}..."
                   class="form-control search-input">
            @if($event->hasModule('divisions') && $divisions->count())
                <select name="division" class="form-control" onchange="this.form.submit()">
                    <option value="">All Divisions</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" {{ request('division') == $division->id ? 'selected' : '' }}>
                            {{ $division->code }} - {{ $division->type }}
                        </option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>

    <!-- Participants Table -->
    <div class="card">
        <table class="table responsive-table" id="participants-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>{{ $participantLabelSingular }} Name</th>
                    <th class="hide-tablet">Company</th>
                    <th>Division</th>
                    <th class="hide-tablet">Event</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($participants as $index => $participant)
                    <tr>
                        <td data-label="#"><strong>{{ $participants->firstItem() + $index }}</strong></td>
                        <td data-label="Name">
                            <strong>{{ $participant->name }}</strong>
                            @if($participant->number)
                                <span class="mobile-subtitle">#{{ $participant->number }}</span>
                            @endif
                            @if($participant->organization)
                                <span class="mobile-subtitle">{{ $participant->organization }}</span>
                            @endif
                        </td>
                        <td data-label="Company" class="hide-tablet">{{ $participant->organization ?? '-' }}</td>
                        <td data-label="Division">
                            @if($participant->division)
                                <span class="badge badge-info">{{ $participant->division->code }}</span>
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>
                        <td data-label="Event" class="hide-tablet">{{ $event->name }}</td>
                        <td data-label="Status">
                            @if($participant->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <a href="{{ route('admin.events.participants.edit', [$event, $participant]) }}"
                                   class="action-btn action-btn-edit"
                                   title="Edit"
                                   onclick="event.stopPropagation();">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.events.participants.destroy', [$event, $participant]) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('Are you sure? This will also remove all entries for this {{ strtolower($participantLabelSingular) }}.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="action-btn action-btn-delete"
                                            title="Delete"
                                            onclick="event.stopPropagation();">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="7" class="text-center" style="padding: 40px; color: #6b7280;">
                            <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            <p>No {{ strtolower($participantLabel) }} yet</p>
                            <a href="{{ route('admin.events.participants.create', $event) }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Add your first {{ strtolower($participantLabelSingular) }}
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($participants->hasPages())
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing {{ $participants->firstItem() }} to {{ $participants->lastItem() }} of {{ $participants->total() }} {{ strtolower($participantLabel) }}
                </div>
                <div class="pagination-links">
                    @if($participants->onFirstPage())
                        <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a href="{{ $participants->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>
                    @endif

                    @foreach($participants->getUrlRange(1, $participants->lastPage()) as $page => $url)
                        @if($page == $participants->currentPage())
                            <span class="page-link active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($participants->hasMorePages())
                        <a href="{{ $participants->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>
                    @else
                        <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
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

        .responsive-table tbody tr td[data-label="Name"] {
            background: #f8fafc;
            border-radius: 8px 8px 0 0;
            flex-direction: column;
            align-items: flex-start;
        }

        .responsive-table tbody tr td[data-label="Name"]::before {
            display: none;
        }

        .mobile-subtitle {
            display: block;
            font-size: 12px;
            color: #6b7280;
            font-weight: normal;
            margin-top: 2px;
        }

        .responsive-table tbody tr td[data-label="#"] {
            display: none;
        }

        .responsive-table tbody tr td[data-label="Actions"] {
            justify-content: flex-end;
            background: #f9fafb;
            border-radius: 0 0 8px 8px;
            padding: 10px 15px;
        }

        .responsive-table tbody tr td[data-label="Actions"]::before {
            display: none;
        }

        .action-buttons {
            gap: 10px;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }

        .pagination-container {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .pagination-info {
            order: 2;
        }

        .pagination-links {
            order: 1;
            flex-wrap: wrap;
            justify-content: center;
        }

        .filter-form {
            flex-direction: column;
        }

        .filter-form .form-control,
        .filter-form .btn {
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

    /* Pagination Styles */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .pagination-info {
        color: #6b7280;
        font-size: 14px;
    }

    .pagination-links {
        display: flex;
        gap: 5px;
    }

    .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: white;
        color: #374151;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.15s ease;
    }

    .page-link:hover:not(.disabled):not(.active) {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .page-link.active {
        background: #1e40af;
        border-color: #1e40af;
        color: white;
    }

    .page-link.disabled {
        color: #9ca3af;
        cursor: not-allowed;
        opacity: 0.6;
    }
</style>
@endsection
