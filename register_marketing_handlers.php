<?php
/**
 * Force register AccountsHandler and ContactsHandler for Marketing notifications
 * Run via browser: http://localhost:8080/register_marketing_handlers.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Register Marketing Handlers</title>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;} th{background:#f0f0f0;}</style>";
echo "</head><body>";
echo "<h2>Registering Marketing Notification Handlers</h2>";
echo "<hr>";

try {
    if (!file_exists('config.php')) {
        throw new Exception("config.php not found. Make sure you're running this from the Vtiger root directory.");
    }
    
    require_once 'config.php';
    
    if (!file_exists('include/database/PearDatabase.php')) {
        throw new Exception("PearDatabase.php not found.");
    }
    
    require_once 'include/database/PearDatabase.php';

    global $adb;
    $adb = PearDatabase::getInstance();
    
    if (!$adb) {
        throw new Exception("Failed to initialize database connection");
    }
    
    echo "<p><strong>Database connection: OK</strong></p>";
    echo "<hr>";

    $handlers = array(
        array(
            'name' => 'AccountsHandler',
            'event' => 'vtiger.entity.aftersave.final',
            'path' => 'modules/Accounts/AccountsHandler.php',
            'class' => 'AccountsHandler'
        ),
        array(
            'name' => 'ContactsHandler',
            'event' => 'vtiger.entity.aftersave.final',
            'path' => 'modules/Contacts/ContactsHandler.php',
            'class' => 'ContactsHandler'
        )
    );

    foreach ($handlers as $handler) {
        echo "<h3>" . htmlspecialchars($handler['name']) . "</h3>";
        
        // Check if handler file exists
        if (!file_exists($handler['path'])) {
            echo "<p style='color:red;'>ERROR: Handler file not found: " . htmlspecialchars($handler['path']) . "</p><br>";
            continue;
        }
        
        $check = $adb->pquery(
            "SELECT eventhandler_id, is_active FROM vtiger_eventhandlers 
             WHERE event_name = ? AND handler_path = ? AND handler_class = ?",
            array($handler['event'], $handler['path'], $handler['class'])
        );
        
        if ($adb->num_rows($check) > 0) {
            $row = $adb->fetchByAssoc($check);
            echo "<p>Status: <strong>Already registered</strong></p>";
            echo "<p>Handler ID: " . htmlspecialchars($row['eventhandler_id']) . "</p>";
            echo "<p>Active: " . ($row['is_active'] ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>') . "</p>";
            
            if (!$row['is_active']) {
                $adb->pquery(
                    "UPDATE vtiger_eventhandlers SET is_active = 1 
                     WHERE eventhandler_id = ?",
                    array($row['eventhandler_id'])
                );
                echo "<p style='color:green;'><strong>✓ Activated!</strong></p>";
            }
        } else {
            $handlerId = $adb->getUniqueId('vtiger_eventhandlers');
            $result = $adb->pquery(
                "INSERT INTO vtiger_eventhandlers 
                 (eventhandler_id, event_name, handler_path, handler_class, cond, is_active, dependent_on)
                 VALUES (?, ?, ?, ?, '', 1, '[]')",
                array($handlerId, $handler['event'], $handler['path'], $handler['class'])
            );
            
            if ($result) {
                echo "<p style='color:green;'><strong>✓ Registered successfully!</strong></p>";
                echo "<p>Handler ID: $handlerId</p>";
            } else {
                echo "<p style='color:red;'><strong>✗ Registration failed!</strong></p>";
            }
        }
        
        echo "<br>";
    }

    echo "<hr>";
    echo "<h3>Verification</h3>";

    $verify = $adb->pquery(
        "SELECT handler_class, is_active, event_name, handler_path
         FROM vtiger_eventhandlers 
         WHERE handler_class IN ('AccountsHandler', 'ContactsHandler')",
        array()
    );

    echo "<table>";
    echo "<tr><th>Handler Class</th><th>Event</th><th>Path</th><th>Active</th></tr>";

    $foundCount = 0;
    while ($row = $adb->fetchByAssoc($verify)) {
        $foundCount++;
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['handler_class']) . "</td>";
        echo "<td>" . htmlspecialchars($row['event_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['handler_path']) . "</td>";
        echo "<td>" . ($row['is_active'] ? '<span style="color:green;">✓ Yes</span>' : '<span style="color:red;">✗ No</span>') . "</td>";
        echo "</tr>";
    }
    
    if ($foundCount == 0) {
        echo "<tr><td colspan='4' style='color:red;'>No handlers found!</td></tr>";
    }

    echo "</table>";

    echo "<hr>";
    if ($foundCount >= 2) {
        echo "<p style='color:green;'><strong>✓ Done! Both handlers are now registered and active.</strong></p>";
    } else {
        echo "<p style='color:orange;'><strong>⚠ Warning: Only $foundCount handler(s) found. Expected 2.</strong></p>";
    }
    echo "<p>You can now test by creating/editing Organizations or Contacts and assigning them to users.</p>";
    
} catch (Exception $e) {
    echo "<div style='background:#fee;border:1px solid #f00;padding:10px;margin:10px 0;'>";
    echo "<h3 style='color:red;'>ERROR</h3>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "</body></html>";
