# 🚀 AWS Elastic Beanstalk Deployment Guide
## Laravel + Supabase + React Native Compatible

### 📋 **Why Elastic Beanstalk is Perfect for Your Setup**

- ✅ **Laravel Ready**: Native PHP support with Composer
- ✅ **Supabase Compatible**: Perfect for PostgreSQL connections
- ✅ **React Native Friendly**: RESTful API endpoints work seamlessly
- ✅ **Auto-scaling**: Handles traffic spikes automatically
- ✅ **Load Balancer**: Built-in for high availability

---

## 🎯 **Architecture Overview**

```
React Native App ↔ AWS Elastic Beanstalk (Laravel API) ↔ Supabase Database
                 ↕
            Direct Supabase Connection (optional)
```

**Your React Native app can:**
- Connect to your Laravel API on Elastic Beanstalk
- Also connect directly to Supabase for real-time features
- Use Laravel for complex business logic and Supabase for data

---

## 🚀 **Step 1: Prepare Your Application**

### 1.1 Create Elastic Beanstalk Configuration

First, let's create the configuration files:

#### Create `.ebextensions` folder and configuration:

Create these files in your admin folder:

**`.ebextensions/01-php.config`**
**`.ebextensions/02-composer.config`**
**`.platform/nginx/nginx.conf`** (for routing)

### 1.2 Update Environment File

We'll create a production environment file specifically for Elastic Beanstalk.

---

## 🔧 **Step 2: AWS Elastic Beanstalk Setup**

### 2.1 Access Elastic Beanstalk Console

1. **Go to AWS Console**: https://console.aws.amazon.com
2. **Search for "Elastic Beanstalk"**
3. **Click "Create application"**

### 2.2 Application Configuration

