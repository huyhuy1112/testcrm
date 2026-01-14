<?php
/**
 * TEST PROCESS REQUEST - Find why request processing hangs
 * 
 * File này sẽ test process request và bắt lỗi trong quá trình xử lý
 * 
 * SỬ DỤNG:
 *   1. Upload file này lên hosting (cùng cấp với index.php)
 *   2. Truy cập: https://supertestcrm.tdbsolution.com/test_process_request.php
 *   3. Xem errors và fix
 *   4. XÓA file này sau khi fix xong (bảo mật)
 */

// Enable ALL error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
ini_set('max_execution_time', '60'); // Increase timeout

// Start output buffering
ob_start();

echo "<h1>TEST PROCESS REQUEST - FIND HANGING ISSUE</h1>";
echo "<hr>";

// Step 1: Load all required files
echo "<h2>Step 1: Loading Required Files</h2>";

try {
    if (!file_exists("vendor/autoload.php")) {
        die("✗ vendor/autoload.php NOT FOUND");
    }
    require_once 'vendor/autoload.php';
    echo "✅ Composer loaded<br>";
    flush();
    ob_flush();
    
    if (!file_exists("config.php")) {
        die("✗ config.php NOT FOUND");
    }
    include_once 'config.php';
    echo "✅ config.php loaded<br>";
    flush();
    ob_flush();
    
    if (!file_exists("config.inc.php")) {
        die("✗ config.inc.php NOT FOUND");
    }
    include_once 'config.inc.php';
    echo "✅ config.inc.php loaded<br>";
    flush();
    ob_flush();
    
    // Load WebUI
    if (!file_exists("includes/main/WebUI.php")) {
        die("✗ includes/main/WebUI.php NOT FOUND");
    }
    require_once 'includes/main/WebUI.php';
    echo "✅ WebUI.php loaded<br>";
    flush();
    ob_flush();
    
} catch (Error $e) {
    echo "<p style='color:red'><strong>FATAL ERROR loading files: " . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
} catch (Exception $e) {
    echo "<p style='color:red'><strong>EXCEPTION loading files: " . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    exit;
}

echo "<hr>";

// Step 2: Create WebUI instance
echo "<h2>Step 2: Creating WebUI Instance</h2>";

try {
    $webUI = new Vtiger_WebUI();
    echo "✅ WebUI created<br>";
    flush();
    ob_flush();
} catch (Error $e) {
    echo "<p style='color:red'><strong>FATAL ERROR creating WebUI: " . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

echo "<hr>";

// Step 3: Create Request
echo "<h2>Step 3: Creating Request</h2>";

try {
    // Set up minimal request
    $_REQUEST['module'] = 'Home';
    $_REQUEST['view'] = 'DashBoard';
    $_REQUEST['action'] = 'index';
    
    $request = new Vtiger_Request($_REQUEST, $_REQUEST);
    echo "✅ Request created<br>";
    echo "Module: " . $request->get('module') . "<br>";
    echo "View: " . $request->get('view') . "<br>";
    flush();
    ob_flush();
} catch (Error $e) {
    echo "<p style='color:red'><strong>FATAL ERROR creating Request: " . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

echo "<hr>";

// Step 4: Test Database Connection
echo "<h2>Step 4: Testing Database Connection</h2>";

try {
    global $adb;
    if (empty($adb)) {
        throw new Exception("Database connection (\$adb) not available");
    }
    
    // Simple query test
    $result = $adb->pquery("SELECT 1 as test", array());
    if ($result) {
        echo "✅ Database connection OK<br>";
        flush();
        ob_flush();
    } else {
        throw new Exception("Database query failed");
    }
} catch (Exception $e) {
    echo "<p style='color:red'><strong>DATABASE ERROR: " . $e->getMessage() . "</strong></p>";
    exit;
}

echo "<hr>";

// Step 5: Test Current User
echo "<h2>Step 5: Checking Current User</h2>";

try {
    global $current_user;
    
    if (empty($current_user)) {
        echo "⚠️ No current user (may need login)<br>";
        echo "This is OK for initial load - will redirect to login<br>";
    } else {
        echo "✅ Current user: " . $current_user->user_name . " (ID: " . $current_user->id . ")<br>";
    }
    flush();
    ob_flush();
} catch (Exception $e) {
    echo "<p style='color:orange'><strong>USER CHECK WARNING: " . $e->getMessage() . "</strong></p>";
    echo "<p>This may be OK if user needs to login</p>";
}

echo "<hr>";

// Step 6: Try to process request with timeout protection
echo "<h2>Step 6: Processing Request (with timeout protection)</h2>";
echo "<p><strong>This may take a moment...</strong></p>";
flush();
ob_flush();

// Set up error handler to catch any errors during processing
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<p style='color:red'><strong>ERROR during processing:</strong></p>";
    echo "<p>Level: $errno</p>";
    echo "<p>Message: $errstr</p>";
    echo "<p>File: $errfile</p>";
    echo "<p>Line: $errline</p>";
    return true; // Don't execute PHP internal error handler
});

// Set up exception handler
set_exception_handler(function($exception) {
    echo "<p style='color:red'><strong>UNCAUGHT EXCEPTION:</strong></p>";
    echo "<p>Message: " . $exception->getMessage() . "</p>";
    echo "<p>File: " . $exception->getFile() . "</p>";
    echo "<p>Line: " . $exception->getLine() . "</p>";
    echo "<pre>" . $exception->getTraceAsString() . "</pre>";
});

try {
    // Capture output during processing
    ob_start();
    
    // Try to process - but don't let it hang forever
    $start_time = time();
    $max_time = 30; // 30 seconds max
    
    // Use output buffering to catch any output
    $webUI->process($request);
    
    $elapsed = time() - $start_time;
    
    $output = ob_get_clean();
    
    if ($elapsed > $max_time) {
        echo "<p style='color:red'><strong>⚠️ Request processing took too long: {$elapsed} seconds</strong></p>";
    } else {
        echo "<p style='color:green'><strong>✅ Request processed in {$elapsed} seconds</strong></p>";
    }
    
    if (!empty($output)) {
        echo "<h3>Output captured:</h3>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 2000)) . "</pre>";
        if (strlen($output) > 2000) {
            echo "<p>... (truncated, total: " . strlen($output) . " bytes)</p>";
        }
    } else {
        echo "<p>⚠️ No output captured (may have been redirected or sent headers)</p>";
    }
    
} catch (Error $e) {
    ob_end_clean();
    echo "<p style='color:red'><strong>FATAL ERROR processing request: " . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color:red'><strong>EXCEPTION processing request: " . $e->getMessage() . "</strong></p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";

// Step 7: Check for headers sent
echo "<h2>Step 7: Checking Headers</h2>";

if (headers_sent($file, $line)) {
    echo "<p style='color:orange'><strong>⚠️ Headers already sent</strong></p>";
    echo "<p>File: $file</p>";
    echo "<p>Line: $line</p>";
} else {
    echo "✅ Headers not sent yet<br>";
}

echo "<hr>";

// Step 8: Check PHP error log
echo "<h2>Step 8: Recent PHP Errors</h2>";

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
        $recent = array_slice($lines, -20);
        echo "<pre>" . htmlspecialchars(implode("\n", $recent)) . "</pre>";
        break;
    }
}

echo "<hr>";
echo "<h2>✅ TEST COMPLETE</h2>";
echo "<p><strong>Lưu ý:</strong> Xóa file này sau khi fix xong để bảo mật!</p>";

// Restore error handlers
restore_error_handler();
restore_exception_handler();

ob_end_flush();
?>

