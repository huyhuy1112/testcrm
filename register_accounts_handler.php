<?php
/**
 * Force register AccountsHandler for Accounts/Organizations notification system
 * Run via browser: http://localhost:8080/register_accounts_handler.php
 */

require_once 'config.php';
require_once 'include/database/PearDatabase.php';

global $adb;
$adb = PearDatabase::getInstance();

$eventName = 'vtiger.entity.aftersave.final';
$handlerPath = 'modules/Accounts/AccountsHandler.php';
$handlerClass = 'AccountsHandler';

$check = $adb->pquery(
    "SELECT eventhandler_id, is_active FROM vtiger_eventhandlers 
     WHERE event_name = ? AND handler_path = ? AND handler_class = ?",
    array($eventName, $handlerPath, $handlerClass)
);

if ($adb->num_rows($check) > 0) {
    $row = $adb->fetchByAssoc($check);
    echo "AccountsHandler already registered!<br>";
    echo "Handler ID: " . $row['eventhandler_id'] . "<br>";
    
    if (!$row['is_active']) {
        $adb->pquery(
            "UPDATE vtiger_eventhandlers SET is_active = 1 
             WHERE eventhandler_id = ?",
            array($row['eventhandler_id'])
        );
        echo "Handler activated.<br>";
    }
} else {
    $handlerId = $adb->getUniqueId('vtiger_eventhandlers');
    $adb->pquery(
        "INSERT INTO vtiger_eventhandlers 
         (eventhandler_id, event_name, handler_path, handler_class, cond, is_active, dependent_on)
         VALUES (?, ?, ?, ?, '', 1, '[]')",
        array($handlerId, $eventName, $handlerPath, $handlerClass)
    );
    echo "AccountsHandler registered successfully!<br>";
    echo "Handler ID: $handlerId<br>";
}

echo "<br>Done! Accounts/Organizations notifications should now work.";

