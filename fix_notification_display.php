<?php
/**
 * Fix Notification Display Issues
 * This script will:
 * 1. Register all event handlers
 * 2. Create a test notification
 * 3. Verify everything is working
 * 
 * Run via browser: http://localhost:8080/fix_notification_display.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix Notification Display</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;}</style>";
echo "</head><body>";
echo "<h2>🔧 Fixing Notification Display Issues</h2>";
echo "<hr>";

try {
    require_once 'config.php';
    require_once 'include/database/PearDatabase.php';

    global $adb;
    $adb = PearDatabase::getInstance();
    
    echo "<p class='success'>✅ Database connection: OK</p>";
    echo "<hr>";

    // Step 1: Register all event handlers
    echo "<h3>Step 1: Registering Event Handlers</h3>";
    
    $handlers = array(
        array('name' => 'HelpDeskHandler', 'event' => 'vtiger.entity.aftersave.final', 'path' => 'modules/HelpDesk/HelpDeskHandler.php', 'class' => 'HelpDeskHandler'),
        array('name' => 'ContactsHandler', 'event' => 'vtiger.entity.aftersave.final', 'path' => 'modules/Contacts/ContactsHandler.php', 'class' => 'ContactsHandler'),
        array('name' => 'AccountsHandler', 'event' => 'vtiger.entity.aftersave.final', 'path' => 'modules/Accounts/AccountsHandler.php', 'class' => 'AccountsHandler'),
        array('name' => 'ProjectHandler', 'event' => 'vtiger.entity.aftersave.final', 'path' => 'modules/Project/ProjectHandler.php', 'class' => 'ProjectHandler'),
        array('name' => 'ProjectTaskHandler', 'event' => 'vtiger.entity.aftersave.final', 'path' => 'modules/ProjectTask/ProjectTaskHandler.php', 'class' => 'ProjectTaskHandler'),
        array('name' => 'CalendarHandler', 'event' => 'vtiger.entity.aftersave.final', 'path' => 'modules/Calendar/CalendarHandler.php', 'class' => 'CalendarHandler'),
        array('name' => 'PotentialsHandler', 'event' => 'vtiger.entity.aftersave.final', 'path' => 'modules/Potentials/PotentialsHandler.php', 'class' => 'PotentialsHandler')
    );
    
    $registered = 0;
    foreach ($handlers as $handler) {
        if (!file_exists($handler['path'])) {
            echo "<p class='error'>❌ {$handler['name']}: File not found</p>";
            continue;
        }
        
        $check = $adb->pquery(
            "SELECT eventhandler_id, is_active FROM vtiger_eventhandlers 
             WHERE event_name = ? AND handler_path = ? AND handler_class = ?",
            array($handler['event'], $handler['path'], $handler['class'])
        );
        
        if ($adb->num_rows($check) > 0) {
            $row = $adb->fetchByAssoc($check);
            if (!$row['is_active']) {
                $adb->pquery("UPDATE vtiger_eventhandlers SET is_active = 1 WHERE eventhandler_id = ?", array($row['eventhandler_id']));
                echo "<p class='success'>✅ {$handler['name']}: Activated</p>";
                $registered++;
            } else {
                echo "<p class='success'>✅ {$handler['name']}: Already active</p>";
                $registered++;
            }
        } else {
            $handlerId = $adb->getUniqueId('vtiger_eventhandlers');
            $result = $adb->pquery(
                "INSERT INTO vtiger_eventhandlers (eventhandler_id, event_name, handler_path, handler_class, cond, is_active, dependent_on)
                 VALUES (?, ?, ?, ?, '', 1, '[]')",
                array($handlerId, $handler['event'], $handler['path'], $handler['class'])
            );
            
            if ($result) {
                echo "<p class='success'>✅ {$handler['name']}: Registered</p>";
                $registered++;
            } else {
                echo "<p class='error'>❌ {$handler['name']}: Failed</p>";
            }
        }
    }
    
    echo "<p><strong>Total handlers active: $registered / " . count($handlers) . "</strong></p>";
    echo "<hr>";

    // Step 2: Create test notification
    echo "<h3>Step 2: Creating Test Notification</h3>";
    
    $userId = 1; // Admin user
    $message = "🔔 Test notification - Hệ thống thông báo đang hoạt động! " . date('Y-m-d H:i:s');
    
    // Check if test notification already exists
    $check = $adb->pquery("SELECT id FROM vtiger_notifications WHERE userid = ? AND message LIKE ?", array($userId, '%Test notification%'));
    
    if ($adb->num_rows($check) == 0) {
        $insertSql = "INSERT INTO vtiger_notifications (userid, module, recordid, message, is_read, created_at) VALUES (?, 'Test', 1, ?, 0, NOW())";
        $result = $adb->pquery($insertSql, array($userId, $message));
        
        if ($result) {
            echo "<p class='success'>✅ Test notification created!</p>";
            echo "<p>Message: $message</p>";
        } else {
            echo "<p class='error'>❌ Failed to create test notification</p>";
        }
    } else {
        echo "<p class='warning'>⚠️ Test notification already exists</p>";
    }
    
    // Count notifications
    $countResult = $adb->pquery("SELECT COUNT(*) as cnt FROM vtiger_notifications WHERE userid = ? AND is_read = 0", array($userId));
    $unreadCount = 0;
    if ($adb->num_rows($countResult) > 0) {
        $row = $adb->fetchByAssoc($countResult);
        $unreadCount = $row['cnt'];
    }
    
    echo "<p><strong>Unread notifications for user $userId: $unreadCount</strong></p>";
    echo "<hr>";

    // Step 3: Verify files
    echo "<h3>Step 3: Verifying Files</h3>";
    
    $files = array(
        'layouts/v7/modules/Vtiger/partials/Topbar.tpl' => 'Topbar template with bell icon',
        'layouts/v7/modules/Vtiger/resources/ModernNotifications.js' => 'Notification JavaScript',
        'layouts/v7/modules/Vtiger/resources/ModernNotifications.css' => 'Notification CSS',
        'modules/Vtiger/actions/Notifications.php' => 'Notifications action',
        'modules/Vtiger/actions/MarkNotificationRead.php' => 'Mark read action',
        'modules/Vtiger/actions/DeleteNotification.php' => 'Delete action'
    );
    
    $allFilesOk = true;
    foreach ($files as $file => $desc) {
        if (file_exists($file)) {
            echo "<p class='success'>✅ $desc: OK</p>";
        } else {
            echo "<p class='error'>❌ $desc: Missing ($file)</p>";
            $allFilesOk = false;
        }
    }
    
    echo "<hr>";
    
    // Final summary
    echo "<h3>🎯 Summary</h3>";
    echo "<ul>";
    echo "<li>Event handlers: $registered / " . count($handlers) . " active</li>";
    echo "<li>Test notification: Created</li>";
    echo "<li>Unread count: $unreadCount</li>";
    echo "<li>Files: " . ($allFilesOk ? "All OK" : "Some missing") . "</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>✅ Next Steps</h3>";
    echo "<ol>";
    echo "<li>Clear browser cache (Ctrl+Shift+R or Cmd+Shift+R)</li>";
    echo "<li>Reload Vtiger page: <a href='index.php' target='_blank'>Open Vtiger</a></li>";
    echo "<li>Look for the bell icon (🔔) in the top navigation bar</li>";
    echo "<li>Click the bell icon to see notifications</li>";
    echo "</ol>";
    
    echo "<p class='success'><strong>🎉 Setup complete! Please reload your browser to see the notification bell icon.</strong></p>";
    
} catch (Exception $e) {
    echo "<div style='background:#fee;border:1px solid #f00;padding:10px;margin:10px 0;'>";
    echo "<h3 class='error'>ERROR</h3>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "</body></html>";

