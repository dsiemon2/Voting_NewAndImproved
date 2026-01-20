<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'My Voting Software') }}</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Sidebar Component Styles -->
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }

        /* Header Wrapper */
        .top-header-wrapper {
            width: 100%;
            background-color: #1e3a8a;
        }

        /* Header */
        .top-header {
            background-color: #1e3a8a;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
           
        }

        .top-header .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .top-header .logo img {
            height: 50px;
        }

        .top-header .logo span {
            font-size: 20px;
            font-weight: bold;
        }

        .top-header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .top-header .user-info span {
            font-size: 14px;
        }

        .top-header .logout-btn {
            background-color: #ff6600;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }

        .top-header .logout-btn:hover {
            background-color: #e55c00;
        }

        /* Container */
        .container {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        /* Sidebar styles are in /css/sidebar.css */

        /* Content */
        .content {
            flex: 1;
            padding: 20px;
            background-color: #f8fafc;
        }

        /* Page Title */
        .page-title {
            font-size: 24px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1e3a8a;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-success {
            background-color: #10b981;
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        .btn-warning {
            background-color: #ff6600;
            color: white;
        }

        .btn-warning:hover {
            background-color: #e55c00;
        }

        .btn-danger {
            background-color: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .table th {
            background-color: #1e3a8a;
            color: white;
            font-weight: bold;
        }

        .table tr:hover {
            background-color: #f3f4f6;
        }

        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 10px center;
            background-repeat: no-repeat;
            background-size: 20px;
            padding-right: 40px;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #dc2626;
        }

        .alert-warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #f59e0b;
        }

        .alert-info {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #2563eb;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #10b981;
            color: white;
        }

        .badge-danger {
            background-color: #dc2626;
            color: white;
        }

        .badge-warning {
            background-color: #f59e0b;
            color: white;
        }

        .badge-info {
            background-color: #2563eb;
            color: white;
        }

        /* Grid */
        .grid {
            display: grid;
            gap: 20px;
        }

        .grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card .stat-icon {
            font-size: 40px;
            color: #2563eb;
            margin-bottom: 10px;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #1e3a8a;
        }

        .stat-card .stat-label {
            color: #6b7280;
            font-size: 14px;
        }

        /* Mobile Toggle - styles in /css/sidebar.css */

        /* Responsive */
        @media screen and (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .grid-2, .grid-3, .grid-4 {
                grid-template-columns: 1fr;
            }

            .top-header {
                flex-wrap: wrap;
                gap: 10px;
            }

            .table {
                display: block;
                overflow-x: auto;
            }
        }

        /* Utility classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .mt-1 { margin-top: 5px; }
        .mt-2 { margin-top: 10px; }
        .mt-3 { margin-top: 15px; }
        .mt-4 { margin-top: 20px; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mb-3 { margin-bottom: 15px; }
        .mb-4 { margin-bottom: 20px; }
        .d-flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .align-center { align-items: center; }
        .gap-2 { gap: 10px; }

        /* Template/Card Grid */
        .template-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        @media screen and (max-width: 1024px) {
            .template-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media screen and (max-width: 600px) {
            .template-grid {
                grid-template-columns: 1fr;
            }
        }

        .template-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .template-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .template-card-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .template-card-icon {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .template-card-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .template-card-body {
            padding: 20px;
        }

        .template-card-footer {
            padding: 15px 20px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* User Avatar */
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .user-info-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
        }

        .action-btn-edit {
            background-color: #fef3c7;
            color: #d97706;
        }

        .action-btn-edit:hover {
            background-color: #fde68a;
            color: #b45309;
        }

        .action-btn-delete {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .action-btn-delete:hover {
            background-color: #fecaca;
            color: #b91c1c;
        }

        .action-btn-view {
            background-color: #dbeafe;
            color: #2563eb;
        }

        .action-btn-view:hover {
            background-color: #bfdbfe;
            color: #1d4ed8;
        }

        .action-btn-vote {
            background-color: #d1fae5;
            color: #10b981;
        }

        .action-btn-vote:hover {
            background-color: #a7f3d0;
            color: #059669;
        }

        /* Label Tags */
        .label-tag {
            display: inline-block;
            background: #f3f4f6;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            color: #4b5563;
            margin-right: 5px;
            margin-bottom: 5px;
        }

        /* Module Badge */
        .module-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            margin-right: 5px;
            margin-bottom: 5px;
        }

        /* Filter Form */
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .filter-form .form-control {
            min-width: 180px;
        }

        .filter-form .search-input {
            flex: 1;
            min-width: 200px;
        }

        /* Module Link Cards */
        .module-link-card {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
            border: 1px solid #e5e7eb;
        }

        .module-link-card:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
            transform: translateY(-2px);
        }

        .module-link-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .module-link-info strong {
            display: block;
            color: #1f2937;
            margin-bottom: 3px;
        }

        .module-link-info span {
            font-size: 12px;
            color: #6b7280;
        }

        /* Event Context Banner */
        .event-context-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            padding: 12px 15px;
            margin: -20px -20px 15px -20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .event-context-banner .event-name {
            font-weight: bold;
            font-size: 14px;
        }

        .event-context-banner .event-template {
            font-size: 11px;
            opacity: 0.8;
        }

        .event-context-banner a {
            color: white;
            font-size: 11px;
            text-decoration: none;
            opacity: 0.8;
        }

        .event-context-banner a:hover {
            opacity: 1;
        }

        /* Sidebar Event Menu styles are in /css/sidebar.css */

        /* Responsive Grid Fix */
        @media screen and (max-width: 900px) {
            .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    @auth
        <!-- Top Header -->
        <div class="top-header-wrapper">
            <header class="top-header">
                <div class="logo">
                    <img src="{{ asset('images/MyVotingSoftware_DoubleSize.png') }}" alt="Logo" style="height: 110px;">
                    <span>My Voting Software</span>
                </div>
                <div class="user-info">
                    <span><i class="fas fa-user"></i> {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                    <span class="badge badge-info">{{ auth()->user()->role?->name ?? 'User' }}</span>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </header>
        </div>

        <!-- Mobile Toggle -->
        <button class="mobile-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i> Menu
        </button>

        <div class="container">
            <!-- Sidebar Component -->
            <x-sidebar />

            <!-- Content -->
            <main class="content">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> {{ session('info') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error">
                        <ul style="list-style: none; margin: 0; padding: 0;">
                            @foreach($errors->all() as $error)
                                <li><i class="fas fa-times-circle"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    @else
        {{ $slot ?? '' }}
        @yield('content')
    @endauth

    <!-- Sidebar Component JavaScript -->
    <script src="{{ asset('js/sidebar.js') }}"></script>

    @stack('scripts')

    {{-- AI Chat Slider --}}
    @auth
        @php
            $featureSettings = \App\Models\FeatureSettings::first();
            // Default to showing if no settings exist yet
            $showAiChat = !$featureSettings || $featureSettings->ai_chat_enabled;

            // Check page-specific settings (only if settings exist)
            if ($showAiChat && $featureSettings) {
                $routeName = \Illuminate\Support\Facades\Route::currentRouteName() ?? '';
                if (str_contains($routeName, 'voting') && !$featureSettings->ai_show_on_voting) $showAiChat = false;
                if (str_contains($routeName, 'results') && !$featureSettings->ai_show_on_results) $showAiChat = false;
                if (str_contains($routeName, 'admin') && !$featureSettings->ai_show_on_admin) $showAiChat = false;
                if ($routeName === 'dashboard' && !$featureSettings->ai_show_on_dashboard) $showAiChat = false;
                if ($routeName === 'home' && !$featureSettings->ai_show_on_landing) $showAiChat = false;
            }
        @endphp

        @if($showAiChat)
            @include('components.ai-chat-slider', [
                'position' => $featureSettings->ai_chat_position ?? 'bottom-right',
                'buttonColor' => $featureSettings->ai_button_color ?? '#1e40af',
                'panelWidth' => $featureSettings->ai_panel_width ?? 380,
                'voiceInput' => $featureSettings->ai_voice_input ?? true,
                'voiceOutput' => $featureSettings->ai_voice_output ?? true,
            ])
        @endif
    @endauth
</body>
</html>
