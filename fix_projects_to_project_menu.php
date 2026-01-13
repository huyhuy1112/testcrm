<?php
/**
 * Fix PROJECTS → PROJECT Menu Migration
 * 
 * CRITICAL FIX:
 * - Move all modules from invalid "PROJECTS" app to correct "PROJECT" app
 * - Remove all PROJECTS app records
 * - Update display labels
 * - Update language file for menu title
 * - Clear cache
 */

require_once 'config.inc.php';
require_once 'include/database/PearDatabase.php';
require_once 'include/utils/utils.php';

$adb = PearDatabase::getInstance();

echo "==========================================\n";
echo "Fix PROJECTS → PROJECT Menu Migration\n";
echo "==========================================\n\n";

// Step 1: Check current state
echo "Step 1: Checking current database state...\n";

// Check PROJECTS app
$projectsCheck = $adb->pquery("SELECT COUNT(*) as cnt FROM vtiger_app2tab WHERE appname = ?", array('PROJECTS'));
$projectsCount = $adb->query_result($projectsCheck, 0, 'cnt');
echo "  PROJECTS app records: $projectsCount\n";

// Check PROJECT app
$projectCheck = $adb->pquery("SELECT COUNT(*) as cnt FROM vtiger_app2tab WHERE appname = ?", array('PROJECT'));
$projectCount = $adb->query_result($projectCheck, 0, 'cnt');
echo "  PROJECT app records: $projectCount\n";

// List modules in PROJECTS
if ($projectsCount > 0) {
    echo "\n  Modules currently in PROJECTS app:\n";
    $projectsModules = $adb->pquery(
        "SELECT t.name, t.tablabel, a.sequence, a.visible 
         FROM vtiger_app2tab a 
         INNER JOIN vtiger_tab t ON t.tabid = a.tabid 
         WHERE a.appname = 'PROJECTS' 
         ORDER BY a.sequence",
        array()
    );
    while ($row = $adb->fetchByAssoc($projectsModules)) {
        echo "    - {$row['name']} ({$row['tablabel']}) - sequence: {$row['sequence']}\n";
    }
}

echo "\n";

// Step 2: Move all modules from PROJECTS to PROJECT
echo "Step 2: Moving modules from PROJECTS → PROJECT...\n";

if ($projectsCount > 0) {
    // Get all PROJECTS mappings
    $projectsMappings = $adb->pquery(
        "SELECT tabid, sequence, visible FROM vtiger_app2tab WHERE appname = 'PROJECTS' ORDER BY sequence",
        array()
    );
    
    $moved = 0;
    while ($row = $adb->fetchByAssoc($projectsMappings)) {
        $tabid = $row['tabid'];
        $sequence = $row['sequence'];
        $visible = $row['visible'];
        
        // Check if already exists in PROJECT
        $checkExists = $adb->pquery(
            "SELECT 1 FROM vtiger_app2tab WHERE appname = 'PROJECT' AND tabid = ?",
            array($tabid)
        );
        
        if ($adb->num_rows($checkExists) == 0) {
            // Insert into PROJECT
            $adb->pquery(
                "INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible) VALUES (?, ?, ?, ?)",
                array('PROJECT', $tabid, $sequence, $visible)
            );
            
            $moduleName = $adb->query_result(
                $adb->pquery("SELECT name FROM vtiger_tab WHERE tabid = ?", array($tabid)),
                0,
                'name'
            );
            echo "  ✓ Moved $moduleName → PROJECT (sequence: $sequence)\n";
            $moved++;
        } else {
            // Update existing PROJECT mapping
            $adb->pquery(
                "UPDATE vtiger_app2tab SET sequence = ?, visible = ? WHERE appname = 'PROJECT' AND tabid = ?",
                array($sequence, $visible, $tabid)
            );
            
            $moduleName = $adb->query_result(
                $adb->pquery("SELECT name FROM vtiger_tab WHERE tabid = ?", array($tabid)),
                0,
                'name'
            );
            echo "  ✓ Updated $moduleName in PROJECT (sequence: $sequence)\n";
            $moved++;
        }
    }
    
    echo "  Total modules moved/updated: $moved\n";
} else {
    echo "  No PROJECTS records found - skipping migration\n";
}

