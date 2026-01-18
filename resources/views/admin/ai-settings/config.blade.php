@extends('layouts.app')

@section('content')
<div>
    <div class="d-flex justify-between align-center mb-4">
        <h1 class="page-title" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">
            <i class="fas fa-sliders-h"></i> AI Configuration
        </h1>
        <button type="button" class="btn btn-success" id="saveConfig" onclick="saveConfig()">
            <i class="fas fa-check"></i> Save Changes
        </button>
    </div>

    <form id="configForm">
        @csrf
        <div class="grid grid-2">
            <!-- Model Settings -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-microchip"></i> Model Settings
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Default Model</label>
                        <select class="form-control" name="default_model">
                            <option value="gpt-4o-realtime" {{ $config->default_model === 'gpt-4o-realtime' ? 'selected' : '' }}>GPT-4o Realtime</option>
                            <option value="gpt-4o" {{ $config->default_model === 'gpt-4o' ? 'selected' : '' }}>GPT-4o</option>
                            <option value="gpt-4-turbo" {{ $config->default_model === 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo</option>
                            <option value="gpt-3.5-turbo" {{ $config->default_model === 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                            <option value="claude-3-opus" {{ $config->default_model === 'claude-3-opus' ? 'selected' : '' }}>Claude 3 Opus</option>
                            <option value="claude-3-sonnet" {{ $config->default_model === 'claude-3-sonnet' ? 'selected' : '' }}>Claude 3 Sonnet</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Temperature</label>
                        <input type="range" class="form-control" name="temperature" min="0" max="2" step="0.1"
                               value="{{ $config->temperature }}" oninput="updateTempValue(this.value)">
                        <small style="color: #6b7280;">Current: <span id="tempValue">{{ $config->temperature }}</span></small>
                        <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 5px;">
                            Lower = more focused and deterministic. Higher = more creative and varied.
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Max Tokens</label>
                        <input type="number" class="form-control" name="max_tokens" value="{{ $config->max_tokens }}"
                               min="100" max="32000">
                        <small style="color: #6b7280;">Maximum response length (100-32000)</small>
                    </div>
                </div>
            </div>

            <!-- Voice Settings -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-microphone"></i> Voice Settings
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Default Voice</label>
                        <select class="form-control" name="selected_voice">
                            <option value="alloy" {{ $config->selected_voice === 'alloy' ? 'selected' : '' }}>Alloy (Neutral)</option>
                            <option value="ash" {{ $config->selected_voice === 'ash' ? 'selected' : '' }}>Ash (Male)</option>
                            <option value="ballad" {{ $config->selected_voice === 'ballad' ? 'selected' : '' }}>Ballad (Female)</option>
                            <option value="coral" {{ $config->selected_voice === 'coral' ? 'selected' : '' }}>Coral (Female)</option>
                            <option value="echo" {{ $config->selected_voice === 'echo' ? 'selected' : '' }}>Echo (Male)</option>
                            <option value="sage" {{ $config->selected_voice === 'sage' ? 'selected' : '' }}>Sage (Female)</option>
                            <option value="shimmer" {{ $config->selected_voice === 'shimmer' ? 'selected' : '' }}>Shimmer (Female)</option>
                            <option value="verse" {{ $config->selected_voice === 'verse' ? 'selected' : '' }}>Verse (Male)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Speech Speed</label>
                        <input type="range" class="form-control" name="speech_speed" min="0.5" max="2" step="0.1"
                               value="{{ $config->speech_speed ?? 1.0 }}" oninput="updateSpeedValue(this.value)">
                        <small style="color: #6b7280;">Current: <span id="speedValue">{{ $config->speech_speed ?? 1.0 }}</span>x</small>
                    </div>

                    <div class="form-group">
                        <label class="switch-label">
                            <input type="checkbox" name="enable_tts" {{ $config->enable_tts ? 'checked' : '' }}>
                            <span class="switch-text">Enable Text-to-Speech</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Safety Settings -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-shield-alt"></i> Safety & Privacy Settings
            </div>
            <div class="card-body">
                <div class="grid grid-2">
                    <div>
                        <div class="form-group">
                            <label class="switch-label">
                                <input type="checkbox" name="content_filter" {{ $config->content_filter ? 'checked' : '' }}>
                                <span class="switch-text">Enable Content Filtering</span>
                            </label>
                            <small style="color: #6b7280; display: block; margin-top: 5px;">Block inappropriate or harmful content</small>
                        </div>

                        <div class="form-group">
                            <label class="switch-label">
                                <input type="checkbox" name="pii_detection" {{ $config->pii_detection ? 'checked' : '' }}>
                                <span class="switch-text">PII Detection & Masking</span>
                            </label>
                            <small style="color: #6b7280; display: block; margin-top: 5px;">Detect and mask personal identifiable information</small>
                        </div>
                    </div>

                    <div>
                        <div class="form-group">
                            <label class="switch-label">
                                <input type="checkbox" name="transcript_logging" {{ $config->transcript_logging ? 'checked' : '' }}>
                                <span class="switch-text">Log Transcripts</span>
                            </label>
                            <small style="color: #6b7280; display: block; margin-top: 5px;">Save conversation transcripts for review</small>
                        </div>

                        <div class="form-group">
                            <label class="switch-label">
                                <input type="checkbox" name="record_calls" {{ $config->record_calls ? 'checked' : '' }}>
                                <span class="switch-text">Record Voice Calls</span>
                            </label>
                            <small style="color: #6b7280; display: block; margin-top: 5px;">Record voice interactions (requires user consent)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Providers Link -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-brain"></i> AI Provider Configuration
            </div>
            <div class="card-body">
                @php
                    $selectedProvider = \App\Models\AiProvider::getSelected();
                @endphp
                @if($selectedProvider && $selectedProvider->is_configured)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Active Provider:</strong> {{ $selectedProvider->name }} ({{ $selectedProvider->default_model }})
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No AI provider is currently configured.
                    </div>
                @endif
                <p style="color: #6b7280; margin-bottom: 15px;">
                    Configure API keys for OpenAI, Anthropic Claude, Google Gemini, DeepSeek, Groq, Mistral, and Grok (xAI).
                </p>
                <a href="{{ route('admin.ai-providers.index') }}" class="btn btn-primary">
                    <i class="fas fa-cog"></i> Manage AI Providers
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .switch-label {
        display: flex;
        align-items: center;
        cursor: pointer;
    }
    .switch-label input[type="checkbox"] {
        width: 20px;
        height: 20px;
        margin-right: 10px;
        cursor: pointer;
    }
    .switch-text {
        font-weight: 500;
        color: #374151;
    }

    input[type="range"] {
        -webkit-appearance: none;
        width: 100%;
        height: 8px;
        border-radius: 4px;
        background: #e5e7eb;
        outline: none;
    }
    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #2563eb;
        cursor: pointer;
    }
    input[type="range"]::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #2563eb;
        cursor: pointer;
        border: none;
    }
</style>
@endpush

@push('scripts')
<script>
    function updateTempValue(val) {
        document.getElementById('tempValue').textContent = val;
    }

    function updateSpeedValue(val) {
        document.getElementById('speedValue').textContent = val;
    }

    async function saveConfig() {
        const form = document.getElementById('configForm');
        const formData = new FormData(form);

        // Convert checkboxes to boolean
        const data = {};
        formData.forEach((value, key) => {
            if (key === '_token') return;
            data[key] = value;
        });

        // Handle unchecked checkboxes
        ['enable_tts', 'content_filter', 'pii_detection', 'transcript_logging', 'record_calls'].forEach(field => {
            data[field] = form.querySelector(`[name="${field}"]`).checked;
        });

        try {
            const res = await fetch('{{ route("admin.ai-settings.config.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            if (res.ok) {
                alert('Configuration saved successfully!');
            } else {
                alert('Failed to save configuration');
            }
        } catch (err) {
            alert('Error saving configuration');
        }
    }
</script>
@endpush
