@extends('layouts.app')

@section('content')
<div>
    <!-- Header -->
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <h1 class="page-title"><i class="fas fa-poll"></i> Voting Types</h1>
        </div>
        <a href="{{ route('admin.voting-types.create') }}" class="btn btn-warning">
            <i class="fas fa-plus"></i> New Voting Type
        </a>
    </div>

    <!-- Voting Types Table -->
    <div class="card mb-4">
        <table class="table responsive-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th class="hide-tablet">Description</th>
                    <th>Places</th>
                    <th class="hide-mobile">Point Distribution</th>
                    <th class="hide-tablet">Events</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($votingTypes as $type)
                    <tr>
                        <td data-label="Name">
                            <strong>{{ $type->name }}</strong>
                            <span class="mobile-subtitle">{{ \Illuminate\Support\Str::limit($type->description, 40) }}</span>
                            <span class="mobile-subtitle">
                                @foreach($type->placeConfigs->sortBy('place')->take(3) as $config)
                                    {{ ordinal($config->place) }}: {{ $config->points }}pts{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </span>
                        </td>
                        <td data-label="Description" class="hide-tablet">
                            <span style="color: #6b7280;">{{ \Illuminate\Support\Str::limit($type->description, 50) }}</span>
                        </td>
                        <td data-label="Places">
                            <span class="badge badge-info">{{ $type->placeConfigs->count() }}</span>
                        </td>
                        <td data-label="Points" class="hide-mobile">
                            @foreach($type->placeConfigs->sortBy('place')->take(5) as $config)
                                <span class="label-tag">
                                    {{ ordinal($config->place) }}: {{ $config->points }}pts
                                </span>
                            @endforeach
                            @if($type->placeConfigs->count() > 5)
                                <span style="color: #9ca3af; font-size: 12px;">+{{ $type->placeConfigs->count() - 5 }} more</span>
                            @endif
                        </td>
                        <td data-label="Events" class="hide-tablet">{{ $type->events_count ?? 0 }}</td>
                        <td data-label="Status">
                            @if($type->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <a href="{{ route('admin.voting-types.edit', $type) }}"
                                   class="action-btn action-btn-edit"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(($type->events_count ?? 0) === 0)
                                    <form action="{{ route('admin.voting-types.destroy', $type) }}"
                                          method="POST"
                                          style="display: inline;"
                                          onsubmit="return confirm('Are you sure you want to delete this voting type?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="action-btn action-btn-delete"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="7" class="text-center" style="padding: 40px; color: #6b7280;">
                            <i class="fas fa-poll" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            <p>No voting types configured</p>
                            <a href="{{ route('admin.voting-types.create') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Create your first voting type
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($votingTypes->hasPages())
            <div style="padding: 15px; border-top: 1px solid #e5e7eb;">
                {{ $votingTypes->links() }}
            </div>
        @endif
    </div>

    <!-- Presets Section -->
    <div class="card-header mb-3">
        <i class="fas fa-magic"></i> Quick Start Presets
    </div>
    <div class="template-grid">
        <div class="card">
            <div style="padding: 20px;">
                <h3 style="margin-bottom: 10px; color: #1e3a8a;"><i class="fas fa-medal" style="color: #ff6600;"></i> Standard 3-2-1</h3>
                <p style="color: #6b7280; font-size: 14px; margin-bottom: 15px;">Classic 3 places with 3, 2, 1 points</p>
                <form action="{{ route('admin.voting-types.preset') }}" method="POST">
                    @csrf
                    <input type="hidden" name="preset" value="standard">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-plus"></i> Create
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div style="padding: 20px;">
                <h3 style="margin-bottom: 10px; color: #1e3a8a;"><i class="fas fa-trophy" style="color: #ff6600;"></i> Extended 5-4-3-2-1</h3>
                <p style="color: #6b7280; font-size: 14px; margin-bottom: 15px;">5 places with descending points</p>
                <form action="{{ route('admin.voting-types.preset') }}" method="POST">
                    @csrf
                    <input type="hidden" name="preset" value="extended">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-plus"></i> Create
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div style="padding: 20px;">
                <h3 style="margin-bottom: 10px; color: #1e3a8a;"><i class="fas fa-crown" style="color: #ff6600;"></i> Top-Heavy 5-3-1</h3>
                <p style="color: #6b7280; font-size: 14px; margin-bottom: 15px;">3 places with emphasis on 1st place</p>
                <form action="{{ route('admin.voting-types.preset') }}" method="POST">
                    @csrf
                    <input type="hidden" name="preset" value="top-heavy">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-plus"></i> Create
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@php
function ordinal($number) {
    $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
    if (($number % 100) >= 11 && ($number % 100) <= 13) {
        return $number . 'th';
    }
    return $number . $ends[$number % 10];
}
@endphp

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

    @media screen and (max-width: 900px) {
        .hide-mobile {
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

        .template-grid {
            grid-template-columns: 1fr;
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
