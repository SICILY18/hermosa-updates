# 📋 AWS Migration Checklist

## ✅ Pre-Migration Tasks

### 1. Backup Your Data
- [ ] Export your Supabase data (already using Supabase, so this is your primary database)
- [ ] Download all files from current hosting
- [ ] Save current environment variables
- [ ] Document current domain configuration

### 2. Prepare Your Application
- [ ] Update `amplify.yml` configuration file
- [ ] Create AWS environment template (`env-aws-production-template.txt`)
- [ ] Test your application locally with production settings
- [ ] Ensure all dependencies are up to date

### 3. Clean Up Hostinger Files ✅ COMPLETED
- [x] Remove `debug-hostinger.php`
- [x] Remove `deploy-to-hostinger.bat`
- [x] Remove `HOSTINGER_DEPLOYMENT_GUIDE.md`
- [x] Remove `env-mysql-production.txt`
- [x] Remove `env-production-sample.txt`

## 🚀 AWS Deployment Tasks

### 1. Set Up AWS Amplify
- [ ] Create AWS account (if not already have one)
- [ ] Navigate to AWS Amplify console
- [ ] Choose deployment method:
  - [ ] Option A: Connect Git repository (recommended)
  - [ ] Option B: Manual upload via ZIP file

### 2. Configure Build Settings
- [ ] Upload/configure `amplify.yml` file
- [ ] Set up environment variables in Amplify console
- [ ] Configure build settings for Laravel

### 3. Environment Variables Setup
Copy these variables to AWS Amplify Environment Variables:

```
APP_NAME=Hermosa Water District
APP_ENV=production
APP_KEY=base64:87ZG61ohphum1Y72rfhtdXAR3vT7FTNFik2sTdC39cE=
APP_DEBUG=false
APP_URL=https://your-app.amplifyapp.com

# Supabase Database
DB_CONNECTION=pgsql
DB_HOST=your-supabase-host.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=your-supabase-username
DB_PASSWORD=your-supabase-password

# Additional Supabase Config
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_ROLE_KEY=your-service-role-key
```

### 4. Domain Configuration
- [ ] Configure custom domain in AWS Amplify (optional)
- [ ] Update DNS records to point to AWS
- [ ] Enable SSL certificate (automatic with Amplify)

## 🔍 Testing Tasks

### 1. Functional Testing
- [ ] Test application loading
- [ ] Test database connectivity (Supabase)
- [ ] Test user authentication
- [ ] Test all major features:
  - [ ] Customer management
  - [ ] Payment processing
  - [ ] Admin dashboard
  - [ ] Reports generation
  - [ ] Ticket system

### 2. Performance Testing
- [ ] Test page load speeds
- [ ] Test database query performance
- [ ] Test asset loading (CSS, JS, images)

### 3. Security Testing
- [ ] Verify SSL certificate is working
- [ ] Test secure database connections
- [ ] Verify environment variables are properly set

## 📊 Post-Migration Tasks

### 1. DNS and Domain
- [ ] Update domain nameservers (if using AWS Route 53)
- [ ] Verify domain is pointing to new AWS hosting
- [ ] Test domain accessibility from different locations

### 2. Monitoring Setup
- [ ] Set up AWS CloudWatch monitoring
- [ ] Configure error alerting
- [ ] Set up log monitoring

### 3. Documentation Updates
- [ ] Update deployment documentation
- [ ] Update README with new deployment instructions
- [ ] Document new environment setup process

## 🛠️ Rollback Plan (if needed)

- [ ] Keep old Hostinger hosting active for 30 days
- [ ] Document rollback procedures
- [ ] Have backup of all configuration files

## 📞 Support Contacts

- **AWS Amplify Support**: Available through AWS Console
- **Supabase Support**: https://supabase.com/support
- **Domain Registrar**: Contact your domain provider for DNS changes

## 🎯 Success Criteria

Migration is successful when:
- [ ] Application loads correctly on new AWS URL
- [ ] All features work as expected
- [ ] Database connections are stable
- [ ] Performance is equal or better than before
- [ ] SSL certificate is active
- [ ] Domain points to new hosting (if applicable)

---

**Estimated Migration Time**: 2-4 hours
**Recommended Migration Window**: During low-traffic hours 