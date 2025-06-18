<?php
/**
 * MySQL Connection Test for Hermosa Water District
 * Run this after migrating from Supabase to MySQL
 */

echo "<h2>🔍 MySQL Database Connection Test</h2>";
echo "<hr>";

// Test 1: Check if MySQL extension is available
echo "<h3>1. MySQL Extension Check</h3>";
if (extension_loaded('pdo_mysql')) {
    echo "✅ PDO MySQL extension is loaded<br>";
} else {
    echo "❌ PDO MySQL extension is NOT loaded<br>";
}

echo "<br>";

// Test 2: Direct MySQL connection test
echo "<h3>2. MySQL Database Connection Test</h3>";

// Your actual MySQL credentials from Hostinger
$host = 'localhost';  // Host should be localhost for Hostinger
$port = '3306';
$dbname = 'u604006452_hermosa';  // Your database name
$user = 'u604006452_hermosa_dev';  // Your username  
$password = 'Thesis2025$';  // Your password

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    echo "✅ MySQL connection successful!<br>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetchColumn();
    echo "✅ MySQL version: " . htmlspecialchars($version) . "<br>";
    
    // Test if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "✅ Found " . count($tables) . " tables:<br>";
    foreach ($tables as $table) {
        echo "   - " . htmlspecialchars($table) . "<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ MySQL connection failed:<br>";
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<br>";
    echo "<strong>Solutions:</strong><br>";
    echo "1. Check MySQL credentials in this file<br>";
    echo "2. Verify database exists on Hostinger<br>";
    echo "3. Make sure username has access to database<br>";
}

echo "<br>";

// Test 3: Check record counts (if connection successful)
if (isset($pdo)) {
    echo "<h3>3. Data Migration Verification</h3>";
    
    $tables_to_check = [
        'users',
        'admin', 
        'customers_tb',
        'staff_tb',
        'bills',
        'payments',
        'rates_tb',
        'announcements_tb'
    ];
    
    foreach ($tables_to_check as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $count = $stmt->fetchColumn();
            echo "✅ {$table}: {$count} records<br>";
        } catch (PDOException $e) {
            echo "❌ {$table}: Error - " . htmlspecialchars($e->getMessage()) . "<br>";
        }
    }
    
    echo "<br>";
    
    // Test 4: Sample data check
    echo "<h3>4. Sample Data Check</h3>";
    
    try {
        // Check if initial customer exists
        $stmt = $pdo->query("SELECT name, email, account_number FROM customers_tb LIMIT 1");
        $customer = $stmt->fetch();
        
        if ($customer) {
            echo "✅ Sample customer found:<br>";
            echo "   - Name: " . htmlspecialchars($customer['name']) . "<br>";
            echo "   - Email: " . htmlspecialchars($customer['email']) . "<br>";
            echo "   - Account: " . htmlspecialchars($customer['account_number']) . "<br>";
        } else {
            echo "⚠️ No customers found - check data import<br>";
        }
        
    } catch (PDOException $e) {
        echo "❌ Error checking sample data: " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}

echo "<br>";

// Test 5: Laravel Integration Test (if Laravel is available)
echo "<h3>5. Laravel Integration Test</h3>";

if (file_exists('.env')) {
    echo "✅ .env file exists<br>";
    
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
                    $result = $connection->select('SELECT COUNT(*) as count FROM customers_tb');
                    echo "✅ Laravel MySQL connection successful!<br>";
                    echo "✅ Laravel can access data: " . $result[0]->count . " customers<br>";
                    
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
        echo "❌ vendor/autoload.php not found<br>";
    }
} else {
    echo "❌ .env file not found<br>";
}

echo "<br>";
echo "<hr>";
echo "<h3>🎯 Migration Status Summary</h3>";

if (isset($pdo)) {
    echo "<p style='color: green;'><strong>✅ SUCCESS: MySQL connection is working!</strong></p>";
    echo "<p>Your migration from Supabase to MySQL appears to be successful.</p>";
    
    echo "<h4>Next Steps:</h4>";
    echo "<ul>";
    echo "<li>✅ Update your .env file with the correct MySQL credentials</li>";
    echo "<li>✅ Clear Laravel cache if you have SSH access</li>";
    echo "<li>✅ Test your website functionality</li>";
    echo "<li>✅ Remove test files after everything is working</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'><strong>❌ ISSUE: MySQL connection failed</strong></p>";
    echo "<p>Please check your database credentials and try again.</p>";
    
    echo "<h4>Troubleshooting:</h4>";
    echo "<ul>";
    echo "<li>Verify database credentials in Hostinger control panel</li>";
    echo "<li>Make sure database user has proper permissions</li>";
    echo "<li>Check if database name is correct</li>";
    echo "<li>Update credentials in this test file</li>";
    echo "</ul>";
}

echo "<br>";
echo "<p><em>Remember to delete this test file after migration is complete!</em></p>";
?> 