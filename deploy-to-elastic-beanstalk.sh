#!/bin/bash

echo "=================================================="
echo "    AWS ELASTIC BEANSTALK DEPLOYMENT SCRIPT"
echo "    Laravel + Supabase + React Native Compatible"
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the correct directory
if [[ ! -f "artisan" ]]; then
    print_error "artisan file not found. Please run this script from the Laravel project root"
    exit 1
fi

print_status "Starting Elastic Beanstalk deployment preparation..."

# 1. Install/Update Dependencies
print_status "Installing PHP dependencies (production)..."
composer install --no-dev --optimize-autoloader --no-interaction

if [[ $? -ne 0 ]]; then
    print_error "Composer install failed"
    exit 1
fi

print_status "Installing Node.js dependencies..."
npm ci --only=production

if [[ $? -ne 0 ]]; then
    print_error "npm install failed"
    exit 1
fi

# 2. Build assets
print_status "Building frontend assets..."
npm run build

if [[ $? -ne 0 ]]; then
    print_error "npm run build failed"
    exit 1
fi

# 3. Laravel optimizations
print_status "Running Laravel optimizations..."

# Clear any existing cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 4. Set proper permissions
print_status "Setting proper file permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 5. Verify critical files exist
print_status "Verifying deployment readiness..."

required_files=(
    "public/index.php"
    ".ebextensions/01-php.config"
    ".ebextensions/02-composer.config"
    ".ebextensions/03-cors.config"
    ".platform/nginx/nginx.conf"
    "composer.json"
    "package.json"
)

for file in "${required_files[@]}"; do
    if [[ ! -f "$file" ]]; then
        print_error "Required file missing: $file"
        exit 1
    fi
done

# 6. Create deployment package
print_status "Creating Elastic Beanstalk deployment package..."

# Create a temporary directory for the package
mkdir -p eb-deployment-package

print_status "Copying files to deployment package..."

# Copy files to deployment package, excluding unnecessary files
rsync -av --exclude='node_modules' \
          --exclude='.git' \
          --exclude='tests' \
          --exclude='storage/logs/*' \
          --exclude='storage/framework/cache/*' \
          --exclude='storage/framework/sessions/*' \
          --exclude='storage/framework/views/*' \
          --exclude='.env' \
          --exclude='*.zip' \
          --exclude='eb-deployment-package' \
          ./ eb-deployment-package/

# Create important directories that might be excluded
mkdir -p eb-deployment-package/storage/logs
mkdir -p eb-deployment-package/storage/framework/cache
mkdir -p eb-deployment-package/storage/framework/sessions  
mkdir -p eb-deployment-package/storage/framework/views

print_status "Creating deployment ZIP file..."
cd eb-deployment-package
zip -r ../hermosa-water-district-eb.zip . -q
cd ..

# Clean up
rm -rf eb-deployment-package

print_status "Deployment preparation completed successfully!"
echo ""
echo "=================================================="
echo "📦 ELASTIC BEANSTALK DEPLOYMENT PACKAGE READY"
echo "=================================================="
echo "File: hermosa-water-district-eb.zip"
echo "Size: $(du -h hermosa-water-district-eb.zip | cut -f1)"
echo ""
echo "🚀 NEXT STEPS:"
echo "1. Go to AWS Elastic Beanstalk Console"
echo "2. Create new application: 'hermosa-water-district'"
echo "3. Choose Platform: PHP 8.1"
echo "4. Upload the ZIP file: hermosa-water-district-eb.zip"
echo "5. Configure environment variables (see guide)"
echo "6. Deploy and test your application"
echo ""
echo "📚 For detailed instructions, see:"
echo "   - AWS_ELASTIC_BEANSTALK_COMPLETE_GUIDE.md"
echo ""
echo "🌐 COMPATIBILITY:"
echo "✅ Laravel Backend API"
echo "✅ Supabase Database" 
echo "✅ React Native Mobile App"
echo "✅ CORS Configured"
echo "✅ Auto-scaling Ready"
echo "==================================================" 