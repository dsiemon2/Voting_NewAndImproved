@extends('layouts.app')

@section('content')
<div>
    <div class="d-flex justify-between align-center mb-4">
        <h1 class="page-title" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">
            <i class="fas fa-cog"></i> System Settings
        </h1>
        <button type="button" class="btn btn-success" onclick="saveSettings()">
            <i class="fas fa-check"></i> Save Changes
        </button>
    </div>

    <form id="settingsForm">
        @csrf

        <div class="grid grid-2">
            <!-- Organization Settings -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-building"></i> Organization
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Organization Name</label>
                        <input type="text" class="form-control" name="organization_name"
                               value="{{ $settings->organization_name }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contact Email</label>
                        <input type="email" class="form-control" name="organization_email"
                               value="{{ $settings->organization_email }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" name="organization_phone"
                               value="{{ $settings->organization_phone }}" placeholder="+1 (555) 123-4567">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="organization_address" rows="2">{{ $settings->organization_address }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Regional Settings -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-globe-americas"></i> Regional Settings
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Timezone</label>
                        <select class="form-control" name="timezone">
                            <option value="America/New_York" {{ $settings->timezone === 'America/New_York' ? 'selected' : '' }}>Eastern Time (US & Canada)</option>
                            <option value="America/Chicago" {{ $settings->timezone === 'America/Chicago' ? 'selected' : '' }}>Central Time (US & Canada)</option>
                            <option value="America/Denver" {{ $settings->timezone === 'America/Denver' ? 'selected' : '' }}>Mountain Time (US & Canada)</option>
                            <option value="America/Los_Angeles" {{ $settings->timezone === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (US & Canada)</option>
                            <option value="UTC" {{ $settings->timezone === 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="Europe/London" {{ $settings->timezone === 'Europe/London' ? 'selected' : '' }}>London</option>
                            <option value="Europe/Paris" {{ $settings->timezone === 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                            <option value="Asia/Tokyo" {{ $settings->timezone === 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date Format</label>
                        <select class="form-control" name="date_format">
                            <option value="M d, Y" {{ $settings->date_format === 'M d, Y' ? 'selected' : '' }}>Jan 15, 2026</option>
                            <option value="d/m/Y" {{ $settings->date_format === 'd/m/Y' ? 'selected' : '' }}>15/01/2026</option>
                            <option value="m/d/Y" {{ $settings->date_format === 'm/d/Y' ? 'selected' : '' }}>01/15/2026</option>
                            <option value="Y-m-d" {{ $settings->date_format === 'Y-m-d' ? 'selected' : '' }}>2026-01-15</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Time Format</label>
                        <select class="form-control" name="time_format">
                            <option value="h:i A" {{ $settings->time_format === 'h:i A' ? 'selected' : '' }}>12-hour (3:30 PM)</option>
                            <option value="H:i" {{ $settings->time_format === 'H:i' ? 'selected' : '' }}>24-hour (15:30)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branding -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-palette"></i> Branding
            </div>
            <div class="card-body">
                <div class="grid grid-2">
                    <div>
                        <div class="form-group">
                            <label class="form-label">Primary Color</label>
                            <div class="d-flex align-center gap-2">
                                <input type="color" class="form-control" name="primary_color"
                                       value="{{ $settings->primary_color ?? '#1e40af' }}" style="width: 60px; height: 42px;">
                                <input type="text" class="form-control" value="{{ $settings->primary_color ?? '#1e40af' }}"
                                       style="flex: 1;" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Accent Color</label>
                            <div class="d-flex align-center gap-2">
                                <input type="color" class="form-control" name="accent_color"
                                       value="{{ $settings->accent_color ?? '#ff6600' }}" style="width: 60px; height: 42px;">
                                <input type="text" class="form-control" value="{{ $settings->accent_color ?? '#ff6600' }}"
                                       style="flex: 1;" readonly>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="form-group">
                            <label class="form-label">Logo</label>
                            <div class="upload-zone" style="padding: 20px;">
                                <i class="fas fa-image" style="font-size: 2rem; color: #9ca3af;"></i>
                                <p style="margin: 10px 0 0; color: #6b7280;">Click to upload logo</p>
                                <small style="color: #9ca3af;">PNG, JPG, SVG (max 2MB)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Voting Defaults -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-vote-yea"></i> Voting Defaults
            </div>
            <div class="card-body">
                <div class="grid grid-3">
                    <div class="form-group">
                        <label class="form-label">Default Voting Type</label>
                        <select class="form-control" name="default_voting_type">
                            <option value="1">Standard Ranked (3-2-1)</option>
                            <option value="2">Extended Ranked (5-4-3-2-1)</option>
                            <option value="3">Top-Heavy (5-3-1)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Max Votes Per User</label>
                        <input type="number" class="form-control" name="max_votes_per_user"
                               value="{{ $settings->max_votes_per_user ?? 1 }}" min="1" max="10">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Allow Vote Changes</label>
                        <select class="form-control" name="allow_vote_changes">
                            <option value="0" {{ !$settings->allow_vote_changes ? 'selected' : '' }}>No</option>
                            <option value="1" {{ $settings->allow_vote_changes ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-shield-alt"></i> Security
            </div>
            <div class="card-body">
                <div class="grid grid-2">
                    <div class="feature-toggle">
                        <div>
                            <h6><i class="fas fa-envelope-open-text" style="color: #2563eb;"></i> Require Email Verification</h6>
                            <small style="color: #6b7280;">Users must verify email before voting</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="require_email_verification"
                                   {{ $settings->require_email_verification ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="feature-toggle">
                        <div>
                            <h6><i class="fas fa-tools" style="color: #f59e0b;"></i> Maintenance Mode</h6>
                            <small style="color: #6b7280;">Temporarily disable public access</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="maintenance_mode"
                                   {{ $settings->maintenance_mode ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label class="form-label">Maintenance Message</label>
                    <textarea class="form-control" name="maintenance_message" rows="2"
                              placeholder="We're currently performing maintenance. Please check back soon.">{{ $settings->maintenance_message }}</textarea>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .feature-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background: #f9fafb;
        border-radius: 8px;
    }
    .feature-toggle h6 {
        margin: 0 0 5px 0;
        color: #1e3a8a;
    }

    .upload-zone {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .upload-zone:hover {
        border-color: #2563eb;
        background: #f0f9ff;
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
</style>
@endpush

@push('scripts')
<script>
    // Sync color inputs with text display
    document.querySelectorAll('input[type="color"]').forEach(input => {
        input.addEventListener('input', function() {
            this.nextElementSibling.value = this.value;
        });
    });

    async function saveSettings() {
        const form = document.getElementById('settingsForm');
        const formData = new FormData(form);
        const data = {};

        formData.forEach((value, key) => {
            if (key === '_token') return;
            data[key] = value;
        });

        // Handle checkboxes
        data.require_email_verification = form.querySelector('[name="require_email_verification"]').checked;
        data.maintenance_mode = form.querySelector('[name="maintenance_mode"]').checked;
        data.allow_vote_changes = form.querySelector('[name="allow_vote_changes"]').value === '1';

        try {
            const res = await fetch('{{ route("admin.ai-settings.settings.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            if (res.ok) {
                alert('Settings saved successfully!');
            } else {
                alert('Failed to save settings');
            }
        } catch (err) {
            alert('Error saving settings');
        }
    }
</script>
@endpush
