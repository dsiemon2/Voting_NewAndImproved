@extends('layouts.app')

@section('content')
<style>
    .export-container {
        max-width: 900px;
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
        color: #dc2626;
    }

    .breadcrumb {
        color: #64748b;
        font-size: 14px;
    }

    .breadcrumb a {
        color: #1a3a5c;
        text-decoration: none;
    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    .export-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .export-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .export-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .export-card-icon {
        padding: 40px;
        text-align: center;
        color: white;
    }

    .export-card-icon.results {
        background: linear-gradient(135deg, #1a3a5c 0%, #0d7a3e 100%);
    }

    .export-card-icon.ballots {
        background: linear-gradient(135deg, #f39c12 0%, #ff8533 100%);
    }

    .export-card-icon i {
        font-size: 64px;
        margin-bottom: 15px;
    }

    .export-card-icon h3 {
        margin: 0;
        font-size: 22px;
    }

    .export-card-body {
        padding: 25px;
    }

    .export-card-body p {
        color: #64748b;
        margin-bottom: 20px;
        line-height: 1.6;
    }

    .export-options {
        margin-bottom: 20px;
    }

    .export-option {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .export-option:hover {
        background: #f1f5f9;
    }

    .export-option input[type="radio"],
    .export-option input[type="checkbox"] {
        margin-right: 12px;
        width: 18px;
        height: 18px;
        accent-color: #1a3a5c;
    }

    .export-option .option-label {
        font-weight: 600;
        color: #1e293b;
    }

    .export-option .option-desc {
        font-size: 12px;
        color: #64748b;
        margin-left: 30px;
    }

    .btn-export {
        width: 100%;
        padding: 15px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.2s;
    }

    .btn-export.results {
        background: linear-gradient(135deg, #1a3a5c 0%, #0d7a3e 100%);
        color: white;
    }

    .btn-export.results:hover {
        box-shadow: 0 6px 20px rgba(30, 58, 138, 0.3);
        transform: translateY(-2px);
    }

    .btn-export.ballots {
        background: linear-gradient(135deg, #f39c12 0%, #ff8533 100%);
        color: white;
    }

    .btn-export.ballots:hover {
        box-shadow: 0 6px 20px rgba(255, 102, 0, 0.3);
        transform: translateY(-2px);
    }

    .preview-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .preview-header {
        background: #2c3e50;
        color: white;
        padding: 20px 25px;
    }

    .preview-header h3 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .preview-body {
        padding: 25px;
    }

    .preview-table {
        width: 100%;
        border-collapse: collapse;
    }

    .preview-table th {
        background: #1a3a5c;
        color: white;
        padding: 12px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
    }

    .preview-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
    }

    .preview-table tr:hover {
        background: #f8fafc;
    }

    .division-badge {
        display: inline-block;
        background: #e2e8f0;
        color: #475569;
        padding: 3px 10px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 12px;
    }

    .no-data {
        text-align: center;
        padding: 40px;
        color: #94a3b8;
    }

    .no-data i {
        font-size: 48px;
        margin-bottom: 15px;
        display: block;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        text-decoration: none;
        margin-top: 25px;
        font-weight: 500;
    }

    .back-link:hover {
        color: #1a3a5c;
    }

    @media print {
        .export-grid,
        .back-link,
        .page-header {
            display: none;
        }
        .preview-section {
            box-shadow: none;
        }
    }
</style>

<div class="export-container">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-file-pdf"></i>
                Export & Print
            </h1>
            <div class="breadcrumb">
                <a href="{{ route('admin.events.index') }}">Events</a> /
                <a href="{{ route('admin.events.show', $event) }}">{{ $event->name }}</a> /
                Export
            </div>
        </div>
    </div>

    <!-- Export Options Grid -->
    <div class="export-grid">
        <!-- Results PDF Card -->
        <div class="export-card">
            <div class="export-card-icon results">
                <i class="fas fa-trophy"></i>
                <h3>Results PDF</h3>
            </div>
            <div class="export-card-body">
                <p>Export voting results as a professionally formatted PDF document with rankings and point totals.</p>

                <form action="{{ route('admin.events.pdf.results', $event) }}" method="GET" target="_blank">
                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                    <input type="hidden" name="type" value="results">

                    <div class="export-options">
                        <label class="export-option">
                            <input type="radio" name="results_format" value="all" checked>
                            <div>
                                <div class="option-label">All Division Types</div>
                                <div class="option-desc">Side-by-side results for all categories</div>
                            </div>
                        </label>
                        @foreach($event->template->getDivisionTypes() as $type)
                        <label class="export-option">
                            <input type="radio" name="results_format" value="{{ $type['code'] }}">
                            <div>
                                <div class="option-label">{{ $type['name'] }} Only</div>
                                <div class="option-desc">Results for {{ $type['name'] }} divisions</div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <button type="submit" class="btn-export results">
                        <i class="fas fa-download"></i>
                        Download Results PDF
                    </button>
                </form>
            </div>
        </div>

        <!-- Voting Ballots Card -->
        <div class="export-card">
            <div class="export-card-icon ballots">
                <i class="fas fa-vote-yea"></i>
                <h3>Voting Ballots</h3>
            </div>
            <div class="export-card-body">
                <p>Generate printable voting ballots for judges and participants with entry numbers and spaces for rankings.</p>

                <form action="{{ route('admin.events.pdf.ballot', $event) }}" method="GET" target="_blank">
                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                    <input type="hidden" name="type" value="ballots">

                    <div class="export-options">
                        <label class="export-option">
                            <input type="radio" name="ballot_format" value="individual" checked>
                            <div>
                                <div class="option-label">Individual Ballots</div>
                                <div class="option-desc">One ballot per page per division</div>
                            </div>
                        </label>
                        <label class="export-option">
                            <input type="radio" name="ballot_format" value="combined">
                            <div>
                                <div class="option-label">Combined Ballot</div>
                                <div class="option-desc">All divisions on one ballot</div>
                            </div>
                        </label>
                        <label class="export-option">
                            <input type="checkbox" name="include_entries" value="1" checked>
                            <div>
                                <div class="option-label">Include Entry List</div>
                                <div class="option-desc">Show all entries with numbers for reference</div>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="btn-export ballots">
                        <i class="fas fa-print"></i>
                        Generate Ballots
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Section -->
    <div class="preview-section">
        <div class="preview-header">
            <h3>
                <i class="fas fa-eye"></i>
                Current {{ $event->template->entry_label ?? 'Entries' }} Preview
            </h3>
        </div>
        <div class="preview-body">
            @if($event->entries->count() > 0)
                <table class="preview-table">
                    <thead>
                        <tr>
                            <th>Entry #</th>
                            <th>{{ $event->template->entry_label ?? 'Entry' }} Name</th>
                            <th>{{ $event->template->participant_label ?? 'Participant' }}</th>
                            <th>Division</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($event->entries->take(10) as $entry)
                            <tr>
                                <td>
                                    <strong>{{ $entry->entry_number ?? $entry->id }}</strong>
                                </td>
                                <td>{{ $entry->name }}</td>
                                <td>{{ $entry->participant->name ?? '-' }}</td>
                                <td>
                                    <span class="division-badge">{{ $entry->division->code ?? '-' }}</span>
                                </td>
                            </tr>
                        @endforeach
                        @if($event->entries->count() > 10)
                            <tr>
                                <td colspan="4" style="text-align: center; color: #94a3b8;">
                                    ... and {{ $event->entries->count() - 10 }} more entries
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            @else
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>No entries found for this event.</p>
                    <p>
                        <a href="{{ route('admin.events.entries.index', $event) }}" style="color: #1a3a5c;">
                            Add {{ $event->template->entry_label ?? 'Entries' }}
                        </a>
                        or
                        <a href="{{ route('admin.events.import', $event) }}" style="color: #1a3a5c;">
                            Import from file
                        </a>
                    </p>
                </div>
            @endif
        </div>
    </div>

    <a href="{{ route('admin.events.show', $event) }}" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Back to Event
    </a>
</div>
@endsection
