<?php
/**
 * CREATE CACHE DIRECTORIES - Production Fix
 * 
 * File này tạo tất cả cache directories cần thiết
 * Upload lên hosting và truy cập để tạo directories
 * 
 * SỬ DỤNG:
 *   1. Upload file này lên hosting (cùng cấp với index.php)
 *   2. Truy cập: https://supertestcrm.tdbsolution.com/create_cache_dirs.php
 *   3. Cache directories sẽ được tạo tự động
 *   4. XÓA file này sau khi fix xong (bảo mật)
 */

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>CREATE CACHE DIRECTORIES</h1>";
echo "<hr>";

// Directories to create
$directories = [
    'cache',
    'cache/templates_c',
    'cache/images',
    'cache/import',
    'cache/upload',
    'cache/htmlpurifier',
    'cache/htmlpurifier/HTML'
];

echo "<h2>1. Tạo Cache Directories</h2>";

$created = [];
$exists = [];
$failed = [];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            $created[] = $dir;
            echo "<p style='color:green'>✓ Đã tạo: $dir/</p>";
        } else {
            $failed[] = $dir;
            echo "<p style='color:red'>✗ Không thể tạo: $dir/</p>";
        }
    } else {
        $exists[] = $dir;
        echo "<p style='color:blue'>→ Đã tồn tại: $dir/</p>";
    }
}

echo "<hr>";

// Set permissions
echo "<h2>2. Set Permissions</h2>";

if (is_dir('cache')) {
    // Set permissions recursively
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator('cache', RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        if ($item->isDir()) {
            chmod($item->getPathname(), 0755);
        } else {
            chmod($item->getPathname(), 0644);
        }
    }
    
    // Set cache directory itself
    chmod('cache', 0755);
    
    echo "<p style='color:green'>✓ Permissions đã được set cho cache/</p>";
} else {
    echo "<p style='color:red'>✗ cache/ không tồn tại</p>";
}

echo "<hr>";

// Verify
echo "<h2>3. Verify</h2>";

$all_ok = true;
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $readable = is_readable($dir) ? 'YES' : 'NO';
        $writable = is_writable($dir) ? 'YES' : 'NO';
        
        $status = ($readable === 'YES' && $writable === 'YES') ? '✓' : '✗';
        $color = ($readable === 'YES' && $writable === 'YES') ? 'green' : 'red';
        
        echo "<p style='color:$color'>$status $dir/ - Permissions: $perms, Readable: $readable, Writable: $writable</p>";
        
        if ($readable !== 'YES' || $writable !== 'YES') {
            $all_ok = false;
        }
    } else {
        echo "<p style='color:red'>✗ $dir/ - KHÔNG TỒN TẠI</p>";
        $all_ok = false;
    }
}

echo "<hr>";

if ($all_ok) {
    echo "<h2 style='color:green'>✅ HOÀN TẤT</h2>";
    echo "<p>Tất cả cache directories đã được tạo và set permissions đúng!</p>";
    echo "<p>Bây giờ thử truy cập website lại: <a href='index.php'>index.php</a></p>";
} else {
    echo "<h2 style='color:red'>⚠️ CÓ LỖI</h2>";
    echo "<p>Một số directories không được tạo hoặc permissions sai.</p>";
    echo "<p>Hãy chạy lệnh thủ công:</p>";
    echo "<pre>";
    echo "mkdir -p cache/templates_c cache/images cache/import cache/upload cache/htmlpurifier/HTML\n";
    echo "chmod -R 755 cache/\n";
    echo "</pre>";
}

echo "<hr>";
echo "<p><strong>Lưu ý:</strong> Xóa file này sau khi fix xong để bảo mật!</p>";
?>

