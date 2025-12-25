<?php
/**
 * Force register PotentialsHandler for Opportunities notification system
 * 
 * This script safely registers the PotentialsHandler event handler
 * for creating notifications when Opportunities are assigned to users.
 * 
 * SAFE TO RUN MULTIPLE TIMES - checks for existing handler before registering
 * 
 * Usage:
 *   php register_potentials_handler.php
 *   OR
 *   Access via browser: http://localhost:8080/register_potentials_handler.php
 */

require_once 'config.php';
require_once 'include/database/PearDatabase.php';
require_once 'include/events/include.inc';

global $adb;
$adb = PearDatabase::getInstance();

// Configuration
$eventName = 'vtiger.entity.aftersave.final';
$handlerPath = 'modules/Potentials/PotentialsHandler.php';
$handlerClass = 'PotentialsHandler';

echo "========================================\n";
echo "PotentialsHandler Registration Script\n";
echo "========================================\n\n";

// Check if handler already exists
$checkResult = $adb->pquery(
    "SELECT eventhandler_id, is_active 
     FROM vtiger_eventhandlers 
     WHERE event_name = ? AND handler_path = ? AND handler_class = ?",
    array($eventName, $handlerPath, $handlerClass)
);

if ($adb->num_rows($checkResult) > 0) {
    $row = $adb->fetchByAssoc($checkResult);
    $handlerId = $row['eventhandler_id'];
    $isActive = $row['is_active'];
    
    echo "[OK] PotentialsHandler already registered\n";
    echo "      Handler ID: $handlerId\n";
    echo "      Status: " . ($isActive ? 'Active' : 'Inactive') . "\n";
    echo "      Event: $eventName\n";
    echo "      Path: $handlerPath\n\n";
    
    if (!$isActive) {
        echo "[INFO] Handler exists but is inactive. Activating...\n";
        $adb->pquery(
            "UPDATE vtiger_eventhandlers SET is_active = 1 WHERE eventhandler_id = ?",
            array($handlerId)
        );
        echo "[OK] Handler activated successfully\n\n";
    }
    
    echo "No action needed. Exiting.\n";
    exit(0);
}

// Handler does not exist - register it
echo "[INFO] PotentialsHandler not found. Registering...\n";

try {
    $em = new VTEventsManager($adb);
    
    // Register handler using VTEventsManager (handles duplicate check internally)
    $em->registerHandler($eventName, $handlerPath, $handlerClass);
    
    // Verify registration
    $verifyResult = $adb->pquery(
        "SELECT eventhandler_id, is_active 
         FROM vtiger_eventhandlers 
         WHERE event_name = ? AND handler_path = ? AND handler_class = ?",
        array($eventName, $handlerPath, $handlerClass)
    );
    
    if ($adb->num_rows($verifyResult) > 0) {
        $row = $adb->fetchByAssoc($verifyResult);
        $handlerId = $row['eventhandler_id'];
        $isActive = $row['is_active'];
        
        echo "[OK] PotentialsHandler registered successfully\n";
        echo "      Handler ID: $handlerId\n";
        echo "      Status: " . ($isActive ? 'Active' : 'Inactive') . "\n";
        echo "      Event: $eventName\n";
        echo "      Path: $handlerPath\n\n";
        
        echo "========================================\n";
        echo "SUCCESS: Handler is now active\n";
        echo "========================================\n";
        echo "\n";
        echo "The handler will now create notifications when:\n";
        echo "  - A new Opportunity is created and assigned to a user\n";
        echo "  - An Opportunity's 'Assigned To' field is changed\n";
        echo "\n";
        echo "Note: Notifications are only sent to USERS (not GROUPS)\n";
        echo "\n";
    } else {
        echo "[ERROR] Registration failed - handler not found after registration\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "[ERROR] Failed to register handler: " . $e->getMessage() . "\n";
    echo "        File: " . $e->getFile() . "\n";
    echo "        Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "Done.\n";

