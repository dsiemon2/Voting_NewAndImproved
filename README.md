# Voting System - New and Improved

A modern, flexible voting application built with Laravel 11, featuring dynamic event configuration, configurable voting types, and responsive design.

**Production Domain:** www.votigopro.com

## Features

- **Dynamic Event Templates**: Create different event types (Food Competition, Photo Contest, General Vote, etc.)
- **Configurable Voting Types**: Standard 3-2-1, Extended 5-4-3-2-1, Approval voting, Weighted voting with judges
- **Modular Architecture**: Enable/disable features (Divisions, Participants, Import, PDF) per event
- **Responsive Design**: Works on desktop, tablet, and mobile (admin tables convert to card layouts)
- **Role-Based Access**: Administrator, Member, User, Judge roles
- **REST API**: Full API for mobile/external integrations
- **Docker Ready**: Runs on port 8100

### AI Assistant (New in v1.1)
- **Multi-Provider AI**: 7 providers (OpenAI, Anthropic, Gemini, DeepSeek, Groq, Mistral, Grok)
- **Voice Input**: Speak commands using OpenAI Whisper
- **Natural Language**: Query results, statistics, and manage events conversationally
- **Guided Wizards**: Step-by-step CRUD operations through chat
- **Context-Aware**: Understands which event you're discussing

### Payment Processing (New in v1.2)
- **Multiple Gateways**: Stripe, Braintree, Square, Authorize.net
- **Secure Storage**: API keys stored encrypted in database
- **Test Mode**: Sandbox testing for all providers
- **Gateway Management**: Admin panel for configuration

### Subscription System (New in v1.2)
- **4 Pricing Tiers**: Free, Non-Profit, Professional, Premium
- **Stripe Integration**: Checkout, billing portal, webhooks
- **Feature Gates**: Plan-based access control
- **Usage Tracking**: Monitor events and entries vs plan limits

### SMS Notifications (New in v1.3)
- **Twilio Integration**: SMS delivery for trial codes and notifications
- **Admin Configuration**: Manage credentials via admin panel
- **Test Mode**: Sandbox testing before going live

## Tech Stack

- **Backend**: Laravel 11, PHP 8.3
- **Database**: MariaDB 10.4
- **Frontend**: Blade templates, Tailwind CSS
- **Caching/Sessions**: Redis
- **Containerization**: Docker & Docker Compose

## Quick Start with Docker

### Prerequisites

- Docker Desktop installed
- Git

### Installation

1. **Clone or navigate to the project**:
   ```bash
   cd C:\xampp\htdocs\Voting_NewAndImproved
   ```

2. **Copy environment file**:
   ```bash
   cp .env.example .env
   ```

3. **Build and start containers**:
   ```bash
   docker-compose up -d --build
   ```

4. **Install PHP dependencies**:
   ```bash
   docker-compose exec app composer install
   ```

5. **Generate application key**:
   ```bash
   docker-compose exec app php artisan key:generate
   ```

6. **Run database migrations**:
   ```bash
   docker-compose exec app php artisan migrate
   ```

7. **Create admin user** (optional):
   ```bash
   docker-compose exec app php artisan tinker
   ```
   Then run:
   ```php
   App\Models\User::create([
       'first_name' => 'Admin',
       'last_name' => 'User',
       'email' => 'admin@example.com',
       'password' => bcrypt('password'),
       'role_id' => 1,
       'is_active' => true,
   ]);
   ```

8. **Install and build frontend assets**:
   ```bash
   docker-compose exec app npm install
   docker-compose exec app npm run build
   ```

9. **Access the application**:
   - Web: http://localhost:8100
   - Database: localhost:3307

### Docker Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f app

# Run artisan commands
docker-compose exec app php artisan <command>

# Access container shell
docker-compose exec app bash

