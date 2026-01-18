@extends('layouts.app')

@section('content')
<div>
    <h1 class="page-title">
        <i class="fas fa-volume-up"></i> Voices, Languages & Mode
    </h1>

    <!-- Toast Container -->
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    <!-- Assistant Mode Section -->
    <div class="card mb-4">
        <div class="card-header" style="background: #f59e0b; color: white;">
            <i class="fas fa-exchange-alt"></i> Assistant Mode
        </div>
        <div class="card-body">
            <p style="color: #6b7280; margin-bottom: 20px;">Choose how the AI assistant will interact with users.</p>

            <div class="grid grid-2">
                <div class="mode-card {{ $config->assistant_mode === 'ai_only' ? 'selected' : '' }}"
                     data-mode="ai_only" onclick="selectMode('ai_only')">
                    <div style="text-align: center; padding: 20px;">
                        <i class="fas fa-robot" style="font-size: 3rem; color: #2563eb;"></i>
                        <h4 style="margin-top: 15px;">Fully Automated</h4>
                        <p style="color: #6b7280; margin-bottom: 0;">The AI handles all interactions autonomously. Set it and forget it!</p>
                        @if($config->assistant_mode === 'ai_only')
                            <span class="badge badge-info" style="margin-top: 10px;"><i class="fas fa-check"></i> Active</span>
                        @endif
                    </div>
                </div>

                <div class="mode-card {{ $config->assistant_mode === 'hybrid' ? 'selected' : '' }}"
                     data-mode="hybrid" onclick="selectMode('hybrid')">
                    <div style="text-align: center; padding: 20px;">
                        <i class="fas fa-user" style="font-size: 3rem; color: #10b981;"></i>
                        <h4 style="margin-top: 15px;">Interactive Mode</h4>
                        <p style="color: #6b7280; margin-bottom: 0;">AI provides suggestions and guidance, but you confirm important actions.</p>
                        @if($config->assistant_mode === 'hybrid')
                            <span class="badge badge-info" style="margin-top: 10px;"><i class="fas fa-check"></i> Active</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Intensity Section (shown only in hybrid mode) -->
    <div class="card mb-4" id="intensitySection" style="{{ $config->assistant_mode !== 'hybrid' ? 'display:none;' : '' }}">
        <div class="card-header" style="background: #dc2626; color: white;">
            <i class="fas fa-tachometer-alt"></i> AI Assistance Intensity
        </div>
        <div class="card-body">
            <p style="color: #6b7280; margin-bottom: 20px;">How proactive should the AI be with suggestions?</p>

            <div class="grid grid-4">
                <button type="button" class="intensity-btn {{ $config->intensity === 'gentle' ? 'active' : '' }}"
                        data-intensity="gentle" onclick="selectIntensity('gentle')"
                        style="background: {{ $config->intensity === 'gentle' ? '#10b981' : '#f3f4f6' }}; color: {{ $config->intensity === 'gentle' ? 'white' : '#374151' }};">
                    <i class="fas fa-smile" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                    <strong>Gentle</strong>
                    <small style="display: block; color: {{ $config->intensity === 'gentle' ? 'rgba(255,255,255,0.8)' : '#6b7280' }};">Occasional tips</small>
                </button>

                <button type="button" class="intensity-btn {{ $config->intensity === 'moderate' ? 'active' : '' }}"
                        data-intensity="moderate" onclick="selectIntensity('moderate')"
                        style="background: {{ $config->intensity === 'moderate' ? '#f59e0b' : '#f3f4f6' }}; color: {{ $config->intensity === 'moderate' ? 'white' : '#374151' }};">
                    <i class="fas fa-meh" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                    <strong>Moderate</strong>
                    <small style="display: block; color: {{ $config->intensity === 'moderate' ? 'rgba(255,255,255,0.8)' : '#6b7280' }};">Regular guidance</small>
                </button>

                <button type="button" class="intensity-btn {{ $config->intensity === 'persistent' ? 'active' : '' }}"
                        data-intensity="persistent" onclick="selectIntensity('persistent')"
                        style="background: {{ $config->intensity === 'persistent' ? '#dc2626' : '#f3f4f6' }}; color: {{ $config->intensity === 'persistent' ? 'white' : '#374151' }};">
                    <i class="fas fa-frown" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                    <strong>Persistent</strong>
                    <small style="display: block; color: {{ $config->intensity === 'persistent' ? 'rgba(255,255,255,0.8)' : '#6b7280' }};">Frequent help</small>
                </button>

                <button type="button" class="intensity-btn {{ $config->intensity === 'full' ? 'active' : '' }}"
                        data-intensity="full" onclick="selectIntensity('full')"
                        style="background: {{ $config->intensity === 'full' ? '#1e3a8a' : '#f3f4f6' }}; color: {{ $config->intensity === 'full' ? 'white' : '#374151' }};">
                    <i class="fas fa-robot" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                    <strong>Full AI</strong>
                    <small style="display: block; color: {{ $config->intensity === 'full' ? 'rgba(255,255,255,0.8)' : '#6b7280' }};">Maximum assist</small>
                </button>
            </div>
        </div>
    </div>

    <!-- Voice Selection Section -->
    <div class="card mb-4">
        <div class="card-header" style="background: #2563eb; color: white;">
            <i class="fas fa-user-circle"></i> Select AI Voice
        </div>
        <div class="card-body">
            <p style="color: #6b7280; margin-bottom: 20px;">Choose the voice for your AI assistant. Click to select, hover to preview.</p>

            <!-- Male Voices -->
            <h6 style="color: #6b7280; margin-bottom: 15px;"><i class="fas fa-mars"></i> Male Voices</h6>
            <div class="grid grid-3 mb-4">
                @foreach($voices['male'] as $voice)
                <div class="voice-card {{ $config->selected_voice === $voice['id'] ? 'selected' : '' }}"
                     data-voice="{{ $voice['id'] }}" onclick="selectVoice('{{ $voice['id'] }}')">
                    <button type="button" class="play-btn" onclick="event.stopPropagation(); previewVoice('{{ $voice['id'] }}')" title="Preview voice">
                        <i class="fas fa-play"></i>
                    </button>
                    <div class="voice-avatar" style="background: {{ $voice['color'] }};">
                        <i class="fas fa-user" style="color: white; font-size: 2.5rem;"></i>
                    </div>
                    <h5>{{ $voice['name'] }}</h5>
                    <span class="badge badge-info">{{ $voice['accent'] }}</span>
                    <small style="display: block; color: #6b7280; margin-top: 5px;">{{ $voice['description'] }}</small>
                    <small style="display: block; color: #9ca3af; font-size: 0.75rem;">{{ $voice['detail'] }}</small>
                    @if($config->selected_voice === $voice['id'])
                        <span class="badge badge-success" style="margin-top: 10px;"><i class="fas fa-check"></i> Active</span>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Female Voices -->
            <h6 style="color: #6b7280; margin-bottom: 15px;"><i class="fas fa-venus"></i> Female Voices</h6>
            <div class="grid grid-3 mb-4">
                @foreach($voices['female'] as $voice)
                <div class="voice-card {{ $config->selected_voice === $voice['id'] ? 'selected' : '' }}"
                     data-voice="{{ $voice['id'] }}" onclick="selectVoice('{{ $voice['id'] }}')">
                    <button type="button" class="play-btn" onclick="event.stopPropagation(); previewVoice('{{ $voice['id'] }}')" title="Preview voice">
                        <i class="fas fa-play"></i>
                    </button>
                    <div class="voice-avatar" style="background: {{ $voice['color'] }};">
                        <i class="fas fa-user" style="color: white; font-size: 2.5rem;"></i>
                    </div>
                    <h5>{{ $voice['name'] }}</h5>
                    <span class="badge badge-info">{{ $voice['accent'] }}</span>
                    <small style="display: block; color: #6b7280; margin-top: 5px;">{{ $voice['description'] }}</small>
                    <small style="display: block; color: #9ca3af; font-size: 0.75rem;">{{ $voice['detail'] }}</small>
                    @if($config->selected_voice === $voice['id'])
                        <span class="badge badge-success" style="margin-top: 10px;"><i class="fas fa-check"></i> Active</span>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Info about accents -->
            <div class="alert alert-warning">
                <h6><i class="fas fa-exclamation-triangle"></i> About Voice Accents</h6>
                <p style="margin-bottom: 10px;"><strong>OpenAI's TTS API offers American English voices.</strong> British, Australian, and other accents require additional TTS providers.</p>
                <p style="margin-bottom: 0;"><strong>For more accent options:</strong> Integration with
                    <a href="https://elevenlabs.io" target="_blank" style="color: #92400e;">ElevenLabs</a> or
                    <a href="https://play.ht" target="_blank" style="color: #92400e;">PlayHT</a> can provide British English, Australian, Irish, Scottish, and 50+ more accent options.
                </p>
            </div>
        </div>
    </div>

    <!-- Languages Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-between align-center" style="background: #10b981; color: white;">
            <span><i class="fas fa-language"></i> Supported Languages</span>
            <button type="button" class="btn btn-sm" style="background: white; color: #10b981;" onclick="openAddLanguageModal()">
                <i class="fas fa-plus"></i> Add Language
            </button>
        </div>
        <div class="card-body">
            <p style="color: #6b7280; margin-bottom: 20px;">Enable or disable languages for your voice assistant.</p>

            <div class="grid grid-3">
                @forelse($languages as $language)
                <div class="lang-card {{ !$language->enabled ? 'disabled-lang' : '' }}">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 2rem; margin-right: 12px;">{{ $language->flag ?? '🌐' }}</span>
                            <div>
                                <strong>{{ $language->name }}</strong>
                                <br><small style="color: #6b7280;">{{ $language->native_name }} ({{ $language->code }})</small>
                            </div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="lang-{{ $language->code }}"
                                   {{ $language->enabled ? 'checked' : '' }}
                                   onchange="toggleLanguage('{{ $language->id }}', this.checked)">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e5e7eb;">
                        <small style="color: #6b7280;">
                            <i class="fas fa-file-alt"></i> {{ $language->doc_count ?? 0 }} KB documents
                        </small>
                    </div>
                </div>
                @empty
                <div style="grid-column: 1 / -1;">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No languages configured. Click "Add Language" to get started.
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-icon" style="color: #f59e0b;">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="stat-value" style="font-size: 1.5rem; text-transform: capitalize;">{{ $config->assistant_mode === 'ai_only' ? 'Automated' : 'Interactive' }}</div>
            <div class="stat-label">Assistant Mode</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #dc2626;">
                <i class="fas fa-tachometer-alt"></i>
            </div>
            <div class="stat-value" style="font-size: 1.5rem; text-transform: capitalize;">{{ $config->intensity }}</div>
            <div class="stat-label">AI Intensity</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #2563eb;">
                <i class="fas fa-volume-up"></i>
            </div>
            <div class="stat-value" style="font-size: 1.5rem; text-transform: capitalize;">{{ $config->selected_voice }}</div>
            <div class="stat-label">Current Voice</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color: #10b981;">
                <i class="fas fa-language"></i>
            </div>
            <div class="stat-value" style="font-size: 1.5rem;">{{ $languages->where('enabled', true)->count() }}</div>
            <div class="stat-label">Active Languages</div>
        </div>
    </div>
