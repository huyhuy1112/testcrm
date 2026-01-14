<?php
/**
 * ENABLE ERRORS TEMPORARILY - Quick fix for white screen
 * 
 * File này sẽ tạo một file index_test.php với error display enabled
 * để xem errors thực sự
 * 
 * SỬ DỤNG:
 *   1. Upload file này lên hosting
 *   2. Truy cập: https://supertestcrm.tdbsolution.com/enable_errors_temporarily.php
 *   3. File sẽ tạo index_test.php với errors enabled
 *   4. Truy cập: https://supertestcrm.tdbsolution.com/index_test.php
 *   5. Xem errors và fix
 *   6. XÓA các file test sau khi fix xong
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>ENABLE ERRORS TEMPORARILY</h1>";
echo "<hr>";

// Read index.php
if (!file_exists('index.php')) {
    die("index.php NOT FOUND");
}

$index_content = file_get_contents('index.php');

// Create index_test.php with errors enabled
$test_content = "<?php\n";
$test_content .= "// Enable all errors\n";
$test_content .= "error_reporting(E_ALL);\n";
$test_content .= "ini_set('display_errors', '1');\n";
$test_content .= "ini_set('display_startup_errors', '1');\n";
$test_content .= "ini_set('log_errors', '1');\n\n";
$test_content .= "// Original index.php content\n";
$test_content .= substr($index_content, 5); // Remove <?php from original

if (file_put_contents('index_test.php', $test_content)) {
    chmod('index_test.php', 0644);
    echo "<p style='color:green'><strong>✓ index_test.php đã được tạo</strong></p>";
    echo "<p>Bây giờ truy cập: <a href='index_test.php' target='_blank'>index_test.php</a></p>";
    echo "<p>Bạn sẽ thấy errors thực sự gây white screen!</p>";
} else {
    echo "<p style='color:red'><strong>✗ Không thể tạo index_test.php</strong></p>";
    echo "<p>Hãy sửa index.php trực tiếp, thêm vào đầu file (sau <?php):</p>";
    echo "<pre>";
    echo "error_reporting(E_ALL);\n";
    echo "ini_set('display_errors', '1');\n";
    echo "ini_set('display_startup_errors', '1');\n";
    echo "</pre>";
}

echo "<hr>";
echo "<p><strong>Lưu ý:</strong> Xóa file này và index_test.php sau khi fix xong!</p>";
?>

