<?php
/**
 * Verify placeholder modules are properly registered
 */

require_once 'config.inc.php';
require_once 'include/database/PearDatabase.php';

$adb = PearDatabase::getInstance();

echo "=== Verifying Placeholder Modules ===\n\n";

// Check Evaluate
echo "1. Evaluate Module:\n";
$evalResult = $adb->pquery("SELECT tabid, name, parent, isentitytype, presence, tabsequence FROM vtiger_tab WHERE name = ?", array('Evaluate'));
if ($adb->num_rows($evalResult) > 0) {
    $row = $adb->fetchByAssoc($evalResult);
    echo "   ✓ Registered in vtiger_tab\n";
    echo "   - tabid: {$row['tabid']}\n";
    echo "   - parent: {$row['parent']}\n";
    echo "   - isentitytype: {$row['isentitytype']} (should be 0)\n";
    echo "   - presence: {$row['presence']} (should be 0 = visible)\n";
    echo "   - sequence: {$row['tabsequence']}\n";
    
    // Check parent tab relation
    $relResult = $adb->pquery("SELECT sequence FROM vtiger_parenttabrel WHERE tabid = ?", array($row['tabid']));
    if ($adb->num_rows($relResult) > 0) {
        $relSeq = $adb->query_result($relResult, 0, 'sequence');
        echo "   ✓ Linked to MARKETING (sequence: $relSeq)\n";
    } else {
        echo "   ✗ NOT linked to MARKETING!\n";
    }
} else {
    echo "   ✗ NOT found in vtiger_tab!\n";
}

// Check Plans
echo "\n2. Plans Module:\n";
$plansResult = $adb->pquery("SELECT tabid, name, parent, isentitytype, presence, tabsequence FROM vtiger_tab WHERE name = ?", array('Plans'));
if ($adb->num_rows($plansResult) > 0) {
    $row = $adb->fetchByAssoc($plansResult);
    echo "   ✓ Registered in vtiger_tab\n";
    echo "   - tabid: {$row['tabid']}\n";
    echo "   - parent: {$row['parent']}\n";
    echo "   - isentitytype: {$row['isentitytype']} (should be 0)\n";
    echo "   - presence: {$row['presence']} (should be 0 = visible)\n";
    echo "   - sequence: {$row['tabsequence']}\n";
    
    // Check parent tab relation
    $relResult = $adb->pquery("SELECT sequence FROM vtiger_parenttabrel WHERE tabid = ?", array($row['tabid']));
    if ($adb->num_rows($relResult) > 0) {
        $relSeq = $adb->query_result($relResult, 0, 'sequence');
        echo "   ✓ Linked to MARKETING (sequence: $relSeq)\n";
    } else {
        echo "   ✗ NOT linked to MARKETING!\n";
    }
} else {
    echo "   ✗ NOT found in vtiger_tab!\n";
}

// Check files
echo "\n3. File Structure:\n";
$files = array(
    'modules/Evaluate/Evaluate.php',
    'modules/Evaluate/manifest.xml',
    'modules/Evaluate/models/Module.php',
    'modules/Plans/Plans.php',
    'modules/Plans/manifest.xml',
    'modules/Plans/models/Module.php'
);

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "   ✓ $file\n";
    } else {
        echo "   ✗ MISSING: $file\n";
    }
}

echo "\n=== Verification Complete ===\n";
echo "\nNext: Go to Settings > Menu Editor and drag Evaluate/Plans into MARKETING\n";

