<?php
/**
 * CREATE .HTACCESS - Production Fix
 * 
 * File này tạo .htaccess từ htaccess.txt hoặc tạo mới
 * Upload lên hosting và truy cập để tạo .htaccess
 * 
 * SỬ DỤNG:
 *   1. Upload file này lên hosting (cùng cấp với index.php)
 *   2. Truy cập: https://supertestcrm.tdbsolution.com/create_htaccess.php
 *   3. .htaccess sẽ được tạo tự động
 *   4. XÓA file này sau khi fix xong (bảo mật)
 */

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>CREATE .HTACCESS</h1>";
echo "<hr>";

// Check if .htaccess already exists
if (file_exists('.htaccess')) {
    echo "<p style='color:orange'><strong>.htaccess đã tồn tại!</strong></p>";
    echo "<p>Nội dung hiện tại:</p>";
    echo "<pre>" . htmlspecialchars(file_get_contents('.htaccess')) . "</pre>";
    echo "<hr>";
}

// Try to create from htaccess.txt
if (file_exists('htaccess.txt')) {
    echo "<h2>1. Tạo từ htaccess.txt</h2>";
    $htaccess_content = file_get_contents('htaccess.txt');
    
    // Add production settings
    $htaccess_content .= "\n\n# Production settings\n";
    $htaccess_content .= "php_flag output_buffering On\n";
    $htaccess_content .= "php_value output_buffering 4096\n";
    $htaccess_content .= "php_flag display_errors Off\n";
    $htaccess_content .= "php_value memory_limit 512M\n";
    $htaccess_content .= "php_value max_execution_time 300\n";
    
    if (file_put_contents('.htaccess', $htaccess_content)) {
        chmod('.htaccess', 0644);
        echo "<p style='color:green'><strong>✓ .htaccess đã được tạo từ htaccess.txt</strong></p>";
        echo "<p>Nội dung:</p>";
        echo "<pre>" . htmlspecialchars($htaccess_content) . "</pre>";
    } else {
        echo "<p style='color:red'><strong>✗ Không thể tạo .htaccess</strong></p>";
    }
} else {
    echo "<h2>1. Tạo .htaccess mới</h2>";
    
    $htaccess_content = <<<'EOF'
Options -Indexes

# Enable output buffering
php_flag output_buffering On
php_value output_buffering 4096

# Disable display errors (production)
php_flag display_errors Off

# Memory and execution time
php_value memory_limit 512M
php_value max_execution_time 300
EOF;
    
    if (file_put_contents('.htaccess', $htaccess_content)) {
        chmod('.htaccess', 0644);
        echo "<p style='color:green'><strong>✓ .htaccess đã được tạo</strong></p>";
        echo "<p>Nội dung:</p>";
        echo "<pre>" . htmlspecialchars($htaccess_content) . "</pre>";
    } else {
        echo "<p style='color:red'><strong>✗ Không thể tạo .htaccess</strong></p>";
        echo "<p>Hãy tạo thủ công bằng lệnh:</p>";
        echo "<pre>cp htaccess.txt .htaccess</pre>";
    }
}

echo "<hr>";

// Verify
if (file_exists('.htaccess')) {
    $perms = substr(sprintf('%o', fileperms('.htaccess')), -4);
    echo "<h2>2. Verify</h2>";
    echo "<p>File: .htaccess</p>";
    echo "<p>Permissions: $perms</p>";
    echo "<p>Readable: " . (is_readable('.htaccess') ? 'YES' : 'NO') . "</p>";
    echo "<p style='color:green'><strong>✓ .htaccess đã sẵn sàng!</strong></p>";
} else {
    echo "<p style='color:red'><strong>✗ .htaccess vẫn chưa được tạo</strong></p>";
}

echo "<hr>";
echo "<p><strong>Lưu ý:</strong> Xóa file này sau khi fix xong để bảo mật!</p>";
?>