**Application Information:**
- **Application name**: `hermosa-water-district`
- **Platform**: `PHP`
- **Platform version**: `PHP 8.1 running on 64bit Amazon Linux 2`
- **Application code**: Upload your code (we'll create the package)

### 2.3 Instance Configuration

**Choose instance type:**
- **For testing**: `t3.micro` (Free tier eligible)
- **For production**: `t3.small` or higher

---

## 📦 **Step 3: Prepare Deployment Package**

### 3.1 Install Dependencies and Build

Run these commands in your admin folder:

```bash
# Install PHP dependencies (production only)
composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
npm ci
npm run build

# Clear and cache Laravel configs
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3.2 Create Deployment ZIP

We'll create a deployment-ready package:

**Exclude these files/folders:**
- `node_modules/`
- `.git/`
- `tests/`
- `storage/logs/*`
- `.env` (we'll set environment variables in AWS)

---

## ⚙️ **Step 4: Environment Variables Configuration**

In **Elastic Beanstalk Console** → **Configuration** → **Software**, add these environment variables:

### **Laravel Core Variables**
```
APP_NAME=Hermosa Water District
APP_ENV=production
APP_KEY=base64:87ZG61ohphum1Y72rfhtdXAR3vT7FTNFik2sTdC39cE=
APP_DEBUG=false
APP_URL=http://your-eb-env.region.elasticbeanstalk.com
```

### **Database (Supabase) Variables**
```
DB_CONNECTION=pgsql
DB_HOST=your-project.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=your-supabase-username
DB_PASSWORD=your-supabase-password
DB_SSLMODE=require
```

### **Supabase API Variables** (for direct connections)
```
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_ROLE_KEY=your-service-role-key
```

### **Session & Cache Variables**
```
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync
```

### **CORS Configuration** (Important for React Native)
```
CORS_ALLOWED_ORIGINS=*
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With
```

---

## 🔄 **Step 5: React Native Integration**

### 5.1 API Endpoints

Your Laravel app will provide RESTful API endpoints that your React Native app can consume:

**Example API endpoints:**
```
GET  /api/customers
POST /api/customers
GET  /api/payments
POST /api/payments
GET  /api/bills
```

### 5.2 React Native Configuration

In your React Native app, configure the API base URL:

```javascript
// config/api.js
const API_BASE_URL = 'http://your-eb-env.region.elasticbeanstalk.com/api';

// For production, use your custom domain:
// const API_BASE_URL = 'https://api.hermosawaterdistrict.com/api';
```

### 5.3 Supabase Direct Connection (Optional)

Your React Native app can also connect directly to Supabase for:
- Real-time subscriptions
- File uploads
- Authentication (if needed)

```javascript
// React Native Supabase client
import { createClient } from '@supabase/supabase-js'

const supabaseUrl = 'https://your-project.supabase.co'
const supabaseAnonKey = 'your-anon-key'

export const supabase = createClient(supabaseUrl, supabaseAnonKey)
```

---

## 🔒 **Step 6: Security & CORS Configuration**

### 6.1 Laravel CORS Setup

Update `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'], // Configure for production
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
```

### 6.2 API Rate Limiting

In `routes/api.php`:

```php
Route::middleware(['throttle:api'])->group(function () {
    // Your API routes
});
```

---

## 🌐 **Step 7: Domain Configuration (Optional)**

### 7.1 Custom Domain Setup

1. **In Elastic Beanstalk Console**:
   - Go to **Configuration** → **Load balancer**
   - Add SSL certificate via **AWS Certificate Manager**

2. **Update DNS**:
   - Point your domain to the Elastic Beanstalk environment
   - Example: `api.hermosawaterdistrict.com`

### 7.2 Update APP_URL

Once you have a custom domain:
```
APP_URL=https://api.hermosawaterdistrict.com
```

---

## 📱 **Step 8: Mobile App Integration**

### 8.1 HTTP Client Setup (React Native)

```javascript
// services/api.js
import axios from 'axios';

const api = axios.create({
  baseURL: 'https://your-eb-env.region.elasticbeanstalk.com/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Example API calls
export const getCustomers = () => api.get('/customers');
export const createPayment = (paymentData) => api.post('/payments', paymentData);
```

### 8.2 Error Handling

```javascript
// Handle API errors
api.interceptors.response.use(
  response => response,
  error => {
    console.error('API Error:', error.response?.data);
    return Promise.reject(error);
  }
);
```

---

## 🔍 **Step 9: Testing Your Setup**

### 9.1 Test Laravel API

```bash
# Test endpoints
curl https://your-eb-env.region.elasticbeanstalk.com/api/customers
curl https://your-eb-env.region.elasticbeanstalk.com/api/health
```

### 9.2 Test Database Connection

Create a test endpoint in Laravel:

```php
// routes/api.php
Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => 'Database connected successfully']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Database connection failed'], 500);
    }
});
```

### 9.3 Test from React Native

```javascript
// Test API connectivity
const testConnection = async () => {
  try {
    const response = await fetch('https://your-eb-env.region.elasticbeanstalk.com/api/test-db');
    const data = await response.json();
    console.log('API Test:', data);
  } catch (error) {
    console.error('Connection failed:', error);
  }
};
```

---

## 🛠️ **Troubleshooting**

### Common Issues:

**1. CORS Errors in React Native**
- Solution: Configure CORS properly in Laravel
- Add OPTIONS method support

**2. Database Connection Issues**
- Solution: Verify Supabase credentials
- Check security group settings

**3. SSL Certificate Issues**
- Solution: Use AWS Certificate Manager
- Ensure domain validation

**4. Performance Issues**
- Solution: Enable Laravel caching
- Use CloudFront CDN

---

## 📊 **Monitoring & Maintenance**

### 9.1 CloudWatch Monitoring

- **Application logs**: Available in Elastic Beanstalk console
- **Database monitoring**: Available in Supabase dashboard
- **API performance**: Monitor response times

### 9.2 Auto-scaling Configuration

Configure auto-scaling based on:
- CPU utilization
- Request count
- Response time

---

## 🎉 **Benefits of This Architecture**

**For Laravel Backend:**
- ✅ Robust hosting environment
- ✅ Auto-scaling capabilities
- ✅ Easy deployments
- ✅ Built-in monitoring

**For React Native App:**
- ✅ Fast API responses
- ✅ RESTful endpoints
- ✅ Optional Supabase direct access
- ✅ Offline capability support

**For Supabase:**
- ✅ Maintains your existing database
- ✅ Real-time features available
- ✅ Built-in authentication
- ✅ File storage capabilities

---

**Ready to deploy? Let's create the configuration files and deployment package!** 🚀 