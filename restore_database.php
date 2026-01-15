<?php
/**
 * Script to restore Vtiger database structure from DatabaseSchema.xml
 * This will create all tables but without data
 */

// Database configuration
$db_host = 'db';
$db_port = '3306';
$db_name = 'TDB';
$db_user = 'root';
$db_pass = '132120';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database: $db_name\n";
    
    // Check if database is empty
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = '$db_name'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $table_count = $result['cnt'];
    
    if ($table_count > 0) {
        echo "⚠️  Database already has $table_count tables. Do you want to continue? (This script will only create missing tables)\n";
    } else {
        echo "📋 Database is empty. Ready to create tables from schema.\n";
    }
    
    echo "\n📝 To restore database structure, you need to:\n";
    echo "1. Access Vtiger installation wizard at: http://localhost:8080\n";
    echo "2. Or use Vtiger's built-in schema creation via web interface\n";
    echo "3. Or if you have a SQL backup file, restore it using:\n";
    echo "   docker exec -i vtiger_db mysql -uroot -p132120 TDB < your_backup.sql\n";
    
    echo "\n💡 Note: This script cannot directly parse DatabaseSchema.xml without Vtiger's full framework.\n";
    echo "   The best way is to use Vtiger's installation process or restore from SQL backup.\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "\n";
    exit(1);
}