# Rebuild after changes
docker-compose up -d --build
```

## Local Development (Without Docker)

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 18+
- MySQL/MariaDB
- Redis (optional)

### Installation

1. **Install PHP dependencies**:
   ```bash
   composer install
   ```

2. **Copy environment file and configure**:
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

3. **Generate application key**:
   ```bash
   php artisan key:generate
   ```

4. **Run migrations**:
   ```bash
   php artisan migrate
   ```

5. **Install frontend dependencies**:
   ```bash
   npm install
   npm run dev
   ```

6. **Start development server**:
   ```bash
   php artisan serve --port=8100
   ```

## Project Structure

```
Voting_NewAndImproved/
├── app/
│   ├── Actions/              # Single-purpose action classes
│   ├── DTOs/                 # Data Transfer Objects
│   ├── Enums/                # PHP Enums
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/        # Admin controllers
│   │   │   ├── Api/          # API controllers
│   │   │   ├── Auth/         # Authentication
│   │   │   └── Voting/       # Voting controllers
│   │   ├── Middleware/       # Custom middleware
│   │   ├── Requests/         # Form validation
│   │   └── Resources/        # API resources
│   ├── Models/               # Eloquent models
│   ├── Repositories/         # Repository pattern
│   │   ├── Contracts/        # Interfaces
│   │   └── Eloquent/         # Implementations
│   ├── Services/             # Business logic
│   └── Providers/
├── config/
│   └── voting.php            # Voting configuration
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── docker/                   # Docker configuration
├── resources/
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript
│   └── views/                # Blade templates
├── routes/
│   ├── web.php               # Web routes
│   └── api.php               # API routes
├── docker-compose.yml
├── Dockerfile
└── README.md
```

## Configuration

### Event Templates

Event templates define the structure of different event types:

- **Food Competition**: Includes Divisions, Chefs, Entries, Import, PDF
- **Photo Contest**: Includes Categories, Photographers, Judging Panel
- **General Vote**: Minimal - just Entries, Voting, Results

### Voting Types

Configure point systems per event:

| Type | Places | Points |
|------|--------|--------|
| Standard 3-2-1 | 3 | 3, 2, 1 |
| Extended 5-4-3-2-1 | 5 | 5, 4, 3, 2, 1 |
| Top-Heavy 5-3-1 | 3 | 5, 3, 1 |
| Equal Weight | N/A | 1 each |
| Weighted Judged | 3 | With multipliers |

### Modules

Enable/disable per event:

- **Divisions**: Professional/Amateur categories
- **Participants**: Manage contestants (Chefs, Artists, etc.)
- **Entries**: Items being voted on
- **Import**: Bulk data import from spreadsheets
- **Voting**: Cast votes (always enabled)
- **Results**: View results (always enabled)
- **Reports**: Generate detailed reports
- **PDF Export**: Print ballots and results
- **Judging Panel**: Weighted judge voting

### Subscription Plans

| Plan | Price | Events | Entries | Key Features |
|------|-------|--------|---------|--------------|
| Free Trial | $0/mo | 1 | 20 | Basic Voting, Real-time Results, Custom Templates, PDF Ballots |
| Non-Profit | $9.99/mo | 3 | 100 | All Voting Types, Excel Import, Email Support |
| Professional | $29.99/mo | 10 | Unlimited | Judging Panels, Advanced Analytics, Priority Support |
| Premium | $59.00/mo | Unlimited | Unlimited | White-label, API Access, Custom Integrations, Dedicated Support |

### Payment Gateways

Configure at `/admin/payment-processing`:

| Provider | Fee | Features |
|----------|-----|----------|
| Stripe | 2.9% + 30c | Cards, ACH, Apple Pay, Google Pay |
| Braintree | 2.59% + 49c | Cards, PayPal, Venmo |
| Square | 2.6% + 10c | Cards, Cash App Pay |
| Authorize.net | 2.9% + 30c | Cards, eChecks |

## API Endpoints

### Authentication

```
POST /login          - Login
POST /logout         - Logout
```

### Events

```
GET  /api/events              - List events
GET  /api/events/{id}         - Get event details
GET  /api/events/{id}/modules - Get event modules
```

### Voting

```
POST /api/voting/{event}/vote      - Cast vote
GET  /api/voting/{event}/my-votes  - Get user's votes
GET  /api/voting/{event}/has-voted - Check if voted
```

### Results

```
GET /api/results/{event}             - Get results
GET /api/results/{event}/leaderboard - Get leaderboard
```

## User Roles

| Role | Permissions |
|------|-------------|
| Administrator | Full access |
| Member | Events, Voting, Results |
| User | Voting, Results only |
| Judge | Weighted voting |

## Contributing

1. Create a feature branch
2. Make your changes
3. Run tests: `php artisan test`
4. Submit a pull request

## License

Proprietary - All rights reserved.
