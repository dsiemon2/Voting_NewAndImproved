@extends('layouts.app')

@section('content')
<div class="page-title d-flex justify-between align-center">
    <span><i class="fas fa-brain"></i> AI Model Configuration</span>
</div>

<!-- Active Provider Summary -->
@if($selectedProvider && $selectedProvider->is_configured)
<div class="card" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: white; margin-bottom: 20px;">
    <div class="d-flex justify-between align-center">
        <div>
            <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Currently Active Provider</div>
            <div style="font-size: 20px; font-weight: bold;">{{ $selectedProvider->name }}</div>
            <div style="font-size: 14px; opacity: 0.9;">Model: {{ $selectedProvider->default_model }}</div>
        </div>
        <div style="font-size: 48px; opacity: 0.3;">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i> <strong>No AI Provider Configured</strong> - Configure an API key below to enable AI features.
</div>
@endif

<!-- How Selection Works -->
<div class="card" style="background: #f0f9ff; border: 1px solid #bae6fd; margin-bottom: 20px;">
    <div class="d-flex gap-4" style="align-items: flex-start;">
        <div style="flex: 1; padding: 10px; border-right: 1px solid #bae6fd;">
            <div style="font-weight: 600; color: #0369a1; margin-bottom: 5px;">
                <i class="fas fa-toggle-on"></i> Available Toggle
            </div>
            <div style="font-size: 13px; color: #475569;">
                Enable/disable providers you want to keep configured. Disabled providers won't appear as options.
            </div>
        </div>
        <div style="flex: 1; padding: 10px;">
            <div style="font-weight: 600; color: #059669; margin-bottom: 5px;">
                <i class="fas fa-check-circle"></i> Select Button
            </div>
            <div style="font-size: 13px; color: #475569;">
                Choose which provider to use for AI responses. <strong>Only one provider can be active at a time.</strong>
            </div>
        </div>
    </div>
</div>

