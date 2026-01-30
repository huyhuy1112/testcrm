<?php
/**
 * Test Notification API directly
 * Run via browser: http://localhost:8080/test_notification_api.php
 */

require_once 'config.php';
require_once 'include/database/PearDatabase.php';
require_once 'include/Webservices/Utils.php';
require_once 'modules/Users/Users.php';

// Set current user
$current_user = Users::getActiveAdminUser();
$_SESSION['authenticated_user_id'] = $current_user->id;
$_SESSION['app_unique_key'] = $application_unique_key;

global $adb, $current_user;

echo "<h2>Testing Notification API</h2>";
echo "<hr>";

// Test 1: Check database
echo "<h3>Test 1: Database Check</h3>";
$countResult = $adb->pquery("SELECT COUNT(*) as cnt FROM vtiger_notifications WHERE userid = ?", array($current_user->id));
$row = $adb->fetchByAssoc($countResult);
echo "<p>Total notifications for user {$current_user->id}: {$row['cnt']}</p>";

$unreadResult = $adb->pquery("SELECT COUNT(*) as cnt FROM vtiger_notifications WHERE userid = ? AND is_read = 0", array($current_user->id));
$unreadRow = $adb->fetchByAssoc($unreadResult);
echo "<p>Unread notifications: {$unreadRow['cnt']}</p>";

// Test 2: Get notifications directly
echo "<h3>Test 2: Direct Database Query</h3>";
$sql = "SELECT id, module, recordid, message, created_at
        FROM vtiger_notifications
        WHERE userid = ? AND is_read = 0
        ORDER BY created_at DESC
        LIMIT 20";

$result = $adb->pquery($sql, array($current_user->id));
$list = array();
while ($row = $adb->fetchByAssoc($result)) {
    $list[] = $row;
}

echo "<p>Found " . count($list) . " unread notifications:</p>";
echo "<pre>";
print_r($list);
echo "</pre>";

// Test 3: Test action class
echo "<h3>Test 3: Action Class Test</h3>";
try {
    require_once 'modules/Vtiger/actions/Notifications.php';
    
    // Create a mock request
    class MockRequest {
        private $params = array();
        public function get($key) {
            return isset($this->params[$key]) ? $this->params[$key] : null;
        }
        public function set($key, $value) {
            $this->params[$key] = $value;
        }
    }
    
    $request = new MockRequest();
    $request->set('type', 'unread');
    
    $action = new Vtiger_Notifications_Action();
    
    // Capture output
    ob_start();
    $action->process($request);
    $output = ob_get_clean();
    
    echo "<p>Action output:</p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    $json = json_decode($output, true);
    if ($json) {
        echo "<p class='success'>✅ JSON is valid</p>";
        echo "<p>Success: " . ($json['success'] ? 'Yes' : 'No') . "</p>";
        echo "<p>Count: " . $json['count'] . "</p>";
        echo "<p>Type: " . $json['type'] . "</p>";
    } else {
        echo "<p class='error'>❌ Invalid JSON</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}

// Test 4: Create more test notifications
echo "<h3>Test 4: Create More Test Notifications</h3>";
for ($i = 1; $i <= 3; $i++) {
    $message = "Test notification #$i - " . date('Y-m-d H:i:s');
    $insertSql = "INSERT INTO vtiger_notifications (userid, module, recordid, message, is_read, created_at) 
                   VALUES (?, 'Test', $i, ?, 0, NOW())";
    $result = $adb->pquery($insertSql, array($current_user->id, $message));
    
    if ($result) {
        echo "<p class='success'>✅ Created notification #$i</p>";
    } else {
        echo "<p class='error'>❌ Failed to create notification #$i</p>";
    }
}

echo "<hr>";
echo "<h3>✅ Test Complete</h3>";
echo "<p>Now try accessing: <a href='index.php?module=Vtiger&action=Notifications&type=unread' target='_blank'>Notification API</a></p>";
echo "<p>Or go to Vtiger: <a href='index.php' target='_blank'>Vtiger Home</a></p>";


