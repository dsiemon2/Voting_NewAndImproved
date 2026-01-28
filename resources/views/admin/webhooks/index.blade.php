@extends('layouts.app')

@section('content')
<div class="page-title d-flex justify-between align-center">
    <span><i class="fas fa-plug"></i> Webhooks</span>
    <button class="btn btn-primary" onclick="openCreateModal()">
        <i class="fas fa-plus"></i> New Webhook
    </button>
</div>

<!-- Summary -->
<div class="grid grid-3" style="margin-bottom: 20px;">
    <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%); color: white;">
        <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Active Webhooks</div>
        <div style="font-size: 28px; font-weight: bold;">{{ $allWebhooks->where('is_active', true)->count() }}</div>
    </div>
    <div class="card" style="background: linear-gradient(135deg, #2eaa5e 0%, #60a5fa 100%); color: white;">
        <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Total Webhooks</div>
        <div style="font-size: 28px; font-weight: bold;">{{ $allWebhooks->count() }}</div>
    </div>
    <div class="card" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); color: white;">
        <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Available Events</div>
        <div style="font-size: 28px; font-weight: bold;">{{ count($availableEvents) }}</div>
    </div>
</div>

<!-- Info Box -->
<div class="card" style="background: #f0f9ff; border: 1px solid #bae6fd; margin-bottom: 20px;">
    <div class="d-flex gap-4" style="align-items: flex-start;">
        <div style="flex: 1; padding: 10px; border-right: 1px solid #bae6fd;">
            <div style="font-weight: 600; color: #0369a1; margin-bottom: 5px;">
                <i class="fas fa-bell"></i> Event Notifications
            </div>
            <div style="font-size: 13px; color: #475569;">
                Webhooks send HTTP POST requests to external URLs when events occur in your voting application.
            </div>
        </div>
        <div style="flex: 1; padding: 10px;">
            <div style="font-weight: 600; color: #059669; margin-bottom: 5px;">
                <i class="fas fa-shield-alt"></i> Secure Delivery
            </div>
            <div style="font-size: 13px; color: #475569;">
                Add a secret key to sign payloads. The signature is sent in the X-Webhook-Signature header.
            </div>
        </div>
    </div>
</div>

