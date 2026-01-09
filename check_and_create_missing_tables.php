<?php
/**
 * Comprehensive script to check and create all missing database tables for notification system
 * Run via browser: http://localhost:8080/check_and_create_missing_tables.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Check & Create Missing Tables</title>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;} th{background:#f0f0f0;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";
echo "</head><body>";
echo "<h2>🔍 Checking and Creating Missing Database Tables</h2>";
echo "<hr>";

try {
    require_once 'config.php';
    require_once 'include/database/PearDatabase.php';

    global $adb;
    $adb = PearDatabase::getInstance();
    
    if (!$adb) {
        throw new Exception("Failed to initialize database connection");
    }
    
    echo "<p class='success'><strong>✅ Database connection: OK</strong></p>";
    echo "<hr>";

    // Check if vtiger_notifications table exists
    $checkNotifications = $adb->query("SHOW TABLES LIKE 'vtiger_notifications'");
    $notificationsExists = ($adb->num_rows($checkNotifications) > 0);

    echo "<h3>📊 Table Status Check</h3>";
    echo "<table>";
    echo "<tr><th>Table Name</th><th>Status</th><th>Action</th></tr>";

    // 1. vtiger_notifications
    if ($notificationsExists) {
        echo "<tr><td><strong>vtiger_notifications</strong></td><td class='success'>✅ Exists</td><td>-</td></tr>";
    } else {
        echo "<tr><td><strong>vtiger_notifications</strong></td><td class='error'>❌ Missing</td><td>Creating...</td></tr>";
        
        $createSql = "CREATE TABLE `vtiger_notifications` (
          `id` int(19) NOT NULL AUTO_INCREMENT,
          `userid` int(19) NOT NULL,
          `module` varchar(100) NOT NULL,
          `recordid` int(19) NOT NULL,
          `message` text NOT NULL,
          `is_read` tinyint(1) DEFAULT 0,
          `read_at` datetime NULL,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`),
          KEY `idx_userid` (`userid`),
          KEY `idx_is_read` (`is_read`),
          KEY `idx_created_at` (`created_at`),
          KEY `idx_userid_is_read` (`userid`, `is_read`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $adb->query($createSql);
        echo "<tr><td colspan='3' class='success'>✅ Created vtiger_notifications table</td></tr>";
    }

    // Check other critical tables used by handlers
    $criticalTables = array(
        'vtiger_crmentity',
        'vtiger_users',
        'vtiger_account',
        'vtiger_contactdetails',
        'vtiger_project',
        'vtiger_projecttask',
        'vtiger_potential',
        'vtiger_activity',
        'vtiger_troubletickets',
        'vtiger_eventhandlers',
        'vtiger_cron_task'
    );

    $missingTables = array();
    $existingTables = array();

    foreach ($criticalTables as $table) {
        $check = $adb->query("SHOW TABLES LIKE '$table'");
        if ($adb->num_rows($check) > 0) {
            $existingTables[] = $table;
            echo "<tr><td>$table</td><td class='success'>✅ Exists</td><td>-</td></tr>";
        } else {
            $missingTables[] = $table;
            echo "<tr><td>$table</td><td class='error'>❌ Missing</td><td class='warning'>⚠️ Critical - should exist</td></tr>";
        }
    }

    echo "</table>";

    echo "<hr>";
    echo "<h3>📋 Summary</h3>";
    echo "<ul>";
    echo "<li><strong>Existing tables:</strong> " . count($existingTables) . "</li>";
    echo "<li><strong>Missing tables:</strong> " . count($missingTables) . "</li>";
    echo "</ul>";

    if (count($missingTables) > 0) {
        echo "<hr>";
        echo "<h3 class='warning'>⚠️ Missing Critical Tables</h3>";
        echo "<p class='warning'>The following tables are missing. These should have been created during installation:</p>";
        echo "<ul>";
        foreach ($missingTables as $table) {
            echo "<li class='error'>$table</li>";
        }
        echo "</ul>";
        echo "<p class='warning'><strong>Recommendation:</strong> Re-run the installation wizard to create all required tables.</p>";
    }

    // Verify vtiger_notifications structure
    if ($notificationsExists || $adb->num_rows($adb->query("SHOW TABLES LIKE 'vtiger_notifications'")) > 0) {
        echo "<hr>";
        echo "<h3>✅ Verifying vtiger_notifications Structure</h3>";
        
        $columns = $adb->query("DESCRIBE vtiger_notifications");
        $requiredColumns = array('id', 'userid', 'module', 'recordid', 'message', 'is_read', 'read_at', 'created_at');
        $existingColumns = array();
        
        while ($row = $adb->fetchByAssoc($columns)) {
            $existingColumns[] = $row['Field'];
        }
        
        $missingColumns = array_diff($requiredColumns, $existingColumns);
        
        if (empty($missingColumns)) {
            echo "<p class='success'>✅ All required columns exist!</p>";
            echo "<table>";
            echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
            $columns = $adb->query("DESCRIBE vtiger_notifications");
            while ($row = $adb->fetchByAssoc($columns)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='error'>❌ Missing columns: " . implode(', ', $missingColumns) . "</p>";
            // Add missing columns
            foreach ($missingColumns as $col) {
                if ($col == 'read_at') {
                    $adb->query("ALTER TABLE vtiger_notifications ADD COLUMN read_at DATETIME NULL AFTER is_read");
                    echo "<p class='success'>✅ Added column: read_at</p>";
                }
            }
        }
    }

    echo "<hr>";
    echo "<h3>🎯 Next Steps</h3>";
    echo "<ol>";
    echo "<li>✅ <strong>vtiger_notifications table:</strong> " . ($notificationsExists || $adb->num_rows($adb->query("SHOW TABLES LIKE 'vtiger_notifications'")) > 0 ? "Ready" : "Created") . "</li>";
    echo "<li>📝 <strong>Register Event Handlers:</strong> <a href='register_all_notification_handlers.php'>Run registration script</a></li>";
    echo "<li>⏰ <strong>Register Cron Job:</strong> <a href='register_deadline_cron.php'>Run cron registration</a></li>";
    if (count($missingTables) > 0) {
        echo "<li class='warning'>⚠️ <strong>Re-run Installation:</strong> Some critical tables are missing. Consider re-running installation wizard.</li>";
    }
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<div style='background:#fee;border:1px solid #f00;padding:10px;margin:10px 0;'>";
    echo "<h3 class='error'>ERROR</h3>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "</body></html>";

