{{--
    Sidebar Navigation Component

    Usage: <x-sidebar />

    This component handles:
    - Main admin navigation menu
    - Event context menu (when managing a specific event)
    - Scroll position persistence (via sidebar.js)
    - Mobile responsive behavior

    Required assets:
    - /css/sidebar.css
    - /js/sidebar.js
--}}

@php
    // Get current event from route or cookie
    $currentEvent = null;
    $managingEventId = null;

    // First check if we're on an event-specific route
    $routeEvent = request()->route('event');

    if ($routeEvent) {
        // We're on an event page - use this event
        if (is_object($routeEvent)) {
            $currentEvent = $routeEvent;
            $managingEventId = $routeEvent->id;
        } else {
            $managingEventId = $routeEvent;
            $currentEvent = \App\Models\Event::with('template')->find($routeEvent);
        }
    } else {
        // Not on event page - check cookie for managed event (use $_COOKIE directly for JS-set cookies)
        $managingEventId = $_COOKIE['managing_event_id'] ?? null;
        if ($managingEventId && is_numeric($managingEventId)) {
            $currentEvent = \App\Models\Event::with('template')->find($managingEventId);
        }
    }

    $isAdmin = auth()->user()->role?->name === 'Administrator';
@endphp

<nav class="sidebar" id="sidebar">
    {{-- Event Context Menu --}}
    @if($currentEvent)
        <div class="sidebar__event-menu">
            <div class="sidebar__event-selector-wrapper">
                <a href="{{ route('admin.events.show', $currentEvent) }}" class="sidebar__event-selector">
                    <span>
                        <i class="fas {{ $currentEvent->template->icon ?? 'fa-calendar' }}"></i>
                        {{ \Illuminate\Support\Str::limit($currentEvent->name, 20) }}
                    </span>
                </a>
                <a href="javascript:void(0)" onclick="Sidebar.clearManagedEvent()" class="sidebar__event-close" title="Stop managing this event">
                    <i class="fas fa-times"></i>
                </a>
            </div>

            <div class="sidebar__menu-header">Event Actions</div>
            <ul class="sidebar__menu-list">
                <li>
                    <a href="{{ route('voting.index', $currentEvent) }}" class="{{ request()->routeIs('voting.*') ? 'active' : '' }}">
                        <i class="fas fa-vote-yea"></i> Vote Now
                    </a>
                </li>
                <li>
                    <a href="{{ route('results.index', $currentEvent) }}" class="{{ request()->routeIs('results.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> View Results
                    </a>
                </li>
            </ul>

            <div class="sidebar__menu-header">Manage</div>
            <ul class="sidebar__menu-list">
                @if($currentEvent->hasModule('divisions'))
                    <li>
                        <a href="{{ route('admin.events.divisions.index', $currentEvent) }}" class="{{ request()->routeIs('*.divisions.*') ? 'active' : '' }}">
                            <i class="fas fa-layer-group"></i> Divisions
                        </a>
                    </li>
                @endif
                @if($currentEvent->hasModule('participants'))
                    <li>
                        <a href="{{ route('admin.events.participants.index', $currentEvent) }}" class="{{ request()->routeIs('*.participants.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> {{ $currentEvent->template->participant_label ?? 'Participants' }}
                        </a>
                    </li>
                @endif
                @if($currentEvent->hasModule('entries'))
                    <li>
                        <a href="{{ route('admin.events.entries.index', $currentEvent) }}" class="{{ request()->routeIs('*.entries.*') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list"></i> {{ $currentEvent->template->entry_label ?? 'Entries' }}
                        </a>
                    </li>
                @endif
                @if($currentEvent->hasModule('categories'))
                    <li>
                        <a href="{{ route('admin.events.categories.index', $currentEvent) }}" class="{{ request()->routeIs('*.categories.*') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i> Categories
                        </a>
                    </li>
                @endif
                @if($currentEvent->hasModule('import'))
                    <li>
                        <a href="{{ route('admin.events.import', $currentEvent) }}" class="{{ request()->routeIs('*.import') ? 'active' : '' }}">
                            <i class="fas fa-file-import"></i> Import
                        </a>
                    </li>
                @endif
                @if($currentEvent->hasModule('pdf'))
                    <li>
                        <a href="{{ route('admin.events.ballots', $currentEvent) }}" class="{{ request()->routeIs('*.ballots') ? 'active' : '' }}">
                            <i class="fas fa-file-pdf"></i> Print Ballots
                        </a>
                    </li>
                @endif
                @if($currentEvent->hasModule('judging'))
                    <li>
                        <a href="{{ route('admin.events.judges.index', $currentEvent) }}" class="{{ request()->routeIs('*.judges.*') ? 'active' : '' }}">
                            <i class="fas fa-gavel"></i> Judging Panel
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('admin.events.edit', $currentEvent) }}" class="{{ request()->routeIs('admin.events.edit') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i> Event Settings
                    </a>
                </li>
            </ul>
        </div>
    @endif

    {{-- Admin Menu --}}
    <div class="sidebar__menu-header">
        <i class="fas fa-cog"></i> Admin Menu
    </div>
    <ul class="sidebar__menu-list">
        @if($isAdmin)
            <li>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i> User Management
                </a>
            </li>
        @endif
        <li>
            <a href="{{ route('admin.events.index') }}" class="{{ request()->routeIs('admin.events.index') || request()->routeIs('admin.events.create') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i> Events
            </a>
        </li>
        <li>
            <a href="{{ route('admin.templates.index') }}" class="{{ request()->routeIs('admin.templates.*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i> Templates
            </a>
        </li>
        <li>
            <a href="{{ route('admin.voting-types.index') }}" class="{{ request()->routeIs('admin.voting-types.*') ? 'active' : '' }}">
                <i class="fas fa-poll"></i> Voting Types
            </a>
        </li>
    </ul>

    @if($isAdmin)
        {{-- AI Settings --}}
        <div class="sidebar__menu-header sidebar__menu-header--spaced">
            <i class="fas fa-robot"></i> AI Settings
        </div>
        <ul class="sidebar__menu-list">
            <li>
                <a href="{{ route('admin.ai-settings.voices') }}" class="{{ request()->routeIs('admin.ai-settings.voices') ? 'active' : '' }}">
                    <i class="fas fa-volume-up"></i> Voices & Languages
                </a>
            </li>
            <li>
                <a href="{{ route('admin.ai-settings.config') }}" class="{{ request()->routeIs('admin.ai-settings.config') ? 'active' : '' }}">
                    <i class="fas fa-sliders-h"></i> AI Config
                </a>
            </li>
            <li>
                <a href="{{ route('admin.ai-settings.tools') }}" class="{{ request()->routeIs('admin.ai-settings.tools') ? 'active' : '' }}">
                    <i class="fas fa-tools"></i> AI Tools
                </a>
            </li>
            <li>
                <a href="{{ route('admin.ai-settings.knowledge-base') }}" class="{{ request()->routeIs('admin.ai-settings.knowledge-base') ? 'active' : '' }}">
                    <i class="fas fa-book"></i> Knowledge Base
                </a>
            </li>
            <li>
                <a href="{{ route('admin.ai-settings.greeting') }}" class="{{ request()->routeIs('admin.ai-settings.greeting') ? 'active' : '' }}">
                    <i class="fas fa-comment-dots"></i> Greeting
                </a>
            </li>
            <li>
                <a href="{{ route('admin.ai-providers.index') }}" class="{{ request()->routeIs('admin.ai-providers.*') ? 'active' : '' }}">
                    <i class="fas fa-brain"></i> AI Providers
                </a>
            </li>
        </ul>

        {{-- System --}}
        <div class="sidebar__menu-header sidebar__menu-header--spaced">
            <i class="fas fa-cogs"></i> System
        </div>
        <ul class="sidebar__menu-list">
            <li>
                <a href="{{ route('admin.ai-settings.features') }}" class="{{ request()->routeIs('admin.ai-settings.features') ? 'active' : '' }}">
                    <i class="fas fa-toggle-on"></i> Features
                </a>
            </li>
            <li>
                <a href="{{ route('admin.ai-settings.settings') }}" class="{{ request()->routeIs('admin.ai-settings.settings') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
            <li>
                <a href="{{ route('admin.payment-processing.index') }}" class="{{ request()->routeIs('admin.payment-processing.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card"></i> Payment Gateways
                </a>
            </li>
            <li>
                <a href="{{ route('admin.trial-codes.index') }}" class="{{ request()->routeIs('admin.trial-codes.*') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i> Trial Codes
                </a>
            </li>
            <li>
                <a href="{{ route('admin.twilio-settings.index') }}" class="{{ request()->routeIs('admin.twilio-settings.*') ? 'active' : '' }}">
                    <i class="fas fa-sms"></i> Twilio SMS
                </a>
            </li>
        </ul>
    @endif

    {{-- Account --}}
    <div class="sidebar__menu-header sidebar__menu-header--spaced">
        <i class="fas fa-user-circle"></i> Account
    </div>
    <ul class="sidebar__menu-list">
        <li>
            <a href="{{ route('account.index') }}" class="{{ request()->routeIs('account.*') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i> Account Settings
            </a>
        </li>
        <li>
            <a href="{{ route('subscription.manage') }}" class="{{ request()->routeIs('subscription.manage') ? 'active' : '' }}">
                <i class="fas fa-id-card"></i> My Subscription
            </a>
        </li>
        <li>
            <a href="{{ route('subscription.pricing') }}" class="{{ request()->routeIs('subscription.pricing') ? 'active' : '' }}">
                <i class="fas fa-tags"></i> Pricing Plans
            </a>
        </li>
    </ul>

    {{-- Reports --}}
    <div class="sidebar__menu-header sidebar__menu-header--spaced">
        <i class="fas fa-chart-bar"></i> Reports
    </div>
    <ul class="sidebar__menu-list">
        <li>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
    </ul>
</nav>

{{-- Pass managing event ID to JavaScript --}}
@if($managingEventId)
    <script>
        window.managingEventId = '{{ $managingEventId }}';
    </script>
@endif
