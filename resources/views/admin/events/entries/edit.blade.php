@extends('layouts.app')

@section('content')
@php
    $entryLabel = $event->template->entry_label ?? 'Entries';
    $entryLabelSingular = \Illuminate\Support\Str::singular($entryLabel);
    $participantLabel = $event->template->participant_label ?? 'Participants';
    $participantLabelSingular = \Illuminate\Support\Str::singular($participantLabel);
@endphp

<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.events.entries.index', $event) }}" class="text-gray-600 hover:text-gray-800 mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Back to {{ $entryLabel }}
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Edit {{ $entryLabelSingular }}</h1>
        <p class="text-gray-600">{{ $entry->name }}</p>
    </div>

    <form action="{{ route('admin.events.entries.update', [$event, $entry]) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">{{ $entryLabelSingular }} Information</h2>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name', $entry->name) }}"
                               class="form-input @error('name') border-red-500 @enderror"
                               required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="number" class="form-label">Number</label>
                        <input type="text"
                               id="number"
                               name="number"
                               value="{{ old('number', $entry->number) }}"
                               placeholder="e.g., 001"
                               class="form-input @error('number') border-red-500 @enderror">
                        @error('number')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($event->hasModule('participants'))
                        <div>
                            <label for="participant_id" class="form-label">{{ $participantLabelSingular }}</label>
                            <select id="participant_id"
                                    name="participant_id"
                                    class="form-select @error('participant_id') border-red-500 @enderror">
                                <option value="">Select {{ $participantLabelSingular }}</option>
                                @foreach($participants as $participant)
                                    <option value="{{ $participant->id }}"
                                            {{ old('participant_id', $entry->participant_id) == $participant->id ? 'selected' : '' }}>
                                        {{ $participant->name }}
                                        @if($participant->number) (#{{ $participant->number }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('participant_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if($event->hasModule('divisions'))
                        <div>
                            <label for="division_id" class="form-label">Division</label>
                            <select id="division_id"
                                    name="division_id"
                                    class="form-select @error('division_id') border-red-500 @enderror">
                                <option value="">No Division</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}"
                                            {{ old('division_id', $entry->division_id) == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('division_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if($event->hasModule('categories'))
                        <div>
                            <label for="category_id" class="form-label">Category</label>
                            <select id="category_id"
                                    name="category_id"
                                    class="form-select @error('category_id') border-red-500 @enderror">
                                <option value="">Select Category</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}"
                                            {{ old('category_id', $entry->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                <div>
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description"
                              name="description"
                              rows="3"
                              class="form-input @error('description') border-red-500 @enderror">{{ old('description', $entry->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">Status</h2>
            </div>
            <div class="card-body">
                <label class="flex items-center">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           {{ old('is_active', $entry->is_active) ? 'checked' : '' }}
                           class="form-checkbox">
                    <span class="ml-2">Active</span>
                </label>
            </div>
        </div>

        <!-- Vote Statistics -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">Vote Statistics</h2>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-2xl font-bold text-primary-600">{{ $entry->votes->count() }}</p>
                        <p class="text-sm text-gray-500">Total Votes</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-2xl font-bold text-green-600">{{ $entry->total_points ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Total Points</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        @php
                            $firstPlaceVotes = $entry->votes->where('place', 1)->count();
                        @endphp
                        <p class="text-2xl font-bold text-secondary-600">{{ $firstPlaceVotes }}</p>
                        <p class="text-sm text-gray-500">1st Place Votes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-between">
            <button type="button"
                    onclick="if(confirm('Are you sure? This will also delete all votes for this {{ strtolower($entryLabelSingular) }}.')) { document.getElementById('delete-form').submit(); }"
                    class="btn-danger">
                <i class="fas fa-trash mr-2"></i> Delete
            </button>

            <div class="flex space-x-4">
                <a href="{{ route('admin.events.entries.index', $event) }}" class="btn-outline">Cancel</a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i> Update
                </button>
            </div>
        </div>
    </form>

    <form id="delete-form"
          action="{{ route('admin.events.entries.destroy', [$event, $entry]) }}"
          method="POST"
          class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
