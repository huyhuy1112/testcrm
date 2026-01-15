<?php
/**
 * Script để Export Full Database Vtiger CRM
 * 
 * CÁCH DÙNG:
 * 1. Upload file này lên hosting (root directory)
 * 2. Sửa database credentials bên dưới
 * 3. Truy cập: https://supertestcrm.tdbsolution.com/export_database.php
 * 4. File SQL sẽ được download về
 * 5. XÓA FILE NÀY sau khi xong để bảo mật!
 */

// ⚠️ ĐIỀN THÔNG TIN DATABASE CỦA BẠN
$db_host = 'localhost';
$db_user = 'nhtdbus8_supertestcrm';
$db_pass = '987456321852huy';
$db_name = 'nhtdbus8_supertestcrm';

// Tên file export
$filename = 'vtiger_full_database_' . date('Y-m-d_His') . '.sql';

// Kết nối database
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("❌ Lỗi kết nối database: " . mysqli_connect_error());
}

echo "<h2>📦 Export Full Database Vtiger CRM</h2>";
echo "<hr>";

// Lấy danh sách tất cả các bảng
$tables = array();
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

echo "<p>✅ Tìm thấy <strong>" . count($tables) . "</strong> bảng trong database</p>";
echo "<hr>";

// Bắt đầu export
$output = "-- ==========================================\n";
$output .= "-- Vtiger CRM - Full Database Export\n";
$output .= "-- Export Date: " . date('Y-m-d H:i:s') . "\n";
$output .= "-- Database: $db_name\n";
$output .= "-- ==========================================\n\n";
$output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$output .= "SET AUTOCOMMIT = 0;\n";
$output .= "START TRANSACTION;\n";
$output .= "SET time_zone = \"+00:00\";\n\n";

// Export từng bảng
$exported = 0;
foreach ($tables as $table) {
    echo "<p>📋 Đang export bảng: <strong>$table</strong>...</p>";
    
    // Export structure
    $output .= "-- --------------------------------------------------------\n";
    $output .= "-- Table structure for table `$table`\n";
    $output .= "-- --------------------------------------------------------\n\n";
    $output .= "DROP TABLE IF EXISTS `$table`;\n";
    
    $create_table = mysqli_query($conn, "SHOW CREATE TABLE `$table`");
    $row = mysqli_fetch_row($create_table);
    $output .= $row[1] . ";\n\n";
    
    // Export data
    $output .= "-- --------------------------------------------------------\n";
    $output .= "-- Dumping data for table `$table`\n";
    $output .= "-- --------------------------------------------------------\n\n";
    
    $data = mysqli_query($conn, "SELECT * FROM `$table`");
    $num_rows = mysqli_num_rows($data);
    
    if ($num_rows > 0) {
        $output .= "LOCK TABLES `$table` WRITE;\n";
        $output .= "/*!40000 ALTER TABLE `$table` DISABLE KEYS */;\n\n";
        
        while ($row = mysqli_fetch_assoc($data)) {
            $output .= "INSERT INTO `$table` VALUES (";
            $values = array();
            foreach ($row as $value) {
                if ($value === NULL) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
                }
            }
            $output .= implode(', ', $values) . ");\n";
        }
        
        $output .= "\n/*!40000 ALTER TABLE `$table` ENABLE KEYS */;\n";
        $output .= "UNLOCK TABLES;\n\n";
    } else {
        $output .= "-- No data in table `$table`\n\n";
    }
    
    $exported++;
}

$output .= "COMMIT;\n";

// Kết thúc
echo "<hr>";
echo "<p style='color:green;'>✅ Đã export <strong>$exported</strong> bảng thành công!</p>";

// Download file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($output));
echo $output;

mysqli_close($conn);

echo "<hr>";
echo "<p style='color:red;'><strong>⚠️ SECURITY WARNING:</strong> Xóa file này sau khi export xong!</p>";