<!-- Provider Cards Grid -->
<div class="grid grid-3" style="margin-bottom: 20px;">
    @foreach($providers as $provider)
    <div class="card provider-card {{ !$provider->is_active ? 'disabled' : '' }}" data-provider-id="{{ $provider->id }}" data-provider-code="{{ $provider->code }}"
         style="border: 2px solid {{ $provider->is_selected ? '#10b981' : '#e5e7eb' }}; position: relative;">

        <!-- Toggle Switch with Label -->
        <div style="position: absolute; top: 12px; right: 15px; display: flex; align-items: center; gap: 8px;">
            <span class="toggle-label" style="font-size: 11px; color: #6b7280; text-transform: uppercase;">
                {{ $provider->is_active ? 'Available' : 'Disabled' }}
            </span>
            <label class="toggle-switch">
                <input type="checkbox" class="provider-toggle" data-provider-id="{{ $provider->id }}"
                       {{ $provider->is_active ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <!-- Selected Badge -->
        @if($provider->is_selected)
        <div style="position: absolute; top: 15px; left: 15px;">
            <span class="badge badge-success"><i class="fas fa-check"></i> Active</span>
        </div>
        @endif

        <!-- Provider Header -->
        <div style="padding: 15px 0; text-align: center; border-bottom: 1px solid #e5e7eb; margin-bottom: 15px;">
            <div style="font-size: 36px; margin-bottom: 10px; color: {{ $provider->is_configured ? '#10b981' : '#9ca3af' }};">
                @switch($provider->code)
                    @case('openai')
                        <i class="fas fa-robot"></i>
                        @break
                    @case('anthropic')
                        <i class="fas fa-brain"></i>
                        @break
                    @case('gemini')
                        <i class="fas fa-gem"></i>
                        @break
                    @case('deepseek')
                        <i class="fas fa-search"></i>
                        @break
                    @case('groq')
                        <i class="fas fa-bolt"></i>
                        @break
                    @case('mistral')
                        <i class="fas fa-wind"></i>
                        @break
                    @case('grok')
                        <i class="fas fa-times"></i>
                        @break
                    @default
                        <i class="fas fa-microchip"></i>
                @endswitch
            </div>
            <h3 style="margin: 0; color: #1f2937;">{{ $provider->name }}</h3>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #6b7280;">{{ $provider->description }}</p>
        </div>

        <!-- API Key Section -->
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-key"></i> API Key
                @if($provider->is_configured)
                    <span class="badge badge-success" style="margin-left: 5px; font-size: 10px;">Configured</span>
                @else
                    <span class="badge badge-danger" style="margin-left: 5px; font-size: 10px;">Not Set</span>
                @endif
            </label>
            <div class="d-flex gap-2">
                <input type="password" class="form-control api-key-input" id="api-key-{{ $provider->id }}"
                       placeholder="{{ $provider->getMaskedApiKey() ?: 'Enter API key...' }}"
                       data-provider-id="{{ $provider->id }}">
                <button class="btn btn-primary btn-sm save-api-key" data-provider-id="{{ $provider->id }}">
                    <i class="fas fa-save"></i>
                </button>
            </div>
        </div>

        <!-- Model Selection -->
        <div class="form-group">
            <label class="form-label"><i class="fas fa-cube"></i> Model</label>
            <select class="form-control model-select" data-provider-id="{{ $provider->id }}">
                @foreach($provider->available_models ?? [] as $model)
                    <option value="{{ $model['id'] }}"
                            {{ ($provider->default_model === $model['id']) ? 'selected' : '' }}
                            {{ ($model['recommended'] ?? false) ? 'data-recommended=true' : '' }}>
                        {{ $model['name'] }}
                        @if($model['recommended'] ?? false) (Recommended) @endif
                    </option>
                @endforeach
            </select>
            @if($provider->available_models)
                @php $selectedModel = collect($provider->available_models)->firstWhere('id', $provider->default_model); @endphp
                @if($selectedModel)
                    <small style="color: #6b7280;">{{ $selectedModel['description'] ?? '' }}</small>
                @endif
            @endif
        </div>

        <!-- Advanced Settings (collapsible) -->
        <details style="margin-top: 10px;">
            <summary style="cursor: pointer; font-size: 13px; color: #6b7280; margin-bottom: 10px;">
                <i class="fas fa-cog"></i> Advanced Settings
            </summary>

            <div class="form-group" style="margin-bottom: 10px;">
                <label class="form-label" style="font-size: 12px;">Temperature: <span class="temp-value">{{ $provider->temperature }}</span></label>
                <input type="range" class="temperature-slider" data-provider-id="{{ $provider->id }}"
                       min="0" max="2" step="0.1" value="{{ $provider->temperature }}"
                       style="width: 100%;">
            </div>

            <div class="form-group" style="margin-bottom: 10px;">
                <label class="form-label" style="font-size: 12px;">Max Tokens</label>
                <input type="number" class="form-control max-tokens-input" data-provider-id="{{ $provider->id }}"
                       value="{{ $provider->max_tokens }}" min="100" max="128000" style="font-size: 13px;">
            </div>
        </details>

        <!-- Action Buttons -->
        <div class="d-flex gap-2" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
            <button class="btn btn-secondary btn-sm test-connection flex-1" data-provider-id="{{ $provider->id }}"
                    {{ !$provider->is_configured ? 'disabled' : '' }}>
                <i class="fas fa-plug"></i> Test
            </button>
            <button class="btn {{ $provider->is_selected ? 'btn-success' : 'btn-primary' }} btn-sm select-provider flex-1"
                    data-provider-id="{{ $provider->id }}"
                    {{ !$provider->is_configured ? 'disabled' : '' }}>
                @if($provider->is_selected)
                    <i class="fas fa-check"></i> Selected
                @else
                    <i class="fas fa-check-circle"></i> Select
                @endif
            </button>
        </div>
    </div>
    @endforeach
</div>

<!-- Info Cards -->
<div class="grid grid-2">
    <div class="card">
        <div class="card-header"><i class="fas fa-info-circle"></i> How It Works</div>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                <strong>1.</strong> Enter your API key for any provider
            </li>
            <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                <strong>2.</strong> Select your preferred model
            </li>
            <li style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                <strong>3.</strong> Click "Test" to verify the connection
            </li>
            <li style="padding: 10px 0;">
                <strong>4.</strong> Click "Select" to make it active
            </li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header"><i class="fas fa-link"></i> Get API Keys</div>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                <a href="https://platform.openai.com/api-keys" target="_blank" style="color: #0d7a3e; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i> OpenAI API Keys
                </a>
            </li>
            <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                <a href="https://console.anthropic.com/settings/keys" target="_blank" style="color: #0d7a3e; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i> Anthropic API Keys
                </a>
            </li>
            <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color: #0d7a3e; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i> Google AI Studio Keys
                </a>
            </li>
            <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                <a href="https://platform.deepseek.com/api_keys" target="_blank" style="color: #0d7a3e; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i> DeepSeek API Keys
                </a>
            </li>
            <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                <a href="https://console.groq.com/keys" target="_blank" style="color: #0d7a3e; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i> Groq API Keys
                </a>
            </li>
            <li style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                <a href="https://console.mistral.ai/api-keys/" target="_blank" style="color: #0d7a3e; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i> Mistral API Keys
                </a>
            </li>
            <li style="padding: 8px 0;">
                <a href="https://console.x.ai/" target="_blank" style="color: #0d7a3e; text-decoration: none;">
                    <i class="fas fa-external-link-alt"></i> xAI (Grok) API Keys
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 26px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #10b981;
}

input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

.provider-card {
    transition: all 0.3s ease;
}

.provider-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.flex-1 {
    flex: 1;
}

.provider-card.disabled {
    opacity: 0.5;
    background: #f9fafb;
}

.provider-card.disabled .provider-content {
    pointer-events: none;
}

/* Keep toggle always clickable */
.provider-card .toggle-switch {
    pointer-events: auto !important;
}

/* Temperature slider styling */
.temperature-slider {
    -webkit-appearance: none;
    appearance: none;
    height: 6px;
    background: #e5e7eb;
    border-radius: 3px;
    outline: none;
}

.temperature-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #0d7a3e;
    cursor: pointer;
}

