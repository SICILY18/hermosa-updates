# 🚀 AWS Amplify - Complete Beginner's Tutorial

## 📋 What You'll Need Before Starting
- [ ] AWS Account (we'll help you create one)
- [ ] Your Laravel application files ready
- [ ] Supabase database credentials
- [ ] A web browser
- [ ] About 30-60 minutes

---

## 🎯 **STEP 1: Create Your AWS Account**

### 1.1 Go to AWS Website
1. Open your web browser and go to: **https://aws.amazon.com**
2. Click **"Create an AWS Account"** (top right corner)

### 1.2 Fill Out Account Information
1. **Email address**: Enter your email
2. **Password**: Create a strong password
3. **AWS account name**: Enter "Hermosa Water District" or your preferred name
4. Click **"Continue"**

### 1.3 Contact Information
1. **Account Type**: Select **"Personal"** (unless you have a business)
2. Fill out your personal information
3. **Phone number**: Enter your phone number
4. Click **"Create Account and Continue"**

### 1.4 Payment Information
1. Add a credit/debit card
2. **Don't worry**: AWS has a generous free tier, and Amplify has free hosting for small applications
3. Your card will only be charged if you exceed free tier limits

### 1.5 Identity Verification
1. AWS will call or text you with a verification code
2. Enter the code when prompted
3. Select **"Basic Plan (Free)"** for support plan

### 1.6 Wait for Activation
- AWS will send you an email when your account is ready (usually within a few minutes)

---

## 🚀 **STEP 2: Access AWS Amplify Console**

### 2.1 Sign Into AWS Console
1. Go to **https://console.aws.amazon.com**
2. Click **"Sign In to the Console"**
3. Enter your email and password

### 2.2 Find AWS Amplify
1. In the AWS Console, you'll see a search bar at the top
2. Type **"Amplify"** in the search box
3. Click on **"AWS Amplify"** from the dropdown results

### 2.3 First Time in Amplify
- You'll see the Amplify welcome page
- Click **"Get Started"** under **"Host your web app"**

---

## 📂 **STEP 3: Prepare Your Application**

### Option A: Using Git Repository (Recommended)

#### 3.1 Upload Your Code to GitHub/GitLab
1. **If you don't have a GitHub account**:
   - Go to **https://github.com** and create a free account
   
2. **Create a new repository**:
   - Click **"New"** button (green button)
   - Repository name: `hermosa-water-district`
   - Make it **Private** (recommended)
   - Click **"Create repository"**

3. **Upload your files**:
   - You can either use Git commands or GitHub's web interface
   - **Easy way**: Click **"uploading an existing file"** link
   - Drag and drop your entire `admin` folder contents
   - Write commit message: "Initial Laravel application upload"
   - Click **"Commit changes"**

### Option B: ZIP Upload (Alternative)
1. Run the deployment script we created:
   ```bash
   cd admin
   chmod +x deploy-to-aws.sh
   ./deploy-to-aws.sh
   ```
2. This creates `hermosa-water-district-aws.zip`

---

## 🔗 **STEP 4: Connect Your Application to Amplify**

### 4.1 Choose Your Deployment Method

**If using Git (Recommended):**
1. In Amplify console, select **"GitHub"** 
2. Click **"Continue"**
3. You'll be redirected to GitHub to authorize AWS
4. Click **"Authorize aws-amplify"**
5. Select your repository: `hermosa-water-district`
6. Select branch: `main` (or `master`)
7. Click **"Next"**

**If using ZIP upload:**
1. Select **"Deploy without Git provider"**
2. Click **"Continue"**
3. Upload your `hermosa-water-district-aws.zip` file

### 4.2 Configure Build Settings
1. **App name**: Enter `hermosa-water-district`
2. **Environment**: Leave as `main`
3. **Build settings**: AWS should detect this automatically
4. If not, click **"Edit"** and paste this:

```yaml
version: 1
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
```

5. Click **"Next"**

---

## ⚙️ **STEP 5: Configure Environment Variables**

### 5.1 Add Environment Variables
1. In the build settings page, scroll down to **"Environment variables"**
2. Click **"Advanced settings"**
3. Add these variables one by one:

**Click "Add environment variable" for each:**

| Variable | Value |
|----------|--------|
| `APP_NAME` | `Hermosa Water District` |
| `APP_ENV` | `production` |
| `APP_KEY` | `base64:87ZG61ohphum1Y72rfhtdXAR3vT7FTNFik2sTdC39cE=` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://main.d1234567890.amplifyapp.com` (you'll get this after deployment) |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `your-supabase-host.supabase.co` |
| `DB_PORT` | `5432` |
| `DB_DATABASE` | `postgres` |
| `DB_USERNAME` | `your-supabase-username` |
| `DB_PASSWORD` | `your-supabase-password` |

### 5.2 Get Your Supabase Credentials
**To find your Supabase credentials:**
1. Go to **https://supabase.com**
2. Sign in to your account
3. Select your project
4. Go to **"Settings"** → **"Database"**
5. Copy the connection details:
   - **Host**: Found in connection string
   - **Database**: `postgres` (default)
   - **Username**: Found in connection string  
   - **Password**: Your database password

---

## 🚀 **STEP 6: Deploy Your Application**

### 6.1 Start Deployment
1. After configuring everything, click **"Save and deploy"**
2. AWS will start building your application
3. You'll see a progress screen with these phases:
   - ✅ Provision
   - ✅ Build  
   - ✅ Deploy
   - ✅ Verify

### 6.2 Monitor Build Process
- The build usually takes 3-5 minutes
- You can click on each phase to see detailed logs
- If there are errors, they'll be shown in red

### 6.3 Success!
- When complete, you'll see all phases with green checkmarks
- You'll get a URL like: `https://main.d1234567890.amplifyapp.com`

---

## 🔧 **STEP 7: Test Your Application**

### 7.1 Access Your Application
1. Click on the provided URL
2. Your Laravel application should load
3. Test key features:
   - [ ] Homepage loads
   - [ ] Login works
   - [ ] Database connections work
   - [ ] Admin panel accessible

### 7.2 Update APP_URL
1. Copy your actual Amplify URL
2. Go back to Amplify console
3. Click **"Environment variables"** in the left menu
4. Edit the `APP_URL` variable with your real URL
5. Save changes
6. Amplify will automatically redeploy

---

## 🌐 **STEP 8: Add Custom Domain (Optional)**

### 8.1 Add Your Domain
1. In Amplify console, click **"Domain management"** (left sidebar)
2. Click **"Add domain"**
3. Enter your domain: `hermosawaterdistrict.com`
4. Click **"Configure domain"**

### 8.2 DNS Configuration
1. AWS will provide nameserver records
2. Go to your domain registrar (where you bought the domain)
3. Update the nameservers to point to AWS
4. Wait 24-48 hours for DNS propagation

---

## 🛠️ **Troubleshooting Common Issues**

### Issue 1: Build Failed
**Solution:**
1. Check build logs in Amplify console
2. Verify your `amplify.yml` file is correct
3. Make sure all required files are uploaded

### Issue 2: 500 Error
**Solution:**
1. Check environment variables are set correctly
2. Verify Supabase credentials
3. Check application logs

### Issue 3: Database Connection Failed
**Solution:**
1. Verify Supabase is running
2. Check database credentials
3. Ensure your Supabase project allows connections

---

## 📞 **Getting Help**

### AWS Support
- **Free tier**: Community forums
- **Paid support**: Available if needed

### Documentation
- **AWS Amplify**: https://docs.aws.amazon.com/amplify/
- **Laravel**: https://laravel.com/docs

---

## 🎉 **Congratulations!**

You've successfully deployed your Laravel application on AWS! 

**Your application is now:**
- ✅ Hosted on AWS Amplify
- ✅ Connected to Supabase database  
- ✅ Secured with HTTPS
- ✅ Automatically scalable
- ✅ Backed up and reliable

**Next steps:**
- Test all application features thoroughly
- Set up monitoring and alerts
- Consider setting up automated deployments

---

**Need help? Don't hesitate to ask! 🚀** 