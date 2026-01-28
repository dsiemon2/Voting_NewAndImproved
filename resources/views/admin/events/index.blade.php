@extends('layouts.app')

@section('content')
<div>
    <!-- Header -->
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <h1 class="page-title"><i class="fas fa-calendar-alt"></i> Events</h1>
        </div>
        <button type="button" class="btn btn-warning" onclick="openEventModal()">
            <i class="fas fa-plus"></i> New Event
        </button>
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
                        @foreach($templates as $template)
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
                                <button type="button" class="action-btn action-btn-edit" title="Edit Event" onclick="openEventModal({{ $event->id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.events.clear-data', $event) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('WARNING: This will permanently delete ALL data for this event.\n\nAre you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn" style="background: #fef3c7; color: #b45309;" title="Clear All Event Data">
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
                            <button type="button" class="btn btn-primary mt-2" onclick="openEventModal()">
                                <i class="fas fa-plus"></i> Create your first event
                            </button>
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

<!-- Event Modal -->
<div class="modal-overlay" id="eventModal">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <h2 id="eventModalTitle"><i class="fas fa-calendar-plus"></i> Create New Event</h2>
            <button class="modal-close" onclick="closeEventModal()">&times;</button>
        </div>
        <form id="eventForm" onsubmit="submitEventForm(event)">
            <div class="modal-body">
                <div class="modal-error" id="eventModalError"></div>
                <input type="hidden" id="eventEditId" value="">

                <div class="form-group">
                    <label class="form-label">Event Name <span style="color:#dc2626">*</span></label>
                    <input type="text" id="eventName" name="name" class="form-control" placeholder="e.g., Soup Cookoff 2026" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="eventDescription" name="description" class="form-control" rows="2" style="min-height:60px;"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group" id="eventTemplateGroup">
                        <label class="form-label">Event Template <span style="color:#dc2626">*</span></label>
                        <select id="eventTemplate" name="event_template_id" class="form-control" required>
                            <option value="">Select Template...</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" data-modules="{{ json_encode($template->modules->pluck('id')) }}">
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Voting Type <span style="color:#dc2626">*</span></label>
                        <select id="eventVotingType" name="voting_type_id" class="form-control" required>
                            <option value="">Select Voting Type...</option>
                            @foreach($votingTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Event Date</label>
                        <input type="date" id="eventDate" name="event_date" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">State</label>
                        <select id="eventState" name="state_id" class="form-control">
                            <option value="">Select State...</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" id="eventLocation" name="location" class="form-control" placeholder="Venue name or address">
                </div>

                <div style="display:flex;gap:20px;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#f9fafb;">
                        <input type="checkbox" id="eventActive" name="is_active" value="1" checked style="width:18px;height:18px;">
                        <span>Active</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#f9fafb;">
                        <input type="checkbox" id="eventPublic" name="is_public" value="1" style="width:18px;height:18px;">
                        <span>Public</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEventModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="eventSubmitBtn">
                    <i class="fas fa-save"></i> Create Event
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .mobile-subtitle { display: none; }
    @media screen and (max-width: 1023px) { .hide-tablet { display: none; } }
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
        .responsive-table tbody tr td[data-label="Event"] { background: #f8fafc; border-radius: 8px 8px 0 0; flex-direction: column; align-items: flex-start; }
        .responsive-table tbody tr td[data-label="Event"]::before { display: none; }
        .mobile-subtitle { display: block; font-size: 12px; color: #6b7280; font-weight: normal; margin-top: 2px; }
        .responsive-table tbody tr td[data-label="Actions"] { justify-content: center; background: #f9fafb; border-radius: 0 0 8px 8px; padding: 10px 15px; flex-wrap: wrap; }
        .responsive-table tbody tr td[data-label="Actions"]::before { display: none; }
        .action-buttons { gap: 8px; flex-wrap: wrap; justify-content: center; }
        .action-btn { width: 36px; height: 36px; font-size: 14px; }
        .d-flex.gap-2 { flex-direction: column; }
        .d-flex.gap-2 > div, .d-flex.gap-2 > button { width: 100%; }
    }
    @media screen and (max-width: 480px) {
        .d-flex.justify-between { flex-direction: column; gap: 15px; }
        .d-flex.justify-between .btn { width: 100%; }
    }
</style>

@push('scripts')
<script>
function openEventModal(eventId = null) {
    const modal = document.getElementById('eventModal');
    const title = document.getElementById('eventModalTitle');
    const submitBtn = document.getElementById('eventSubmitBtn');
    const editId = document.getElementById('eventEditId');
    const errorDiv = document.getElementById('eventModalError');
    const templateGroup = document.getElementById('eventTemplateGroup');

    document.getElementById('eventForm').reset();
    errorDiv.classList.remove('active');
    document.getElementById('eventActive').checked = true;
    document.getElementById('eventPublic').checked = false;
    templateGroup.style.display = '';

    if (eventId) {
        title.innerHTML = '<i class="fas fa-calendar-alt"></i> Edit Event';
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Event';
        editId.value = eventId;
        templateGroup.style.display = 'none';
        document.getElementById('eventTemplate').removeAttribute('required');

        fetch(`/admin/events/${eventId}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(ev => {
            document.getElementById('eventName').value = ev.name || '';
            document.getElementById('eventDescription').value = ev.description || '';
            document.getElementById('eventDate').value = ev.event_date ? ev.event_date.substring(0, 10) : '';
            document.getElementById('eventLocation').value = ev.location || '';
            document.getElementById('eventState').value = ev.state_id || '';
            document.getElementById('eventVotingType').value = ev.voting_config ? ev.voting_config.voting_type_id : '';
            document.getElementById('eventActive').checked = ev.is_active;
            document.getElementById('eventPublic').checked = ev.is_public;
        });
    } else {
        title.innerHTML = '<i class="fas fa-calendar-plus"></i> Create New Event';
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Create Event';
        editId.value = '';
        document.getElementById('eventTemplate').setAttribute('required', '');
    }

    modal.classList.add('active');
}

function closeEventModal() {
    document.getElementById('eventModal').classList.remove('active');
}

function submitEventForm(e) {
    e.preventDefault();
    const editId = document.getElementById('eventEditId').value;
    const form = document.getElementById('eventForm');
    const formData = new FormData(form);
    const errorDiv = document.getElementById('eventModalError');

    const data = {};
    formData.forEach((v, k) => { if (v !== '') data[k] = v; });

    if (!document.getElementById('eventActive').checked) delete data['is_active'];
    if (!document.getElementById('eventPublic').checked) delete data['is_public'];

    if (editId) data['_method'] = 'PUT';

    const url = editId ? `/admin/events/${editId}` : '/admin/events';

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
            closeEventModal();
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

document.getElementById('eventModal').addEventListener('click', function(e) {
    if (e.target === this) closeEventModal();
});
</script>
@endpush
@endsection
