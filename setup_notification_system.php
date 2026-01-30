<?php
/**
 * Complete setup script for Notification System
 * This script will:
 * 1. Check and create vtiger_notifications table if missing
 * 2. Verify all required tables exist
 * 3. Register all event handlers
 * 4. Register DeadlineReminder cron job
 * 
 * Run via browser: http://localhost:8080/setup_notification_system.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Complete Notification System Setup</title>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;margin:10px 0;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f0f0f0;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;font-weight:bold;} .info{color:blue;} .step{background:#f9f9f9;padding:15px;margin:10px 0;border-left:4px solid #007bff;}</style>";
echo "</head><body>";
echo "<h1>🔔 Complete Notification System Setup</h1>";
echo "<hr>";

try {
    require_once 'config.php';
    require_once 'include/database/PearDatabase.php';

    global $adb;
    $adb = PearDatabase::getInstance();
    
    if (!$adb) {
        throw new Exception("Failed to initialize database connection");
    }
    
    echo "<div class='step'>";
    echo "<h2>Step 1: Database Connection</h2>";
    echo "<p class='success'>✅ Database connection: OK</p>";
    echo "</div>";

    // Step 2: Check and create vtiger_notifications table
    echo "<div class='step'>";
    echo "<h2>Step 2: Create vtiger_notifications Table</h2>";
    
    $checkNotifications = $adb->query("SHOW TABLES LIKE 'vtiger_notifications'");
    $notificationsExists = ($adb->num_rows($checkNotifications) > 0);

    if (!$notificationsExists) {
        echo "<p class='warning'>⚠️ Table does not exist. Creating...</p>";
        
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
        echo "<p class='success'>✅ Table created successfully!</p>";
    } else {
        echo "<p class='success'>✅ Table already exists</p>";
        
        // Check if read_at column exists
        $columns = $adb->query("DESCRIBE vtiger_notifications");
        $hasReadAt = false;
        while ($row = $adb->fetchByAssoc($columns)) {
            if ($row['Field'] == 'read_at') {
                $hasReadAt = true;
                break;
            }
        }
        
        if (!$hasReadAt) {
            echo "<p class='warning'>⚠️ Adding missing 'read_at' column...</p>";
            $adb->query("ALTER TABLE vtiger_notifications ADD COLUMN read_at DATETIME NULL AFTER is_read");
            echo "<p class='success'>✅ Column added!</p>";
        }
    }
    echo "</div>";

    // Step 3: Verify required tables
    echo "<div class='step'>";
    echo "<h2>Step 3: Verify Required Tables</h2>";
    
    $requiredTables = array(
        'vtiger_crmentity', 'vtiger_users', 'vtiger_notifications',
        'vtiger_account', 'vtiger_contactdetails', 'vtiger_project',
        'vtiger_projecttask', 'vtiger_potential', 'vtiger_activity',
        'vtiger_troubletickets', 'vtiger_eventhandlers', 'vtiger_cron_task'
    );
    
    $missing = array();
    foreach ($requiredTables as $table) {
        $check = $adb->query("SHOW TABLES LIKE '$table'");
        if ($adb->num_rows($check) == 0) {
            $missing[] = $table;
        }
    }
    
    if (empty($missing)) {
        echo "<p class='success'>✅ All required tables exist (" . count($requiredTables) . " tables)</p>";
    } else {
        echo "<p class='error'>❌ Missing tables: " . implode(', ', $missing) . "</p>";
        echo "<p class='warning'>⚠️ Please re-run installation wizard to create missing tables.</p>";
    }
    echo "</div>";

    // Step 4: Register Event Handlers
    echo "<div class='step'>";
    echo "<h2>Step 4: Register Event Handlers</h2>";
    
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
    $activated = 0;
    $skipped = 0;
    $failed = 0;
    
    echo "<table>";
    echo "<tr><th>Handler</th><th>Status</th></tr>";
    
    foreach ($handlers as $handler) {
        if (!file_exists($handler['path'])) {
            echo "<tr><td>{$handler['name']}</td><td class='error'>❌ File not found</td></tr>";
            $failed++;
            continue;
        }
        
        $check = $adb->pquery(
            "SELECT eventhandler_id, is_active FROM vtiger_eventhandlers 
             WHERE event_name = ? AND handler_path = ? AND handler_class = ?",
            array($handler['event'], $handler['path'], $handler['class'])
        );
        
        if ($adb->num_rows($check) > 0) {
            $row = $adb->fetchByAssoc($check);
            if ($row['is_active']) {
                echo "<tr><td>{$handler['name']}</td><td class='success'>✅ Active</td></tr>";
                $skipped++;
            } else {
                $adb->pquery("UPDATE vtiger_eventhandlers SET is_active = 1 WHERE eventhandler_id = ?", array($row['eventhandler_id']));
                echo "<tr><td>{$handler['name']}</td><td class='success'>✅ Activated</td></tr>";
                $activated++;
            }
        } else {
            $handlerId = $adb->getUniqueId('vtiger_eventhandlers');
            $result = $adb->pquery(
                "INSERT INTO vtiger_eventhandlers (eventhandler_id, event_name, handler_path, handler_class, cond, is_active, dependent_on)
                 VALUES (?, ?, ?, ?, '', 1, '[]')",
                array($handlerId, $handler['event'], $handler['path'], $handler['class'])
            );
            
            if ($result) {
                echo "<tr><td>{$handler['name']}</td><td class='success'>✅ Registered</td></tr>";
                $registered++;
            } else {
                echo "<tr><td>{$handler['name']}</td><td class='error'>❌ Failed</td></tr>";
                $failed++;
            }
        }
    }
    
    echo "</table>";
    echo "<p><strong>Summary:</strong> $registered registered, $activated activated, $skipped already active, $failed failed</p>";
    echo "</div>";

    // Step 5: Register Cron Job
    echo "<div class='step'>";
    echo "<h2>Step 5: Register DeadlineReminder Cron Job</h2>";
    
    $cronName = 'DeadlineReminder';
    $handlerFile = 'cron/DeadlineReminder.service';
    $frequency = 86400; // 24 hours
    
    $check = $adb->pquery("SELECT cronid, status FROM vtiger_cron_task WHERE name = ?", array($cronName));
    
    if ($adb->num_rows($check) > 0) {
        $row = $adb->fetchByAssoc($check);
        if ($row['status']) {
            echo "<p class='success'>✅ Cron job already active</p>";
        } else {
            $adb->pquery("UPDATE vtiger_cron_task SET status = 1, handler_file = ?, frequency = ? WHERE cronid = ?", 
                        array($handlerFile, $frequency, $row['cronid']));
            echo "<p class='success'>✅ Cron job activated</p>";
        }
    } else {
        $cronId = $adb->getUniqueId('vtiger_cron_task');
        $result = $adb->pquery(
            "INSERT INTO vtiger_cron_task (cronid, name, handler_file, frequency, status, laststart, lastend, sequence)
             VALUES (?, ?, ?, ?, 1, 0, 0, 0)",
            array($cronId, $cronName, $handlerFile, $frequency)
        );
        
        if ($result) {
            echo "<p class='success'>✅ Cron job registered successfully!</p>";
        } else {
            echo "<p class='error'>❌ Failed to register cron job</p>";
        }
    }
    echo "</div>";

    // Final Summary
    echo "<hr>";
    echo "<h2>🎉 Setup Complete!</h2>";
    echo "<div class='step'>";
    echo "<h3>✅ What's Ready:</h3>";
    echo "<ul>";
    echo "<li>✅ Database table: vtiger_notifications</li>";
    echo "<li>✅ Event handlers: " . ($registered + $activated + $skipped) . " handlers active</li>";
    echo "<li>✅ Cron job: DeadlineReminder registered</li>";
    echo "</ul>";
    
    echo "<h3>📝 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Test the notification system by creating/editing records and assigning them to users</li>";
    echo "<li>Check notifications at: <a href='index.php?module=Vtiger&view=Index'>Vtiger Home</a></li>";
    echo "<li>Verify cron job runs: Check cron logs or wait 24 hours for deadline reminders</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#fee;border:1px solid #f00;padding:10px;margin:10px 0;'>";
    echo "<h3 class='error'>ERROR</h3>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "</body></html>";


