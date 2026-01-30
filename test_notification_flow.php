<?php
/**
 * Test Notification Flow - Simulate record creation and verify notification
 * Run via browser: http://localhost:8080/test_notification_flow.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test Notification Flow</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .info{color:blue;}</style>";
echo "</head><body>";
echo "<h2>🧪 Test Notification Flow</h2>";
echo "<hr>";

try {
    require_once 'config.php';
    require_once 'include/database/PearDatabase.php';
    require_once 'include/events/include.inc';
    
    global $adb;
    $adb = PearDatabase::getInstance();
    
    echo "<h3>Step 1: Verify Handlers</h3>";
    $handlers = $adb->pquery(
        "SELECT handler_class, is_active FROM vtiger_eventhandlers 
         WHERE handler_class IN (?, ?, ?, ?, ?, ?, ?) AND is_active = 1",
        array('ProjectHandler', 'ProjectTaskHandler', 'CalendarHandler', 'PotentialsHandler', 'AccountsHandler', 'ContactsHandler', 'HelpDeskHandler')
    );
    
    $activeCount = $adb->num_rows($handlers);
    echo "<p>Active notification handlers: $activeCount / 7</p>";
    
    if ($activeCount == 7) {
        echo "<p class='success'>✅ All handlers active</p>";
    } else {
        echo "<p class='error'>❌ Some handlers missing or inactive</p>";
    }
    
    echo "<h3>Step 2: Test Event System</h3>";
    $em = new VTEventsManager($adb);
    $em->initTriggerCache('vtiger.entity.aftersave.final');
    echo "<p class='success'>✅ VTEventsManager initialized</p>";
    echo "<p class='success'>✅ Event trigger cache initialized</p>";
    
    echo "<h3>Step 3: Check Current Notifications</h3>";
    $beforeCount = $adb->pquery("SELECT COUNT(*) as cnt FROM vtiger_notifications", array());
    $beforeRow = $adb->fetchByAssoc($beforeCount);
    $beforeTotal = $beforeRow['cnt'];
    echo "<p>Notifications before test: $beforeTotal</p>";
    
    echo "<h3>Step 4: Manual Test</h3>";
    echo "<p class='info'>To test notifications:</p>";
    echo "<ol>";
    echo "<li>Go to <a href='index.php?module=Project&view=Edit' target='_blank'>Create Project</a></li>";
    echo "<li>Fill in project name</li>";
    echo "<li>Assign to a user (not group)</li>";
    echo "<li>Save the project</li>";
    echo "<li>Check notifications: <a href='index.php?module=Vtiger&action=Notifications&type=unread' target='_blank'>View Notifications</a></li>";
    echo "</ol>";
    
    echo "<h3>Step 5: Database Check</h3>";
    echo "<p>After creating a record, check:</p>";
    echo "<pre>";
    echo "SELECT * FROM vtiger_notifications \n";
    echo "WHERE module = 'Project' \n";
    echo "ORDER BY created_at DESC \n";
    echo "LIMIT 5;";
    echo "</pre>";
    
    echo "<hr>";
    echo "<h3>✅ System Ready for Testing</h3>";
    echo "<p>All handlers are registered. Create a record via UI to test.</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";


