# 🚀 AWS Deployment Guide for Hermosa Water District

## 📋 Prerequisites
- AWS Account with appropriate permissions
- Domain name (optional but recommended)
- Supabase database (already configured)
- Laravel application ready for deployment

## 🎯 AWS Services We'll Use
- **AWS Amplify** - For hosting the Laravel application
- **AWS Route 53** - For domain management (optional)
- **AWS Certificate Manager** - For SSL certificates
- **AWS CloudFront** - For CDN (integrated with Amplify)

## 🔧 Step 1: Prepare Your Application for AWS

### 1.1 Update Environment Configuration
Create a new environment file for AWS production:

```bash
# Copy your current env file as a template
cp .env admin/.env.aws-production
```

### 1.2 Configure Build Settings
Update your `package.json` to include production build scripts if not already present.

## 🌐 Step 2: Deploy with AWS Amplify

### 2.1 Option A: Deploy via Git Repository (Recommended)

1. **Push your code to a Git repository** (GitHub, GitLab, or AWS CodeCommit)
2. **Login to AWS Console** and navigate to AWS Amplify
3. **Create New App** → **Host web app**
4. **Connect your repository**
5. **Configure build settings**:

```yaml
# amplify.yml
version: 1
backend:
  phases:
    build:
      commands:
        - echo "No backend build required for Laravel with Supabase"
frontend:
  phases:
    preBuild:
      commands:
        - composer install --no-dev --optimize-autoloader
        - npm ci
    build:
      commands:
        - npm run build
        - php artisan config:cache
        - php artisan route:cache
        - php artisan view:cache
  artifacts:
    baseDirectory: /
    files:
      - '**/*'
  cache:
    paths:
      - node_modules/**/*
      - vendor/**/*
```

### 2.2 Option B: Manual Deploy via ZIP

1. **Prepare deployment package**:
```bash
# Run the build process
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create deployment package
zip -r hermosa-water-district.zip . -x "node_modules/*" "*.git*" "tests/*" "storage/logs/*"
```

2. **Upload to Amplify**:
   - Go to AWS Amplify Console
   - Choose "Deploy without Git"
   - Upload your ZIP file

## ⚙️ Step 3: Configure Environment Variables in AWS

In AWS Amplify Console, go to **App Settings** → **Environment Variables** and add:

```
APP_NAME=Hermosa Water District
APP_ENV=production
APP_KEY=base64:87ZG61ohphum1Y72rfhtdXAR3vT7FTNFik2sTdC39cE=
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

# Supabase Database Configuration
DB_CONNECTION=pgsql
DB_HOST=your-supabase-host
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=your-supabase-username
DB_PASSWORD=your-supabase-password

# Session and Cache
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@hermosawaterdistrict.com
MAIL_FROM_NAME="${APP_NAME}"
```

## 🔒 Step 4: Configure SSL and Domain

### 4.1 Custom Domain (Optional)
1. In Amplify Console, go to **Domain Management**
2. Add your custom domain
3. AWS will automatically provision SSL certificate via ACM

### 4.2 Route 53 Configuration (If using AWS for DNS)
1. Create hosted zone for your domain
2. Update nameservers at your domain registrar
3. Create A/AAAA records pointing to Amplify

## 📁 Step 5: File Structure for AWS Amplify

Your application should have this structure:
```
admin/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
│   ├── index.php (Laravel entry point)
│   └── ...
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
├── artisan
├── composer.json
└── amplify.yml
```

## 🔧 Step 6: Advanced Configuration

### 6.1 Custom PHP Configuration
Create `admin/.platform.app.yaml` for PHP settings:

```yaml
name: app
type: php:8.1
disk: 2048

build:
  flavor: composer

web:
  document_root: "public"
  passthru: "/index.php"

mounts:
  "storage":
    source: local
    source_path: "storage"
  "bootstrap/cache":
    source: local
    source_path: "cache"
```

### 6.2 Redirects Configuration
In Amplify Console, set up redirects for SPA behavior:

```
Source: </^[^.]+$|\.(?!(css|gif|ico|jpg|js|png|txt|svg|woff|ttf|map|json)$)([^.]+$)/>
Target: /index.php
Status: 200 (Rewrite)
```

## 🚀 Step 7: Deployment Process

### 7.1 Automated Deployment (Git-based)
1. Push changes to your repository
2. Amplify will automatically detect changes and deploy

### 7.2 Manual Deployment
1. Prepare updated ZIP package
2. Upload via Amplify Console
3. Deploy new version

## 🔍 Step 8: Testing and Monitoring

### 8.1 Test Your Deployment
- [ ] Application loads correctly
- [ ] Database connections work (Supabase)
- [ ] Authentication functions properly
- [ ] All routes are accessible
- [ ] Static assets load correctly

### 8.2 Monitor Performance
- Use AWS CloudWatch (integrated with Amplify)
- Monitor application logs
- Set up alerts for errors

## 🛠️ Troubleshooting

### Common Issues:

1. **500 Internal Server Error**
   - Check environment variables
   - Verify file permissions
   - Check application logs in Amplify

2. **Database Connection Issues**
   - Verify Supabase credentials
   - Check network connectivity
   - Ensure SSL is properly configured

3. **Static Assets Not Loading**
   - Run `npm run build` before deployment
   - Check Vite configuration
   - Verify public path settings

## 📞 Support Resources

- **AWS Amplify Documentation**: https://docs.aws.amazon.com/amplify/
- **Laravel Deployment**: https://laravel.com/docs/deployment
- **Supabase Documentation**: https://supabase.com/docs

---

**Your Laravel application with Supabase database is now ready for AWS deployment! 🎉** 