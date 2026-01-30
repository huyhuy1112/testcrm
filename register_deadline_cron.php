<?php
/**
 * Register DeadlineReminder cron job
 * Run via browser: http://localhost:8080/register_deadline_cron.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Register Deadline Reminder Cron</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;}</style>";
echo "</head><body>";
echo "<h2>⏰ Registering Deadline Reminder Cron Job</h2>";
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

    $cronName = 'DeadlineReminder';
    $handlerFile = 'cron/DeadlineReminder.service';
    $frequency = 86400; // 24 hours (1 day)

    // Check if cron job already exists
    $check = $adb->pquery(
        "SELECT cronid, status FROM vtiger_cron_task WHERE name = ?",
        array($cronName)
    );

    if ($adb->num_rows($check) > 0) {
        $row = $adb->fetchByAssoc($check);
        echo "<p><strong>Status:</strong> Cron job already exists!</p>";
        echo "<p>Cron ID: " . htmlspecialchars($row['cronid']) . "</p>";
        echo "<p>Status: " . ($row['status'] ? '<span class="success">Active</span>' : '<span class="error">Inactive</span>') . "</p>";
        
        if (!$row['status']) {
            $adb->pquery(
                "UPDATE vtiger_cron_task SET status = 1, handler_file = ?, frequency = ? WHERE cronid = ?",
                array($handlerFile, $frequency, $row['cronid'])
            );
            echo "<p class='success'><strong>✅ Activated and updated!</strong></p>";
        } else {
            // Update handler file and frequency in case they changed
            $adb->pquery(
                "UPDATE vtiger_cron_task SET handler_file = ?, frequency = ? WHERE cronid = ?",
                array($handlerFile, $frequency, $row['cronid'])
            );
            echo "<p class='success'><strong>✅ Updated handler file and frequency!</strong></p>";
        }
    } else {
        // Create new cron job
        $cronId = $adb->getUniqueId('vtiger_cron_task');
        $result = $adb->pquery(
            "INSERT INTO vtiger_cron_task 
             (cronid, name, handler_file, frequency, status, laststart, lastend, sequence)
             VALUES (?, ?, ?, ?, 1, 0, 0, 0)",
            array($cronId, $cronName, $handlerFile, $frequency)
        );
        
        if ($result) {
            echo "<p class='success'><strong>✅ Cron job registered successfully!</strong></p>";
            echo "<p>Cron ID: $cronId</p>";
            echo "<p>Name: $cronName</p>";
            echo "<p>Handler: $handlerFile</p>";
            echo "<p>Frequency: $frequency seconds (24 hours)</p>";
        } else {
            throw new Exception("Failed to insert cron job");
        }
    }

    echo "<hr>";
    echo "<h3>✅ Verification</h3>";

    $verify = $adb->pquery(
        "SELECT cronid, name, handler_file, frequency, status 
         FROM vtiger_cron_task 
         WHERE name = ?",
        array($cronName)
    );

    if ($adb->num_rows($verify) > 0) {
        $row = $adb->fetchByAssoc($verify);
        echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
        echo "<tr><th>Field</th><th>Value</th></tr>";
        echo "<tr><td>Cron ID</td><td>" . htmlspecialchars($row['cronid']) . "</td></tr>";
        echo "<tr><td>Name</td><td>" . htmlspecialchars($row['name']) . "</td></tr>";
        echo "<tr><td>Handler File</td><td>" . htmlspecialchars($row['handler_file']) . "</td></tr>";
        echo "<tr><td>Frequency</td><td>" . htmlspecialchars($row['frequency']) . " seconds (" . round($row['frequency']/3600, 1) . " hours)</td></tr>";
        echo "<tr><td>Status</td><td>" . ($row['status'] ? '<span class="success">✓ Active</span>' : '<span class="error">✗ Inactive</span>') . "</td></tr>";
        echo "</table>";
        
        echo "<hr>";
        echo "<p class='success'><strong>🎉 Done! Deadline Reminder cron job is registered and active.</strong></p>";
        echo "<p>The cron job will check for deadlines daily and send reminder notifications when deadlines are within 7 days.</p>";
    } else {
        echo "<p class='error'>❌ Cron job not found after registration!</p>";
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


