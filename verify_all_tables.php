<?php
/**
 * Comprehensive verification of all tables required by notification system
 * Run via browser: http://localhost:8080/verify_all_tables.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Verify All Required Tables</title>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;margin:10px 0;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f0f0f0;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;font-weight:bold;} .info{color:blue;}</style>";
echo "</head><body>";
echo "<h2>🔍 Comprehensive Table Verification for Notification System</h2>";
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

    // All tables used by notification system
    $requiredTables = array(
        // Core Vtiger tables
        'vtiger_crmentity' => 'Core entity table - tracks all records',
        'vtiger_users' => 'User accounts table',
        
        // Notification system
        'vtiger_notifications' => 'Notification messages table',
        
        // Module tables used by handlers
        'vtiger_account' => 'Accounts/Organizations module',
        'vtiger_contactdetails' => 'Contacts module',
        'vtiger_project' => 'Projects module',
        'vtiger_projecttask' => 'Project Tasks module',
        'vtiger_potential' => 'Potentials/Opportunities module',
        'vtiger_activity' => 'Calendar/Tasks/Events module',
        'vtiger_troubletickets' => 'HelpDesk/Tickets module',
        
        // System tables
        'vtiger_eventhandlers' => 'Event handler registration',
        'vtiger_cron_task' => 'Cron job registration'
    );

    $existingTables = array();
    $missingTables = array();
    $tableDetails = array();

    echo "<h3>📊 Table Status</h3>";
    echo "<table>";
    echo "<tr><th>Table Name</th><th>Status</th><th>Description</th><th>Row Count</th></tr>";

    foreach ($requiredTables as $table => $description) {
        $check = $adb->query("SHOW TABLES LIKE '$table'");
        
        if ($adb->num_rows($check) > 0) {
            $existingTables[] = $table;
            
            // Get row count
            $countResult = $adb->query("SELECT COUNT(*) as cnt FROM $table");
            $rowCount = 0;
            if ($adb->num_rows($countResult) > 0) {
                $row = $adb->fetchByAssoc($countResult);
                $rowCount = $row['cnt'];
            }
            
            $tableDetails[$table] = array(
                'exists' => true,
                'rowCount' => $rowCount,
                'description' => $description
            );
            
            echo "<tr>";
            echo "<td><strong>$table</strong></td>";
            echo "<td class='success'>✅ Exists</td>";
            echo "<td>$description</td>";
            echo "<td class='info'>" . number_format($rowCount) . "</td>";
            echo "</tr>";
        } else {
            $missingTables[] = $table;
            $tableDetails[$table] = array(
                'exists' => false,
                'rowCount' => 0,
                'description' => $description
            );
            
            echo "<tr>";
            echo "<td><strong>$table</strong></td>";
            echo "<td class='error'>❌ Missing</td>";
            echo "<td>$description</td>";
            echo "<td class='error'>N/A</td>";
            echo "</tr>";
        }
    }

    echo "</table>";

    echo "<hr>";
    echo "<h3>📋 Summary</h3>";
    echo "<ul>";
    echo "<li><strong class='success'>Existing tables:</strong> " . count($existingTables) . " / " . count($requiredTables) . "</li>";
    echo "<li><strong class='error'>Missing tables:</strong> " . count($missingTables) . " / " . count($requiredTables) . "</li>";
    echo "</ul>";

    // Check vtiger_notifications structure
    if (in_array('vtiger_notifications', $existingTables)) {
        echo "<hr>";
        echo "<h3>✅ Verifying vtiger_notifications Structure</h3>";
        
        $columns = $adb->query("DESCRIBE vtiger_notifications");
        $requiredColumns = array(
            'id' => 'int(19) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'userid' => 'int(19) NOT NULL',
            'module' => 'varchar(100) NOT NULL',
            'recordid' => 'int(19) NOT NULL',
            'message' => 'text NOT NULL',
            'is_read' => 'tinyint(1) DEFAULT 0',
            'read_at' => 'datetime NULL',
            'created_at' => 'datetime NOT NULL'
        );
        
        $existingColumns = array();
        while ($row = $adb->fetchByAssoc($columns)) {
            $existingColumns[$row['Field']] = $row;
        }
        
        echo "<table>";
        echo "<tr><th>Column</th><th>Required</th><th>Status</th><th>Current Type</th></tr>";
        
        $allColumnsOk = true;
        foreach ($requiredColumns as $colName => $requiredType) {
            if (isset($existingColumns[$colName])) {
                $currentType = $existingColumns[$colName]['Type'] . ' ' . 
                              ($existingColumns[$colName]['Null'] == 'NO' ? 'NOT NULL' : 'NULL');
                echo "<tr>";
                echo "<td><strong>$colName</strong></td>";
                echo "<td>$requiredType</td>";
                echo "<td class='success'>✅ OK</td>";
                echo "<td>$currentType</td>";
                echo "</tr>";
            } else {
                $allColumnsOk = false;
                echo "<tr>";
                echo "<td><strong>$colName</strong></td>";
                echo "<td>$requiredType</td>";
                echo "<td class='error'>❌ Missing</td>";
                echo "<td>-</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
        
        if (!$allColumnsOk) {
            echo "<p class='error'><strong>⚠️ Some columns are missing. Please run check_and_create_missing_tables.php to fix.</strong></p>";
        } else {
            // Check indexes
            $indexes = $adb->query("SHOW INDEXES FROM vtiger_notifications");
            $indexList = array();
            while ($row = $adb->fetchByAssoc($indexes)) {
                if ($row['Key_name'] != 'PRIMARY') {
                    $indexList[] = $row['Key_name'];
                }
            }
            $indexList = array_unique($indexList);
            
            echo "<p class='success'><strong>✅ All required columns exist!</strong></p>";
            echo "<p class='info'><strong>Indexes:</strong> " . implode(', ', $indexList) . "</p>";
        }
    } else {
        echo "<hr>";
        echo "<h3 class='error'>❌ vtiger_notifications table is missing!</h3>";
        echo "<p>Please run: <a href='check_and_create_missing_tables.php'>check_and_create_missing_tables.php</a></p>";
    }

    // Check if critical core tables exist
    $criticalCoreTables = array('vtiger_crmentity', 'vtiger_users');
    $missingCore = array_intersect($criticalCoreTables, $missingTables);
    
    if (!empty($missingCore)) {
        echo "<hr>";
        echo "<h3 class='error'>❌ Critical Core Tables Missing!</h3>";
        echo "<p class='error'><strong>These tables are essential for Vtiger to function:</strong></p>";
        echo "<ul>";
        foreach ($missingCore as $table) {
            echo "<li class='error'>$table</li>";
        }
        echo "</ul>";
        echo "<p class='warning'><strong>⚠️ Recommendation:</strong> Re-run the Vtiger installation wizard. The database may be incomplete.</p>";
    }

    // Check module tables
    $moduleTables = array('vtiger_account', 'vtiger_contactdetails', 'vtiger_project', 
                         'vtiger_projecttask', 'vtiger_potential', 'vtiger_activity', 'vtiger_troubletickets');
    $missingModules = array_intersect($moduleTables, $missingTables);
    
    if (!empty($missingModules)) {
        echo "<hr>";
        echo "<h3 class='warning'>⚠️ Some Module Tables Missing</h3>";
        echo "<p>These module tables are missing. Handlers for these modules will not work:</p>";
        echo "<ul>";
        foreach ($missingModules as $table) {
            echo "<li class='warning'>$table - " . $tableDetails[$table]['description'] . "</li>";
        }
        echo "</ul>";
    }

    echo "<hr>";
    echo "<h3>🎯 Next Steps</h3>";
    echo "<ol>";
    
    if (in_array('vtiger_notifications', $missingTables)) {
        echo "<li class='error'><strong>Create vtiger_notifications:</strong> <a href='check_and_create_missing_tables.php'>Run table creation script</a></li>";
    } else {
        echo "<li class='success'><strong>✅ vtiger_notifications:</strong> Table exists</li>";
    }
    
    if (!empty($missingCore)) {
        echo "<li class='error'><strong>⚠️ Re-run Installation:</strong> Critical core tables are missing. Please re-run Vtiger installation wizard.</li>";
    }
    
    if (in_array('vtiger_notifications', $existingTables)) {
        echo "<li><strong>Register Event Handlers:</strong> <a href='register_all_notification_handlers.php'>Run registration script</a></li>";
        echo "<li><strong>Register Cron Job:</strong> <a href='register_deadline_cron.php'>Run cron registration</a></li>";
    }
    
    echo "</ol>";
    
    // Final status
    echo "<hr>";
    if (count($missingTables) == 0) {
        echo "<p class='success'><strong>🎉 All required tables exist! Notification system is ready to use.</strong></p>";
    } else {
        $criticalMissing = count($missingCore);
        $nonCriticalMissing = count($missingTables) - $criticalMissing;
        
        if ($criticalMissing > 0) {
            echo "<p class='error'><strong>❌ $criticalMissing critical table(s) missing. System may not function properly.</strong></p>";
        }
        if ($nonCriticalMissing > 0) {
            echo "<p class='warning'><strong>⚠️ $nonCriticalMissing non-critical table(s) missing. Some features may not work.</strong></p>";
        }
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

