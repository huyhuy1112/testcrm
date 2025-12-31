<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing...<br>";

if (!file_exists('config.php')) {
    die("ERROR: config.php not found");
}

echo "config.php: OK<br>";

require_once 'config.php';

if (!file_exists('include/database/PearDatabase.php')) {
    die("ERROR: PearDatabase.php not found");
}

echo "PearDatabase.php: OK<br>";

require_once 'include/database/PearDatabase.php';

$adb = PearDatabase::getInstance();

if (!$adb) {
    die("ERROR: Failed to get database instance");
}

echo "Database: OK<br>";

// Test query
$result = $adb->pquery("SELECT COUNT(*) as cnt FROM vtiger_eventhandlers WHERE handler_class IN ('AccountsHandler', 'ContactsHandler')", array());
$row = $adb->fetchByAssoc($result);
echo "Current handlers found: " . $row['cnt'] . "<br>";

echo "<br>If you see this, the script is working!<br>";
echo "Now try: <a href='register_marketing_handlers.php'>register_marketing_handlers.php</a>";

