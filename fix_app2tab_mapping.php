<?php
/**
 * Fix app-to-tab mapping for Evaluate and Plans modules
 * This ensures they appear in MARKETING menu in Vtiger v7
 */

require_once 'config.inc.php';
require_once 'include/database/PearDatabase.php';
require_once 'include/utils/utils.php';

$adb = PearDatabase::getInstance();

echo "=== Fixing App-to-Tab Mapping for Evaluate and Plans ===\n\n";

// Get module tabids
$evaluateResult = $adb->pquery("SELECT tabid FROM vtiger_tab WHERE name = ?", array('Evaluate'));
$plansResult = $adb->pquery("SELECT tabid FROM vtiger_tab WHERE name = ?", array('Plans'));

if ($adb->num_rows($evaluateResult) == 0 || $adb->num_rows($plansResult) == 0) {
    die("ERROR: Modules not found in vtiger_tab!\n");
}

$evaluateTabId = $adb->query_result($evaluateResult, 0, 'tabid');
$plansTabId = $adb->query_result($plansResult, 0, 'tabid');

echo "Evaluate tabid: $evaluateTabId\n";
echo "Plans tabid: $plansTabId\n\n";

// Step 1: Insert app mapping records
echo "1. Inserting app-to-tab mappings...\n";

// Check if records already exist
$checkEvaluate = $adb->pquery("SELECT 1 FROM vtiger_app2tab WHERE appname = ? AND tabid = ?", 
    array('MARKETING', $evaluateTabId));
$checkPlans = $adb->pquery("SELECT 1 FROM vtiger_app2tab WHERE appname = ? AND tabid = ?", 
    array('MARKETING', $plansTabId));

// Get max sequence for MARKETING
$maxSeqResult = $adb->pquery("SELECT MAX(sequence) as max_seq FROM vtiger_app2tab WHERE appname = ?", 
    array('MARKETING'));
$maxSeq = $adb->query_result($maxSeqResult, 0, 'max_seq');
$nextSeq = ($maxSeq ? $maxSeq + 1 : 1);

if ($adb->num_rows($checkEvaluate) == 0) {
    $adb->pquery("INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible) VALUES (?, ?, ?, ?)",
        array('MARKETING', $evaluateTabId, $nextSeq, 1));
    echo "   ✓ Inserted Evaluate -> MARKETING (sequence: $nextSeq)\n";
    $nextSeq++;
} else {
    echo "   - Evaluate -> MARKETING mapping already exists\n";
}

if ($adb->num_rows($checkPlans) == 0) {
    $adb->pquery("INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible) VALUES (?, ?, ?, ?)",
        array('MARKETING', $plansTabId, $nextSeq, 1));
    echo "   ✓ Inserted Plans -> MARKETING (sequence: $nextSeq)\n";
} else {
    echo "   - Plans -> MARKETING mapping already exists\n";
}

// Step 2: Verify/Update vtiger_tab flags
echo "\n2. Verifying vtiger_tab flags...\n";

$tabResult = $adb->pquery("SELECT tabid, name, presence, isentitytype, customized FROM vtiger_tab WHERE tabid IN (?, ?)",
    array($evaluateTabId, $plansTabId));

while ($row = $adb->fetchByAssoc($tabResult)) {
    $tabid = $row['tabid'];
    $name = $row['name'];
    $presence = $row['presence'];
    $isentitytype = $row['isentitytype'];
    $customized = $row['customized'];
    
    echo "   $name (tabid: $tabid):\n";
    echo "     - presence: $presence (should be 0)\n";
    echo "     - isentitytype: $isentitytype (current)\n";
    echo "     - customized: $customized (current)\n";
    
    // Update if needed
    $needsUpdate = false;
    $updateFields = array();
    
    if ($presence != 0) {
        $needsUpdate = true;
        $updateFields[] = "presence = 0";
    }
    
    if ($needsUpdate) {
        $updateSql = "UPDATE vtiger_tab SET " . implode(", ", $updateFields) . " WHERE tabid = ?";
        $adb->pquery($updateSql, array($tabid));
        echo "     ✓ Updated flags\n";
    } else {
        echo "     ✓ Flags are correct\n";
    }
}

// Step 3: Verify app mappings
echo "\n3. Verifying app mappings...\n";

$appResult = $adb->pquery("SELECT appname, tabid, sequence, visible FROM vtiger_app2tab WHERE tabid IN (?, ?) ORDER BY appname, sequence",
    array($evaluateTabId, $plansTabId));

if ($adb->num_rows($appResult) > 0) {
    while ($row = $adb->fetchByAssoc($appResult)) {
        $moduleName = ($row['tabid'] == $evaluateTabId) ? 'Evaluate' : 'Plans';
        echo "   ✓ $moduleName -> {$row['appname']} (sequence: {$row['sequence']}, visible: {$row['visible']})\n";
    }
} else {
    echo "   ✗ No app mappings found!\n";
}

// Step 4: Verify parenttabrel (should already exist)
echo "\n4. Verifying parenttabrel mappings...\n";

$parentResult = $adb->pquery("SELECT p.parenttab_label, ptr.tabid, ptr.sequence 
    FROM vtiger_parenttabrel ptr 
    INNER JOIN vtiger_parenttab p ON p.parenttabid = ptr.parenttabid 
    WHERE ptr.tabid IN (?, ?) ORDER BY ptr.sequence",
    array($evaluateTabId, $plansTabId));

if ($adb->num_rows($parentResult) > 0) {
    while ($row = $adb->fetchByAssoc($parentResult)) {
        $moduleName = ($row['tabid'] == $evaluateTabId) ? 'Evaluate' : 'Plans';
        echo "   ✓ $moduleName -> {$row['parenttab_label']} (sequence: {$row['sequence']})\n";
    }
} else {
    echo "   ✗ No parenttabrel mappings found!\n";
}

echo "\n=== Fix Complete ===\n";
echo "\nNext steps:\n";
echo "1. Clear cache: rm -rf cache/* templates_c/*\n";
echo "2. Logout and login again\n";
echo "3. Check MARKETING menu - Evaluate and Plans should appear\n";