</div>

<!-- Add Language Modal -->
<div id="addLanguageModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h5><i class="fas fa-plus"></i> Add Language</h5>
            <button type="button" class="modal-close" onclick="closeAddLanguageModal()">&times;</button>
        </div>
        <form action="{{ route('admin.ai-settings.languages.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Language</label>
                    <select class="form-control" name="code" required>
                        <option value="">Select a language...</option>
                        @foreach(\App\Models\AiLanguage::getAvailableLanguages() as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddLanguageModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Language</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .mode-card {
        background: white;
        border-radius: 8px;
        border: 3px solid transparent;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .mode-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .mode-card.selected {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.25);
    }

    .intensity-btn {
        padding: 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
    }
    .intensity-btn:hover {
        transform: scale(1.02);
    }
    .intensity-btn.active {
        transform: scale(1.05);
    }

    .voice-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid transparent;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
    }
    .voice-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .voice-card.selected {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.25);
    }
    .voice-card .play-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0;
        transition: opacity 0.2s;
        background: #f3f4f6;
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        cursor: pointer;
    }
    .voice-card:hover .play-btn {
        opacity: 1;
    }
    .voice-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }

    .lang-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }
    .lang-card.disabled-lang {
        opacity: 0.5;
    }

    /* Switch Toggle */
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 26px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: #10b981;
    }
    input:checked + .slider:before {
        transform: translateX(24px);
    }

    /* Modal */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background: white;
        border-radius: 8px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.25);
    }
    .modal-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header h5 {
        margin: 0;
        color: #1e3a8a;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6b7280;
    }
    .modal-body {
        padding: 20px;
    }
    .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Toast */
    .toast {
        background: white;
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
    }
    .toast.success { border-left: 4px solid #10b981; }
    .toast.error { border-left: 4px solid #dc2626; }
    .toast.info { border-left: 4px solid #2563eb; }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @media screen and (max-width: 768px) {
        .grid-3 { grid-template-columns: 1fr; }
        .grid-4 { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endpush

@push('scripts')
<script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    async function selectMode(mode) {
        try {
            const res = await fetch('{{ route("admin.ai-settings.voices.mode") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ mode })
            });

            if (res.ok) {
                // Update UI
                document.querySelectorAll('.mode-card').forEach(card => {
                    card.classList.remove('selected');
                    const badge = card.querySelector('.badge.badge-info');
                    if (badge) badge.remove();
                });

                const selectedCard = document.querySelector(`[data-mode="${mode}"]`);
                selectedCard.classList.add('selected');
                selectedCard.querySelector('div').insertAdjacentHTML('beforeend',
                    '<span class="badge badge-info" style="margin-top: 10px;"><i class="fas fa-check"></i> Active</span>');

                // Show/hide intensity section
                document.getElementById('intensitySection').style.display = mode === 'hybrid' ? '' : 'none';

                showToast(`Mode changed to ${mode === 'ai_only' ? 'Fully Automated' : 'Interactive'}`);
            }
        } catch (err) {
            showToast('Failed to update mode', 'error');
        }
    }

    async function selectIntensity(intensity) {
        try {
            const res = await fetch('{{ route("admin.ai-settings.voices.intensity") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ intensity })
            });

            if (res.ok) {
                // Reload to update button styles
                location.reload();
            }
        } catch (err) {
            showToast('Failed to update intensity', 'error');
        }
    }

    async function selectVoice(voiceId) {
        try {
            const res = await fetch('{{ route("admin.ai-settings.voices.select") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ voice: voiceId })
            });

            if (res.ok) {
                // Update UI
                document.querySelectorAll('.voice-card').forEach(card => {
                    card.classList.remove('selected');
                    const badge = card.querySelector('.badge.badge-success');
                    if (badge) badge.remove();
                });

                const selectedCard = document.querySelector(`[data-voice="${voiceId}"]`);
                selectedCard.classList.add('selected');
                selectedCard.insertAdjacentHTML('beforeend', '<span class="badge badge-success" style="margin-top: 10px;"><i class="fas fa-check"></i> Active</span>');

                showToast(`Voice changed to ${voiceId}`);
            }
        } catch (err) {
            showToast('Failed to update voice', 'error');
        }
    }

    function previewVoice(voiceId) {
        // Use browser speech synthesis for preview
        const utterance = new SpeechSynthesisUtterance(`Hello! I'm ${voiceId}, your AI voting assistant. How can I help you today?`);
        window.speechSynthesis.speak(utterance);
        showToast(`Playing preview for ${voiceId}...`, 'info');
    }

    async function toggleLanguage(langId, enabled) {
        try {
            const res = await fetch(`{{ url('admin/ai-settings/languages') }}/${langId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ enabled })
            });

            if (res.ok) {
                showToast(`Language ${enabled ? 'enabled' : 'disabled'}`);
            }
        } catch (err) {
            showToast('Failed to update language', 'error');
        }
    }

    function openAddLanguageModal() {
        document.getElementById('addLanguageModal').style.display = 'flex';
    }

    function closeAddLanguageModal() {
        document.getElementById('addLanguageModal').style.display = 'none';
    }

    // Close modal on outside click
    document.getElementById('addLanguageModal').addEventListener('click', function(e) {
        if (e.target === this) closeAddLanguageModal();
    });
</script>
@endpush