<!-- Webhooks Table -->
<div class="card">
    <table class="data-table" style="width: 100%;">
        <thead>
            <tr>
                <th>Name</th>
                <th>URL</th>
                <th>Events</th>
                <th>Status</th>
                <th>Last Triggered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($webhooks as $webhook)
            <tr>
                <td><strong>{{ $webhook->name }}</strong></td>
                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $webhook->url }}">
                    {{ $webhook->url }}
                </td>
                <td>
                    @foreach($webhook->events ?? [] as $event)
                    <span class="badge badge-secondary" style="margin: 2px;">{{ $event }}</span>
                    @endforeach
                </td>
                <td>
                    <span class="badge {{ $webhook->is_active ? 'badge-success' : 'badge-secondary' }}">
                        {{ $webhook->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @if($webhook->last_status)
                    <span class="badge {{ $webhook->last_status === 'success' ? 'badge-success' : 'badge-danger' }}" style="margin-left: 5px;">
                        {{ $webhook->last_status }}
                    </span>
                    @endif
                </td>
                <td>
                    {{ $webhook->last_triggered_at ? $webhook->last_triggered_at->diffForHumans() : 'Never' }}
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <button class="btn btn-secondary btn-sm" onclick="viewLogs({{ $webhook->id }})" title="View Logs">
                            <i class="fas fa-history"></i>
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="testWebhook({{ $webhook->id }})" title="Test">
                            <i class="fas fa-play"></i>
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="editWebhook({{ $webhook->id }})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn {{ $webhook->is_active ? 'btn-warning' : 'btn-success' }} btn-sm"
                                onclick="toggleWebhook({{ $webhook->id }})" title="{{ $webhook->is_active ? 'Disable' : 'Enable' }}">
                            <i class="fas {{ $webhook->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteWebhook({{ $webhook->id }})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">
                    <i class="fas fa-plug" style="font-size: 48px; margin-bottom: 10px; opacity: 0.3;"></i>
                    <p>No webhooks configured yet.</p>
                    <button class="btn btn-primary" onclick="openCreateModal()">Create Your First Webhook</button>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($webhooks->hasPages())
        <div style="padding: 15px; border-top: 1px solid #e5e7eb;">
            {{ $webhooks->links() }}
        </div>
    @endif
</div>

<!-- Create/Edit Modal -->
<div id="webhookModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-plug"></i> New Webhook</h3>
            <button class="btn btn-secondary btn-sm" onclick="closeModal()">&times;</button>
        </div>
        <form id="webhookForm">
            <input type="hidden" id="webhookId" value="">

            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" class="form-control" id="webhookName" required>
            </div>

            <div class="form-group">
                <label class="form-label">URL * <small style="color: #6b7280;">(must be HTTPS in production)</small></label>
                <input type="url" class="form-control" id="webhookUrl" required placeholder="https://your-api.com/webhook">
            </div>

            <div class="form-group">
                <label class="form-label">Secret Key <small style="color: #6b7280;">(optional, for signature verification)</small></label>
                <input type="text" class="form-control" id="webhookSecret" placeholder="your-secret-key">
            </div>

            <div class="form-group">
                <label class="form-label">Events * <small style="color: #6b7280;">(select at least one)</small></label>
                <div class="events-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                    @foreach($availableEvents as $code => $label)
                    <label class="d-flex align-center gap-2" style="padding: 8px; background: #f9fafb; border-radius: 4px; cursor: pointer;">
                        <input type="checkbox" name="events[]" value="{{ $code }}" class="event-checkbox">
                        <span style="font-size: 13px;">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Retry Count</label>
                    <input type="number" class="form-control" id="webhookRetry" min="0" max="10" value="3">
                </div>

                <div class="form-group">
                    <label class="form-label">Timeout (seconds)</label>
                    <input type="number" class="form-control" id="webhookTimeout" min="5" max="120" value="30">
                </div>
            </div>

            <div class="form-group">
                <label class="d-flex align-center gap-2">
                    <input type="checkbox" id="webhookActive" checked>
                    <span>Active</span>
                </label>
            </div>

            <div class="d-flex gap-2" style="margin-top: 20px;">
                <button type="button" class="btn btn-secondary flex-1" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary flex-1">
                    <i class="fas fa-save"></i> Save Webhook
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Logs Modal -->
<div id="logsModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 800px; max-height: 80vh; overflow-y: auto;">
        <div class="modal-header">
            <h3><i class="fas fa-history"></i> Webhook Logs</h3>
            <button class="btn btn-secondary btn-sm" onclick="closeLogsModal()">&times;</button>
        </div>
        <div id="logsContent">
            <p style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading...</p>
        </div>
        <div class="d-flex gap-2" style="margin-top: 20px;">
            <button class="btn btn-danger btn-sm" onclick="clearLogs()">
                <i class="fas fa-trash"></i> Clear Logs
            </button>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
}
.flex-1 { flex: 1; }
.log-entry {
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 10px;
    margin-bottom: 10px;
}
.log-entry.success { border-left: 4px solid #10b981; }
.log-entry.failed { border-left: 4px solid #ef4444; }
</style>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let webhooks = @json($webhooks->items());
let currentWebhookId = null;

function openCreateModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plug"></i> New Webhook';
    document.getElementById('webhookId').value = '';
    document.getElementById('webhookForm').reset();
    document.querySelectorAll('.event-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('webhookModal').style.display = 'flex';
}

function editWebhook(id) {
    const webhook = webhooks.find(w => w.id === id);
    if (!webhook) return;

    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Webhook';
    document.getElementById('webhookId').value = webhook.id;
    document.getElementById('webhookName').value = webhook.name;
    document.getElementById('webhookUrl').value = webhook.url;
    document.getElementById('webhookSecret').value = webhook.secret || '';
    document.getElementById('webhookRetry').value = webhook.retry_count;
    document.getElementById('webhookTimeout').value = webhook.timeout;
    document.getElementById('webhookActive').checked = webhook.is_active;

    document.querySelectorAll('.event-checkbox').forEach(cb => {
        cb.checked = (webhook.events || []).includes(cb.value);
    });

    document.getElementById('webhookModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('webhookModal').style.display = 'none';
}

function closeLogsModal() {
    document.getElementById('logsModal').style.display = 'none';
}

function toggleWebhook(id) {
    fetch(`/admin/webhooks/${id}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Error toggling webhook');
    });
}

function testWebhook(id) {
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`/admin/webhooks/${id}/test`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
        } else {
            btn.innerHTML = '<i class="fas fa-times"></i>';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-danger');
            alert('Test failed: ' + (data.message || 'Unknown error'));
        }
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-play"></i>';
            btn.classList.remove('btn-success', 'btn-danger');
            btn.classList.add('btn-primary');
            btn.disabled = false;
        }, 3000);
    })
    .catch(err => {
        btn.innerHTML = '<i class="fas fa-play"></i>';
        btn.disabled = false;
        alert('Error testing webhook');
    });
}

function deleteWebhook(id) {
    if (!confirm('Are you sure you want to delete this webhook?')) return;

    fetch(`/admin/webhooks/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Error deleting webhook');
    });
}

function viewLogs(id) {
    currentWebhookId = id;
    document.getElementById('logsContent').innerHTML = '<p style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading...</p>';
    document.getElementById('logsModal').style.display = 'flex';

    fetch(`/admin/webhooks/${id}/logs`)
    .then(res => res.json())
    .then(data => {
        if (data.logs && data.logs.length > 0) {
            let html = '';
            data.logs.forEach(log => {
                const statusClass = log.status === 'success' ? 'success' : 'failed';
                html += `
                    <div class="log-entry ${statusClass}">
                        <div class="d-flex justify-between">
                            <strong>${log.event}</strong>
                            <span class="badge ${statusClass === 'success' ? 'badge-success' : 'badge-danger'}">${log.response_code || log.status}</span>
                        </div>
                        <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">
                            ${new Date(log.created_at).toLocaleString()}
                            ${log.duration_ms ? ` - ${log.duration_ms}ms` : ''}
                        </div>
                        ${log.error_message ? `<div style="color: #ef4444; margin-top: 5px; font-size: 12px;">${log.error_message}</div>` : ''}
                    </div>
                `;
            });
            document.getElementById('logsContent').innerHTML = html;
        } else {
            document.getElementById('logsContent').innerHTML = '<p style="text-align: center; padding: 20px; color: #6b7280;">No logs found.</p>';
        }
    });
}

function clearLogs() {
    if (!currentWebhookId || !confirm('Are you sure you want to clear all logs?')) return;

    fetch(`/admin/webhooks/${currentWebhookId}/logs`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('logsContent').innerHTML = '<p style="text-align: center; padding: 20px; color: #6b7280;">No logs found.</p>';
        }
    });
}

document.getElementById('webhookForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const id = document.getElementById('webhookId').value;
    const isEdit = !!id;

    const events = [];
    document.querySelectorAll('.event-checkbox:checked').forEach(cb => {
        events.push(cb.value);
    });

    if (events.length === 0) {
        alert('Please select at least one event');
        return;
    }

    const data = {
        name: document.getElementById('webhookName').value,
        url: document.getElementById('webhookUrl').value,
        secret: document.getElementById('webhookSecret').value || null,
        events: events,
        retry_count: parseInt(document.getElementById('webhookRetry').value),
        timeout: parseInt(document.getElementById('webhookTimeout').value),
        is_active: document.getElementById('webhookActive').checked
    };

    const url = isEdit ? `/admin/webhooks/${id}` : '/admin/webhooks';
    const method = isEdit ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Error saving webhook');
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeLogsModal();
    }
});
</script>
@endpush
