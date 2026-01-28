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
                <i class="fas fa-gavel"></i> Judging Panel
            </h1>
        </div>
        <button type="button"
                onclick="document.getElementById('create-modal').classList.remove('hidden')"
                class="btn btn-warning">
            <i class="fas fa-plus"></i> Add Judge
        </button>
    </div>

    <!-- Judges Table -->
    <div class="card">
        <table class="table responsive-table" id="judges-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Judge Name</th>
                    <th class="hide-tablet">Title</th>
                    <th>Vote Weight</th>
                    <th class="hide-tablet">Permissions</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($judges as $index => $judge)
                    <tr>
                        <td data-label="#"><strong>{{ $judges->firstItem() + $index }}</strong></td>
                        <td data-label="Name">
                            <strong>{{ $judge->user->full_name }}</strong>
                            <span class="mobile-subtitle">{{ $judge->user->email }}</span>
                            @if($judge->title)
                                <span class="mobile-subtitle">{{ $judge->title }}</span>
                            @endif
                        </td>
                        <td data-label="Title" class="hide-tablet">{{ $judge->title ?? '-' }}</td>
                        <td data-label="Weight">
                            <span class="weight-badge">{{ number_format($judge->vote_weight, 2) }}x</span>
                        </td>
                        <td data-label="Permissions" class="hide-tablet">
                            <div class="permission-badges">
                                @if($judge->can_see_results)
                                    <span class="badge badge-info" title="Can see results before public">
                                        <i class="fas fa-eye"></i> Results
                                    </span>
                                @endif
                                @if($judge->can_vote_own_division)
                                    <span class="badge badge-warning" title="Can vote in own division">
                                        <i class="fas fa-vote-yea"></i> Own Div
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td data-label="Status">
                            @if($judge->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td data-label="Actions">
                            <div class="action-buttons">
                                <button type="button"
                                        onclick="event.stopPropagation(); editJudge({{ $judge->id }}, '{{ $judge->user->full_name }}', {{ $judge->vote_weight }}, '{{ addslashes($judge->title ?? '') }}', '{{ addslashes($judge->bio ?? '') }}', {{ $judge->is_active ? 'true' : 'false' }}, {{ $judge->can_see_results ? 'true' : 'false' }}, {{ $judge->can_vote_own_division ? 'true' : 'false' }})"
                                        class="action-btn action-btn-edit"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.events.judges.destroy', [$event, $judge]) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('Remove {{ $judge->user->full_name }} from the judging panel?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="action-btn action-btn-delete"
                                            title="Remove"
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
                            <i class="fas fa-gavel" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            <p>No judges assigned yet</p>
                            <button type="button"
                                    onclick="document.getElementById('create-modal').classList.remove('hidden')"
                                    class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Add your first judge
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($judges->hasPages())
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing {{ $judges->firstItem() }} to {{ $judges->lastItem() }} of {{ $judges->total() }} judges
                </div>
                <div class="pagination-links">
                    @if($judges->onFirstPage())
                        <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a href="{{ $judges->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>
                    @endif

                    @foreach($judges->getUrlRange(1, $judges->lastPage()) as $page => $url)
                        @if($page == $judges->currentPage())
                            <span class="page-link active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($judges->hasMorePages())
                        <a href="{{ $judges->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>
                    @else
                        <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Weight Explanation -->
    <div class="info-card mt-4">
        <h4><i class="fas fa-info-circle"></i> About Vote Weights</h4>
        <p>Judges with a weight of <strong>1.00x</strong> have votes that count normally. A weight of <strong>2.00x</strong> means their votes count double, and so on. The weight multiplies the points assigned to each place they vote for.</p>
    </div>
</div>

<!-- Create Modal -->
<div id="create-modal" class="modal-overlay hidden">
    <div class="modal-container">
        <form action="{{ route('admin.events.judges.store', $event) }}" method="POST">
            @csrf
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Add Judge</h3>
                <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select User <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-control" required>
                        <option value="">Choose a user...</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <small class="form-text">Only active users not already assigned as judges are shown.</small>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Vote Weight <span class="text-danger">*</span></label>
                        <input type="number" name="vote_weight" class="form-control" value="1.00" min="0.01" max="99.99" step="0.01" required>
                        <small class="form-text">1.00 = normal, 2.00 = double</small>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., Head Judge, Guest Judge">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" rows="2" class="form-control" placeholder="Short bio for display (optional)"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Permissions</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>Active</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="can_see_results" value="1" checked>
                            <span>Can See Results Early</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="can_vote_own_division" value="1">
                            <span>Can Vote Own Division</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Judge
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
                <h3><i class="fas fa-edit"></i> Edit Judge</h3>
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Judge</label>
                    <input type="text" id="edit-judge-name" class="form-control" disabled style="background: #f3f4f6;">
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Vote Weight <span class="text-danger">*</span></label>
                        <input type="number" id="edit-vote-weight" name="vote_weight" class="form-control" min="0.01" max="99.99" step="0.01" required>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label">Title</label>
                        <input type="text" id="edit-title" name="title" class="form-control" placeholder="e.g., Head Judge">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea id="edit-bio" name="bio" rows="2" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Permissions</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="edit-is-active" name="is_active" value="1">
                            <span>Active</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="edit-can-see-results" name="can_see_results" value="1">
                            <span>Can See Results Early</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="edit-can-vote-own-division" name="can_vote_own_division" value="1">
                            <span>Can Vote Own Division</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Judge
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Weight Badge */
    .weight-badge {
        display: inline-block;
        padding: 4px 10px;
        background: linear-gradient(135deg, #0d6e38 0%, #2eaa5e 100%);
        color: white;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
    }

    /* Permission Badges */
    .permission-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .permission-badges .badge {
        font-size: 11px;
        padding: 3px 8px;
    }

    /* Info Card */
    .info-card {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 15px 20px;
    }

    .info-card h4 {
        margin: 0 0 10px 0;
        color: #0d6e38;
        font-size: 14px;
    }

    .info-card p {
        margin: 0;
        color: #374151;
        font-size: 14px;
        line-height: 1.6;
    }

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

        .responsive-table tbody tr td[data-label="Name"] strong {
            color: #0d6e38;
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

        .info-card {
            margin-top: 15px;
        }
    }

    @media screen and (max-width: 480px) {
        .d-flex.justify-between {
            flex-direction: column;
            gap: 15px;
        }

        .d-flex.justify-between .btn,
        .d-flex.justify-between button {
            width: 100%;
        }

        .modal-container {
            margin: 10px;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .checkbox-group {
            flex-direction: column;
            gap: 10px;
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
        background: #0d6e38;
        border-color: #0d6e38;
        color: white;
    }

    .page-link.disabled {
        color: #9ca3af;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Row hover */
    .responsive-table tbody tr:hover {
        background-color: #f9fafb;
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
        max-width: 550px;
        margin: 20px;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        background: #1a3a5c;
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

    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #f9fafb;
        transition: background 0.15s;
    }

    .checkbox-label:hover {
        background: #f3f4f6;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
    }

    .mt-4 {
        margin-top: 20px;
    }
</style>

@push('scripts')
<script>
function editJudge(id, name, weight, title, bio, isActive, canSeeResults, canVoteOwnDivision) {
    document.getElementById('edit-form').action = '{{ route("admin.events.judges.index", $event) }}/' + id;
    document.getElementById('edit-judge-name').value = name;
    document.getElementById('edit-vote-weight').value = weight;
    document.getElementById('edit-title').value = title;
    document.getElementById('edit-bio').value = bio;
    document.getElementById('edit-is-active').checked = isActive;
    document.getElementById('edit-can-see-results').checked = canSeeResults;
    document.getElementById('edit-can-vote-own-division').checked = canVoteOwnDivision;
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
