<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Detailed System Check v2</h1>";

// 1. Check Vendor
echo "<h2>1. Dependencies (Vendor)</h2>";
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (file_exists($autoloadPath)) {
    echo "Vendor autoload found: <span style='color:green'>YES</span><br>";
    
    try {
        require_once $autoloadPath;
        echo "Vendor loaded successfully: <span style='color:green'>YES</span><br>";
    } catch (\Throwable $e) {
        echo "Vendor load failed: <span style='color:red'>" . $e->getMessage() . "</span><br>";
    }
    
} else {
    echo "Vendor autoload found: <span style='color:red'>NO - Did you upload the 'vendor' folder?</span><br>";
    exit; // Stop here if no vendor
}

// 2. Check .env content
echo "<h2>2. Environment (.env)</h2>";
if (file_exists(__DIR__ . '/../.env')) {
    echo ".env file exists.<br>";
    // Optional: Peek at APP_KEY (mask it) to see if it reads correct
    $lines = file(__DIR__ . '/../.env');
    $hasKey = false;
    foreach($lines as $line) {
        if(strpos($line, 'APP_KEY') === 0 && strlen(trim($line)) > 10) {
            $hasKey = true;
        }
    }
    echo "APP_KEY seems present: " . ($hasKey ? "<span style='color:green'>YES</span>" : "<span style='color:red'>NO</span>") . "<br>";
} else {
    echo "<span style='color:red'>.env file MISSING</span><br>";
}

// 3. Try to Boot Laravel (Minimal)
echo "<h2>3. Laravel Boot Test</h2>";
try {
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo "Application instance created: <span style='color:green'>YES</span><br>";
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "Kernel resolved: <span style='color:green'>YES</span><br>";
    
    echo "<h3>If you see this, PHP/Laravel core is working. The 500 error is likely in the Database connection or View configuration.</h3>";
    
} catch (\Throwable $e) {
    echo "<strong>Laravel Boot Failed:</strong><br>";
    echo "<pre style='background:#eee;padding:10px;color:red'>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
}
