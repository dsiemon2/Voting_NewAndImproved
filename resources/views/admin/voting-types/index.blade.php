@extends('layouts.app')

@section('content')
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
<div>
    <!-- Header -->
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <h1 class="page-title"><i class="fas fa-poll"></i> Voting Types</h1>
        </div>
        <button type="button" class="btn btn-warning" onclick="openVotingTypeModal()">
            <i class="fas fa-plus"></i> New Voting Type
        </button>
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
                                <button type="button"
                                   class="action-btn action-btn-edit"
                                   title="Edit"
                                   onclick="openVotingTypeModal({{ $type->id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
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
                            <button type="button" class="btn btn-primary mt-2" onclick="openVotingTypeModal()">
                                <i class="fas fa-plus"></i> Create your first voting type
                            </button>
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
                <h3 style="margin-bottom: 10px; color: #1a3a5c;"><i class="fas fa-medal" style="color: #f39c12;"></i> Standard 3-2-1</h3>
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
                <h3 style="margin-bottom: 10px; color: #1a3a5c;"><i class="fas fa-trophy" style="color: #f39c12;"></i> Extended 5-4-3-2-1</h3>
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
                <h3 style="margin-bottom: 10px; color: #1a3a5c;"><i class="fas fa-crown" style="color: #f39c12;"></i> Top-Heavy 5-3-1</h3>
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

