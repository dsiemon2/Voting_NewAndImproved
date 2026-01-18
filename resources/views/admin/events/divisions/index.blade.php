@extends('layouts.app')

@section('content')
<div>
    <!-- Header -->
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <a href="{{ route('admin.events.show', $event) }}" style="color: #6b7280; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to {{ $event->name }}
            </a>
            <h1 class="page-title" style="margin-top: 10px;">
                <i class="fas fa-layer-group"></i> Divisions
            </h1>
        </div>
        <button type="button"
                onclick="document.getElementById('create-modal').classList.remove('hidden')"
                class="btn btn-warning">
            <i class="fas fa-plus"></i> Add Division
        </button>
    </div>

    <!-- Divisions Table -->
    <div class="card">
        <table class="table responsive-table" id="divisions-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Division</th>
                    <th class="hide-tablet">Description</th>
                    <th class="hide-tablet">Event</th>
                    <th>Entries</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($divisions as $index => $division)
                    <tr>
                        <td data-label="#"><strong>{{ $divisions->firstItem() + $index }}</strong></td>
                        <td data-label="Division">
                            <strong style="color: #1e40af;">{{ $division->code }}</strong>
                            <span class="mobile-subtitle">{{ $division->type ?? $division->name }}</span>
                        </td>
                        <td data-label="Description" class="hide-tablet">{{ $division->type ?? $division->name }}</td>
                        <td data-label="Event" class="hide-tablet">{{ $event->name }}</td>
                        <td data-label="Entries">
                            <span class="badge badge-primary">{{ $division->entries_count ?? $division->entries->count() }}</span>
                        </td>
                        <td data-label="Status">
                            @if($division->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <button type="button"
                                        onclick="event.stopPropagation(); editDivision({{ $division->id }}, '{{ addslashes($division->code) }}', '{{ addslashes($division->type ?? '') }}', '{{ addslashes($division->name ?? '') }}', '{{ addslashes($division->description ?? '') }}', {{ $division->is_active ? 'true' : 'false' }}, {{ $division->display_order ?? 0 }})"
                                        class="action-btn action-btn-edit"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.events.divisions.destroy', [$event, $division]) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('Are you sure? This will also affect all entries in this division.')">
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
                            <i class="fas fa-layer-group" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            <p>No divisions yet</p>
                            <button type="button"
                                    onclick="document.getElementById('create-modal').classList.remove('hidden')"
                                    class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Add your first division
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($divisions->hasPages())
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing {{ $divisions->firstItem() }} to {{ $divisions->lastItem() }} of {{ $divisions->total() }} divisions
                </div>
                <div class="pagination-links">
                    @if($divisions->onFirstPage())
                        <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a href="{{ $divisions->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>
                    @endif

                    @foreach($divisions->getUrlRange(1, $divisions->lastPage()) as $page => $url)
                        @if($page == $divisions->currentPage())
                            <span class="page-link active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($divisions->hasMorePages())
                        <a href="{{ $divisions->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>
                    @else
                        <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Create Modal -->
<div id="create-modal" class="modal-overlay hidden">
    <div class="modal-container">
        <form action="{{ route('admin.events.divisions.store', $event) }}" method="POST">
            @csrf
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Add Division</h3>
                <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g., P1, A1" required>
                        <small class="form-text">Short code like P1, A1, etc.</small>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="">Select Type...</option>
                            @if($event->template && $event->template->getDivisionTypes())
                                @foreach($event->template->getDivisionTypes() as $divType)
                                    <option value="{{ $divType['name'] }}">{{ $divType['name'] }} ({{ $divType['code'] }})</option>
                                @endforeach
                            @else
                                <option value="Professional">Professional</option>
                                <option value="Amateur">Amateur</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g., Professional 1">
                    <small class="form-text">Display name (optional, auto-generated from type + number)</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-control" placeholder="Optional description"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="{{ $divisions->count() + 1 }}" min="1">
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Active</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Division
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="modal-overlay hidden">
    <div class="modal-container">
        <form id="edit-form" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Division</h3>
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" id="edit-code" name="code" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select id="edit-type" name="type" class="form-control" required>
                            <option value="">Select Type...</option>
                            @if($event->template && $event->template->getDivisionTypes())
                                @foreach($event->template->getDivisionTypes() as $divType)
                                    <option value="{{ $divType['name'] }}">{{ $divType['name'] }} ({{ $divType['code'] }})</option>
                                @endforeach
                            @else
                                <option value="Professional">Professional</option>
                                <option value="Amateur">Amateur</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" id="edit-name" name="name" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="edit-description" name="description" rows="2" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" id="edit-display-order" name="display_order" class="form-control" min="1">
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="edit-is-active" name="is_active" value="1">
                        <span>Active</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Division
                </button>
            </div>
        </form>
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

        /* First cell (Division code) becomes the card header */
        .responsive-table tbody tr td:first-child {
            background: #f8fafc;
            border-radius: 8px 8px 0 0;
            font-size: 16px;
        }

        /* Show mobile subtitle on mobile */
        .mobile-subtitle {
            display: block;
            font-size: 12px;
            color: #6b7280;
            font-weight: normal;
            margin-top: 2px;
        }

        /* Hide the # column on mobile - redundant info */
        .responsive-table tbody tr td[data-label="#"] {
            display: none;
        }

        /* Make actions cell a full row at bottom */
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

        /* Pagination responsive */
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
    }

    /* Small mobile tweaks */
    @media screen and (max-width: 480px) {
        .responsive-table tbody tr td {
            padding: 10px 12px;
            font-size: 14px;
        }

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

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-overlay.hidden {
        display: none;
    }

    .modal-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        width: 100%;
        max-width: 500px;
        margin: 20px;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #1e3a8a;
        color: white;
        border-radius: 8px 8px 0 0;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .modal-close {
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        opacity: 0.8;
    }

    .modal-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 15px 20px;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0 0 8px 8px;
    }

    .form-row {
        display: flex;
        gap: 15px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .form-text {
        display: block;
        margin-top: 4px;
        font-size: 12px;
        color: #6b7280;
    }

    .text-danger {
        color: #dc2626;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
    }

    /* Modal Responsive */
    @media screen and (max-width: 768px) {
        .modal-container {
            margin: 10px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header h3 {
            font-size: 16px;
        }

        .modal-body {
            padding: 15px;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .modal-footer {
            flex-direction: column-reverse;
            gap: 8px;
        }

        .modal-footer .btn {
            width: 100%;
        }
    }
</style>

@push('scripts')
<script>
function editDivision(id, code, type, name, description, isActive, displayOrder) {
    document.getElementById('edit-form').action = '{{ route("admin.events.divisions.index", $event) }}/' + id;
    document.getElementById('edit-code').value = code;
    document.getElementById('edit-type').value = type;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-description').value = description;
    document.getElementById('edit-is-active').checked = isActive;
    document.getElementById('edit-display-order').value = displayOrder || 1;
    document.getElementById('edit-modal').classList.remove('hidden');
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('create-modal').classList.add('hidden');
        document.getElementById('edit-modal').classList.add('hidden');
    }
});

// Close modals on outside click
['create-modal', 'edit-modal'].forEach(function(modalId) {
    document.getElementById(modalId).addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
});
</script>
@endpush
@endsection
