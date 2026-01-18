<!-- Top Navigation Bar -->
<nav class="bg-primary-600 text-white shadow-lg">
    <div class="px-4 py-3 flex items-center justify-between">
        <!-- Mobile menu button -->
        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-md hover:bg-primary-700">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Page Title / Breadcrumb -->
        <div class="flex-1 px-4">
            @if(isset($pageTitle))
                <h2 class="text-lg font-semibold">{{ $pageTitle }}</h2>
            @endif
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
            <!-- Current Event Indicator -->
            @if(isset($currentEvent))
                <span class="hidden md:inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-700">
                    <i class="fas fa-calendar-check mr-2"></i>
                    {{ $currentEvent->name }}
                </span>
            @endif

            <!-- User Info -->
            <div class="flex items-center space-x-2">
                <span class="hidden md:inline text-sm">
                    {{ auth()->user()->email }}
                </span>
                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-primary-700">
                    {{ auth()->user()->role?->name ?? 'User' }}
                </span>
            </div>
        </div>
    </div>
</nav>
