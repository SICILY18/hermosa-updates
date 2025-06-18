#!/bin/bash

echo "=================================================="
echo "    AWS DEPLOYMENT PREPARATION SCRIPT"
echo "    Hermosa Water District - Laravel Application"
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
    print_error "artisan file not found. Please run this script from the Laravel project root (admin folder)"
    exit 1
fi

print_status "Starting AWS deployment preparation..."

# 1. Install/Update Dependencies
print_status "Installing PHP dependencies..."
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

# Generate optimized files
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Set proper permissions
print_status "Setting proper file permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 5. Verify critical files exist
print_status "Verifying deployment readiness..."

required_files=(
    "public/index.php"
    "amplify.yml"
    "env-aws-production-template.txt"
    "composer.json"
    "package.json"
)

for file in "${required_files[@]}"; do
    if [[ ! -f "$file" ]]; then
        print_error "Required file missing: $file"
        exit 1
    fi
done

# 6. Create deployment package (optional)
print_status "Creating deployment package..."

# Create a temporary directory for the package
mkdir -p deployment-package

# Copy files to deployment package
rsync -av --exclude='node_modules' \
          --exclude='.git' \
          --exclude='tests' \
          --exclude='storage/logs/*' \
          --exclude='storage/framework/cache/*' \
          --exclude='storage/framework/sessions/*' \
          --exclude='storage/framework/views/*' \
          --exclude='.env' \
          ./ deployment-package/

# Copy the AWS environment template as .env
cp env-aws-production-template.txt deployment-package/.env

print_status "Creating deployment ZIP file..."
cd deployment-package
zip -r ../hermosa-water-district-aws.zip . -q
cd ..

# Clean up
rm -rf deployment-package

print_status "Deployment preparation completed successfully!"
echo ""
echo "=================================================="
echo "📦 DEPLOYMENT PACKAGE READY"
echo "=================================================="
echo "File: hermosa-water-district-aws.zip"
echo ""
echo "🚀 NEXT STEPS:"
echo "1. Update your Supabase credentials in AWS Amplify environment variables"
echo "2. Upload the ZIP file to AWS Amplify or push to your Git repository"
echo "3. Configure your custom domain in AWS Amplify (optional)"
echo "4. Test your deployment thoroughly"
echo ""
echo "📚 For detailed instructions, see: AWS_DEPLOYMENT_GUIDE.md"
echo "==================================================" 