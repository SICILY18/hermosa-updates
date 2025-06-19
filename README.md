# Hermosa Water District - Backend API

Laravel backend API for the Hermosa Water District management system.

## Features

- 🔐 Admin authentication with sessions
- 👥 Staff and customer management
- 💰 Payment processing and billing
- 📢 Announcements system
- 🎟️ Ticket management
- 📊 Dashboard statistics
- 🗄️ Supabase PostgreSQL integration
- 🔄 RESTful API endpoints

## Tech Stack

- **Framework:** Laravel 9.x
- **Database:** PostgreSQL (via Supabase)
- **Authentication:** Laravel Sessions
- **Frontend Integration:** Inertia.js (for admin panel)
- **External API:** Supabase REST API

## Getting Started

### Prerequisites

- PHP 8.0 or higher
- Composer
- Node.js and npm
- PostgreSQL database (Supabase recommended)

### Local Development Setup

1. Clone the repository:
```bash
git clone <your-backend-repo-url>
cd hermosa-water-district-backend
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure your environment variables in `.env`:
```env
APP_NAME="Hermosa Water District"
APP_URL=http://localhost:8000
DB_CONNECTION=pgsql
DB_HOST=your-supabase-host
DB_DATABASE=postgres
DB_USERNAME=your-username
DB_PASSWORD=your-password
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_ROLE_KEY=your-service-role-key
FRONTEND_URL=http://localhost:3000
```

7. Run database migrations:
```bash
php artisan migrate
```

8. Seed the database (optional):
```bash
php artisan db:seed
```

9. Build frontend assets:
```bash
npm run build
```

10. Start the development server:
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

## Deployment on Render

This application is configured for deployment on Render.

### Deploy to Render

1. **Create a new Web Service on Render**
   - Connect your GitHub repository
   - Choose "PHP" as the environment
   - Use the following settings:

2. **Build Settings:**
   - Build Command: `composer install --no-dev --optimize-autoloader && php artisan config:cache && php artisan route:cache && php artisan view:cache && npm install && npm run build`
   - Start Command: `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT`

3. **Environment Variables:**
   Add all the variables from your `env.example` file to Render's environment variables section.

4. **Database Setup:**
   - Create a PostgreSQL database on Supabase
   - Add the database credentials to your Render environment variables

5. **Deploy:**
   - Push your code to GitHub
   - Render will automatically deploy your application

## API Endpoints

### Authentication
- `POST /api/admin-login` - Admin login
- `POST /api/admin-logout` - Admin logout
- `GET /api/check-auth` - Check authentication status
- `GET /api/user` - Get current user

### Dashboard
- `GET /api/dashboard/stats` - Get dashboard statistics

### Accounts Management
- `GET /api/accounts` - List all accounts
- `POST /api/accounts/staff` - Create staff account
- `PUT /api/accounts/staff/{id}` - Update staff account
- `DELETE /api/accounts/staff/{id}` - Delete staff account
- `POST /api/accounts/customer` - Create customer account
- `PUT /api/accounts/customer/{id}` - Update customer account
- `DELETE /api/accounts/customer/{id}` - Delete customer account

### Rate Management
- `GET /api/rates` - Get all rates
- `POST /api/rates` - Create new rate
- `PUT /api/rates/{id}` - Update rate
- `DELETE /api/rates/{id}` - Delete rate

### Announcements
- `GET /api/announcements` - Get all announcements
- `POST /api/announcements` - Create announcement
- `PUT /api/announcements/{id}` - Update announcement
- `DELETE /api/announcements/{id}` - Delete announcement

### Tickets
- `GET /api/tickets` - Get all tickets
- `POST /api/tickets` - Create ticket
- `GET /api/tickets/{id}` - Get specific ticket
- `PUT /api/tickets/{id}` - Update ticket
- `DELETE /api/tickets/{id}` - Delete ticket

### Payments
- `GET /api/payments` - Get all payments
- `POST /api/payments` - Create payment
- `POST /api/payments/{id}/approve` - Approve payment

## Database Schema

The application uses Supabase PostgreSQL with the following main tables:

- `staff_tb` - Staff members
- `customers_tb` - Customer accounts
- `payments` - Payment records
- `bills` - Billing information
- `rates_tb` - Water rates
- `announcements_tb` - System announcements
- `tickets_tb` - Support tickets

## Configuration

### CORS Configuration
The application is configured to accept requests from:
- Your frontend domain (set via `FRONTEND_URL`)
- All Vercel domains (`*.vercel.app`)
- Local development domains

### Session Configuration
- Sessions are stored in the database
- Session lifetime is configurable via `SESSION_LIFETIME`
- CSRF protection is enabled for all non-API routes

### Supabase Integration
The application uses Supabase for:
- PostgreSQL database hosting
- Real-time subscriptions (if needed)
- Row Level Security (RLS) policies

## Environment Variables

| Variable | Description | Required |
|----------|-------------|----------|
| `APP_KEY` | Laravel application key | Yes |
| `APP_URL` | Application URL | Yes |
| `DB_HOST` | Database host | Yes |
| `DB_DATABASE` | Database name | Yes |
| `DB_USERNAME` | Database username | Yes |
| `DB_PASSWORD` | Database password | Yes |
| `SUPABASE_URL` | Supabase project URL | Yes |
| `SUPABASE_ANON_KEY` | Supabase anonymous key | Yes |
| `SUPABASE_SERVICE_ROLE_KEY` | Supabase service role key | Yes |
| `FRONTEND_URL` | Frontend application URL | Yes |

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests (`php artisan test`)
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## License

This project is proprietary software for Hermosa Water District.

## Support

For support and questions, please contact the development team or create an issue in the repository.