<!-- Voting Type Modal -->
<div class="modal-overlay" id="vtModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2 id="vtModalTitle"><i class="fas fa-poll"></i> Create Voting Type</h2>
            <button class="modal-close" onclick="closeVotingTypeModal()">&times;</button>
        </div>
        <form id="vtForm" onsubmit="submitVotingTypeForm(event)">
            <div class="modal-body">
                <div class="modal-error" id="vtModalError"></div>
                <input type="hidden" id="vtEditId" value="">

                <div class="form-group">
                    <label class="form-label">Name <span style="color:#dc2626">*</span></label>
                    <input type="text" id="vtName" name="name" class="form-control" placeholder="e.g., Standard 3-2-1" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="vtDescription" name="description" class="form-control" rows="2" style="min-height:60px;"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Category <span style="color:#dc2626">*</span></label>
                        <select id="vtCategory" name="category" class="form-control" required>
                            <option value="ranked">Ranked</option>
                            <option value="weighted">Weighted</option>
                            <option value="rating">Rating</option>
                            <option value="approval">Approval</option>
                            <option value="cumulative">Cumulative</option>
                        </select>
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:4px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" id="vtActive" name="is_active" value="1" checked style="width:18px;height:18px;">
                            <span>Active</span>
                        </label>
                    </div>
                </div>

                <!-- Point Configuration -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <label class="form-label" style="margin-bottom:0;">Point Configuration</label>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addModalPlace()">
                        <i class="fas fa-plus"></i> Add Place
                    </button>
                </div>
                <div id="modalPlacesContainer"></div>

                <!-- Preview -->
                <div style="margin-top:12px;">
                    <label class="form-label">Preview</label>
                    <div id="modalPreview" style="display:flex;flex-wrap:wrap;gap:6px;">
                        <span style="color:#9ca3af;">Add places to see preview</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeVotingTypeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="vtSubmitBtn">
                    <i class="fas fa-save"></i> Create Voting Type
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .mobile-subtitle { display: none; }
    @media screen and (max-width: 1023px) { .hide-tablet { display: none; } }
    @media screen and (max-width: 900px) { .hide-mobile { display: none; } }
    @media screen and (max-width: 768px) {
        .responsive-table { border: 0; }
        .responsive-table thead { display: none; }
        .responsive-table tbody { display: flex; flex-direction: column; align-items: center; }
        .responsive-table tbody tr { display: block; margin-bottom: 15px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 0; width: 100%; max-width: 400px; }
        .responsive-table tbody tr.empty-row { display: table-row; max-width: none; }
        .responsive-table tbody tr.empty-row td { display: table-cell; }
        .responsive-table tbody tr td { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border: none; border-bottom: 1px solid #f3f4f6; text-align: right; }
        .responsive-table tbody tr td:last-child { border-bottom: none; }
        .responsive-table tbody tr td::before { content: attr(data-label); font-weight: 600; color: #374151; text-align: left; flex-shrink: 0; margin-right: 15px; }
        .responsive-table tbody tr td[data-label="Name"] { background: #f8fafc; border-radius: 8px 8px 0 0; flex-direction: column; align-items: flex-start; }
        .responsive-table tbody tr td[data-label="Name"]::before { display: none; }
        .mobile-subtitle { display: block; font-size: 12px; color: #6b7280; font-weight: normal; margin-top: 2px; }
        .responsive-table tbody tr td[data-label="Actions"] { justify-content: flex-end; background: #f9fafb; border-radius: 0 0 8px 8px; padding: 10px 15px; }
        .responsive-table tbody tr td[data-label="Actions"]::before { display: none; }
        .action-buttons { gap: 10px; }
        .action-btn { width: 40px; height: 40px; font-size: 16px; }
        .template-grid { grid-template-columns: 1fr; }
    }
    @media screen and (max-width: 480px) {
        .d-flex.justify-between { flex-direction: column; gap: 15px; }
        .d-flex.justify-between .btn { width: 100%; }
    }
    .place-row-modal { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; padding: 8px 12px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb; }
    .place-row-modal .place-label { font-weight: 600; min-width: 80px; font-size: 14px; }
    .place-row-modal input[type="number"] { width: 80px; padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; }
    .place-row-modal .remove-place { background: none; border: none; color: #dc2626; cursor: pointer; font-size: 16px; padding: 4px; }
    .place-row-modal .remove-place:hover { color: #991b1b; }
</style>

@push('scripts')
<script>
let modalPlaceCount = 0;

function ordinalModal(n) {
    const s = ['th','st','nd','rd'];
    const v = n % 100;
    return n + (s[(v-20)%10] || s[v] || s[0]);
}

function addModalPlace(place, points) {
    modalPlaceCount++;
    const p = place || modalPlaceCount;
    const pts = points || (modalPlaceCount <= 5 ? 6 - modalPlaceCount : 1);
    const container = document.getElementById('modalPlacesContainer');
    const html = `<div class="place-row-modal" data-place="${p}">
        <span class="place-label">${ordinalModal(p)} Place</span>
        <input type="number" name="places[${modalPlaceCount-1}][place]" value="${p}" hidden>
        <input type="number" name="places[${modalPlaceCount-1}][points]" value="${pts}" min="1" max="100" onchange="updateModalPreview()" required>
        <span style="color:#6b7280;font-size:13px;">points</span>
        <button type="button" class="remove-place" onclick="removeModalPlace(this)"><i class="fas fa-times"></i></button>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
    updateModalPreview();
}

function removeModalPlace(btn) {
    btn.closest('.place-row-modal').remove();
    renumberModalPlaces();
    updateModalPreview();
}

function renumberModalPlaces() {
    const rows = document.querySelectorAll('.place-row-modal');
    modalPlaceCount = rows.length;
    rows.forEach((row, i) => {
        const p = i + 1;
        row.dataset.place = p;
        row.querySelector('.place-label').textContent = ordinalModal(p) + ' Place';
        const inputs = row.querySelectorAll('input[type="number"]');
        inputs[0].name = `places[${i}][place]`;
        inputs[0].value = p;
        inputs[1].name = `places[${i}][points]`;
    });
}

function updateModalPreview() {
    const preview = document.getElementById('modalPreview');
    const rows = document.querySelectorAll('.place-row-modal');
    if (rows.length === 0) {
        preview.innerHTML = '<span style="color:#9ca3af;">Add places to see preview</span>';
        return;
    }
    let html = '';
    rows.forEach((row, i) => {
        const pts = row.querySelectorAll('input[type="number"]')[1].value;
        html += `<span class="label-tag" style="background:#dbeafe;color:#1e40af;">${ordinalModal(i+1)}: ${pts} pts</span>`;
    });
    preview.innerHTML = html;
}

function openVotingTypeModal(vtId = null) {
    const modal = document.getElementById('vtModal');
    const title = document.getElementById('vtModalTitle');
    const submitBtn = document.getElementById('vtSubmitBtn');
    const editId = document.getElementById('vtEditId');
    const errorDiv = document.getElementById('vtModalError');

    document.getElementById('vtForm').reset();
    document.getElementById('modalPlacesContainer').innerHTML = '';
    modalPlaceCount = 0;
    errorDiv.classList.remove('active');
    document.getElementById('vtActive').checked = true;

    if (vtId) {
        title.innerHTML = '<i class="fas fa-edit"></i> Edit Voting Type';
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Voting Type';
        editId.value = vtId;

        fetch(`/admin/voting-types/${vtId}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(vt => {
            document.getElementById('vtName').value = vt.name || '';
            document.getElementById('vtDescription').value = vt.description || '';
            document.getElementById('vtCategory').value = vt.category || 'ranked';
            document.getElementById('vtActive').checked = vt.is_active;

            if (vt.place_configs && vt.place_configs.length) {
                vt.place_configs.sort((a,b) => a.place - b.place).forEach(pc => {
                    addModalPlace(pc.place, pc.points);
                });
            }
        });
    } else {
        title.innerHTML = '<i class="fas fa-poll"></i> Create Voting Type';
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Create Voting Type';
        editId.value = '';
        addModalPlace(1, 3);
        addModalPlace(2, 2);
        addModalPlace(3, 1);
    }

    modal.classList.add('active');
}

function closeVotingTypeModal() {
    document.getElementById('vtModal').classList.remove('active');
}

function submitVotingTypeForm(e) {
    e.preventDefault();
    const editId = document.getElementById('vtEditId').value;
    const form = document.getElementById('vtForm');
    const formData = new FormData(form);
    const errorDiv = document.getElementById('vtModalError');

    if (!document.getElementById('vtActive').checked) {
        formData.delete('is_active');
    }

    const data = {};
    formData.forEach((v, k) => {
        const match = k.match(/^places\[(\d+)\]\[(\w+)\]$/);
        if (match) {
            if (!data.places) data.places = [];
            const idx = parseInt(match[1]);
            if (!data.places[idx]) data.places[idx] = {};
            data.places[idx][match[2]] = parseInt(v);
        } else {
            data[k] = v;
        }
    });

    if (editId) data['_method'] = 'PUT';

    const url = editId ? `/admin/voting-types/${editId}` : '/admin/voting-types';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(r => r.json().then(d => ({ok: r.ok, data: d})))
    .then(({ok, data}) => {
        if (ok && data.success) {
            closeVotingTypeModal();
            location.reload();
        } else {
            const errors = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'An error occurred.');
            errorDiv.innerHTML = errors;
            errorDiv.classList.add('active');
        }
    })
    .catch(() => {
        errorDiv.innerHTML = 'An error occurred. Please try again.';
        errorDiv.classList.add('active');
    });
}

document.getElementById('vtModal').addEventListener('click', function(e) {
    if (e.target === this) closeVotingTypeModal();
});
</script>
@endpush
@endsection
