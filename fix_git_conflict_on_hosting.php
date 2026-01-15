<?php
/**
 * Script để fix git conflict trên hosting
 * 
 * CÁCH DÙNG:
 * 1. Upload file này lên hosting (root directory)
 * 2. Truy cập: https://supertestcrm.tdbsolution.com/fix_git_conflict_on_hosting.php
 * 3. Script sẽ tự động backup và pull code mới
 * 4. Sau đó điền lại database credentials vào config.inc.php
 */

echo "<h2>🔧 Fix Git Conflict trên Hosting</h2>";
echo "<hr>";

$rootDir = dirname(__FILE__);
$configFile = $rootDir . '/config.inc.php';
$backupFile = $rootDir . '/config.inc.php.backup.' . date('YmdHis');

// Bước 1: Backup config.inc.php
echo "<h3>Bước 1: Backup config.inc.php</h3>";
if (file_exists($configFile)) {
    if (copy($configFile, $backupFile)) {
        echo "<p style='color:green;'>✅ Đã backup: " . basename($backupFile) . "</p>";
        
        // Đọc database credentials từ file hiện tại
        $configContent = file_get_contents($configFile);
        preg_match("/db_username['\"]\s*=>\s*['\"]([^'\"]*)['\"]/", $configContent, $usernameMatch);
        preg_match("/db_password['\"]\s*=>\s*['\"]([^'\"]*)['\"]/", $configContent, $passwordMatch);
        preg_match("/db_name['\"]\s*=>\s*['\"]([^'\"]*)['\"]/", $configContent, $dbnameMatch);
        
        $savedUsername = isset($usernameMatch[1]) ? $usernameMatch[1] : '';
        $savedPassword = isset($passwordMatch[1]) ? $passwordMatch[1] : '';
        $savedDbname = isset($dbnameMatch[1]) ? $dbnameMatch[1] : '';
        
        echo "<p>📝 Database credentials đã lưu:</p>";
        echo "<ul>";
        echo "<li>Username: " . ($savedUsername ? $savedUsername : '<em>trống</em>') . "</li>";
        echo "<li>Password: " . ($savedPassword ? '***' : '<em>trống</em>') . "</li>";
        echo "<li>Database: " . ($savedDbname ? $savedDbname : '<em>trống</em>') . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color:red;'>❌ Không thể backup config.inc.php</p>";
        exit;
    }
} else {
    echo "<p style='color:orange;'>⚠️ File config.inc.php không tồn tại</p>";
}

echo "<hr>";

// Bước 2: Hướng dẫn chạy git commands
echo "<h3>Bước 2: Chạy Git Commands</h3>";
echo "<p>Vì không thể chạy git commands qua PHP, bạn cần chạy thủ công:</p>";

echo "<div style='background:#f5f5f5;padding:15px;border-radius:5px;margin:10px 0;'>";
echo "<strong>SSH Commands:</strong><br>";
echo "<code style='display:block;margin:10px 0;'>";
echo "cd " . $rootDir . "<br>";
echo "git stash<br>";
echo "git pull origin main<br>";
echo "</code>";
echo "</div>";

echo "<p><strong>Hoặc qua cPanel Terminal:</strong></p>";
echo "<ol>";
echo "<li>Vào cPanel → Terminal</li>";
echo "<li>Chạy các lệnh trên</li>";
echo "</ol>";

echo "<hr>";

// Bước 3: Hướng dẫn restore database credentials
echo "<h3>Bước 3: Restore Database Credentials</h3>";

if (!empty($savedUsername) || !empty($savedDbname)) {
    echo "<p>Sau khi pull xong, mở file <code>config.inc.php</code> và điền lại:</p>";
    echo "<div style='background:#e8f5e9;padding:15px;border-radius:5px;margin:10px 0;'>";
    echo "<code>";
    echo "\$dbconfig['db_username'] = '" . htmlspecialchars($savedUsername) . "';<br>";
    echo "\$dbconfig['db_password'] = '" . htmlspecialchars($savedPassword) . "';<br>";
    echo "\$dbconfig['db_name'] = '" . htmlspecialchars($savedDbname) . "';<br>";
    echo "</code>";
    echo "</div>";
} else {
    echo "<p>Điền database credentials vào <code>config.inc.php</code>:</p>";
    echo "<div style='background:#e8f5e9;padding:15px;border-radius:5px;margin:10px 0;'>";
    echo "<code>";
    echo "\$dbconfig['db_username'] = 'nhtdbus8_supertestcrm';<br>";
    echo "\$dbconfig['db_password'] = '987456321852huy';<br>";
    echo "\$dbconfig['db_name'] = 'nhtdbus8_supertestcrm';<br>";
    echo "</code>";
    echo "</div>";
}

echo "<hr>";

// Bước 4: Tự động restore (nếu có thể)
if (!empty($savedUsername) && !empty($savedDbname)) {
    echo "<h3>Bước 4: Tự Động Restore (Tùy Chọn)</h3>";
    echo "<p>Nếu bạn muốn, script có thể tự động restore database credentials sau khi pull.</p>";
    echo "<p><strong>Lưu ý:</strong> Chỉ chạy sau khi đã pull code mới từ GitHub!</p>";
    
    if (isset($_GET['restore']) && $_GET['restore'] == 'yes') {
        if (file_exists($configFile)) {
            $newConfig = file_get_contents($configFile);
            
            // Restore credentials
            $newConfig = preg_replace(
                "/db_username['\"]\s*=>\s*['\"][^'\"]*['\"]/",
                "db_username'] = '" . $savedUsername . "'",
                $newConfig
            );
            $newConfig = preg_replace(
                "/db_password['\"]\s*=>\s*['\"][^'\"]*['\"]/",
                "db_password'] = '" . $savedPassword . "'",
                $newConfig
            );
            $newConfig = preg_replace(
                "/db_name['\"]\s*=>\s*['\"][^'\"]*['\"]/",
                "db_name'] = '" . $savedDbname . "'",
                $newConfig
            );
            
            if (file_put_contents($configFile, $newConfig)) {
                echo "<p style='color:green;'>✅ Đã restore database credentials!</p>";
            } else {
                echo "<p style='color:red;'>❌ Không thể restore. Vui lòng sửa thủ công.</p>";
            }
        }
    } else {
        echo "<p><a href='?restore=yes' style='background:#4CAF50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🔧 Restore Database Credentials</a></p>";
        echo "<p style='color:orange;'><strong>⚠️ CHỈ CLICK SAU KHI ĐÃ PULL CODE MỚI!</strong></p>";
    }
}

echo "<hr>";
echo "<h3>✅ Hoàn Thành</h3>";
echo "<p>1. Đã backup config.inc.php</p>";
echo "<p>2. Chạy git commands (xem Bước 2)</p>";
echo "<p>3. Restore database credentials (xem Bước 3 hoặc 4)</p>";
echo "<p>4. Xóa file backup: <code>rm " . basename($backupFile) . "</code></p>";
echo "<p>5. <strong>XÓA FILE NÀY</strong> sau khi xong để bảo mật!</p>";

echo "<hr>";
echo "<p style='color:red;'><strong>⚠️ SECURITY WARNING:</strong> Xóa file này sau khi fix xong!</p>";

