@extends('layouts.app')

@section('content')
<style>
    .import-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .page-title {
        font-size: 24px;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-title i {
        color: #1e3a8a;
    }

    .breadcrumb {
        color: #64748b;
        font-size: 14px;
    }

    .breadcrumb a {
        color: #1e3a8a;
        text-decoration: none;
    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    .import-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .import-card-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        color: white;
        padding: 20px 25px;
    }

    .import-card-header h2 {
        margin: 0;
        font-size: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .import-card-body {
        padding: 30px;
    }

    .import-type-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .import-type-tab {
        padding: 12px 20px;
        background: #f1f5f9;
        border: 2px solid transparent;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 600;
        color: #475569;
    }

    .import-type-tab:hover {
        background: #e2e8f0;
    }

    .import-type-tab.active {
        background: #1e3a8a;
        color: white;
        border-color: #1e3a8a;
    }

    .import-type-tab i {
        margin-right: 8px;
    }

    .drop-zone {
        border: 3px dashed #cbd5e1;
        border-radius: 10px;
        padding: 50px 30px;
        text-align: center;
        background: #f8fafc;
        transition: all 0.2s;
        cursor: pointer;
    }

    .drop-zone:hover {
        border-color: #1e3a8a;
        background: #f1f5f9;
    }

    .drop-zone.dragover {
        border-color: #ff6600;
        background: #fff7ed;
    }

    .drop-zone i {
        font-size: 48px;
        color: #94a3b8;
        margin-bottom: 15px;
    }

    .drop-zone h3 {
        color: #1e293b;
        margin-bottom: 10px;
    }

    .drop-zone p {
        color: #64748b;
        margin-bottom: 20px;
    }

    .file-input-wrapper {
        position: relative;
        display: inline-block;
    }

    .file-input-wrapper input[type="file"] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .btn-browse {
        background: #1e3a8a;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-browse:hover {
        background: #1e40af;
    }

    .selected-file {
        display: none;
        margin-top: 20px;
        padding: 15px 20px;
        background: #d1fae5;
        border-radius: 8px;
        color: #10b981;
    }

    .selected-file.show {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .selected-file i {
        margin-right: 10px;
    }

    .btn-remove-file {
        background: none;
        border: none;
        color: #dc2626;
        cursor: pointer;
        font-size: 18px;
    }

    .template-info {
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: 8px;
        padding: 20px;
        margin-top: 25px;
    }

    .template-info h4 {
        color: #15803d;
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .template-info p {
        color: #166534;
        margin: 0 0 15px 0;
        line-height: 1.6;
    }

    .template-info .format-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .template-info .format-table th,
    .template-info .format-table td {
        border: 1px solid #86efac;
        padding: 10px;
        text-align: left;
    }

    .template-info .format-table th {
        background: #dcfce7;
        font-weight: 600;
    }

    .btn-download-template {
        background: #15803d;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        margin-top: 15px;
    }

    .btn-download-template:hover {
        background: #166534;
        color: white;
    }

    .submit-section {
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-import {
        background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
        color: white;
        border: none;
        padding: 15px 40px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-import:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 102, 0, 0.3);
    }

    .btn-import:disabled {
        background: #9ca3af;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-cancel {
        background: #f1f5f9;
        color: #475569;
        border: 2px solid #e2e8f0;
        padding: 13px 30px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #d1fae5;
        color: #10b981;
        border: 1px solid #86efac;
    }

    .alert-error {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fca5a5;
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }
</style>

<div class="import-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-file-import"></i>
                Import Data
            </h1>
            <div class="breadcrumb">
                <a href="{{ route('admin.events.index') }}">Events</a> /
                <a href="{{ route('admin.events.show', $event) }}">{{ $event->name }}</a> /
                Import
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> {{ session('info') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <!-- Import Form Card -->
    <div class="import-card">
        <div class="import-card-header">
            <h2>
                <i class="fas fa-upload"></i>
                Import {{ $event->template->participant_label ?? 'Participants' }} & {{ $event->template->entry_label ?? 'Entries' }}
            </h2>
        </div>
        <div class="import-card-body">
            <form action="{{ route('admin.events.import.process', $event) }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf

                <!-- Import Type Tabs -->
                <div class="import-type-tabs">
                    <label class="import-type-tab active" data-type="combined">
                        <input type="radio" name="type" value="combined" checked hidden>
                        <i class="fas fa-layer-group"></i>
                        All Data (Recommended)
                    </label>
                    <label class="import-type-tab" data-type="participants">
                        <input type="radio" name="type" value="participants" hidden>
                        <i class="fas fa-users"></i>
                        {{ $event->template->participant_label ?? 'Participants' }} Only
                    </label>
                    <label class="import-type-tab" data-type="entries">
                        <input type="radio" name="type" value="entries" hidden>
                        <i class="fas fa-list-alt"></i>
                        {{ $event->template->entry_label ?? 'Entries' }} Only
                    </label>
                    <label class="import-type-tab" data-type="divisions">
                        <input type="radio" name="type" value="divisions" hidden>
                        <i class="fas fa-th-large"></i>
                        Divisions Only
                    </label>
                </div>

                <!-- File Drop Zone -->
                <div class="drop-zone" id="dropZone">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <h3>Drag & Drop your file here</h3>
                    <p>Supports Excel (.xlsx, .xls) and CSV files</p>
                    <div class="file-input-wrapper">
                        <button type="button" class="btn-browse">Browse Files</button>
                        <input type="file" name="file" id="fileInput" accept=".csv,.xlsx,.xls" required>
                    </div>
                </div>

                <!-- Selected File Display -->
                <div class="selected-file" id="selectedFile">
                    <span>
                        <i class="fas fa-file-excel"></i>
                        <span id="fileName"></span>
                    </span>
                    <button type="button" class="btn-remove-file" id="removeFile">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Template Format Info -->
                <div class="template-info">
                    <h4><i class="fas fa-info-circle"></i> Expected File Format for {{ $event->template->name ?? 'Food Competition' }}</h4>
                    <p>Your Excel file should have the following columns:</p>
                    <table class="format-table">
                        <thead>
                            <tr>
                                <th>Column A</th>
                                <th>Column B</th>
                                <th>Column C, D, E...</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Division (e.g., P1, A1)</td>
                                <td>{{ $event->template->participant_label ?? 'Chef' }} Name</td>
                                <td>{{ $event->template->entry_label ?? 'Entry' }} Names</td>
                            </tr>
                        </tbody>
                    </table>
                    <p style="margin-top: 15px;">
                        <strong>Division Prefixes:</strong>
                        @if($event->template->division_types)
                            @foreach($event->template->division_types as $type)
                                <span style="background: #dcfce7; padding: 2px 8px; border-radius: 4px; margin-right: 5px;">
                                    {{ $type['code'] }} = {{ $type['name'] }}
                                </span>
                            @endforeach
                        @else
                            <span style="background: #dcfce7; padding: 2px 8px; border-radius: 4px; margin-right: 5px;">P = Professional</span>
                            <span style="background: #dcfce7; padding: 2px 8px; border-radius: 4px;">A = Amateur</span>
                        @endif
                    </p>
                    <a href="#" class="btn-download-template">
                        <i class="fas fa-download"></i>
                        Download Sample Template
                    </a>
                </div>

                <!-- Submit Section -->
                <div class="submit-section">
                    <a href="{{ route('admin.events.show', $event) }}" class="btn-cancel">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn-import" id="importBtn" disabled>
                        <i class="fas fa-file-import"></i>
                        Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const selectedFile = document.getElementById('selectedFile');
    const fileName = document.getElementById('fileName');
    const removeFile = document.getElementById('removeFile');
    const importBtn = document.getElementById('importBtn');
    const tabs = document.querySelectorAll('.import-type-tab');

    // Tab switching
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            updateFileDisplay();
        }
    });

    // File input change
    fileInput.addEventListener('change', updateFileDisplay);

    // Remove file
    removeFile.addEventListener('click', function() {
        fileInput.value = '';
        selectedFile.classList.remove('show');
        dropZone.style.display = 'block';
        importBtn.disabled = true;
    });

    function updateFileDisplay() {
        if (fileInput.files.length) {
            fileName.textContent = fileInput.files[0].name;
            selectedFile.classList.add('show');
            dropZone.style.display = 'none';
            importBtn.disabled = false;
        }
    }
});
</script>
@endsection
