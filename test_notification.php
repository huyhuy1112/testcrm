<?php
/**
 * Test script to create a notification
 * Run via browser: http://localhost:8080/test_notification.php
 */

require_once 'config.php';
require_once 'include/database/PearDatabase.php';

global $adb;
$adb = PearDatabase::getInstance();

// Get current user ID (or use admin user ID = 1)
$userId = 1; // Change this to your user ID

// Create a test notification
$message = "🔔 Test notification - Hệ thống thông báo đang hoạt động! " . date('Y-m-d H:i:s');
$insertSql = "INSERT INTO vtiger_notifications (userid, module, recordid, message, is_read, created_at) VALUES (?, 'Test', 1, ?, 0, NOW())";
$result = $adb->pquery($insertSql, array($userId, $message));

if ($result) {
    echo "✅ Test notification created successfully!<br>";
    echo "User ID: $userId<br>";
    echo "Message: $message<br>";
    echo "<br><a href='index.php'>Go to Vtiger</a> to see the notification bell icon.";
} else {
    echo "❌ Failed to create notification";
}

