<?php
/**
 * Database Connection Test for Supabase
 * Run this file directly to test your database connection
 */

echo "<h2>🔍 Supabase Database Connection Test</h2>";
echo "<hr>";

// Test 1: Check if PostgreSQL extension is available
echo "<h3>1. PostgreSQL Extension Check</h3>";
if (extension_loaded('pdo_pgsql')) {
    echo "✅ PDO PostgreSQL extension is loaded<br>";
} else {
    echo "❌ PDO PostgreSQL extension is NOT loaded<br>";
    echo "⚠️ Contact your hosting provider to enable PostgreSQL support<br>";
}

echo "<br>";

// Test 2: Direct PDO connection test
echo "<h3>2. Direct Database Connection Test</h3>";

$host = 'db.bpdfqqvnpjpvrqpgpoqf.supabase.co';
$port = '5432';
$dbname = 'postgres';
$user = 'postgres';
$password = 'Thesis2025$';

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "✅ Database connection successful!<br>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "✅ Database version: " . htmlspecialchars($version) . "<br>";
    
    // Test if tables exist
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "✅ Found " . count($tables) . " tables:<br>";
    foreach ($tables as $table) {
        echo "   - " . htmlspecialchars($table) . "<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed:<br>";
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<br>";
    echo "<strong>Common solutions:</strong><br>";
    echo "1. Check if PostgreSQL is enabled on your hosting<br>";
    echo "2. Verify database credentials<br>";
    echo "3. Check if your hosting IP is whitelisted in Supabase<br>";
    echo "4. Ensure SSL connection is properly configured<br>";
}

echo "<br>";

// Test 3: Laravel Configuration Test (if Laravel is available)
echo "<h3>3. Laravel Environment Test</h3>";

if (file_exists('.env')) {
    echo "✅ .env file exists<br>";
    
    // Try to load Laravel if available
    if (file_exists('vendor/autoload.php')) {
        echo "✅ Vendor autoload exists<br>";
        
        try {
            require_once 'vendor/autoload.php';
            
            if (class_exists('Illuminate\Support\Facades\DB')) {
                echo "✅ Laravel loaded successfully<br>";
                
                // Test Laravel database connection
                try {
                    $app = require_once 'bootstrap/app.php';
                    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
                    $kernel->bootstrap();
                    
                    $connection = \Illuminate\Support\Facades\DB::connection();
                    $pdo = $connection->getPdo();
                    echo "✅ Laravel database connection successful!<br>";
                    
                } catch (Exception $e) {
                    echo "❌ Laravel database connection failed:<br>";
                    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
                }
            }
        } catch (Exception $e) {
            echo "❌ Could not load Laravel:<br>";
            echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
        }
    } else {
        echo "❌ vendor/autoload.php not found - run 'composer install'<br>";
    }
} else {
    echo "❌ .env file not found<br>";
    echo "⚠️ Create a .env file with your database configuration<br>";
}

echo "<br>";

// Test 4: File Permissions Check
echo "<h3>4. File Permissions Check</h3>";

$paths = [
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'bootstrap/cache'
];

foreach ($paths as $path) {
    if (is_dir($path)) {
        if (is_writable($path)) {
            echo "✅ {$path} is writable<br>";
        } else {
            echo "❌ {$path} is not writable - set permissions to 755<br>";
        }
    } else {
        echo "❌ {$path} directory does not exist<br>";
    }
}

echo "<br>";
echo "<hr>";
echo "<p><strong>🔧 Next Steps:</strong></p>";
echo "<ul>";
echo "<li>If database connection fails, check with your hosting provider about PostgreSQL support</li>";
echo "<li>Ensure your Supabase project allows connections from your hosting IP</li>";
echo "<li>Check Laravel error logs in storage/logs/ for detailed error messages</li>";
echo "<li>Verify all file permissions are correctly set</li>";
echo "</ul>";
?> 