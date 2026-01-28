<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Your Account - VotigoPro</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1a3a5c 0%, #0d7a3e 100%);
            --primary-dark: #1a3a5c;
            --primary-blue: #0d7a3e;
            --accent-orange: #f39c12;
            --accent-orange-dark: #d68910;
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
            height: 110px;
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

        .register-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1000px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* Left Side - Benefits */
        .benefits-section {
            background: var(--primary-gradient);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .benefits-section h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .benefits-section .subtitle {
            opacity: 0.9;
            margin-bottom: 30px;
            font-size: 1rem;
        }

        .benefit-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 25px;
        }

        .benefit-icon {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .benefit-content h4 {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .benefit-content p {
            font-size: 0.9rem;
            opacity: 0.85;
            line-height: 1.4;
        }

        .plan-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 30px;
            margin-top: 20px;
        }

        .plan-badge .plan-name {
            font-weight: bold;
            text-transform: capitalize;
        }

        .plan-badge .plan-price {
            opacity: 0.9;
        }

        /* Right Side - Form */
        .form-section {
            padding: 50px;
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(13, 122, 62, 0.1);
        }

        .name-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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

        .btn-register {
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

        .btn-register:hover {
            background: var(--accent-orange-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(243, 156, 18, 0.3);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #6b7280;
        }

        .login-link a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
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

        /* Plan Selection */
        .plan-selector {
            margin-bottom: 25px;
        }

        .plan-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .plan-option {
            position: relative;
        }

        .plan-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .plan-option label {
            display: block;
            padding: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .plan-option label:hover {
            border-color: var(--primary-blue);
        }

        .plan-option input:checked + label {
            border-color: var(--primary-blue);
            background: #eff6ff;
        }

        .plan-option-name {
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 0.95rem;
        }

        .plan-option-price {
            color: #6b7280;
            font-size: 0.85rem;
            margin-top: 3px;
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
            .register-wrapper {
                grid-template-columns: 1fr;
            }

            .benefits-section {
                padding: 30px;
            }

            .form-section {
                padding: 30px;
            }

            .name-row {
                grid-template-columns: 1fr;
            }

            .plan-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="{{ route('home') }}" class="navbar-brand">
                <img src="{{ asset('images/VotigoPro.png') }}" alt="VotigoPro Logo">
            </a>
            <a href="{{ route('home') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="register-wrapper">
            <!-- Left Side - Benefits -->
            <div class="benefits-section">
                <h2>Start Running Better Events</h2>
                <p class="subtitle">Join hundreds of event organizers who trust VotigoPro</p>

                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <div class="benefit-content">
                        <h4>Get Started in Minutes</h4>
                        <p>Set up your first event and start accepting votes right away.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="benefit-content">
                        <h4>Real-time Results</h4>
                        <p>Watch votes come in live with automatic tallying and beautiful displays.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="benefit-content">
                        <h4>Secure & Reliable</h4>
                        <p>Your voting data is safe with enterprise-grade security.</p>
                    </div>
                </div>

                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="benefit-content">
                        <h4>Dedicated Support</h4>
                        <p>Our team is here to help you run successful events.</p>
                    </div>
                </div>

                <div class="plan-badge">
                    <i class="fas fa-tag"></i>
                    @if(isset($selectedPlan) && $selectedPlan)
                        <span class="plan-name">{{ $selectedPlan->name }}</span>
                        <span class="plan-price">- ${{ number_format($selectedPlan->price, 2) }}/mo</span>
                    @else
                        <span class="plan-name">Free Trial</span>
                        <span class="plan-price">- $0.00/mo</span>
                    @endif
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="form-section">
                <h2><i class="fas fa-user-plus" style="color: var(--accent-orange);"></i> Create Account</h2>
                <p class="subtitle">Fill in your details to get started</p>

                @if($errors->any())
                    <div class="error-message">
                        @foreach($errors->all() as $error)
                            <p><i class="fas fa-exclamation-circle"></i> {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register.submit') }}">
                    @csrf

                    <!-- Plan Selection -->
                    <div class="plan-selector">
                        <label style="font-weight: 600; color: #374151; margin-bottom: 10px; display: block;">Select Your Plan</label>
                        <div class="plan-options">
                            <div class="plan-option">
                                <input type="radio" name="plan" id="plan_free" value="free" {{ ($planCode ?? 'free') == 'free' ? 'checked' : '' }}>
                                <label for="plan_free">
                                    <div class="plan-option-name">Free Trial</div>
                                    <div class="plan-option-price">$0/mo</div>
                                </label>
                            </div>
                            <div class="plan-option">
                                <input type="radio" name="plan" id="plan_nonprofit" value="nonprofit" {{ ($planCode ?? '') == 'nonprofit' ? 'checked' : '' }}>
                                <label for="plan_nonprofit">
                                    <div class="plan-option-name">Non-Profit</div>
                                    <div class="plan-option-price">$9.99/mo</div>
                                </label>
                            </div>
                            <div class="plan-option">
                                <input type="radio" name="plan" id="plan_professional" value="professional" {{ ($planCode ?? '') == 'professional' ? 'checked' : '' }}>
                                <label for="plan_professional">
                                    <div class="plan-option-name">Professional</div>
                                    <div class="plan-option-price">$29.99/mo</div>
                                </label>
                            </div>
                            <div class="plan-option">
                                <input type="radio" name="plan" id="plan_premium" value="premium" {{ ($planCode ?? '') == 'premium' ? 'checked' : '' }}>
                                <label for="plan_premium">
                                    <div class="plan-option-name">Premium</div>
                                    <div class="plan-option-price">$59.00/mo</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="name-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required placeholder="John">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required placeholder="Doe">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="organization">Organization (Optional)</label>
                        <input type="text" id="organization" name="organization" value="{{ old('organization') }}" placeholder="Your company or organization">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="john@example.com">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" required placeholder="Minimum 8 characters">
                            <button type="button" class="password-toggle" onclick="togglePassword('password', 'password-icon')">
                                <i class="fas fa-eye" id="password-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Confirm your password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'confirm-icon')">
                                <i class="fas fa-eye" id="confirm-icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-register">
                        <i class="fas fa-rocket"></i> Create My Account
                    </button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="{{ route('login') }}">Sign in</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        &copy; {{ date('Y') }} VotigoPro. All rights reserved.
    </footer>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Update plan badge when selection changes
        document.querySelectorAll('input[name="plan"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const planDetails = {
                    'free': { name: 'Free Trial', price: '$0/mo' },
                    'nonprofit': { name: 'Non-Profit', price: '$9.99/mo' },
                    'professional': { name: 'Professional', price: '$29.99/mo' },
                    'premium': { name: 'Premium', price: '$59.00/mo' }
                };

                const selected = planDetails[this.value];
                document.querySelector('.plan-badge .plan-name').textContent = selected.name;
                document.querySelector('.plan-badge .plan-price').textContent = '- ' + selected.price;
            });
        });
    </script>
</body>
</html>
