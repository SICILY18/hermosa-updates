<?php
/**
 * Simple Error Display Script
 * Use this to check for basic PHP errors in your Laravel setup
 */

// Enable all error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🚨 PHP Error Check</h1>";
echo "<p>This script will try to load Laravel and show any errors...</p>";

try {
    echo "<p>1. Checking if autoload exists...</p>";
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        echo "✓ Autoload found<br>";
        require_once __DIR__ . '/vendor/autoload.php';
        echo "✓ Autoload loaded successfully<br>";
    } else {
        echo "❌ Vendor autoload missing - run 'composer install'<br>";
        exit;
    }
    
    echo "<p>2. Checking .env file...</p>";
    if (file_exists(__DIR__ . '/.env')) {
        echo "✓ .env file found<br>";
    } else {
        echo "❌ .env file missing<br>";
        exit;
    }
    
    echo "<p>3. Attempting to bootstrap Laravel...</p>";
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "✓ Laravel app created<br>";
    
    echo "<p>4. Testing kernel...</p>";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "✓ HTTP Kernel created<br>";
    
    echo "<p>5. Creating fake request...</p>";
    $request = Illuminate\Http\Request::create('/', 'GET');
    echo "✓ Request created<br>";
    
    echo "<p>6. Processing request...</p>";
    $response = $kernel->handle($request);
    echo "✓ Request processed<br>";
    echo "Response status: " . $response->getStatusCode() . "<br>";
    
    echo "<h2>🎉 Laravel is working!</h2>";
    echo "<p>If you see this message, Laravel can bootstrap successfully.</p>";
    echo "<p>The blank page issue might be:</p>";
    echo "<ul>";
    echo "<li>Incorrect .htaccess configuration</li>";
    echo "<li>Wrong file paths in your web server setup</li>";
    echo "<li>Missing routes or views</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error Found!</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<h2>❌ Fatal Error Found!</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 