@extends('layouts.app')

@section('content')
<div>
    <!-- Header -->
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <h1 class="page-title"><i class="fas fa-file-alt"></i> Event Templates</h1>
        </div>
        <button type="button" class="btn btn-warning" onclick="openTemplateModal()">
            <i class="fas fa-plus"></i> New Template
        </button>
    </div>

    <!-- Templates Grid -->
    @if($templates->count())
        <div class="template-grid">
            @foreach($templates as $template)
                <div class="template-card">
                    <div class="template-card-header">
                        <div class="template-card-icon">
                            <i class="fas {{ $template->icon ?? 'fa-calendar' }}"></i>
                        </div>
                        <div>
                            <div class="template-card-title">{{ $template->name }}</div>
                            @if($template->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <div class="template-card-body">
                        @if($template->description)
                            <p style="color: #6b7280; font-size: 14px; margin-bottom: 15px;">
                                {{ \Illuminate\Support\Str::limit($template->description, 100) }}
                            </p>
                        @endif

                        <div style="margin-bottom: 15px;">
                            <strong style="font-size: 12px; color: #374151;">Labels:</strong>
                            <div style="margin-top: 5px;">
                                <span class="label-tag">
                                    <i class="fas fa-user"></i> {{ $template->participant_label ?? 'Participant' }}
                                </span>
                                <span class="label-tag">
                                    <i class="fas fa-clipboard"></i> {{ $template->entry_label ?? 'Entry' }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <strong style="font-size: 12px; color: #374151;">Modules:</strong>
                            <div style="margin-top: 5px;">
                                @forelse($template->modules as $module)
                                    <span class="module-badge">
                                        <i class="fas {{ $module->icon ?? 'fa-check' }}"></i>
                                        {{ $module->name }}
                                    </span>
                                @empty
                                    <span style="color: #9ca3af; font-size: 12px;">No modules</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="template-card-footer">
                        <span style="color: #6b7280; font-size: 13px;">
                            <i class="fas fa-calendar-alt"></i> {{ $template->events_count ?? $template->events->count() }} events
                        </span>
                        <div class="action-buttons">
                            <button type="button"
                               class="action-btn action-btn-edit"
                               title="Edit"
                               onclick="openTemplateModal({{ $template->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            @if(($template->events_count ?? $template->events->count()) === 0)
                                <form action="{{ route('admin.templates.destroy', $template) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('Are you sure you want to delete this template?')">
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
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="text-center" style="padding: 60px 20px; color: #6b7280;">
                <i class="fas fa-file-alt" style="font-size: 64px; margin-bottom: 20px; display: block; color: #9ca3af;"></i>
                <h3 style="margin-bottom: 10px; color: #374151;">No Templates Yet</h3>
                <p style="margin-bottom: 20px;">Create your first event template to get started</p>
                <button type="button" class="btn btn-warning" onclick="openTemplateModal()">
                    <i class="fas fa-plus"></i> Create Template
                </button>
            </div>
        </div>
    @endif
</div>

<!-- Template Modal -->
<div class="modal-overlay" id="templateModal">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <h2 id="templateModalTitle"><i class="fas fa-file-alt"></i> Create Event Template</h2>
            <button class="modal-close" onclick="closeTemplateModal()">&times;</button>
        </div>
        <form id="templateForm" onsubmit="submitTemplateForm(event)">
            <div class="modal-body">
                <div class="modal-error" id="templateModalError"></div>
                <input type="hidden" id="templateEditId" value="">

                <div class="form-row">
                    <div class="form-group" style="flex:2;">
                        <label class="form-label">Template Name <span style="color:#dc2626">*</span></label>
                        <input type="text" id="templateName" name="name" class="form-control" placeholder="e.g., Food Competition, Photo Contest" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Icon</label>
                        <input type="text" id="templateIcon" name="icon" class="form-control" value="fa-calendar" placeholder="fa-calendar">
                        <small style="color:#9ca3af;font-size:11px;">Font Awesome class</small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="templateDescription" name="description" class="form-control" rows="2" style="min-height:60px;"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Participant Label</label>
                        <input type="text" id="templateParticipant" name="participant_label" class="form-control" value="Participant" placeholder="e.g., Chef, Photographer">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Entry Label</label>
                        <input type="text" id="templateEntry" name="entry_label" class="form-control" value="Entry" placeholder="e.g., Dish, Photo">
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#f9fafb;">
                        <input type="checkbox" id="templateActive" name="is_active" value="1" checked style="width:18px;height:18px;">
                        <span>Active</span>
                    </label>
                </div>

                <!-- Modules -->
                <div>
                    <label class="form-label">Default Modules</label>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px;" id="templateModulesGrid">
                        @foreach($modules as $module)
                            <label style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;background:#f9fafb;font-size:14px;"
                                   class="template-module-item">
                                <input type="checkbox" name="modules[]" value="{{ $module->id }}"
                                       {{ $module->is_core ? 'checked disabled' : '' }}
                                       style="width:16px;height:16px;">
                                <i class="fas {{ $module->icon ?? 'fa-cube' }}" style="color:#6b7280;"></i>
                                <span>{{ $module->name }}</span>
                                @if($module->is_core)
                                    <small style="color:#9ca3af;">(Core)</small>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeTemplateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="templateSubmitBtn">
                    <i class="fas fa-save"></i> Create Template
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openTemplateModal(templateId = null) {
    const modal = document.getElementById('templateModal');
    const title = document.getElementById('templateModalTitle');
    const submitBtn = document.getElementById('templateSubmitBtn');
    const editId = document.getElementById('templateEditId');
    const errorDiv = document.getElementById('templateModalError');

    document.getElementById('templateForm').reset();
    errorDiv.classList.remove('active');
    document.getElementById('templateActive').checked = true;

    // Reset module checkboxes
    document.querySelectorAll('#templateModulesGrid input[type="checkbox"]').forEach(cb => {
        if (!cb.disabled) cb.checked = false;
    });

    if (templateId) {
        title.innerHTML = '<i class="fas fa-edit"></i> Edit Template';
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Template';
        editId.value = templateId;

        fetch(`/admin/templates/${templateId}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(tmpl => {
            document.getElementById('templateName').value = tmpl.name || '';
            document.getElementById('templateIcon').value = tmpl.icon || 'fa-calendar';
            document.getElementById('templateDescription').value = tmpl.description || '';
            document.getElementById('templateParticipant').value = tmpl.participant_label || 'Participant';
            document.getElementById('templateEntry').value = tmpl.entry_label || 'Entry';
            document.getElementById('templateActive').checked = tmpl.is_active;

            if (tmpl.modules) {
                const moduleIds = tmpl.modules.map(m => m.id);
                document.querySelectorAll('#templateModulesGrid input[type="checkbox"]').forEach(cb => {
                    if (!cb.disabled && moduleIds.includes(parseInt(cb.value))) {
                        cb.checked = true;
                    }
                });
            }
        });
    } else {
        title.innerHTML = '<i class="fas fa-file-alt"></i> Create Event Template';
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Create Template';
        editId.value = '';
    }

    modal.classList.add('active');
}

function closeTemplateModal() {
    document.getElementById('templateModal').classList.remove('active');
}

function submitTemplateForm(e) {
    e.preventDefault();
    const editId = document.getElementById('templateEditId').value;
    const form = document.getElementById('templateForm');
    const formData = new FormData(form);
    const errorDiv = document.getElementById('templateModalError');

    const data = {};
    const modules = [];
    formData.forEach((v, k) => {
        if (k === 'modules[]') {
            modules.push(v);
        } else {
            data[k] = v;
        }
    });
    data.modules = modules;

    if (!document.getElementById('templateActive').checked) delete data['is_active'];

    // Include core modules (disabled checkboxes aren't in formData)
    document.querySelectorAll('#templateModulesGrid input[type="checkbox"]:disabled:checked').forEach(cb => {
        if (!data.modules.includes(cb.value)) data.modules.push(cb.value);
    });

    if (editId) data['_method'] = 'PUT';

    const url = editId ? `/admin/templates/${editId}` : '/admin/templates';

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
            closeTemplateModal();
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

document.getElementById('templateModal').addEventListener('click', function(e) {
    if (e.target === this) closeTemplateModal();
});
</script>
@endpush
@endsection
