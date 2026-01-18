@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.voting-types.index') }}" class="text-gray-600 hover:text-gray-800 mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Back to Voting Types
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Create Voting Type</h1>
        <p class="text-gray-600">Define a new point system for voting</p>
    </div>

    <form action="{{ route('admin.voting-types.store') }}" method="POST" class="space-y-6" id="voting-type-form">
        @csrf

        <!-- Basic Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">Voting Type Information</h2>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="e.g., Standard 3-2-1, Extended 5 Places"
                           class="form-input @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description"
                              name="description"
                              rows="2"
                              class="form-input @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
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
        </div>

        <!-- Point Configuration -->
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold">Point Configuration</h2>
                    <p class="text-sm text-gray-500">Define points for each place</p>
                </div>
                <button type="button"
                        onclick="addPlace()"
                        class="btn-outline btn-sm">
                    <i class="fas fa-plus mr-1"></i> Add Place
                </button>
            </div>
            <div class="card-body">
                <div id="places-container" class="space-y-3">
                    <!-- Places will be added here dynamically -->
                </div>

                @error('places')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Preview -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">Preview</h2>
            </div>
            <div class="card-body">
                <div id="preview-container" class="flex flex-wrap gap-2">
                    <span class="text-gray-500">Add places to see preview</span>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.voting-types.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save mr-2"></i> Create Voting Type
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let placeCount = 0;

function addPlace(place = null, points = null) {
    placeCount++;
    const currentPlace = place || placeCount;
    const currentPoints = points || (placeCount <= 5 ? 6 - placeCount : 1);

    const container = document.getElementById('places-container');
    const placeHtml = `
        <div class="flex items-center gap-4 place-row" data-place="${currentPlace}">
            <div class="flex-shrink-0 w-24">
                <span class="font-medium">${ordinal(currentPlace)} Place</span>
            </div>
            <div class="flex-1">
                <input type="hidden" name="places[${placeCount - 1}][place]" value="${currentPlace}">
                <div class="flex items-center">
                    <input type="number"
                           name="places[${placeCount - 1}][points]"
                           value="${currentPoints}"
                           min="1"
                           max="100"
                           class="form-input w-24"
                           onchange="updatePreview()"
                           required>
                    <span class="ml-2 text-gray-500">points</span>
                </div>
            </div>
            <button type="button" onclick="removePlace(this)" class="text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', placeHtml);
    updatePreview();
}

function removePlace(button) {
    const row = button.closest('.place-row');
    row.remove();
    renumberPlaces();
    updatePreview();
}

function renumberPlaces() {
    const rows = document.querySelectorAll('.place-row');
    placeCount = rows.length;

    rows.forEach((row, index) => {
        const place = index + 1;
        row.dataset.place = place;
        row.querySelector('span.font-medium').textContent = ordinal(place) + ' Place';
        row.querySelector('input[name*="[place]"]').name = `places[${index}][place]`;
        row.querySelector('input[name*="[place]"]').value = place;
        row.querySelector('input[name*="[points]"]').name = `places[${index}][points]`;
    });
}

function updatePreview() {
    const preview = document.getElementById('preview-container');
    const rows = document.querySelectorAll('.place-row');

    if (rows.length === 0) {
        preview.innerHTML = '<span class="text-gray-500">Add places to see preview</span>';
        return;
    }

    let html = '';
    rows.forEach((row, index) => {
        const points = row.querySelector('input[type="number"]').value;
        html += `<span class="px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm">
            ${ordinal(index + 1)}: ${points} pts
        </span>`;
    });

    preview.innerHTML = html;
}

function ordinal(n) {
    const s = ['th', 'st', 'nd', 'rd'];
    const v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
}

// Initialize with 3 default places
document.addEventListener('DOMContentLoaded', function() {
    @if(old('places'))
        @foreach(old('places') as $index => $place)
            addPlace({{ $place['place'] }}, {{ $place['points'] }});
        @endforeach
    @else
        addPlace(1, 3);
        addPlace(2, 2);
        addPlace(3, 1);
    @endif
});
</script>
@endpush
@endsection