echo "\n";

// Step 3: Remove all PROJECTS app records
echo "Step 3: Removing all PROJECTS app records...\n";

$deleteResult = $adb->pquery("DELETE FROM vtiger_app2tab WHERE appname = 'PROJECTS'", array());
$deleted = $adb->getAffectedRowCount($deleteResult);
echo "  ✓ Deleted $deleted PROJECTS app records\n";

echo "\n";

// Step 4: Re-order modules in PROJECT app (ensure correct sequence)
echo "Step 4: Re-ordering modules in PROJECT app...\n";

$targetModules = array(
    'Calendar' => array('sequence' => 1, 'label' => 'Schedule'),
    'Reports' => array('sequence' => 2, 'label' => 'Report'),
    'Documents' => array('sequence' => 3, 'label' => 'Document'),
    'Users' => array('sequence' => 4, 'label' => 'Team')
);

foreach ($targetModules as $moduleName => $config) {
    // Get tabid
    $tabResult = $adb->pquery("SELECT tabid FROM vtiger_tab WHERE name = ?", array($moduleName));
    if ($adb->num_rows($tabResult) > 0) {
        $tabid = $adb->query_result($tabResult, 0, 'tabid');
        
        // Check if exists in PROJECT
        $checkResult = $adb->pquery(
            "SELECT 1 FROM vtiger_app2tab WHERE appname = 'PROJECT' AND tabid = ?",
            array($tabid)
        );
        
        if ($adb->num_rows($checkResult) > 0) {
            // Update sequence
            $adb->pquery(
                "UPDATE vtiger_app2tab SET sequence = ?, visible = 1 WHERE appname = 'PROJECT' AND tabid = ?",
                array($config['sequence'], $tabid)
            );
            echo "  ✓ Set $moduleName sequence to {$config['sequence']}\n";
        } else {
            // Insert if missing
            $adb->pquery(
                "INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible) VALUES (?, ?, ?, ?)",
                array('PROJECT', $tabid, $config['sequence'], 1)
            );
            echo "  ✓ Added $moduleName to PROJECT (sequence: {$config['sequence']})\n";
        }
    } else {
        echo "  ✗ Module $moduleName not found in vtiger_tab\n";
    }
}

echo "\n";

// Step 5: Update display labels
echo "Step 5: Updating module display labels...\n";

foreach ($targetModules as $moduleName => $config) {
    $newLabel = $config['label'];
    
    $updateResult = $adb->pquery(
        "UPDATE vtiger_tab SET tablabel = ? WHERE name = ? AND tablabel != ?",
        array($newLabel, $moduleName, $newLabel)
    );
    
    $affected = $adb->getAffectedRowCount($updateResult);
    if ($affected > 0) {
        echo "  ✓ Updated $moduleName label to '$newLabel'\n";
    } else {
        // Check current label
        $currentResult = $adb->pquery("SELECT tablabel FROM vtiger_tab WHERE name = ?", array($moduleName));
        if ($adb->num_rows($currentResult) > 0) {
            $currentLabel = $adb->query_result($currentResult, 0, 'tablabel');
            if ($currentLabel == $newLabel) {
                echo "  - $moduleName label already correct: '$currentLabel'\n";
            } else {
                echo "  ⚠️ $moduleName label is '$currentLabel' (expected '$newLabel')\n";
            }
        }
    }
}

echo "\n";

// Step 6: Update language file
echo "Step 6: Updating language file for menu title...\n";