.temperature-slider::-moz-range-thumb {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #0d7a3e;
    cursor: pointer;
    border: none;
}
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Save API Key
    document.querySelectorAll('.save-api-key').forEach(btn => {
        btn.addEventListener('click', function() {
            const providerId = this.dataset.providerId;
            const input = document.getElementById('api-key-' + providerId);
            const apiKey = input.value.trim();

            if (!apiKey) {
                alert('Please enter an API key');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/admin/ai-providers/${providerId}/api-key`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ api_key: apiKey })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    input.placeholder = data.masked_key || 'Key saved';
                    location.reload();
                } else {
                    alert(data.message || 'Failed to save API key');
                }
            })
            .catch(err => {
                alert('Error saving API key');
                console.error(err);
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-save"></i>';
            });
        });
    });

    // Test Connection
    document.querySelectorAll('.test-connection').forEach(btn => {
        btn.addEventListener('click', function() {
            const providerId = this.dataset.providerId;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';

            fetch(`/admin/ai-providers/${providerId}/test`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.innerHTML = '<i class="fas fa-check"></i> Success';
                    this.classList.remove('btn-secondary');
                    this.classList.add('btn-success');
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-plug"></i> Test';
                        this.classList.remove('btn-success');
                        this.classList.add('btn-secondary');
                    }, 3000);
                } else {
                    alert('Connection failed: ' + (data.message || 'Unknown error'));
                    this.innerHTML = '<i class="fas fa-times"></i> Failed';
                    setTimeout(() => {
                        this.innerHTML = '<i class="fas fa-plug"></i> Test';
                    }, 3000);
                }
            })
            .catch(err => {
                alert('Error testing connection');
                console.error(err);
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });

    // Select Provider
    document.querySelectorAll('.select-provider').forEach(btn => {
        btn.addEventListener('click', function() {
            const providerId = this.dataset.providerId;
            const modelSelect = document.querySelector(`.model-select[data-provider-id="${providerId}"]`);
            const model = modelSelect ? modelSelect.value : null;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(`/admin/ai-providers/${providerId}/select`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ model: model })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to select provider');
                }
            })
            .catch(err => {
                alert('Error selecting provider');
                console.error(err);
            })
            .finally(() => {
                this.disabled = false;
            });
        });
    });

    // Provider Toggle
    document.querySelectorAll('.provider-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const providerId = this.dataset.providerId;
            const isActive = this.checked;
            const card = this.closest('.provider-card');
            const label = card.querySelector('.toggle-label');

            // Update UI
            if (!isActive) {
                card.classList.add('disabled');
                label.textContent = 'Disabled';
            } else {
                card.classList.remove('disabled');
                label.textContent = 'Available';
            }

            // Persist toggle state to database
            fetch(`/admin/ai-providers/${providerId}/settings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ is_active: isActive })
            });
        });
    });

    // Temperature Slider
    document.querySelectorAll('.temperature-slider').forEach(slider => {
        slider.addEventListener('input', function() {
            const tempValue = this.closest('details').querySelector('.temp-value');
            tempValue.textContent = this.value;
        });

        slider.addEventListener('change', function() {
            const providerId = this.dataset.providerId;
            const temperature = parseFloat(this.value);

            fetch(`/admin/ai-providers/${providerId}/settings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ temperature: temperature })
            });
        });
    });

    // Max Tokens
    document.querySelectorAll('.max-tokens-input').forEach(input => {
        input.addEventListener('change', function() {
            const providerId = this.dataset.providerId;
            const maxTokens = parseInt(this.value);

            fetch(`/admin/ai-providers/${providerId}/settings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ max_tokens: maxTokens })
            });
        });
    });

    // Model Selection
    document.querySelectorAll('.model-select').forEach(select => {
        select.addEventListener('change', function() {
            const providerId = this.dataset.providerId;
            const model = this.value;

            fetch(`/admin/ai-providers/${providerId}/settings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ default_model: model })
            });
        });
    });
});
</script>
@endpush
