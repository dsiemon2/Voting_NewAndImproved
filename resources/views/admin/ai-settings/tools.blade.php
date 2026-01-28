@extends('layouts.app')

@section('content')
<div>
    <h1 class="page-title">
        <i class="fas fa-tools"></i> AI Tools
    </h1>
    <p style="color: #6b7280; margin-bottom: 20px;">Configure which tools the AI assistant can use to help users.</p>

    <div class="grid grid-2">
        @foreach($tools as $tool)
        <div class="card tool-card">
            <div class="card-body">
                <div class="d-flex justify-between align-center">
                    <div class="d-flex align-center gap-2">
                        <div class="tool-icon" style="background: {{ $tool['enabled'] ? '#dbeafe' : '#f3f4f6' }};">
                            <i class="fas {{ $tool['icon'] }}" style="color: {{ $tool['enabled'] ? '#0d7a3e' : '#9ca3af' }};"></i>
                        </div>
                        <div>
                            <h5 style="margin: 0; color: #1a3a5c;">{{ $tool['name'] }}</h5>
                            <small style="color: #6b7280;">{{ $tool['description'] }}</small>
                        </div>
                    </div>
                    <label class="switch">
                        <input type="checkbox" {{ $tool['enabled'] ? 'checked' : '' }}
                               onchange="toggleTool('{{ $tool['id'] }}', this.checked)">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Tool Categories -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-layer-group"></i> Tool Categories
        </div>
        <div class="card-body">
            <div class="grid grid-3">
                <div class="category-card">
                    <div class="category-icon" style="background: #dbeafe; color: #0d7a3e;">
                        <i class="fas fa-search"></i>
                    </div>
                    <h6>Query Tools</h6>
                    <p style="color: #6b7280; font-size: 0.85rem;">Search and retrieve information from events, entries, and results</p>
                    <span class="badge badge-info">3 tools</span>
                </div>

                <div class="category-card">
                    <div class="category-icon" style="background: #d1fae5; color: #10b981;">
                        <i class="fas fa-edit"></i>
                    </div>
                    <h6>Action Tools</h6>
                    <p style="color: #6b7280; font-size: 0.85rem;">Create, update, and manage events and voting data</p>
                    <span class="badge badge-success">2 tools</span>
                </div>

                <div class="category-card">
                    <div class="category-icon" style="background: #fef3c7; color: #f59e0b;">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h6>Analysis Tools</h6>
                    <p style="color: #6b7280; font-size: 0.85rem;">Generate reports and analyze voting patterns</p>
                    <span class="badge badge-warning">2 tools</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Functions -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-code"></i> Available Functions
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Function</th>
                        <th>Description</th>
                        <th>Parameters</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>getEventDetails</code></td>
                        <td>Retrieve details about a specific event</td>
                        <td><code>event_id</code></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <tr>
                        <td><code>searchEntries</code></td>
                        <td>Search entries by name, participant, or division</td>
                        <td><code>query, event_id, division_id</code></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <tr>
                        <td><code>getVotingResults</code></td>
                        <td>Get voting results for an event or division</td>
                        <td><code>event_id, division_id, limit</code></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <tr>
                        <td><code>getParticipantInfo</code></td>
                        <td>Get participant details and their entries</td>
                        <td><code>participant_id</code></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <tr>
                        <td><code>createEvent</code></td>
                        <td>Create a new voting event</td>
                        <td><code>name, template_id, date, ...</code></td>
                        <td><span class="badge badge-danger">Disabled</span></td>
                    </tr>
                    <tr>
                        <td><code>generateReport</code></td>
                        <td>Generate a voting report</td>
                        <td><code>event_id, format, options</code></td>
                        <td><span class="badge badge-danger">Disabled</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .tool-card {
        transition: all 0.2s;
    }
    .tool-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .tool-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .category-card {
        text-align: center;
        padding: 20px;
        background: #f9fafb;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .category-card:hover {
        background: #f3f4f6;
    }
    .category-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 15px;
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

    code {
        background: #f3f4f6;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.85rem;
        color: #dc2626;
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleTool(toolId, enabled) {
        // In a real implementation, this would save to the database
        console.log(`Tool ${toolId} ${enabled ? 'enabled' : 'disabled'}`);
    }
</script>
@endpush
