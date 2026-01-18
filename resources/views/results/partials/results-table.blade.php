@if($results->isEmpty())
    <p class="text-gray-500 text-center py-4">No votes recorded yet.</p>
@else
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">#</th>
                    <th>{{ $entryLabel ?? 'Entry' }}</th>
                    <th>{{ $participantLabel ?? 'Participant' }}</th>
                    @if($showVoteCounts ?? true)
                        <th class="text-center w-16">1st</th>
                        <th class="text-center w-16">2nd</th>
                        <th class="text-center w-16">3rd</th>
                    @endif
                    <th class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $index => $result)
                    <tr class="{{ $index < 3 ? 'bg-yellow-50' : '' }}">
                        <td class="font-bold">
                            @if($index === 0)
                                <span class="text-place-gold"><i class="fas fa-trophy"></i></span>
                            @elseif($index === 1)
                                <span class="text-place-silver"><i class="fas fa-medal"></i></span>
                            @elseif($index === 2)
                                <span class="text-place-bronze"><i class="fas fa-award"></i></span>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </td>
                        <td>
                            <span class="font-medium">{{ $result->entry_name }}</span>
                            @if($result->entry_number)
                                <span class="text-gray-500 text-sm">({{ $result->entry_number }})</span>
                            @endif
                        </td>
                        <td>{{ $result->participant_name ?? '-' }}</td>
                        @if($showVoteCounts ?? true)
                            <td class="text-center">{{ $result->first_place_count }}</td>
                            <td class="text-center">{{ $result->second_place_count }}</td>
                            <td class="text-center">{{ $result->third_place_count }}</td>
                        @endif
                        <td class="text-center font-bold text-lg">
                            {{ number_format($result->total_points, 0) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
