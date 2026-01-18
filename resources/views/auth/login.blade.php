<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In - My Voting Software</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            --primary-dark: #1e3a8a;
            --primary-blue: #2563eb;
            --accent-orange: #ff6600;
            --accent-orange-dark: #e55c00;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navigation */
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 20px;
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .navbar-brand img {
            height: 40px;
        }

        .navbar-brand span {
            font-weight: bold;
            font-size: 1.1rem;
            color: var(--primary-dark);
        }

        .back-link {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .back-link:hover {
            color: var(--primary-dark);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .login-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 900px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* Left Side - Branding */
        .branding-section {
            background: var(--primary-gradient);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .branding-section img {
            max-width: 200px;
            margin-bottom: 30px;
        }

        .branding-section h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .branding-section p {
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features-list {
            text-align: left;
            width: 100%;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            background: rgba(255,255,255,0.1);
            padding: 12px 15px;
            border-radius: 10px;
        }

        .feature-item i {
            font-size: 1.2rem;
            width: 25px;
            text-align: center;
        }

        /* Right Side - Form */
        .form-section {
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-section h2 {
            font-size: 1.8rem;
            color: var(--primary-dark);
            margin-bottom: 10px;
        }

        .form-section .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 50px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            padding: 5px;
        }

        .password-toggle:hover {
            color: var(--primary-dark);
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            cursor: pointer;
        }

        .remember-me input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .forgot-link {
            color: var(--primary-blue);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: var(--accent-orange);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            background: var(--accent-orange-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 102, 0, 0.3);
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #6b7280;
        }

        .register-link a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .error-message p {
            margin: 5px 0;
            font-size: 0.9rem;
        }

        .success-message {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #059669;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        /* Footer */
        .footer {
            background: var(--primary-dark);
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media screen and (max-width: 768px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }

            .branding-section {
                padding: 30px;
            }

            .branding-section img {
                max-width: 150px;
            }

            .form-section {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="{{ route('home') }}" class="navbar-brand">
                <img src="{{ asset('images/MyVotingSoftware_DoubleSize.png') }}" alt="My Voting Software Logo">
                <span>My Voting Software</span>
            </a>
            <a href="{{ route('home') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="login-wrapper">
            <!-- Left Side - Branding -->
            <div class="branding-section">
                <img src="{{ asset('images/MyVotingSoftware_DoubleSize.png') }}" alt="My Voting Software Logo">
                <h2>Welcome Back!</h2>
                <p>Sign in to manage your events, run voting sessions, and view results.</p>

                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-vote-yea"></i>
                        <span>Run live voting sessions</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>View real-time results</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <span>Manage participants & entries</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-trophy"></i>
                        <span>Organize competitions</span>
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="form-section">
                <h2><i class="fas fa-sign-in-alt" style="color: var(--accent-orange);"></i> Sign In</h2>
                <p class="subtitle">Enter your credentials to access your account</p>

                @if($errors->any())
                    <div class="error-message">
                        @foreach($errors->all() as $error)
                            <p><i class="fas fa-exclamation-circle"></i> {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if(session('status'))
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your email" autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" required placeholder="Enter your password" autocomplete="current-password">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="remember-row">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="register-link">
                    Don't have an account? <a href="{{ route('register') }}">Create one now</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        &copy; {{ date('Y') }} My Voting Software. All rights reserved.
    </footer>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
