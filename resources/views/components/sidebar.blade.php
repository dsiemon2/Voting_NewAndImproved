<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-primary-800 text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out overflow-y-auto">
    <!-- Logo/Brand -->
    <div class="flex items-center justify-center h-16 bg-primary-900">
        <h1 class="text-xl font-bold">
            <i class="fas fa-vote-yea mr-2"></i>
            Voting System
        </h1>
    </div>

    <!-- Navigation -->
    <nav class="mt-4">
        <ul class="space-y-1">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('dashboard') }}"
                   class="flex items-center px-4 py-3 text-white hover:bg-primary-700 transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary-900 border-l-4 border-primary-400' : '' }}">
                    <i class="fas fa-tachometer-alt w-6"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Admin Menu -->
            <li class="pt-4">
                <div class="px-4 text-xs font-semibold text-primary-300 uppercase tracking-wider">
                    Admin Menu
                </div>
            </li>

            <!-- User Management (Admin only) -->
            @if(auth()->user()->isAdmin())
            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center px-4 py-3 text-white hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-primary-900 border-l-4 border-primary-400' : '' }}">
                    <i class="fas fa-users w-6"></i>
                    <span>User Management</span>
                </a>
            </li>
            @endif

            <!-- Events -->
            <li>
                <a href="{{ route('admin.events.index') }}"
                   class="flex items-center px-4 py-3 text-white hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.events.index') || request()->routeIs('admin.events.create') ? 'bg-primary-900 border-l-4 border-primary-400' : '' }}">
                    <i class="fas fa-calendar-alt w-6"></i>
                    <span>Events</span>
                </a>
            </li>

            <!-- Event Templates -->
            <li>
                <a href="{{ route('admin.templates.index') }}"
                   class="flex items-center px-4 py-3 text-white hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.templates.*') ? 'bg-primary-900 border-l-4 border-primary-400' : '' }}">
                    <i class="fas fa-file-alt w-6"></i>
                    <span>Event Templates</span>
                </a>
            </li>

            <!-- Voting Types -->
            <li>
                <a href="{{ route('admin.voting-types.index') }}"
                   class="flex items-center px-4 py-3 text-white hover:bg-primary-700 transition-colors {{ request()->routeIs('admin.voting-types.*') ? 'bg-primary-900 border-l-4 border-primary-400' : '' }}">
                    <i class="fas fa-sliders-h w-6"></i>
                    <span>Voting Types</span>
                </a>
            </li>

            <!-- Current Event Section (if event is in session) -->
            @if(isset($currentEvent))
            <li class="pt-4">
                <div class="px-4 text-xs font-semibold text-primary-300 uppercase tracking-wider">
                    {{ $currentEvent->name }}
                </div>
            </li>

            @foreach($eventMenu ?? [] as $menuItem)
            <li>
                <a href="{{ $menuItem['route'] }}"
                   class="flex items-center px-4 py-3 text-white hover:bg-primary-700 transition-colors {{ $menuItem['active'] ? 'bg-primary-900 border-l-4 border-primary-400' : '' }}">
                    <i class="fas {{ $menuItem['icon'] }} w-6"></i>
                    <span>{{ $menuItem['label'] }}</span>
                </a>
            </li>
            @endforeach
            @endif

            <!-- Logout -->
            <li class="pt-4 mt-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center w-full px-4 py-3 text-white hover:bg-red-600 transition-colors">
                        <i class="fas fa-sign-out-alt w-6"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</aside>
