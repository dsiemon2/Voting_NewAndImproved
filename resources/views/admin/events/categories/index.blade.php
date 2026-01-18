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
                <i class="fas fa-tags"></i> Categories
            </h1>
        </div>
        <button type="button"
                onclick="document.getElementById('create-modal').classList.remove('hidden')"
                class="btn btn-warning">
            <i class="fas fa-plus"></i> Add Category
        </button>
    </div>

    <!-- Categories Table -->
    <div class="card">
        <table class="table responsive-table" id="categories-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Name</th>
                    <th class="hide-tablet">Description</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $index => $category)
                    <tr>
                        <td data-label="#"><strong>{{ $categories->firstItem() + $index }}</strong></td>
                        <td data-label="Name">
                            <strong style="color: #1e40af;">{{ $category->name }}</strong>
                            @if($category->description)
                                <span class="mobile-subtitle">{{ $category->description }}</span>
                            @endif
                        </td>
                        <td data-label="Description" class="hide-tablet">{{ $category->description ?? '-' }}</td>
                        <td data-label="Order">
                            <span class="badge badge-secondary">{{ $category->display_order ?? 0 }}</span>
                        </td>
                        <td data-label="Status">
                            @if($category->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <button type="button"
                                        onclick="event.stopPropagation(); editCategory({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}', {{ $category->display_order ?? 0 }}, {{ $category->is_active ? 'true' : 'false' }})"
                                        class="action-btn action-btn-edit"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.events.categories.destroy', [$event, $category]) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('Are you sure you want to delete this category?')">
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
                        <td colspan="6" class="text-center" style="padding: 40px; color: #6b7280;">
                            <i class="fas fa-tags" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            <p>No categories yet</p>
                            <button type="button"
                                    onclick="document.getElementById('create-modal').classList.remove('hidden')"
                                    class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Add your first category
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($categories->hasPages())
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of {{ $categories->total() }} categories
                </div>
                <div class="pagination-links">
                    @if($categories->onFirstPage())
                        <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a href="{{ $categories->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>
                    @endif

                    @foreach($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                        @if($page == $categories->currentPage())
                            <span class="page-link active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($categories->hasMorePages())
                        <a href="{{ $categories->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>
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
        <form action="{{ route('admin.events.categories.store', $event) }}" method="POST">
            @csrf
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Add Category</h3>
                <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g., Best Overall, Most Creative" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-control" placeholder="Optional description"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="{{ $categories->count() + 1 }}" min="0">
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
                    <i class="fas fa-save"></i> Create Category
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
                <h3><i class="fas fa-edit"></i> Edit Category</h3>
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" id="edit-name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="edit-description" name="description" rows="2" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Display Order</label>
                    <input type="number" id="edit-display-order" name="display_order" class="form-control" min="0">
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
                    <i class="fas fa-save"></i> Update Category
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

    @media screen and (max-width: 480px) {
        .d-flex.justify-between {
            flex-direction: column;
            gap: 15px;
        }

        .d-flex.justify-between .btn {
            width: 100%;
        }
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
function editCategory(id, name, description, displayOrder, isActive) {
    document.getElementById('edit-form').action = '{{ route("admin.events.categories.index", $event) }}/' + id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-description').value = description;
    document.getElementById('edit-display-order').value = displayOrder || 0;
    document.getElementById('edit-is-active').checked = isActive;
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
