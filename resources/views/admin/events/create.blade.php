@extends('layouts.app')

@section('content')
<div class="edit-container">
    <!-- Header -->
    <div class="edit-header">
        <a href="{{ route('admin.events.index') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Events
        </a>
        <h1 class="page-title"><i class="fas fa-plus-circle"></i> Create New Event</h1>
        <p class="subtitle">Set up a new voting event</p>
    </div>

    <form action="{{ route('admin.events.store') }}" method="POST">
        @csrf

        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Basic Information</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Event Name <span class="required">*</span></label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="e.g., Soup Cookoff 2026"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description"
                              rows="3"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Optional description of the event">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label class="form-label">Event Template <span class="required">*</span></label>
                        <select name="event_template_id"
                                id="event_template_id"
                                class="form-control @error('event_template_id') is-invalid @enderror"
                                required>
                            <option value="">Select Template...</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}"
                                        {{ old('event_template_id') == $template->id ? 'selected' : '' }}
                                        data-modules="{{ json_encode($template->modules->pluck('id')) }}">
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('event_template_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group half">
                        <label class="form-label">Voting Type <span class="required">*</span></label>
                        <select name="voting_type_id"
                                class="form-control @error('voting_type_id') is-invalid @enderror"
                                required>
                            <option value="">Select Voting Type...</option>
                            @foreach($votingTypes as $type)
                                <option value="{{ $type->id }}"
                                        {{ old('voting_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->placeConfigs->count() }} places)
                                </option>
                            @endforeach
                        </select>
                        @error('voting_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h2><i class="fas fa-calendar-alt"></i> Event Details</h2>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group half">
                        <label class="form-label">Event Date</label>
                        <input type="date"
                               name="event_date"
                               value="{{ old('event_date') }}"
                               class="form-control @error('event_date') is-invalid @enderror">
                        @error('event_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group half">
                        <label class="form-label">State</label>
                        <select name="state_id"
                                class="form-control @error('state_id') is-invalid @enderror">
                            <option value="">Select State...</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}"
                                        {{ old('state_id') == $state->id ? 'selected' : '' }}>
                                    {{ $state->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('state_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text"
                           name="location"
                           value="{{ old('location') }}"
                           placeholder="Venue name or address"
                           class="form-control @error('location') is-invalid @enderror">
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Status Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h2><i class="fas fa-cog"></i> Status Settings</h2>
            </div>
            <div class="card-body">
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <span>Active</span>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox"
                               name="is_public"
                               value="1"
                               {{ old('is_public') ? 'checked' : '' }}>
                        <span>Public (visible without login)</span>
                    </label>

                    <label class="checkbox-label">
                        <input type="checkbox"
                               name="allow_multiple_votes"
                               value="1"
                               {{ old('allow_multiple_votes') ? 'checked' : '' }}>
                        <span>Allow Multiple Votes</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Module Configuration -->
        <div class="card mb-4">
            <div class="card-header">
                <h2><i class="fas fa-puzzle-piece"></i> Enabled Modules</h2>
                <p class="header-subtitle">Select which features are enabled for this event</p>
            </div>
            <div class="card-body">
                <div class="modules-grid" id="modules-grid">
                    @foreach($modules as $module)
                        <label class="module-item" data-module-id="{{ $module->id }}">
                            <input type="checkbox"
                                   name="modules[]"
                                   value="{{ $module->id }}"
                                   {{ in_array($module->id, old('modules', [])) ? 'checked' : '' }}
                                   {{ $module->is_core ? 'checked disabled' : '' }}>
                            <span class="module-info">
                                <i class="fas {{ $module->icon ?? 'fa-cube' }}"></i>
                                {{ $module->name }}
                                @if($module->is_core)
                                    <small>(Required)</small>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="form-actions">
            <div></div>
            <div class="action-right">
                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Event
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    .edit-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .edit-header {
        margin-bottom: 30px;
    }

    .back-link {
        color: #6b7280;
        text-decoration: none;
        display: inline-block;
        margin-bottom: 10px;
    }

    .back-link:hover {
        color: #374151;
    }

    .page-title {
        margin: 0 0 5px 0;
        font-size: 24px;
        color: #1a3a5c;
    }

    .subtitle {
        color: #6b7280;
        margin: 0;
    }

    .card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .card-header {
        background: #1a3a5c;
        color: white;
        padding: 15px 20px;
    }

    .card-header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    .header-subtitle {
        margin: 5px 0 0 0;
        font-size: 13px;
        opacity: 0.8;
    }

    .card-body {
        padding: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-row {
        display: flex;
        gap: 20px;
    }

    .form-group.half {
        flex: 1;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .required {
        color: #dc2626;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        font-size: 14px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: white;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .form-control:focus {
        outline: none;
        border-color: #0d6e38;
        box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
    }

    .form-control.is-invalid {
        border-color: #dc2626;
    }

    .invalid-feedback {
        color: #dc2626;
        font-size: 13px;
        margin-top: 5px;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
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
        transition: background 0.15s, border-color 0.15s;
    }

    .checkbox-label:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
    }

    .modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 12px;
    }

    .module-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s;
    }

    .module-item:hover {
        background: #f9fafb;
        border-color: #d1d5db;
    }

    .module-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
    }

    .module-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .module-info i {
        color: #6b7280;
        width: 18px;
        text-align: center;
    }

    .module-info small {
        color: #9ca3af;
        font-size: 11px;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 20px;
    }

    .action-right {
        display: flex;
        gap: 10px;
    }

    .mb-4 {
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .form-group.half {
            margin-bottom: 20px;
        }

        .checkbox-group {
            flex-direction: column;
            gap: 10px;
        }

        .form-actions {
            flex-direction: column;
            gap: 15px;
        }

        .action-right {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateSelect = document.getElementById('event_template_id');
    const modulesGrid = document.getElementById('modules-grid');

    templateSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const moduleIds = selectedOption.dataset.modules ? JSON.parse(selectedOption.dataset.modules) : [];

        // Update module checkboxes based on template
        document.querySelectorAll('.module-item').forEach(item => {
            const moduleId = parseInt(item.dataset.moduleId);
            const checkbox = item.querySelector('input[type="checkbox"]');

            if (!checkbox.disabled) {
                checkbox.checked = moduleIds.includes(moduleId);
            }
        });
    });
});
</script>
@endpush
@endsection
