<?php
/**
 * Verify Notification Display Setup
 * Run via browser: http://localhost:8080/verify_notification_display.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Verify Notification Display</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;} th{background:#f0f0f0;}</style>";
echo "</head><body>";
echo "<h2>🔍 Verify Notification Display Setup</h2>";
echo "<hr>";

try {
    require_once 'config.php';
    require_once 'include/database/PearDatabase.php';

    global $adb;
    $adb = PearDatabase::getInstance();
    
    echo "<p class='success'>✅ Database connection: OK</p>";
    echo "<hr>";

    // 1. Check notifications in database
    echo "<h3>1. Notifications in Database</h3>";
    $result = $adb->pquery("SELECT id, userid, module, recordid, LEFT(message, 50) as msg, is_read, created_at FROM vtiger_notifications ORDER BY created_at DESC LIMIT 10", array());
    
    echo "<table>";
    echo "<tr><th>ID</th><th>User ID</th><th>Module</th><th>Message</th><th>Read</th><th>Created</th></tr>";
    
    $count = 0;
    while ($row = $adb->fetchByAssoc($result)) {
        $count++;
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['userid']}</td>";
        echo "<td>{$row['module']}</td>";
        echo "<td>" . htmlspecialchars($row['msg']) . "...</td>";
        echo "<td>" . ($row['is_read'] ? 'Yes' : '<strong>No</strong>') . "</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($count == 0) {
        echo "<p class='error'>❌ No notifications found in database!</p>";
        echo "<p>Creating test notification...</p>";
        $message = "Test notification - " . date('Y-m-d H:i:s');
        $adb->pquery("INSERT INTO vtiger_notifications (userid, module, recordid, message, is_read, created_at) VALUES (1, 'Test', 1, ?, 0, NOW())", array($message));
        echo "<p class='success'>✅ Test notification created!</p>";
    } else {
        echo "<p class='success'>✅ Found $count notification(s) in database</p>";
    }
    
    // 2. Check files
    echo "<hr><h3>2. Required Files</h3>";
    $files = array(
        'layouts/v7/modules/Vtiger/partials/Topbar.tpl' => 'Topbar template',
        'layouts/v7/modules/Vtiger/resources/ModernNotifications.js' => 'Notification JS',
        'layouts/v7/modules/Vtiger/resources/ModernNotifications.css' => 'Notification CSS',
        'modules/Vtiger/actions/Notifications.php' => 'Notifications action',
        'modules/Vtiger/actions/MarkNotificationRead.php' => 'Mark read action',
        'modules/Vtiger/actions/DeleteNotification.php' => 'Delete action'
    );
    
    $allOk = true;
    foreach ($files as $file => $desc) {
        if (file_exists($file)) {
            $size = filesize($file);
            echo "<p class='success'>✅ $desc: OK ($size bytes)</p>";
        } else {
            echo "<p class='error'>❌ $desc: MISSING ($file)</p>";
            $allOk = false;
        }
    }
    
    // 3. Check Topbar.tpl for correct IDs
    echo "<hr><h3>3. Topbar Template IDs</h3>";
    $topbarContent = file_get_contents('layouts/v7/modules/Vtiger/partials/Topbar.tpl');
    $requiredIds = array(
        'modern-notifications-container',
        'notificationBell',
        'notificationBadge',
        'modern-notifications-tab-unread-link',
        'modern-notifications-tab-read-link',
        'modern-notifications-tab-unread',
        'modern-notifications-tab-read',
        'modern-notifications-empty-unread',
        'modern-notifications-empty-read',
        'modern-notifications-items',
        'modern-notifications-actions',
        'modern-notifications-mark-all-read',
        'modern-notifications-delete-all'
    );
    
    $missingIds = array();
    foreach ($requiredIds as $id) {
        if (strpos($topbarContent, $id) === false) {
            $missingIds[] = $id;
            echo "<p class='error'>❌ Missing ID: $id</p>";
        } else {
            echo "<p class='success'>✅ Found ID: $id</p>";
        }
    }
    
    if (!empty($missingIds)) {
        echo "<p class='error'><strong>⚠️ Some IDs are missing in Topbar.tpl. Please run fix_notification_display.php</strong></p>";
    }
    
    // 4. Test API endpoint
    echo "<hr><h3>4. Test API Endpoint</h3>";
    echo "<p>Try accessing: <a href='index.php?module=Vtiger&action=Notifications&type=unread' target='_blank'>Notification API</a></p>";
    echo "<p>Expected: JSON response with notifications list</p>";
    
    // 5. Summary
    echo "<hr><h3>📋 Summary</h3>";
    echo "<ul>";
    echo "<li>Notifications in DB: $count</li>";
    echo "<li>Files: " . ($allOk ? "All OK" : "Some missing") . "</li>";
    echo "<li>Topbar IDs: " . (empty($missingIds) ? "All OK" : count($missingIds) . " missing") . "</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>✅ Next Steps</h3>";
    echo "<ol>";
    echo "<li>Clear browser cache (Ctrl+Shift+R)</li>";
    echo "<li>Reload Vtiger: <a href='index.php' target='_blank'>Open Vtiger</a></li>";
    echo "<li>Look for bell icon (🔔) in top navigation</li>";
    echo "<li>Click bell icon to see notifications</li>";
    echo "<li>Open browser console (F12) to check for JavaScript errors</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<div style='background:#fee;border:1px solid #f00;padding:10px;'>";
    echo "<h3 class='error'>ERROR</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</body></html>";

