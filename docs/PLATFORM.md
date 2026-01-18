# Platform Overview

## Voting Application - New and Improved

A modern, flexible voting platform built with Laravel 11, featuring AI-powered assistance, dynamic event configuration, and multi-provider AI support.

## Technology Stack

### Backend
| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | 8.3+ | Server-side language |
| Laravel | 11.x | Web framework |
| MySQL/MariaDB | 10.4+ | Database |
| Redis | 7.x | Caching & sessions (optional) |

### Frontend
| Technology | Purpose |
|------------|---------|
| Blade Templates | Server-side templating |
| Vanilla JavaScript | Client-side interactivity |
| Tailwind CSS | Utility-first styling |
| Font Awesome | Icons |

### AI Integration
| Provider | Purpose |
|----------|---------|
| OpenAI | Chat (GPT-4o) + Voice (Whisper) |
| Anthropic | Chat (Claude) |
| Google | Chat (Gemini) |
| DeepSeek | Chat |
| Groq | Chat (fast inference) |
| Mistral | Chat |
| Grok (xAI) | Chat |

### Infrastructure
| Component | Technology |
|-----------|------------|
| Web Server | Apache (XAMPP) or Nginx |
| Containerization | Docker + Docker Compose |
| Package Manager | Composer (PHP), npm (JS) |

## Architecture

### Design Patterns
1. **MVC** - Laravel's Model-View-Controller
2. **Repository Pattern** - Data access abstraction
3. **Service Layer** - Business logic separation
4. **State Machine** - Wizard flows

### Directory Structure
```
app/
├── Actions/              # Single-purpose action classes
├── DTOs/                 # Data Transfer Objects
├── Enums/                # PHP 8.1 Enums
├── Http/
│   ├── Controllers/
│   │   ├── Admin/        # Event, User, Template management
│   │   ├── Api/          # REST API + AI Chat
│   │   ├── Auth/         # Authentication
│   │   └── Voting/       # Vote casting & results
│   ├── Middleware/       # Request filters
│   ├── Requests/         # Form validation
│   └── Resources/        # API transformers
├── Models/               # Eloquent ORM models
├── Repositories/         # Data access layer
│   ├── Contracts/        # Interfaces
│   └── Eloquent/         # Implementations
├── Services/             # Business logic
│   └── AI/               # AI service layer
└── Providers/            # Service providers

resources/
├── views/
│   ├── admin/            # Admin panel
│   ├── voting/           # Public voting
│   ├── results/          # Results display
│   ├── components/       # Reusable components
│   └── layouts/          # Page layouts
├── css/                  # Stylesheets
└── js/                   # JavaScript

database/
├── migrations/           # Schema definitions
└── seeders/              # Sample data
```

## Core Features

### Event Management
- **Templates**: 6 pre-built event types
- **Voting Types**: Configurable point systems
- **Modules**: Enable/disable features per event
- **Divisions**: Group entries (Professional/Amateur)

### Voting System
- **Ranked Voting**: 1st, 2nd, 3rd place selection
- **Approval Voting**: Select multiple entries
- **Rating Voting**: Score-based voting
- **Weighted Voting**: Judge multipliers

### AI Assistant
- **Natural Language**: Query data conversationally
- **Voice Input**: Speak commands using Whisper
- **Wizards**: Guided multi-step operations
- **Multi-Provider**: 7 AI providers supported

### Results & Analytics
- **Real-time Results**: Live vote tallying
- **Division Grouping**: Results by category
- **Leaderboards**: Ranked standings
- **Vote Breakdown**: Place-by-place counts

## Security

### Authentication
- Laravel's built-in authentication
- Password hashing with bcrypt
- Session-based authentication
- Remember me functionality

### Authorization
- Role-based access control (RBAC)
- 4 roles: Administrator, Member, User, Judge
- Middleware-based route protection

### Data Protection
- CSRF protection on all forms
- XSS prevention in Blade templates
- SQL injection prevention via Eloquent
- Encrypted API key storage

## Deployment Options

### Local Development (XAMPP)
```bash
# Start Apache and MySQL
# Navigate to http://localhost:8100
```

### Docker
```bash
docker-compose up -d
# Navigate to http://localhost:8100
```

### Production
- Configure `.env` for production
- Set `APP_ENV=production`
- Run `php artisan config:cache`
- Set up SSL/TLS

## Configuration

### Environment Variables
```env
# Application
APP_NAME="Voting System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8100

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=voting_new
DB_USERNAME=root
DB_PASSWORD=

# AI Providers (configured via admin UI)
# Keys stored encrypted in database
```

### Key Configuration Files
| File | Purpose |
|------|---------|
| `.env` | Environment variables |
| `config/voting.php` | Voting system settings |
| `config/ai.php` | AI configuration |
| `config/database.php` | Database connections |

## Performance

### Caching
- Route caching: `php artisan route:cache`
- Config caching: `php artisan config:cache`
- View caching: `php artisan view:cache`
- Query result caching with Redis

### Database Optimization
- Proper indexing on frequently queried columns
- Eager loading to prevent N+1 queries
- Generated columns for computed values

## Monitoring

### Logging
- Laravel's built-in logging
- Log levels: debug, info, warning, error
- Log files in `storage/logs/`

### Error Tracking
- Exception handling with context
- AI errors logged with provider info
- Vote casting errors tracked

## Integrations

### Current
- OpenAI (Chat + Whisper)
- Anthropic Claude
- Google Gemini
- Multiple OpenAI-compatible APIs

### Planned
- Webhook notifications
- Email notifications
- SMS notifications
- Calendar integration
