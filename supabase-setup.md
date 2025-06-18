# Supabase Integration Setup Guide

## 1. Environment Configuration

Create or update your `.env` file with the following Supabase configuration:

```env
# App Configuration
APP_NAME=Admin
APP_ENV=local
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost

# Supabase Configuration
SUPABASE_URL=https://your-project-ref.supabase.co
SUPABASE_ANON_KEY=your_supabase_anon_key
SUPABASE_SERVICE_ROLE_KEY=your_supabase_service_role_key
SUPABASE_JWT_SECRET=your_jwt_secret

# Database Configuration (Supabase PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=db.your-project-ref.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_db_password
DB_SSLMODE=require

# Other configurations remain the same...
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

## 2. How to get your Supabase credentials:

1. Go to your Supabase project dashboard
2. Navigate to Settings > API
3. Copy the following:
   - **Project URL** → SUPABASE_URL
   - **Anon Public Key** → SUPABASE_ANON_KEY
   - **Service Role Key** → SUPABASE_SERVICE_ROLE_KEY (keep this secret!)

4. For Database credentials:
   - Go to Settings > Database
   - Copy the connection details
   - **Host** → DB_HOST
   - **Database** → DB_DATABASE (usually 'postgres')
   - **Username** → DB_USERNAME (usually 'postgres')
   - **Password** → Your database password

## 3. Required Tables in Supabase

Make sure you have the following tables in your Supabase database:

### staff_tb
```sql
CREATE TABLE staff_tb (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    role VARCHAR(50) NOT NULL CHECK (role IN ('admin', 'bill handler', 'meter handler')),
    address TEXT NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);
```

### customers_tb
```sql
CREATE TABLE customers_tb (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    customer_type VARCHAR(50) NOT NULL CHECK (customer_type IN ('residential', 'commercial', 'government')),
    address TEXT NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    account_number VARCHAR(20) UNIQUE NOT NULL,
    meter_number VARCHAR(9) UNIQUE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);
```

### payments
```sql
CREATE TABLE payments (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    customer_id INTEGER REFERENCES customers_tb(id),
    bill_id INTEGER,
    amount DECIMAL(10,2) NOT NULL,
    payment_type VARCHAR(20) NOT NULL CHECK (payment_type IN ('Full', 'Partial')),
    payment_method VARCHAR(50) NOT NULL,
    account_number VARCHAR(20) NOT NULL,
    meter_number VARCHAR(9) NOT NULL,
    proof_of_payment TEXT,
    status VARCHAR(20) DEFAULT 'Pending' CHECK (status IN ('Pending', 'Approved', 'Rejected')),
    remaining_balance DECIMAL(10,2) DEFAULT 0,
    approved_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);
```

### bills
```sql
CREATE TABLE bills (
    id SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES customers_tb(id),
    total_amount DECIMAL(10,2) NOT NULL,
    remaining_balance DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'Unpaid' CHECK (status IN ('Unpaid', 'Partial', 'Paid')),
    due_date DATE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);
```

### rates_tb
```sql
CREATE TABLE rates_tb (
    id SERIAL PRIMARY KEY,
    customer_type VARCHAR(50) NOT NULL,
    rate_per_unit DECIMAL(10,4) NOT NULL,
    effective_date DATE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);
```

### announcements_tb
```sql
CREATE TABLE announcements_tb (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    duration INTEGER DEFAULT 30,
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);
```

## 4. Authentication Setup

The system now uses Supabase Auth for user authentication. Staff members will need to be created in both:
1. Supabase Auth (for login credentials)
2. staff_tb table (for role and profile information)

## 5. Installation Steps

1. Copy the environment configuration above to your `.env` file
2. Replace the placeholder values with your actual Supabase credentials
3. Run: `php artisan config:cache`
4. Run: `php artisan key:generate` (if you haven't already)
5. Test the connection by logging in with your admin credentials

## 6. Creating Admin Users

To create admin users, use the `createStaff` endpoint which will:
1. Create the user in Supabase Auth
2. Add the staff record to your database

## 7. Frontend Updates

The login form now expects:
- **Email** instead of username
- **Password** (same as before)

Update your frontend authentication to use email-based login.

## 8. Testing

1. Create a test admin user using the createStaff endpoint
2. Try logging in with the email and password
3. Verify that all database operations work correctly

## Troubleshooting

- **Connection Issues**: Check your Supabase URL and API keys
- **Database Errors**: Verify your database credentials and SSL settings
- **Authentication Errors**: Ensure your service role key is correct
- **CORS Issues**: Configure CORS settings in Supabase dashboard if needed 