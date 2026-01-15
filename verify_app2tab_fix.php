<?php
/**
 * Verify app-to-tab mapping fix
 */

require_once 'config.inc.php';
require_once 'include/database/PearDatabase.php';

$adb = PearDatabase::getInstance();

echo "=== Verifying App-to-Tab Mapping ===\n\n";

// Get module tabids
$evaluateTabId = $adb->query_result($adb->pquery("SELECT tabid FROM vtiger_tab WHERE name = ?", array('Evaluate')), 0, 'tabid');
$plansTabId = $adb->query_result($adb->pquery("SELECT tabid FROM vtiger_tab WHERE name = ?", array('Plans')), 0, 'tabid');

echo "Evaluate tabid: $evaluateTabId\n";
echo "Plans tabid: $plansTabId\n\n";

// Check app2tab mappings
echo "App-to-Tab Mappings:\n";
$appResult = $adb->pquery("SELECT appname, tabid, sequence, visible FROM vtiger_app2tab WHERE tabid IN (?, ?) AND appname = 'MARKETING' ORDER BY sequence",
    array($evaluateTabId, $plansTabId));

$found = 0;
while ($row = $adb->fetchByAssoc($appResult)) {
    $moduleName = ($row['tabid'] == $evaluateTabId) ? 'Evaluate' : 'Plans';
    echo "  ✓ $moduleName -> {$row['appname']} (sequence: {$row['sequence']}, visible: {$row['visible']})\n";
    $found++;
}

if ($found == 2) {
    echo "\n✓ Both modules are correctly mapped to MARKETING app\n";
} else {
    echo "\n✗ Missing mappings! Found: $found/2\n";
}

// Check parenttabrel
echo "\nParent Tab Relations:\n";
$parentResult = $adb->pquery("SELECT p.parenttab_label, ptr.tabid, ptr.sequence 
    FROM vtiger_parenttabrel ptr 
    INNER JOIN vtiger_parenttab p ON p.parenttabid = ptr.parenttabid 
    WHERE ptr.tabid IN (?, ?) ORDER BY ptr.sequence",
    array($evaluateTabId, $plansTabId));

$found = 0;
while ($row = $adb->fetchByAssoc($parentResult)) {
    $moduleName = ($row['tabid'] == $evaluateTabId) ? 'Evaluate' : 'Plans';
    echo "  ✓ $moduleName -> {$row['parenttab_label']} (sequence: {$row['sequence']})\n";
    $found++;
}

if ($found == 2) {
    echo "\n✓ Both modules are correctly linked to MARKETING parent tab\n";
} else {
    echo "\n✗ Missing parent tab relations! Found: $found/2\n";
}

// Check vtiger_tab flags
echo "\nModule Flags:\n";
$tabResult = $adb->pquery("SELECT name, presence, isentitytype, customized FROM vtiger_tab WHERE tabid IN (?, ?)",
    array($evaluateTabId, $plansTabId));

while ($row = $adb->fetchByAssoc($tabResult)) {
    $status = ($row['presence'] == 0) ? '✓' : '✗';
    echo "  $status {$row['name']}: presence={$row['presence']}, isentitytype={$row['isentitytype']}, customized={$row['customized']}\n";
}

echo "\n=== Verification Complete ===\n";


