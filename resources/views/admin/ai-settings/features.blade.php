@extends('layouts.app')

@section('content')
<div>
    <div class="d-flex justify-between align-center mb-4">
        <h1 class="page-title" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">
            <i class="fas fa-toggle-on"></i> Features Configuration
        </h1>
        <button type="button" class="btn btn-success" onclick="saveFeatures()">
            <i class="fas fa-check"></i> Save Changes
        </button>
    </div>

    <form id="featuresForm">
        @csrf

        <!-- Core Features -->
        <div class="card mb-4">
            <div class="card-header" style="background: #2563eb; color: white;">
                <i class="fas fa-cogs"></i> Core Features
            </div>
            <div class="card-body">
                <div class="grid grid-2">
                    <div class="feature-toggle">
                        <div>
                            <h6><i class="fas fa-globe" style="color: #2563eb;"></i> Public Voting</h6>
                            <small style="color: #6b7280;">Allow anonymous users to vote without logging in</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="public_voting" {{ $features->public_voting ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="feature-toggle">
                        <div>
                            <h6><i class="fas fa-chart-line" style="color: #10b981;"></i> Live Results</h6>
                            <small style="color: #6b7280;">Show real-time voting results to users</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="live_results" {{ $features->live_results ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="feature-toggle">
                        <div>
                            <h6><i class="fas fa-gavel" style="color: #f59e0b;"></i> Judging Panel</h6>
                            <small style="color: #6b7280;">Enable dedicated judging panels for events</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="judging_panel" {{ $features->judging_panel ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="feature-toggle">
                        <div>
                            <h6><i class="fas fa-file-import" style="color: #8b5cf6;"></i> Import/Export</h6>
                            <small style="color: #6b7280;">Allow CSV/Excel import and export of data</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="import_export" {{ $features->import_export ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="feature-toggle">
                        <div>
                            <h6><i class="fas fa-file-pdf" style="color: #dc2626;"></i> PDF Reports</h6>
                            <small style="color: #6b7280;">Generate PDF ballots and reports</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="pdf_reports" {{ $features->pdf_reports ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="feature-toggle">
                        <div>
                            <h6><i class="fas fa-tags" style="color: #0d9488;"></i> Categories</h6>
                            <small style="color: #6b7280;">Enable award categories for events</small>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="categories" {{ $features->categories ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Chat Slider Configuration -->
        <div class="card mb-4">
            <div class="card-header" style="background: #1e40af; color: white;">
                <i class="fas fa-robot"></i> AI Chat Slider
            </div>
            <div class="card-body">
                <!-- Master Toggle -->
                <div class="master-toggle">
                    <div>
                        <h6><i class="fas fa-power-off" style="color: #10b981;"></i> Enable AI Chat Slider</h6>
                        <small style="color: #6b7280;">Master switch - Show/hide the floating AI chat panel on all pages</small>
                    </div>
                    <label class="switch switch-lg">
                        <input type="checkbox" name="ai_chat_enabled" id="aiChatMaster"
                               {{ $features->ai_chat_enabled ? 'checked' : '' }} onchange="toggleAiSection()">
                        <span class="slider"></span>
                    </label>
                </div>

                <div id="aiChatOptions" style="{{ !$features->ai_chat_enabled ? 'display:none;' : '' }}">
                    <hr style="margin: 20px 0;">

                    <!-- Page Visibility -->
                    <h6 style="margin-bottom: 15px; color: #374151;"><i class="fas fa-eye"></i> Page Visibility</h6>
                    <div class="grid grid-3 mb-4">
                        <div class="feature-toggle-sm">
                            <span>Voting Pages</span>
                            <label class="switch">
                                <input type="checkbox" name="ai_show_on_voting" {{ $features->ai_show_on_voting ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle-sm">
                            <span>Results Pages</span>
                            <label class="switch">
                                <input type="checkbox" name="ai_show_on_results" {{ $features->ai_show_on_results ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle-sm">
                            <span>Admin Pages</span>
                            <label class="switch">
                                <input type="checkbox" name="ai_show_on_admin" {{ $features->ai_show_on_admin ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle-sm">
                            <span>Dashboard</span>
                            <label class="switch">
                                <input type="checkbox" name="ai_show_on_dashboard" {{ $features->ai_show_on_dashboard ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle-sm">
                            <span>Landing Page</span>
                            <label class="switch">
                                <input type="checkbox" name="ai_show_on_landing" {{ $features->ai_show_on_landing ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle-sm">
                            <span>Mobile Devices</span>
                            <label class="switch">
                                <input type="checkbox" name="ai_show_on_mobile" {{ $features->ai_show_on_mobile ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- AI Capabilities -->
                    <h6 style="margin-bottom: 15px; color: #374151;"><i class="fas fa-magic"></i> AI Capabilities</h6>
                    <div class="grid grid-2 mb-4">
                        <div class="feature-toggle">
                            <div>
                                <h6><i class="fas fa-microphone" style="color: #dc2626;"></i> Voice Input</h6>
                                <small style="color: #6b7280;">Allow users to speak to the AI</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="ai_voice_input" {{ $features->ai_voice_input ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle">
                            <div>
                                <h6><i class="fas fa-volume-up" style="color: #2563eb;"></i> Voice Output</h6>
                                <small style="color: #6b7280;">AI speaks responses aloud</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="ai_voice_output" {{ $features->ai_voice_output ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle">
                            <div>
                                <h6><i class="fas fa-chart-bar" style="color: #10b981;"></i> Charts & Graphs</h6>
                                <small style="color: #6b7280;">AI can display visual charts</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="ai_charts" {{ $features->ai_charts ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle">
                            <div>
                                <h6><i class="fas fa-database" style="color: #f59e0b;"></i> Data Modification</h6>
                                <small style="color: #6b7280;">AI can create/update data (requires confirmation)</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="ai_data_modification" {{ $features->ai_data_modification ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Appearance -->
                    <h6 style="margin-bottom: 15px; color: #374151;"><i class="fas fa-palette"></i> Appearance</h6>
                    <div class="grid grid-3">
                        <div class="form-group">
                            <label class="form-label">Position</label>
                            <select class="form-control" name="ai_chat_position">
                                <option value="bottom-right" {{ $features->ai_chat_position === 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                                <option value="bottom-left" {{ $features->ai_chat_position === 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                                <option value="top-right" {{ $features->ai_chat_position === 'top-right' ? 'selected' : '' }}>Top Right</option>
                                <option value="top-left" {{ $features->ai_chat_position === 'top-left' ? 'selected' : '' }}>Top Left</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Button Color</label>
                            <input type="color" class="form-control" name="ai_button_color"
                                   value="{{ $features->ai_button_color ?? '#1e40af' }}" style="height: 42px;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Panel Width (px)</label>
                            <input type="number" class="form-control" name="ai_panel_width"
                                   value="{{ $features->ai_panel_width ?? 380 }}" min="300" max="600">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Configuration -->
        <div class="card mb-4">
            <div class="card-header" style="background: #10b981; color: white;">
                <i class="fas fa-bell"></i> Notification Configuration
            </div>
            <div class="card-body">
                <!-- Master Toggle -->
                <div class="master-toggle">
                    <div>
                        <h6><i class="fas fa-power-off" style="color: #10b981;"></i> Enable Notifications</h6>
                        <small style="color: #6b7280;">Master switch for all notification types</small>
                    </div>
                    <label class="switch switch-lg">
                        <input type="checkbox" name="notifications_enabled" id="notificationsMaster"
                               {{ $features->notifications_enabled ? 'checked' : '' }} onchange="toggleNotificationsSection()">
                        <span class="slider"></span>
                    </label>
                </div>

                <div id="notificationsOptions" style="{{ !$features->notifications_enabled ? 'display:none;' : '' }}">
                    <hr style="margin: 20px 0;">

                    <!-- Channels -->
                    <h6 style="margin-bottom: 15px; color: #374151;"><i class="fas fa-paper-plane"></i> Notification Channels</h6>
                    <div class="grid grid-3 mb-4">
                        <div class="channel-card {{ $features->notify_email ? 'active' : '' }}">
                            <label>
                                <input type="checkbox" name="notify_email" {{ $features->notify_email ? 'checked' : '' }}>
                                <i class="fas fa-envelope"></i>
                                <span>Email</span>
                            </label>
                        </div>
                        <div class="channel-card {{ $features->notify_sms ? 'active' : '' }}">
                            <label>
                                <input type="checkbox" name="notify_sms" {{ $features->notify_sms ? 'checked' : '' }}>
                                <i class="fas fa-sms"></i>
                                <span>SMS</span>
                            </label>
                        </div>
                        <div class="channel-card {{ $features->notify_push ? 'active' : '' }}">
                            <label>
                                <input type="checkbox" name="notify_push" {{ $features->notify_push ? 'checked' : '' }}>
                                <i class="fas fa-mobile-alt"></i>
                                <span>Push</span>
                            </label>
                        </div>
                    </div>

                    <!-- Notification Types -->
                    <h6 style="margin-bottom: 15px; color: #374151;"><i class="fas fa-list"></i> Notification Types</h6>
                    <div class="grid grid-2">
                        <div class="feature-toggle">
                            <div>
                                <h6><i class="fas fa-calendar-alt" style="color: #2563eb;"></i> Event Reminders</h6>
                                <small style="color: #6b7280;">Upcoming event notifications</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="notify_event_reminders" {{ $features->notify_event_reminders ?? true ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle">
                            <div>
                                <h6><i class="fas fa-vote-yea" style="color: #10b981;"></i> Voting Updates</h6>
                                <small style="color: #6b7280;">Vote confirmation and status</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="notify_voting_updates" {{ $features->notify_voting_updates ?? true ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle">
                            <div>
                                <h6><i class="fas fa-trophy" style="color: #f59e0b;"></i> Result Alerts</h6>
                                <small style="color: #6b7280;">Winners and final results</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="notify_result_alerts" {{ $features->notify_result_alerts ?? true ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle">
                            <div>
                                <h6><i class="fas fa-user-shield" style="color: #8b5cf6;"></i> Admin Alerts</h6>
                                <small style="color: #6b7280;">System and admin notifications</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="notify_admin_alerts" {{ $features->notify_admin_alerts ?? true ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle">
                            <div>
                                <h6><i class="fas fa-shield-alt" style="color: #dc2626;"></i> Security Alerts</h6>
                                <small style="color: #6b7280;">Login attempts and security events</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="notify_security_alerts" {{ $features->notify_security_alerts ?? true ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="feature-toggle">
                            <div>
                                <h6><i class="fas fa-bullhorn" style="color: #ec4899;"></i> Promotional</h6>
                                <small style="color: #6b7280;">Marketing and promotional content</small>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="notify_promotional" {{ $features->notify_promotional ?? false ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
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
        margin-bottom: 10px;
    }
    .feature-toggle h6 {
        margin: 0 0 5px 0;
        color: #1e3a8a;
    }

    .feature-toggle-sm {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        background: #f3f4f6;
        border-radius: 6px;
    }
    .feature-toggle-sm span {
        font-weight: 500;
        color: #374151;
    }

    .master-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-radius: 8px;
    }
    .master-toggle h6 {
        margin: 0 0 5px 0;
        color: #166534;
    }

    .channel-card {
        background: #f3f4f6;
        border: 2px solid transparent;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .channel-card:hover {
        border-color: #d1d5db;
    }
    .channel-card.active {
        background: #dbeafe;
        border-color: #2563eb;
    }
    .channel-card label {
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    .channel-card input[type="checkbox"] {
        display: none;
    }
    .channel-card i {
        font-size: 2rem;
        color: #6b7280;
    }
    .channel-card.active i {
        color: #2563eb;
    }
    .channel-card span {
        font-weight: 500;
    }

    /* Switch Toggle */
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }
    .switch.switch-lg {
        width: 60px;
        height: 32px;
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
    .switch.switch-lg .slider:before {
        height: 26px;
        width: 26px;
    }
    input:checked + .slider {
        background-color: #10b981;
    }
    input:checked + .slider:before {
        transform: translateX(24px);
    }
    .switch.switch-lg input:checked + .slider:before {
        transform: translateX(28px);
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleAiSection() {
        const isEnabled = document.getElementById('aiChatMaster').checked;
        document.getElementById('aiChatOptions').style.display = isEnabled ? '' : 'none';
    }

    function toggleNotificationsSection() {
        const isEnabled = document.getElementById('notificationsMaster').checked;
        document.getElementById('notificationsOptions').style.display = isEnabled ? '' : 'none';
    }

    // Toggle channel card active state
    document.querySelectorAll('.channel-card input[type="checkbox"]').forEach(input => {
        input.addEventListener('change', function() {
            this.closest('.channel-card').classList.toggle('active', this.checked);
        });
    });

    async function saveFeatures() {
        const form = document.getElementById('featuresForm');
        const formData = new FormData(form);
        const data = {};

        // Get all checkbox values
        const checkboxFields = [
            'public_voting', 'live_results', 'judging_panel', 'import_export', 'pdf_reports', 'categories',
            'ai_chat_enabled', 'ai_show_on_voting', 'ai_show_on_results', 'ai_show_on_admin',
            'ai_show_on_dashboard', 'ai_show_on_landing', 'ai_show_on_mobile',
            'ai_voice_input', 'ai_voice_output', 'ai_charts', 'ai_data_modification',
            'notifications_enabled', 'notify_email', 'notify_sms', 'notify_push',
            'notify_event_reminders', 'notify_voting_updates', 'notify_result_alerts',
            'notify_admin_alerts', 'notify_security_alerts', 'notify_promotional'
        ];

        checkboxFields.forEach(field => {
            const checkbox = form.querySelector(`[name="${field}"]`);
            data[field] = checkbox ? checkbox.checked : false;
        });

        // Get other fields
        data.ai_chat_position = form.querySelector('[name="ai_chat_position"]').value;
        data.ai_button_color = form.querySelector('[name="ai_button_color"]').value;
        data.ai_panel_width = parseInt(form.querySelector('[name="ai_panel_width"]').value);

        try {
            const res = await fetch('{{ route("admin.ai-settings.features.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            if (res.ok) {
                alert('Features saved successfully!');
            } else {
                alert('Failed to save features');
            }
        } catch (err) {
            alert('Error saving features');
        }
    }
</script>
@endpush
