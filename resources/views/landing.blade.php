<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Voting Software - Professional Event Voting Made Simple</title>
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
            color: #333;
        }

        /* Navigation */
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
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
            height: 50px;
        }

        .navbar-brand span {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--primary-dark);
        }

        .navbar-nav {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        .navbar-nav a {
            text-decoration: none;
            color: #4b5563;
            font-weight: 500;
            transition: color 0.2s;
        }

        .navbar-nav a:hover {
            color: var(--primary-blue);
        }

        .navbar-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-blue);
            color: var(--primary-blue);
        }

        .btn-outline:hover {
            background: var(--primary-blue);
            color: white;
        }

        .btn-primary {
            background: var(--accent-orange);
            color: white;
            border: 2px solid var(--accent-orange);
        }

        .btn-primary:hover {
            background: var(--accent-orange-dark);
            border-color: var(--accent-orange-dark);
        }

        .btn-lg {
            padding: 15px 35px;
            font-size: 1.1rem;
            border-radius: 8px;
        }

        /* Hero Section */
        .hero-section {
            background: var(--primary-gradient);
            color: white;
            padding: 80px 20px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .hero-features {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }

        .hero-feature {
            background: rgba(255,255,255,0.15);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .hero-cta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .hero-cta .btn-hero-primary {
            background: white;
            color: var(--primary-dark);
            padding: 15px 35px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .hero-cta .btn-hero-primary:hover {
            background: #f0f9ff;
            color: var(--accent-orange);
        }

        .hero-cta .btn-hero-secondary {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 15px 35px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .hero-cta .btn-hero-secondary:hover {
            background: rgba(255,255,255,0.2);
        }

        .hero-image {
            position: relative;
            z-index: 1;
        }

        .hero-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            transform: rotate(2deg);
        }

        .hero-card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .hero-card-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .hero-card-title {
            color: var(--primary-dark);
            font-weight: bold;
            font-size: 1.1rem;
        }

        .hero-card-subtitle {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .hero-card-content {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .hero-card-content p {
            color: #374151;
            margin-bottom: 10px;
        }

        .voting-preview {
            display: flex;
            gap: 10px;
        }

        .voting-box {
            flex: 1;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }

        .voting-box-title {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .voting-box-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-dark);
        }

        .hero-card-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .hero-card-badges span {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-green {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-orange {
            background: #ffedd5;
            color: #c2410c;
        }

        .hero-stats {
            position: absolute;
        }

        .hero-stat-card {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            color: #333;
        }

        .hero-stat-card.top-right {
            position: absolute;
            top: -30px;
            right: -30px;
        }

        .hero-stat-card.bottom-left {
            position: absolute;
            bottom: 30px;
            left: -40px;
        }

        .hero-stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #10b981;
        }

        .hero-stat-label {
            font-size: 0.8rem;
            color: #6b7280;
        }

        /* Features Section */
        .features-section {
            padding: 80px 20px;
            background: white;
        }

        .section-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }

        .section-header p {
            font-size: 1.1rem;
            color: #6b7280;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .feature-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
            color: white;
        }

        .feature-card h4 {
            font-size: 1.25rem;
            color: var(--primary-dark);
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #6b7280;
            line-height: 1.6;
        }

        /* Use Cases Section */
        .use-cases-section {
            padding: 80px 20px;
            background: #f8fafc;
        }

        .use-cases-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            justify-items: center;
        }

        .use-case-pill {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            width: 100%;
            max-width: 180px;
        }

        .use-case-pill:hover {
            transform: scale(1.05);
        }

        .use-case-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .use-case-pill span {
            font-weight: 600;
            color: #374151;
        }

        /* Pricing Section */
        .pricing-section {
            padding: 80px 20px;
            background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        .pricing-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .pricing-card.popular {
            border: 3px solid var(--accent-orange);
        }

        .pricing-card.popular::before {
            content: 'MOST POPULAR';
            position: absolute;
            top: 15px;
            right: -35px;
            background: var(--accent-orange);
            color: white;
            padding: 5px 40px;
            font-size: 0.7rem;
            font-weight: 600;
            transform: rotate(45deg);
        }

        .pricing-tier {
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            color: #6b7280;
        }

        .pricing-tier.highlight {
            color: var(--accent-orange);
        }

        .pricing-price {
            font-size: 3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .pricing-price span {
            font-size: 1rem;
            font-weight: 400;
            color: #6b7280;
        }

        .pricing-description {
            color: #6b7280;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }

        .pricing-features {
            list-style: none;
            margin: 25px 0;
        }

        .pricing-features li {
            padding: 12px 0;
            border-bottom: 1px solid #f1f1f1;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }

        .pricing-features li:last-child {
            border-bottom: none;
        }

        .pricing-features li i {
            color: #22c55e;
        }

        .pricing-features li.disabled {
            color: #adb5bd;
        }

        .pricing-features li.disabled i {
            color: #adb5bd;
        }

        .btn-pricing {
            padding: 14px 30px;
            border-radius: 30px;
            font-weight: 600;
            width: 100%;
            text-align: center;
            display: block;
        }

        .btn-pricing-primary {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-pricing-primary:hover {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
        }

        .btn-pricing-outline {
            background: transparent;
            border: 2px solid var(--primary-blue);
            color: var(--primary-blue);
        }

        .btn-pricing-outline:hover {
            background: var(--primary-blue);
            color: white;
        }

        /* CTA Section */
        .cta-section {
            background: var(--primary-gradient);
            padding: 80px 20px;
            text-align: center;
            color: white;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .cta-section p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .cta-section .btn {
            background: white;
            color: var(--primary-dark);
            padding: 18px 50px;
            font-size: 1.2rem;
            border-radius: 30px;
        }

        .cta-section .btn:hover {
            background: #f0f9ff;
            color: var(--accent-orange);
        }

        /* Footer */
        .footer {
            background: #1f2937;
            color: white;
            padding: 60px 20px 30px;
        }

        .footer-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-brand h5 {
            font-size: 1.25rem;
            margin-bottom: 15px;
        }

        .footer-brand p {
            color: rgba(255,255,255,0.7);
            line-height: 1.6;
        }

        .footer-links h5 {
            font-size: 1rem;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .footer-links a {
            display: block;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            margin-bottom: 10px;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            color: rgba(255,255,255,0.5);
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary-dark);
            cursor: pointer;
        }

        /* Responsive */
        @media screen and (max-width: 1024px) {
            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-features {
                justify-content: center;
            }

            .hero-cta {
                justify-content: center;
            }

            .hero-image {
                display: none;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .pricing-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media screen and (max-width: 768px) {
            .navbar-nav {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .hero-title {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .section-header h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="/" class="navbar-brand">
                <img src="{{ asset('images/MyVotingSoftware_DoubleSize.png') }}" alt="My Voting Software Logo">
                <span>My Voting Software</span>
            </a>
            <ul class="navbar-nav">
                <li><a href="#features">Features</a></li>
                <li><a href="#use-cases">Use Cases</a></li>
                <li><a href="#pricing">Pricing</a></li>
                <li><a href="#about">About</a></li>
            </ul>
            <div class="navbar-buttons">
                <a href="{{ route('login') }}" class="btn btn-outline">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
            </div>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <span class="hero-badge">
                    <i class="fas fa-vote-yea"></i> Professional Voting Platform
                </span>
                <h1 class="hero-title">
                    Event Voting<br>Made Simple
                </h1>
                <p class="hero-subtitle">
                    Run professional voting for competitions, contests, and events of any size.
                    Real-time results, flexible voting types, and beautiful interfaces.
                </p>
                <div class="hero-features">
                    <span class="hero-feature"><i class="fas fa-trophy"></i> Food Competitions</span>
                    <span class="hero-feature"><i class="fas fa-camera"></i> Photo Contests</span>
                    <span class="hero-feature"><i class="fas fa-music"></i> Talent Shows</span>
                    <span class="hero-feature"><i class="fas fa-chart-bar"></i> Real-time Results</span>
                </div>
                <div class="hero-cta">
                    <a href="{{ route('register') }}" class="btn btn-hero-primary">
                        <i class="fas fa-rocket"></i> Start Free Trial
                    </a>
                    <a href="#pricing" class="btn btn-hero-secondary">
                        View Pricing
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-card">
                    <div class="hero-card-header">
                        <div class="hero-card-icon">
                            <i class="fas fa-poll"></i>
                        </div>
                        <div>
                            <div class="hero-card-title">Live Voting</div>
                            <div class="hero-card-subtitle">Soup Cookoff 2025</div>
                        </div>
                    </div>
                    <div class="hero-card-content">
                        <p><strong>Cast your vote by division number:</strong></p>
                        <div class="voting-preview">
                            <div class="voting-box">
                                <div class="voting-box-title">1st Place</div>
                                <div class="voting-box-value">P3</div>
                            </div>
                            <div class="voting-box">
                                <div class="voting-box-title">2nd Place</div>
                                <div class="voting-box-value">P7</div>
                            </div>
                            <div class="voting-box">
                                <div class="voting-box-title">3rd Place</div>
                                <div class="voting-box-value">P1</div>
                            </div>
                        </div>
                    </div>
                    <div class="hero-card-badges">
                        <span class="badge-blue"><i class="fas fa-users"></i> 156 voters</span>
                        <span class="badge-green"><i class="fas fa-check-circle"></i> Live</span>
                        <span class="badge-orange"><i class="fas fa-clock"></i> 2h left</span>
                    </div>
                </div>
                <div class="hero-stat-card top-right">
                    <div class="hero-stat-value">+89%</div>
                    <div class="hero-stat-label">Voter turnout</div>
                </div>
                <div class="hero-stat-card bottom-left">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-star" style="color: #fbbf24;"></i>
                        <span style="font-weight: bold;">4.9</span>
                    </div>
                    <div class="hero-stat-label">User rating</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="section-container">
            <div class="section-header">
                <h2>Why Choose My Voting Software?</h2>
                <p>Everything you need to run professional voting events</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h4>Flexible Divisions</h4>
                    <p>Organize participants into divisions like Professional, Amateur, or any custom categories you need.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Real-time Results</h4>
                    <p>Watch votes come in live with automatic tallying and beautiful result displays.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <h4>Custom Voting Types</h4>
                    <p>Standard ranked (3-2-1), extended (5-4-3-2-1), top-heavy, or create your own point system.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-import"></i>
                    </div>
                    <h4>Easy Import</h4>
                    <p>Import participants and entries from Excel spreadsheets with automatic division creation.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-print"></i>
                    </div>
                    <h4>Printable Ballots</h4>
                    <p>Generate professional PDF ballots for in-person voting events.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h4>Judging Panels</h4>
                    <p>Set up dedicated judges with weighted voting or separate scoring categories.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Use Cases Section -->
    <section class="use-cases-section" id="use-cases">
        <div class="section-container">
            <div class="section-header">
                <h2>Perfect For Any Competition</h2>
                <p>From cookoffs to talent shows, we've got you covered</p>
            </div>
            <div class="use-cases-grid">
                <div class="use-case-pill">
                    <div class="use-case-icon">&#127869;</div>
                    <span>Cookoffs</span>
                </div>
                <div class="use-case-pill">
                    <div class="use-case-icon">&#127856;</div>
                    <span>Bake-offs</span>
                </div>
                <div class="use-case-pill">
                    <div class="use-case-icon">&#128247;</div>
                    <span>Photo Contests</span>
                </div>
                <div class="use-case-pill">
                    <div class="use-case-icon">&#127926;</div>
                    <span>Talent Shows</span>
                </div>
                <div class="use-case-pill">
                    <div class="use-case-icon">&#127867;</div>
                    <span>Wine Tasting</span>
                </div>
                <div class="use-case-pill">
                    <div class="use-case-icon">&#127866;</div>
                    <span>Beer Festivals</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing-section" id="pricing">
        <div class="section-container">
            <div class="section-header">
                <h2>Simple, Transparent Pricing</h2>
                <p>Start free, upgrade as you grow. Cancel anytime.</p>
            </div>
            <div class="pricing-grid">
                <!-- Free Trial -->
                <div class="pricing-card">
                    <div class="pricing-tier">Free Trial</div>
                    <div class="pricing-price">$0<span>/mo</span></div>
                    <p class="pricing-description">Perfect for trying it out</p>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check-circle"></i> 1 Active Event</li>
                        <li><i class="fas fa-check-circle"></i> Up to 20 Entries</li>
                        <li><i class="fas fa-check-circle"></i> Basic Voting Types</li>
                        <li><i class="fas fa-check-circle"></i> Real-time Results</li>
                        <li><i class="fas fa-check-circle"></i> Custom Templates</li>
                        <li><i class="fas fa-check-circle"></i> PDF Ballots</li>
                    </ul>
                    <a href="{{ route('register', ['plan' => 'free']) }}" class="btn btn-pricing btn-pricing-outline">Get Trial Code</a>
                </div>

                <!-- Non-Profit -->
                <div class="pricing-card">
                    <div class="pricing-tier">Non-Profit</div>
                    <div class="pricing-price">$9.99<span>/mo</span></div>
                    <p class="pricing-description">For community organizations</p>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check-circle"></i> 3 Active Events</li>
                        <li><i class="fas fa-check-circle"></i> Up to 100 Entries</li>
                        <li><i class="fas fa-check-circle"></i> All Voting Types</li>
                        <li><i class="fas fa-check-circle"></i> Excel Import</li>
                        <li><i class="fas fa-check-circle"></i> PDF Ballots</li>
                        <li><i class="fas fa-check-circle"></i> Email Support</li>
                    </ul>
                    <a href="{{ route('register', ['plan' => 'nonprofit']) }}" class="btn btn-pricing btn-pricing-outline">Start Plan</a>
                </div>

                <!-- Professional -->
                <div class="pricing-card popular">
                    <div class="pricing-tier highlight">Professional</div>
                    <div class="pricing-price">$29.99<span>/mo</span></div>
                    <p class="pricing-description">For serious event organizers</p>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check-circle"></i> 10 Active Events</li>
                        <li><i class="fas fa-check-circle"></i> Unlimited Entries</li>
                        <li><i class="fas fa-check-circle"></i> Custom Templates</li>
                        <li><i class="fas fa-check-circle"></i> Judging Panels</li>
                        <li><i class="fas fa-check-circle"></i> Advanced Analytics</li>
                        <li><i class="fas fa-check-circle"></i> Priority Support</li>
                    </ul>
                    <a href="{{ route('register', ['plan' => 'professional']) }}" class="btn btn-pricing btn-pricing-primary">Start Plan</a>
                </div>

                <!-- Premium -->
                <div class="pricing-card">
                    <div class="pricing-tier" style="color: var(--primary-blue);">Premium</div>
                    <div class="pricing-price">$59.00<span>/mo</span></div>
                    <p class="pricing-description">For large-scale operations</p>
                    <ul class="pricing-features">
                        <li><i class="fas fa-check-circle"></i> Unlimited Events</li>
                        <li><i class="fas fa-check-circle"></i> Unlimited Everything</li>
                        <li><i class="fas fa-check-circle"></i> White-label Options</li>
                        <li><i class="fas fa-check-circle"></i> API Access</li>
                        <li><i class="fas fa-check-circle"></i> Custom Integrations</li>
                        <li><i class="fas fa-check-circle"></i> Dedicated Support</li>
                    </ul>
                    <a href="{{ route('register', ['plan' => 'premium']) }}" class="btn btn-pricing btn-pricing-outline">Start Plan</a>
                </div>
            </div>
            <div style="text-align: center; margin-top: 40px;">
                <p style="color: #6b7280;"><i class="fas fa-shield-alt"></i> 14-day free trial on all paid plans. No credit card required to start.</p>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section style="padding: 80px 20px;" id="about">
        <div class="section-container">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;">
                <div>
                    <h2 style="font-size: 2rem; color: var(--primary-dark); margin-bottom: 20px;">Built for Event Organizers</h2>
                    <p style="color: #6b7280; line-height: 1.8; margin-bottom: 20px;">
                        We've been there - managing voting for cookoffs, contests, and competitions with paper ballots and spreadsheets.
                        That's why we built My Voting Software.
                    </p>
                    <p style="color: #6b7280; line-height: 1.8; margin-bottom: 30px;">
                        Our platform handles everything from participant registration to real-time result tallying,
                        so you can focus on running a great event instead of counting votes.
                    </p>
                    <div style="display: flex; gap: 40px;">
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: bold; color: var(--primary-blue);">500+</div>
                            <div style="color: #6b7280; font-size: 0.9rem;">Events Hosted</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: bold; color: var(--primary-blue);">25K+</div>
                            <div style="color: #6b7280; font-size: 0.9rem;">Votes Cast</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: bold; color: var(--primary-blue);">99%</div>
                            <div style="color: #6b7280; font-size: 0.9rem;">Satisfaction</div>
                        </div>
                    </div>
                </div>
                <div style="text-align: center;">
                    <div style="background: #f8fafc; border-radius: 20px; padding: 60px;">
                        <i class="fas fa-trophy" style="font-size: 8rem; color: var(--accent-orange); opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <h2>Ready to Run Your Next Event?</h2>
        <p>Start your free trial today - no credit card required</p>
        <a href="{{ route('register') }}" class="btn">
            Get Started Free <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
        </a>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-brand">
                <h5><i class="fas fa-vote-yea" style="color: var(--accent-orange); margin-right: 10px;"></i>My Voting Software</h5>
                <p>Professional event voting made simple. From cookoffs to talent shows, we make running competitions easy.</p>
            </div>
            <div class="footer-links">
                <h5>Product</h5>
                <a href="#features">Features</a>
                <a href="#pricing">Pricing</a>
                <a href="#use-cases">Use Cases</a>
            </div>
            <div class="footer-links">
                <h5>Support</h5>
                <a href="#">Help Center</a>
                <a href="#">Contact Us</a>
                <a href="#">Documentation</a>
            </div>
            <div class="footer-links">
                <h5>Legal</h5>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} My Voting Software. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const nav = document.querySelector('.navbar-nav');
            nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
