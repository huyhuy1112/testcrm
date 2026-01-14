<?php
/**
 * DEBUG WHITE SCREEN - Production Fix
 * 
 * File này giúp debug màn hình trắng
 * Upload lên hosting và truy cập để xem errors
 * 
 * SỬ DỤNG:
 *   1. Upload file này lên hosting (cùng cấp với index.php)
 *   2. Truy cập: https://supertestcrm.tdbsolution.com/debug_white_screen.php
 *   3. Xem errors và fix
 *   4. XÓA file này sau khi fix xong (bảo mật)
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

echo "<h1>DEBUG WHITE SCREEN</h1>";
echo "<hr>";

// 1. Check PHP version
echo "<h2>1. PHP Version</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "PHP SAPI: " . php_sapi_name() . "<br>";
echo "<hr>";

// 2. Check file permissions
echo "<h2>2. File Permissions</h2>";
$files_to_check = [
    'index.php',
    'config.inc.php',
    '.htaccess',
    'vendor/autoload.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        $readable = is_readable($file) ? 'YES' : 'NO';
        echo "$file: Permissions = $perms, Readable = $readable<br>";
    } else {
        echo "$file: <strong style='color:red'>NOT FOUND</strong><br>";
    }
}
echo "<hr>";

// 3. Check directory permissions
echo "<h2>3. Directory Permissions</h2>";
$dirs_to_check = [
    'cache',
    'storage',
    'vendor'
];

foreach ($dirs_to_check as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $readable = is_readable($dir) ? 'YES' : 'NO';
        $writable = is_writable($dir) ? 'YES' : 'NO';
        echo "$dir/: Permissions = $perms, Readable = $readable, Writable = $writable<br>";
    } else {
        echo "$dir/: <strong style='color:red'>NOT FOUND</strong><br>";
    }
}
echo "<hr>";

// 4. Check config.inc.php
echo "<h2>4. Check config.inc.php</h2>";
if (file_exists('config.inc.php')) {
    echo "config.inc.php: <strong style='color:green'>EXISTS</strong><br>";
    
    // Try to include and check database config
    try {
        include_once 'config.inc.php';
        if (isset($dbconfig)) {
            echo "Database Server: " . (isset($dbconfig['db_server']) ? $dbconfig['db_server'] : 'NOT SET') . "<br>";
            echo "Database Name: " . (isset($dbconfig['db_name']) ? $dbconfig['db_name'] : 'NOT SET') . "<br>";
            echo "Database Username: " . (isset($dbconfig['db_username']) ? $dbconfig['db_username'] : 'NOT SET') . "<br>";
            echo "Database Password: " . (isset($dbconfig['db_password']) ? (!empty($dbconfig['db_password']) ? 'SET' : 'EMPTY') : 'NOT SET') . "<br>";
        } else {
            echo "<strong style='color:red'>ERROR: \$dbconfig not defined</strong><br>";
        }
    } catch (Exception $e) {
        echo "<strong style='color:red'>ERROR including config.inc.php: " . $e->getMessage() . "</strong><br>";
    }
} else {
    echo "config.inc.php: <strong style='color:red'>NOT FOUND</strong><br>";
}
echo "<hr>";

// 5. Check vendor/autoload.php
echo "<h2>5. Check vendor/autoload.php</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "vendor/autoload.php: <strong style='color:green'>EXISTS</strong><br>";
    try {
        require_once 'vendor/autoload.php';
        echo "vendor/autoload.php: <strong style='color:green'>LOADED SUCCESSFULLY</strong><br>";
    } catch (Exception $e) {
        echo "<strong style='color:red'>ERROR loading vendor/autoload.php: " . $e->getMessage() . "</strong><br>";
    }
} else {
    echo "vendor/autoload.php: <strong style='color:red'>NOT FOUND</strong><br>";
}
echo "<hr>";

// 6. Check database connection
echo "<h2>6. Test Database Connection</h2>";
if (isset($dbconfig) && !empty($dbconfig['db_server'])) {
    try {
        $host = $dbconfig['db_server'] . (isset($dbconfig['db_port']) ? $dbconfig['db_port'] : '');
        $username = $dbconfig['db_username'];
        $password = $dbconfig['db_password'];
        $database = $dbconfig['db_name'];
        
        $conn = new mysqli($host, $username, $password, $database);
        
        if ($conn->connect_error) {
            echo "<strong style='color:red'>Database Connection FAILED: " . $conn->connect_error . "</strong><br>";
        } else {
            echo "Database Connection: <strong style='color:green'>SUCCESS</strong><br>";
            echo "Database Name: $database<br>";
            $conn->close();
        }
    } catch (Exception $e) {
        echo "<strong style='color:red'>Database Connection ERROR: " . $e->getMessage() . "</strong><br>";
    }
} else {
    echo "<strong style='color:red'>Database config not available</strong><br>";
}
echo "<hr>";

// 7. Check memory limit
echo "<h2>7. PHP Settings</h2>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "<hr>";

// 8. Try to load index.php
echo "<h2>8. Test Loading index.php</h2>";
if (file_exists('index.php')) {
    echo "index.php: <strong style='color:green'>EXISTS</strong><br>";
    
    // Capture any output/errors
    ob_start();
    $error_reporting = error_reporting(E_ALL);
    $display_errors = ini_get('display_errors');
    ini_set('display_errors', '1');
    
    try {
        // Just check if we can include the first few lines
        $content = file_get_contents('index.php');
        if ($content) {
            echo "index.php: <strong style='color:green'>READABLE</strong><br>";
            echo "File size: " . filesize('index.php') . " bytes<br>";
        }
    } catch (Exception $e) {
        echo "<strong style='color:red'>ERROR reading index.php: " . $e->getMessage() . "</strong><br>";
    }
    
    $output = ob_get_clean();
    if (!empty($output)) {
        echo "<strong style='color:orange'>Output captured:</strong><br>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
    
    error_reporting($error_reporting);
    ini_set('display_errors', $display_errors);
} else {
    echo "index.php: <strong style='color:red'>NOT FOUND</strong><br>";
}
echo "<hr>";

// 9. Check for common errors
echo "<h2>9. Common Issues Check</h2>";

// Check if .htaccess exists
if (file_exists('.htaccess')) {
    echo ".htaccess: <strong style='color:green'>EXISTS</strong><br>";
} else {
    echo ".htaccess: <strong style='color:orange'>NOT FOUND (may need to create from htaccess.txt)</strong><br>";
}

// Check if cache directory is writable
if (is_dir('cache') && !is_writable('cache')) {
    echo "cache/: <strong style='color:red'>NOT WRITABLE</strong><br>";
} elseif (is_dir('cache')) {
    echo "cache/: <strong style='color:green'>WRITABLE</strong><br>";
}

echo "<hr>";

// 10. PHP Errors Log
echo "<h2>10. Recent PHP Errors</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $errors = file_get_contents($error_log);
    $recent_errors = array_slice(explode("\n", $errors), -20);
    echo "<pre>" . htmlspecialchars(implode("\n", $recent_errors)) . "</pre>";
} else {
    echo "Error log: " . ($error_log ? $error_log : 'Not set') . "<br>";
    echo "Check server error logs in cPanel<br>";
}

echo "<hr>";
echo "<h2>✅ DEBUG COMPLETE</h2>";
echo "<p><strong>Lưu ý:</strong> Xóa file này sau khi fix xong để bảo mật!</p>";
?>

