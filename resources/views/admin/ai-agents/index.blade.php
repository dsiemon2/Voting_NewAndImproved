@extends('layouts.app')

@section('content')
<div class="page-title d-flex justify-between align-center">
    <span><i class="fas fa-robot"></i> AI Agents</span>
    <button class="btn btn-primary" onclick="openCreateModal()">
        <i class="fas fa-plus"></i> New Agent
    </button>
</div>

<!-- Default Agent Summary -->
@php $defaultAgent = $agents->firstWhere('is_default', true); @endphp
@if($defaultAgent)
<div class="card" style="background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%); color: white; margin-bottom: 20px;">
    <div class="d-flex justify-between align-center">
        <div>
            <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Default AI Agent</div>
            <div style="font-size: 20px; font-weight: bold;">{{ $defaultAgent->name }}</div>
            <div style="font-size: 14px; opacity: 0.9;">Personality: {{ ucfirst($defaultAgent->personality) }}</div>
        </div>
        <div style="font-size: 48px; opacity: 0.3;">
            <i class="fas fa-robot"></i>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i> <strong>No Default Agent</strong> - Set a default agent to enable AI chat.
</div>
@endif

<!-- Info Box -->
<div class="card" style="background: #f0f9ff; border: 1px solid #bae6fd; margin-bottom: 20px;">
    <div class="d-flex gap-4" style="align-items: flex-start;">
        <div style="flex: 1; padding: 10px; border-right: 1px solid #bae6fd;">
            <div style="font-weight: 600; color: #0369a1; margin-bottom: 5px;">
                <i class="fas fa-user-cog"></i> Personality Modes
            </div>
            <div style="font-size: 13px; color: #475569;">
                Each agent can have a different personality: Professional, Friendly, Concise, or Creative.
            </div>
        </div>
        <div style="flex: 1; padding: 10px;">
            <div style="font-weight: 600; color: #059669; margin-bottom: 5px;">
                <i class="fas fa-star"></i> Default Agent
            </div>
            <div style="font-size: 13px; color: #475569;">
                The default agent is used for new conversations. Users can switch agents during chat.
            </div>
        </div>
    </div>
</div>

