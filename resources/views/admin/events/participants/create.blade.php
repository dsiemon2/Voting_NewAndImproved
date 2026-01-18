@extends('layouts.app')

@section('content')
@php
    $participantLabel = $event->template->participant_label ?? 'Participants';
    $participantLabelSingular = \Illuminate\Support\Str::singular($participantLabel);
@endphp

<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.events.participants.index', $event) }}" class="text-gray-600 hover:text-gray-800 mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Back to {{ $participantLabel }}
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Add {{ $participantLabelSingular }}</h1>
        <p class="text-gray-600">{{ $event->name }}</p>
    </div>

    <form action="{{ route('admin.events.participants.store', $event) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Basic Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">{{ $participantLabelSingular }} Information</h2>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               class="form-input @error('name') border-red-500 @enderror"
                               required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="number" class="form-label">Number/ID</label>
                        <input type="text"
                               id="number"
                               name="number"
                               value="{{ old('number') }}"
                               placeholder="e.g., 001"
                               class="form-input @error('number') border-red-500 @enderror">
                        @error('number')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($event->hasModule('divisions'))
                        <div>
                            <label for="division_id" class="form-label">Division</label>
                            <select id="division_id"
                                    name="division_id"
                                    class="form-select @error('division_id') border-red-500 @enderror">
                                <option value="">No Division</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('division_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                <div>
                    <label for="organization" class="form-label">Organization</label>
                    <input type="text"
                           id="organization"
                           name="organization"
                           value="{{ old('organization') }}"
                           class="form-input @error('organization') border-red-500 @enderror">
                    @error('organization')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">Contact Information</h2>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               class="form-input @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel"
                               id="phone"
                               name="phone"
                               value="{{ old('phone') }}"
                               class="form-input @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
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
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="form-checkbox">
                    <span class="ml-2">Active</span>
                </label>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.events.participants.index', $event) }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save mr-2"></i> Create {{ $participantLabelSingular }}
            </button>
        </div>
    </form>
</div>
@endsection
