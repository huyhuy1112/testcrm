<?php
/**
 * Register all notification handlers for Modern Notification System
 * Run via browser: http://localhost:8080/register_all_notification_handlers.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Register All Notification Handlers</title>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;} th{background:#f0f0f0;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";
echo "</head><body>";
echo "<h2>🔔 Registering All Notification Handlers</h2>";
echo "<hr>";

try {
    if (!file_exists('config.php')) {
        throw new Exception("config.php not found. Make sure you're running this from the Vtiger root directory.");
    }
    
    require_once 'config.php';
    require_once 'include/database/PearDatabase.php';

    global $adb;
    $adb = PearDatabase::getInstance();
    
    if (!$adb) {
        throw new Exception("Failed to initialize database connection");
    }
    
    echo "<p class='success'><strong>✅ Database connection: OK</strong></p>";
    echo "<hr>";

    // All notification handlers
    $handlers = array(
        array(
            'name' => 'HelpDeskHandler',
            'event' => 'vtiger.entity.aftersave.final',
            'path' => 'modules/HelpDesk/HelpDeskHandler.php',
            'class' => 'HelpDeskHandler'
        ),
        array(
            'name' => 'ContactsHandler',
            'event' => 'vtiger.entity.aftersave.final',
            'path' => 'modules/Contacts/ContactsHandler.php',
            'class' => 'ContactsHandler'
        ),
        array(
            'name' => 'AccountsHandler',
            'event' => 'vtiger.entity.aftersave.final',
            'path' => 'modules/Accounts/AccountsHandler.php',
            'class' => 'AccountsHandler'
        ),
        array(
            'name' => 'ProjectHandler',
            'event' => 'vtiger.entity.aftersave.final',
            'path' => 'modules/Project/ProjectHandler.php',
            'class' => 'ProjectHandler'
        ),
        array(
            'name' => 'ProjectTaskHandler',
            'event' => 'vtiger.entity.aftersave.final',
            'path' => 'modules/ProjectTask/ProjectTaskHandler.php',
            'class' => 'ProjectTaskHandler'
        ),
        array(
            'name' => 'CalendarHandler',
            'event' => 'vtiger.entity.aftersave.final',
            'path' => 'modules/Calendar/CalendarHandler.php',
            'class' => 'CalendarHandler'
        ),
        array(
            'name' => 'PotentialsHandler',
            'event' => 'vtiger.entity.aftersave.final',
            'path' => 'modules/Potentials/PotentialsHandler.php',
            'class' => 'PotentialsHandler'
        )
    );

    $registered = 0;
    $activated = 0;
    $skipped = 0;

    echo "<table>";
    echo "<tr><th>Handler</th><th>Status</th><th>Details</th></tr>";

    foreach ($handlers as $handler) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($handler['name']) . "</strong></td>";
        
        // Check if handler file exists
        if (!file_exists($handler['path'])) {
            echo "<td class='error'>❌ File not found</td>";
            echo "<td class='error'>" . htmlspecialchars($handler['path']) . "</td>";
            $skipped++;
        } else {
            $check = $adb->pquery(
                "SELECT eventhandler_id, is_active FROM vtiger_eventhandlers 
                 WHERE event_name = ? AND handler_path = ? AND handler_class = ?",
                array($handler['event'], $handler['path'], $handler['class'])
            );
            
            if ($adb->num_rows($check) > 0) {
                $row = $adb->fetchByAssoc($check);
                if ($row['is_active']) {
                    echo "<td class='success'>✅ Already active</td>";
                    echo "<td>ID: " . htmlspecialchars($row['eventhandler_id']) . "</td>";
                    $skipped++;
                } else {
                    $adb->pquery(
                        "UPDATE vtiger_eventhandlers SET is_active = 1 
                         WHERE eventhandler_id = ?",
                        array($row['eventhandler_id'])
                    );
                    echo "<td class='success'>✅ Activated</td>";
                    echo "<td>ID: " . htmlspecialchars($row['eventhandler_id']) . " (was inactive)</td>";
                    $activated++;
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
                    echo "<td class='success'>✅ Registered</td>";
                    echo "<td>New ID: $handlerId</td>";
                    $registered++;
                } else {
                    echo "<td class='error'>❌ Failed</td>";
                    echo "<td class='error'>Database error</td>";
                    $skipped++;
                }
            }
        }
        
        echo "</tr>";
    }

    echo "</table>";

    echo "<hr>";
    echo "<h3>📊 Summary</h3>";
    echo "<ul>";
    echo "<li><strong>Newly registered:</strong> $registered</li>";
    echo "<li><strong>Activated:</strong> $activated</li>";
    echo "<li><strong>Skipped (already active):</strong> $skipped</li>";
    echo "</ul>";

    echo "<hr>";
    echo "<h3>✅ Verification</h3>";

    $verify = $adb->pquery(
        "SELECT handler_class, is_active, event_name, handler_path
         FROM vtiger_eventhandlers 
         WHERE handler_class IN ('HelpDeskHandler', 'ContactsHandler', 'AccountsHandler', 'ProjectHandler', 'ProjectTaskHandler', 'CalendarHandler', 'PotentialsHandler')
         ORDER BY handler_class",
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
        echo "<td>" . ($row['is_active'] ? '<span class="success">✓ Yes</span>' : '<span class="error">✗ No</span>') . "</td>";
        echo "</tr>";
    }
    
    if ($foundCount == 0) {
        echo "<tr><td colspan='4' class='error'>No handlers found!</td></tr>";
    }

    echo "</table>";

    echo "<hr>";
    if ($foundCount >= 7) {
        echo "<p class='success'><strong>🎉 Done! All notification handlers are registered and active.</strong></p>";
        echo "<p>The notification system will now automatically create notifications when:</p>";
        echo "<ul>";
        echo "<li>HelpDesk tickets are assigned</li>";
        echo "<li>Contacts are assigned</li>";
        echo "<li>Accounts/Organizations are assigned</li>";
        echo "<li>Projects are assigned</li>";
        echo "<li>Project Tasks are assigned</li>";
        echo "<li>Calendar events/Tasks are assigned</li>";
        echo "<li>Potentials/Opportunities are assigned</li>";
        echo "</ul>";
    } else {
        echo "<p class='warning'><strong>⚠ Warning: Only $foundCount handler(s) found. Expected 7.</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background:#fee;border:1px solid #f00;padding:10px;margin:10px 0;'>";
    echo "<h3 class='error'>ERROR</h3>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "</body></html>";


