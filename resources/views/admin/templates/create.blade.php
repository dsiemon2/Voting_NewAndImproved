@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.templates.index') }}" class="text-gray-600 hover:text-gray-800 mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Back to Templates
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Create Event Template</h1>
        <p class="text-gray-600">Define a new event type with custom modules and labels</p>
    </div>

    <form action="{{ route('admin.templates.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Basic Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">Template Information</h2>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="name" class="form-label">Template Name <span class="text-red-500">*</span></label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="e.g., Food Competition, Photo Contest"
                               class="form-input @error('name') border-red-500 @enderror"
                               required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="icon" class="form-label">Icon</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md">
                                <i id="icon-preview" class="fas fa-calendar text-gray-500"></i>
                            </span>
                            <input type="text"
                                   id="icon"
                                   name="icon"
                                   value="{{ old('icon', 'fa-calendar') }}"
                                   placeholder="fa-calendar"
                                   class="form-input rounded-l-none @error('icon') border-red-500 @enderror"
                                   onkeyup="updateIconPreview(this.value)">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Font Awesome class (e.g., fa-utensils, fa-camera, fa-trophy)</p>
                        @error('icon')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="form-label">Status</label>
                        <label class="flex items-center mt-2">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="form-checkbox">
                            <span class="ml-2">Active</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description"
                              name="description"
                              rows="3"
                              class="form-input @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Custom Labels -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">Custom Labels</h2>
                <p class="text-sm text-gray-500">Customize terminology used in this event type</p>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="participant_label" class="form-label">Participant Label</label>
                        <input type="text"
                               id="participant_label"
                               name="participant_label"
                               value="{{ old('participant_label', 'Participant') }}"
                               placeholder="e.g., Chef, Photographer, Contestant"
                               class="form-input @error('participant_label') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">What to call participants (Chef, Photographer, etc.)</p>
                        @error('participant_label')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="entry_label" class="form-label">Entry Label</label>
                        <input type="text"
                               id="entry_label"
                               name="entry_label"
                               value="{{ old('entry_label', 'Entry') }}"
                               placeholder="e.g., Dish, Photo, Submission"
                               class="form-input @error('entry_label') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">What to call entries (Dish, Photo, Submission, etc.)</p>
                        @error('entry_label')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Default Modules -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">Default Modules</h2>
                <p class="text-sm text-gray-500">Select which modules are enabled by default for this template</p>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($modules as $module)
                        <label class="flex items-start p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox"
                                   name="modules[]"
                                   value="{{ $module->id }}"
                                   {{ in_array($module->id, old('modules', [])) ? 'checked' : '' }}
                                   {{ $module->is_core ? 'checked disabled' : '' }}
                                   class="form-checkbox mt-1">
                            <div class="ml-3">
                                <span class="font-medium block">
                                    <i class="fas {{ $module->icon ?? 'fa-cube' }} mr-1 text-gray-400"></i>
                                    {{ $module->name }}
                                </span>
                                @if($module->description)
                                    <span class="text-xs text-gray-500">{{ $module->description }}</span>
                                @endif
                                @if($module->is_core)
                                    <span class="text-xs text-gray-400 block">(Required)</span>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.templates.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save mr-2"></i> Create Template
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function updateIconPreview(iconClass) {
    const preview = document.getElementById('icon-preview');
    preview.className = 'fas ' + iconClass + ' text-gray-500';
}
</script>
@endpush
@endsection
