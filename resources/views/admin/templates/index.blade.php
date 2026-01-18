@extends('layouts.app')

@section('content')
<div>
    <!-- Header -->
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <h1 class="page-title"><i class="fas fa-file-alt"></i> Event Templates</h1>
        </div>
        <a href="{{ route('admin.templates.create') }}" class="btn btn-warning">
            <i class="fas fa-plus"></i> New Template
        </a>
    </div>

    <!-- Templates Grid -->
    @if($templates->count())
        <div class="template-grid">
            @foreach($templates as $template)
                <div class="template-card">
                    <div class="template-card-header">
                        <div class="template-card-icon">
                            <i class="fas {{ $template->icon ?? 'fa-calendar' }}"></i>
                        </div>
                        <div>
                            <div class="template-card-title">{{ $template->name }}</div>
                            @if($template->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <div class="template-card-body">
                        @if($template->description)
                            <p style="color: #6b7280; font-size: 14px; margin-bottom: 15px;">
                                {{ \Illuminate\Support\Str::limit($template->description, 100) }}
                            </p>
                        @endif

                        <div style="margin-bottom: 15px;">
                            <strong style="font-size: 12px; color: #374151;">Labels:</strong>
                            <div style="margin-top: 5px;">
                                <span class="label-tag">
                                    <i class="fas fa-user"></i> {{ $template->participant_label ?? 'Participant' }}
                                </span>
                                <span class="label-tag">
                                    <i class="fas fa-clipboard"></i> {{ $template->entry_label ?? 'Entry' }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <strong style="font-size: 12px; color: #374151;">Modules:</strong>
                            <div style="margin-top: 5px;">
                                @forelse($template->modules as $module)
                                    <span class="module-badge">
                                        <i class="fas {{ $module->icon ?? 'fa-check' }}"></i>
                                        {{ $module->name }}
                                    </span>
                                @empty
                                    <span style="color: #9ca3af; font-size: 12px;">No modules</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="template-card-footer">
                        <span style="color: #6b7280; font-size: 13px;">
                            <i class="fas fa-calendar-alt"></i> {{ $template->events_count ?? $template->events->count() }} events
                        </span>
                        <div class="action-buttons">
                            <a href="{{ route('admin.templates.edit', $template) }}"
                               class="action-btn action-btn-edit"
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if(($template->events_count ?? $template->events->count()) === 0)
                                <form action="{{ route('admin.templates.destroy', $template) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('Are you sure you want to delete this template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="action-btn action-btn-delete"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="text-center" style="padding: 60px 20px; color: #6b7280;">
                <i class="fas fa-file-alt" style="font-size: 64px; margin-bottom: 20px; display: block; color: #9ca3af;"></i>
                <h3 style="margin-bottom: 10px; color: #374151;">No Templates Yet</h3>
                <p style="margin-bottom: 20px;">Create your first event template to get started</p>
                <a href="{{ route('admin.templates.create') }}" class="btn btn-warning">
                    <i class="fas fa-plus"></i> Create Template
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
