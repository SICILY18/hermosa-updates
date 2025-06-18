<?php
/**
 * Web-based Laravel Cache Clearer
 * Visit: https://hermosawaterdistrict.com/admin/clear-cache-web.php
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🧹 Laravel Cache Clearer</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;}</style>";

try {
    // Bootstrap Laravel
    require_once __DIR__ . '/bootstrap/app.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

    echo "<h2>Clearing Laravel Caches...</h2>";
    
    // Clear config cache
    echo "<p>1. Clearing config cache...</p>";
    $kernel->call('config:clear');
    echo "<p class='success'>✓ Config cache cleared</p>";
    
    // Clear application cache
    echo "<p>2. Clearing application cache...</p>";
    $kernel->call('cache:clear');
    echo "<p class='success'>✓ Application cache cleared</p>";
    
    // Clear view cache
    echo "<p>3. Clearing view cache...</p>";
    $kernel->call('view:clear');
    echo "<p class='success'>✓ View cache cleared</p>";
    
    // Clear route cache
    echo "<p>4. Clearing route cache...</p>";
    $kernel->call('route:clear');
    echo "<p class='success'>✓ Route cache cleared</p>";
    
    // Clear compiled views
    echo "<p>5. Clearing compiled views...</p>";
    $viewPath = __DIR__ . '/storage/framework/views';
    if (is_dir($viewPath)) {
        $files = glob($viewPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "<p class='success'>✓ Compiled views cleared</p>";
    }
    
    // Clear session files
    echo "<p>6. Clearing session files...</p>";
    $sessionPath = __DIR__ . '/storage/framework/sessions';
    if (is_dir($sessionPath)) {
        $files = glob($sessionPath . '/*');
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== '.gitignore') {
                unlink($file);
            }
        }
        echo "<p class='success'>✓ Session files cleared</p>";
    }
    
    echo "<h2 class='success'>🎉 All caches cleared successfully!</h2>";
    echo "<p><strong>Now try visiting your website:</strong> <a href='https://hermosawaterdistrict.com' target='_blank'>https://hermosawaterdistrict.com</a></p>";
    
} catch (Exception $e) {
    echo "<h2 class='error'>❌ Error occurred:</h2>";
    echo "<p class='error'>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Make sure your .env file is correctly named (with the dot)</li>";
echo "<li>Check that your database credentials are correct</li>";
echo "<li>Visit your main site: <a href='https://hermosawaterdistrict.com'>hermosawaterdistrict.com</a></li>";
echo "</ol>";
?> 