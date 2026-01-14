<?php
/**
 * TEST INDEX.PHP - Find actual error causing white screen
 * 
 * File này sẽ test load index.php và hiển thị errors thực sự
 * 
 * SỬ DỤNG:
 *   1. Upload file này lên hosting (cùng cấp với index.php)
 *   2. Truy cập: https://supertestcrm.tdbsolution.com/test_index_php.php
 *   3. Xem errors và fix
 *   4. XÓA file này sau khi fix xong (bảo mật)
 */

// Enable ALL error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');

// Start output buffering to catch all output
ob_start();

echo "<h1>TEST INDEX.PHP - FIND ACTUAL ERROR</h1>";
echo "<hr>";

// Test 1: Check if we can read index.php
echo "<h2>1. Reading index.php</h2>";
if (file_exists('index.php')) {
    $content = file_get_contents('index.php');
    echo "✓ index.php readable, size: " . strlen($content) . " bytes<br>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "...</pre>";
} else {
    die("✗ index.php NOT FOUND");
}

echo "<hr>";

// Test 2: Try to include config.inc.php
echo "<h2>2. Loading config.inc.php</h2>";
try {
    if (file_exists('config.inc.php')) {
        include_once 'config.inc.php';
        echo "✓ config.inc.php loaded<br>";
        
        if (isset($dbconfig)) {
            echo "✓ \$dbconfig defined<br>";
        } else {
            echo "✗ \$dbconfig NOT defined<br>";
        }
    } else {
        die("✗ config.inc.php NOT FOUND");
    }
} catch (Exception $e) {
    echo "<p style='color:red'><strong>ERROR loading config.inc.php: " . $e->getMessage() . "</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";

// Test 3: Try to include vendor/autoload.php
echo "<h2>3. Loading vendor/autoload.php</h2>";
try {
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
        echo "✓ vendor/autoload.php loaded<br>";
    } else {
        die("✗ vendor/autoload.php NOT FOUND");
    }
} catch (Exception $e) {
    echo "<p style='color:red'><strong>ERROR loading vendor/autoload.php: " . $e->getMessage() . "</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";

// Test 4: Try to include vtigerversion.php
echo "<h2>4. Loading vtigerversion.php</h2>";
try {
    if (file_exists('vtigerversion.php')) {
        include_once 'vtigerversion.php';
        echo "✓ vtigerversion.php loaded<br>";
    } else {
        echo "⚠ vtigerversion.php NOT FOUND (may be OK)<br>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'><strong>ERROR loading vtigerversion.php: " . $e->getMessage() . "</strong></p>";
}

echo "<hr>";

// Test 5: Try to include includes/main/WebUI.php
echo "<h2>5. Loading includes/main/WebUI.php</h2>";
try {
    if (file_exists('includes/main/WebUI.php')) {
        require_once 'includes/main/WebUI.php';
        echo "✓ includes/main/WebUI.php loaded<br>";
    } else {
        echo "⚠ includes/main/WebUI.php NOT FOUND<br>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'><strong>ERROR loading WebUI.php: " . $e->getMessage() . "</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";

// Test 6: Try to simulate index.php execution
echo "<h2>6. Simulating index.php execution</h2>";

// Clear any previous output
ob_clean();

try {
    // Set up environment like index.php does
    if (!file_exists("vendor/autoload.php")) {
        die("vendor/autoload.php NOT FOUND");
    }
    
    require_once 'vendor/autoload.php';
    include_once 'config.php';
    require_once 'includes/main/WebUI.php';
    
    // Try to create WebUI instance
    $webUI = new Vtiger_WebUI();
    echo "✓ Vtiger_WebUI instance created<br>";
    
    // Try to process a request
    $_REQUEST['module'] = 'Home';
    $_REQUEST['view'] = 'DashBoard';
    $request = new Vtiger_Request($_REQUEST, $_REQUEST);
    
    echo "✓ Vtiger_Request created<br>";
    
    // Don't actually process to avoid redirects
    echo "<p style='color:green'><strong>✓ All includes successful!</strong></p>";
    
} catch (Error $e) {
    echo "<p style='color:red'><strong>FATAL ERROR: " . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'><strong>EXCEPTION: " . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";

// Test 7: Check for any output before this point
$output = ob_get_contents();
if (!empty($output) && strlen($output) > 100) {
    echo "<h2>7. Output captured (may contain errors)</h2>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
}

echo "<hr>";

// Test 8: Check PHP error log
echo "<h2>8. Check for recent PHP errors</h2>";
$error_log_paths = [
    'error_log',
    'php_errors.log',
    ini_get('error_log')
];

foreach ($error_log_paths as $log_path) {
    if ($log_path && file_exists($log_path)) {
        echo "<h3>Error log: $log_path</h3>";
        $errors = file_get_contents($log_path);
        $lines = explode("\n", $errors);
        $recent = array_slice($lines, -30);
        echo "<pre>" . htmlspecialchars(implode("\n", $recent)) . "</pre>";
        break;
    }
}

echo "<hr>";
echo "<h2>✅ TEST COMPLETE</h2>";
echo "<p><strong>Lưu ý:</strong> Xóa file này sau khi fix xong để bảo mật!</p>";

ob_end_flush();
?>