$langFile = 'languages/en_us/Vtiger.php';
if (file_exists($langFile)) {
    $langContent = file_get_contents($langFile);
    
    // Check if LBL_PROJECT exists
    if (preg_match("/['\"]LBL_PROJECT['\"]\s*=>\s*['\"]([^'\"]*)['\"]/", $langContent, $matches)) {
        $currentValue = $matches[1];
        if ($currentValue !== 'Management') {
            $langContent = preg_replace(
                "/['\"]LBL_PROJECT['\"]\s*=>\s*['\"][^'\"]*['\"]/",
                "'LBL_PROJECT' => 'Management'",
                $langContent
            );
            file_put_contents($langFile, $langContent);
            echo "  ✓ Updated LBL_PROJECT: '$currentValue' → 'Management'\n";
        } else {
            echo "  - LBL_PROJECT already set to 'Management'\n";
        }
    } else {
        // Add if missing
        if (preg_match('/\$languageStrings\s*=\s*array\s*\(/', $langContent)) {
            // Add before closing of array
            $langContent = preg_replace(
                '/(\$languageStrings\s*=\s*array\s*\()/',
                "$1\n\t\t'LBL_PROJECT' => 'Management',",
                $langContent
            );
            file_put_contents($langFile, $langContent);
            echo "  ✓ Added LBL_PROJECT => 'Management'\n";
        } else {
            echo "  ⚠️ Could not find \$languageStrings array in $langFile\n";
        }
    }
} else {
    echo "  ✗ Language file not found: $langFile\n";
}

echo "\n";

// Step 7: Verification
echo "Step 7: Verifying final state...\n";

// Check PROJECTS is gone
$projectsFinal = $adb->pquery("SELECT COUNT(*) as cnt FROM vtiger_app2tab WHERE appname = 'PROJECTS'", array());
$projectsFinalCount = $adb->query_result($projectsFinal, 0, 'cnt');
if ($projectsFinalCount == 0) {
    echo "  ✓ PROJECTS app completely removed\n";
} else {
    echo "  ✗ WARNING: $projectsFinalCount PROJECTS records still exist!\n";
}

// Check PROJECT app
echo "\n  PROJECT app menu structure:\n";
$projectFinal = $adb->pquery(
    "SELECT t.name, t.tablabel, a.sequence, a.visible 
     FROM vtiger_app2tab a 
     INNER JOIN vtiger_tab t ON t.tabid = a.tabid 
     WHERE a.appname = 'PROJECT' 
     ORDER BY a.sequence",
    array()
);

$found = 0;
while ($row = $adb->fetchByAssoc($projectFinal)) {
    printf("    %d. %s (%s) - visible: %s\n", 
        $row['sequence'], 
        $row['tablabel'], 
        $row['name'],
        $row['visible'] ? 'Yes' : 'No'
    );
    $found++;
}

if ($found == 4) {
    echo "\n  ✓ All 4 modules correctly mapped to PROJECT app\n";
} else {
    echo "\n  ⚠️ Expected 4 modules, found $found\n";
}

echo "\n";

// Step 8: Cache clearing instructions
echo "Step 8: Cache clearing instructions...\n";
echo "\n  Run these commands to clear cache:\n";
echo "    rm -rf cache/*\n";
echo "    rm -rf storage/cache/*\n";
echo "    rm -rf templates_c/*\n";
echo "    find cache -name 'parent_tabdata.php' -delete\n";
echo "\n  Or via Vtiger Admin:\n";
echo "    Settings → Configuration → Clear Cache\n";
echo "\n";

echo "==========================================\n";
echo "✓ Migration Complete!\n";
echo "==========================================\n\n";
echo "Next Steps:\n";
echo "1. Clear cache (see above)\n";
echo "2. Logout and login to Vtiger CRM\n";
echo "3. Check sidebar menu - 'Management' (PROJECT app) should appear\n";
echo "4. Verify modules: Schedule, Report, Document, Team\n";
echo "\n";