<!-- Agents Grid -->
<div class="grid grid-3" style="margin-bottom: 20px;">
    @foreach($agents as $agent)
    <div class="card agent-card {{ !$agent->is_active ? 'disabled' : '' }}"
         data-agent-id="{{ $agent->id }}"
         style="border: 2px solid {{ $agent->is_default ? '#7c3aed' : '#e5e7eb' }}; position: relative;">

        <!-- Status Badges -->
        <div style="position: absolute; top: 12px; right: 15px; display: flex; gap: 5px;">
            @if($agent->is_default)
            <span class="badge badge-primary">Default</span>
            @endif
            <span class="badge {{ $agent->is_active ? 'badge-success' : 'badge-secondary' }}">
                {{ $agent->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <!-- Agent Header -->
        <div style="padding: 15px 0; text-align: center; border-bottom: 1px solid #e5e7eb; margin-bottom: 15px;">
            <div style="font-size: 36px; margin-bottom: 10px; color: {{ $agent->is_active ? '#7c3aed' : '#9ca3af' }};">
                @switch($agent->personality)
                    @case('friendly')
                        <i class="fas fa-smile"></i>
                        @break
                    @case('concise')
                        <i class="fas fa-compress-alt"></i>
                        @break
                    @case('creative')
                        <i class="fas fa-lightbulb"></i>
                        @break
                    @default
                        <i class="fas fa-user-tie"></i>
                @endswitch
            </div>
            <h3 style="margin: 0; color: #1f2937;">{{ $agent->name }}</h3>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #6b7280;">{{ $agent->code }}</p>
        </div>

        <!-- Agent Details -->
        <div class="form-group">
            <label class="form-label"><i class="fas fa-user"></i> Personality</label>
            <div style="font-size: 14px; color: #374151;">{{ ucfirst($agent->personality) }}</div>
        </div>

        <div class="form-group">
            <label class="form-label"><i class="fas fa-thermometer-half"></i> Temperature</label>
            <div style="font-size: 14px; color: #374151;">{{ $agent->temperature }}</div>
        </div>

        @if($agent->description)
        <div class="form-group">
            <label class="form-label"><i class="fas fa-info-circle"></i> Description</label>
            <div style="font-size: 13px; color: #6b7280;">{{ Str::limit($agent->description, 100) }}</div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="d-flex gap-2" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
            <button class="btn btn-secondary btn-sm flex-1" onclick="editAgent({{ $agent->id }})">
                <i class="fas fa-edit"></i> Edit
            </button>
            @if(!$agent->is_default)
            <button class="btn btn-primary btn-sm flex-1" onclick="setDefault({{ $agent->id }})">
                <i class="fas fa-star"></i> Set Default
            </button>
            <button class="btn btn-danger btn-sm" onclick="deleteAgent({{ $agent->id }})" style="width: 40px;">
                <i class="fas fa-trash"></i>
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>

<!-- Create/Edit Modal -->
<div id="agentModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-robot"></i> New Agent</h3>
            <button class="btn btn-secondary btn-sm" onclick="closeModal()">&times;</button>
        </div>
        <form id="agentForm">
            <input type="hidden" id="agentId" value="">

            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" class="form-control" id="agentName" required>
            </div>

            <div class="form-group">
                <label class="form-label">Code * <small style="color: #6b7280;">(unique identifier)</small></label>
                <input type="text" class="form-control" id="agentCode" required pattern="[a-z0-9_]+">
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-control" id="agentDescription" rows="2"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">System Prompt *</label>
                <textarea class="form-control" id="agentPrompt" rows="4" required></textarea>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Personality</label>
                    <select class="form-control" id="agentPersonality">
                        @foreach($personalities as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Temperature</label>
                    <input type="number" class="form-control" id="agentTemperature"
                           min="0" max="2" step="0.1" value="0.7">
                </div>
            </div>

            <div class="form-group">
                <label class="d-flex align-center gap-2">
                    <input type="checkbox" id="agentActive" checked>
                    <span>Active</span>
                </label>
            </div>

            <div class="d-flex gap-2" style="margin-top: 20px;">
                <button type="button" class="btn btn-secondary flex-1" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary flex-1">
                    <i class="fas fa-save"></i> Save Agent
                </button>
            </div>
        </form>
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
.agent-card {
    transition: all 0.3s ease;
}
.agent-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.agent-card.disabled {
    opacity: 0.6;
    background: #f9fafb;
}
.flex-1 {
    flex: 1;
}
</style>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let agents = @json($agents);

function openCreateModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-robot"></i> New Agent';
    document.getElementById('agentId').value = '';
    document.getElementById('agentForm').reset();
    document.getElementById('agentCode').disabled = false;
    document.getElementById('agentModal').style.display = 'flex';
}

function editAgent(id) {
    const agent = agents.find(a => a.id === id);
    if (!agent) return;

    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Agent';
    document.getElementById('agentId').value = agent.id;
    document.getElementById('agentName').value = agent.name;
    document.getElementById('agentCode').value = agent.code;
    document.getElementById('agentCode').disabled = true;
    document.getElementById('agentDescription').value = agent.description || '';
    document.getElementById('agentPrompt').value = agent.system_prompt;
    document.getElementById('agentPersonality').value = agent.personality;
    document.getElementById('agentTemperature').value = agent.temperature;
    document.getElementById('agentActive').checked = agent.is_active;
    document.getElementById('agentModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('agentModal').style.display = 'none';
}

function setDefault(id) {
    fetch(`/admin/ai-agents/${id}/default`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Error setting default');
    });
}

function deleteAgent(id) {
    if (!confirm('Are you sure you want to delete this agent?')) return;

    fetch(`/admin/ai-agents/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Error deleting agent');
    });
}

document.getElementById('agentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const id = document.getElementById('agentId').value;
    const isEdit = !!id;

    const data = {
        name: document.getElementById('agentName').value,
        code: document.getElementById('agentCode').value,
        description: document.getElementById('agentDescription').value,
        system_prompt: document.getElementById('agentPrompt').value,
        personality: document.getElementById('agentPersonality').value,
        temperature: parseFloat(document.getElementById('agentTemperature').value),
        is_active: document.getElementById('agentActive').checked
    };

    const url = isEdit ? `/admin/ai-agents/${id}` : '/admin/ai-agents';
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
        else alert(data.message || 'Error saving agent');
    });
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
@endpush
