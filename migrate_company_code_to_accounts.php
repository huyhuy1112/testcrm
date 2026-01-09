<?php
/**
 * Migrate Company Code to Accounts (Organization) Level
 * Run via browser: http://localhost:8080/migrate_company_code_to_accounts.php
 * 
 * Tasks:
 * 1. Remove cf_855 from Potentials
 * 2. Ensure cf_855 exists in Accounts
 * 3. Update ProjectCodeHandler to read from Account
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(60);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Migrate Company Code to Accounts</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;} .info{color:blue;} table{border-collapse:collapse;width:100%;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;} th{background:#f0f0f0;} .step{background:#f9f9f9;padding:15px;margin:10px 0;border-left:4px solid #007bff;}</style>";
echo "</head><body>";
echo "<h1>🔄 Migrate Company Code to Accounts Level</h1>";
echo "<hr>";

try {
    require_once 'config.php';
    require_once 'include/database/PearDatabase.php';
    
    global $adb;
    $adb = PearDatabase::getInstance();
    
    // Step 1: Verify Modules
    echo "<div class='step'>";
    echo "<h2>Step 1: Verify Modules</h2>";
    
    $accountsTab = $adb->pquery("SELECT tabid FROM vtiger_tab WHERE name = 'Accounts'", array());
    $potentialsTab = $adb->pquery("SELECT tabid FROM vtiger_tab WHERE name = 'Potentials'", array());
    
    if ($adb->num_rows($accountsTab) == 0 || $adb->num_rows($potentialsTab) == 0) {
        throw new Exception("Required modules not found!");
    }
    
    $accountsTabid = $adb->query_result($accountsTab, 0, 'tabid');
    $potentialsTabid = $adb->query_result($potentialsTab, 0, 'tabid');
    
    echo "<p class='success'>✅ Accounts module found (tabid: $accountsTabid)</p>";
    echo "<p class='success'>✅ Potentials module found (tabid: $potentialsTabid)</p>";
    echo "</div>";
    
    // Step 2: Check Company Code in Accounts
    echo "<div class='step'>";
    echo "<h2>Step 2: Ensure Company Code in Accounts</h2>";
    
    $accountsFieldCheck = $adb->pquery(
        "SELECT fieldid, fieldname, fieldlabel, readonly, presence, typeofdata 
         FROM vtiger_field 
         WHERE fieldname = 'cf_855' AND tabid = ?",
        array($accountsTabid)
    );
    
    if ($adb->num_rows($accountsFieldCheck) > 0) {
        $fieldRow = $adb->fetchByAssoc($accountsFieldCheck);
        echo "<p class='info'>ℹ️ Field cf_855 already exists in Accounts (fieldid: {$fieldRow['fieldid']})</p>";
        
        // Verify it's configured correctly
        $needsUpdate = false;
        if ($fieldRow['readonly'] != 0 || $fieldRow['presence'] != 2 || strpos($fieldRow['typeofdata'], 'M') === false) {
            echo "<p class='warning'>⚠️ Field needs update</p>";
            $newTypeofData = str_replace('~O', '~M', $fieldRow['typeofdata']);
            $newTypeofData = str_replace('V~O', 'V~M', $newTypeofData);
            if ($newTypeofData == $fieldRow['typeofdata']) {
                $newTypeofData = 'V~M';
            }
            $adb->pquery(
                "UPDATE vtiger_field SET readonly = 0, presence = 2, typeofdata = ? WHERE fieldid = ?",
                array($newTypeofData, $fieldRow['fieldid'])
            );
            echo "<p class='success'>✅ Updated field properties</p>";
        } else {
            echo "<p class='success'>✅ Field is correctly configured</p>";
        }
    } else {
        // Create field in Accounts
        echo "<p>Creating Company Code field in Accounts...</p>";
        
        // Get Custom Information block for Accounts
        $accountsBlock = $adb->pquery(
            "SELECT blockid FROM vtiger_blocks WHERE tabid = ? AND blocklabel = 'LBL_CUSTOM_INFORMATION'",
            array($accountsTabid)
        );
        
        if ($adb->num_rows($accountsBlock) == 0) {
            throw new Exception("Custom Information block not found in Accounts");
        }
        
        $accountsBlockId = $adb->query_result($accountsBlock, 0, 'blockid');
        
        // Get next fieldid and sequence
        $maxFieldIdResult = $adb->pquery("SELECT MAX(fieldid) as max_id FROM vtiger_field", array());
        $maxFieldIdRow = $adb->fetchByAssoc($maxFieldIdResult);
        $nextFieldId = $maxFieldIdRow['max_id'] + 1;
        
        $seqResult = $adb->pquery(
            "SELECT MAX(sequence) as max_seq FROM vtiger_field WHERE tabid = ? AND block = ?",
            array($accountsTabid, $accountsBlockId)
        );
        $seqRow = $adb->fetchByAssoc($seqResult);
        $nextSequence = ($seqRow['max_seq'] ? $seqRow['max_seq'] + 1 : 1);
        
        // Insert into vtiger_field
        $adb->pquery(
            "INSERT INTO vtiger_field 
            (tabid, fieldid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, 
             readonly, presence, defaultvalue, maximumlength, sequence, block, displaytype, typeofdata, 
             quickcreate, quickcreatesequence, info_type, helpinfo, summaryfield, headerfield)
            VALUES (?, ?, ?, ?, 2, 1, ?, ?, 0, 2, '', 255, ?, ?, 1, 'V~M', 1, ?, 'BAS', '', 0, 0)",
            array(
                $accountsTabid,
                $nextFieldId,
                'cf_855',
                'vtiger_accountscf',
                'cf_855',
                'Company Code',
                $nextSequence,
                $accountsBlockId,
                $nextSequence
            )
        );
        
        echo "<p class='success'>✅ Created Company Code field in Accounts (fieldid: $nextFieldId)</p>";
    }
    
    // Check column in vtiger_accountscf
    $accountsColumnCheck = $adb->pquery(
        "SHOW COLUMNS FROM vtiger_accountscf LIKE 'cf_855'",
        array()
    );
    
    if ($adb->num_rows($accountsColumnCheck) == 0) {
        echo "<p>Adding column cf_855 to vtiger_accountscf...</p>";
        $adb->pquery(
            "ALTER TABLE vtiger_accountscf ADD COLUMN cf_855 VARCHAR(255) DEFAULT NULL",
            array()
        );
        echo "<p class='success'>✅ Added column cf_855 to vtiger_accountscf</p>";
    } else {
        echo "<p class='info'>ℹ️ Column cf_855 already exists in vtiger_accountscf</p>";
    }
    
    echo "</div>";
    
    // Step 3: Remove Company Code from Potentials
    echo "<div class='step'>";
    echo "<h2>Step 3: Remove Company Code from Potentials</h2>";
    
    $potentialsFieldCheck = $adb->pquery(
        "SELECT fieldid, fieldname FROM vtiger_field WHERE fieldname = 'cf_855' AND tabid = ?",
        array($potentialsTabid)
    );
    
    if ($adb->num_rows($potentialsFieldCheck) > 0) {
        $fieldRow = $adb->fetchByAssoc($potentialsFieldCheck);
        $fieldId = $fieldRow['fieldid'];
        
        echo "<p class='warning'>⚠️ Field cf_855 exists in Potentials (fieldid: $fieldId)</p>";
        echo "<p>Removing field from Potentials...</p>";
        
        // Delete field from vtiger_field
        $adb->pquery("DELETE FROM vtiger_field WHERE fieldid = ?", array($fieldId));
        echo "<p class='success'>✅ Removed field cf_855 from Potentials</p>";
        
        // Note: Column in vtiger_potentialscf will remain for data safety (not deleted)
        echo "<p class='info'>ℹ️ Column cf_855 in vtiger_potentialscf is kept for data safety (not deleted)</p>";
    } else {
        echo "<p class='info'>ℹ️ Field cf_855 does not exist in Potentials (already removed or never existed)</p>";
    }
    
    echo "</div>";
    
    // Step 4: Update ProjectCodeHandler
    echo "<div class='step'>";
    echo "<h2>Step 4: Update ProjectCodeHandler</h2>";
    echo "<p class='info'>ℹ️ Handler will be updated to read Company Code from Account</p>";
    echo "<p class='success'>✅ Handler update will be done in next step</p>";
    echo "</div>";
    
    // Final Summary
    echo "<hr>";
    echo "<h2>📋 Summary</h2>";
    echo "<div class='step'>";
    
    // Verify Accounts field
    $accountsVerify = $adb->pquery(
        "SELECT fieldid, fieldlabel, readonly, presence, typeofdata 
         FROM vtiger_field 
         WHERE fieldname = 'cf_855' AND tabid = ?",
        array($accountsTabid)
    );
    
    echo "<h3>Accounts - Company Code Field:</h3>";
    if ($adb->num_rows($accountsVerify) > 0) {
        $acc = $adb->fetchByAssoc($accountsVerify);
        echo "<ul>";
        echo "<li>Field ID: {$acc['fieldid']}</li>";
        echo "<li>Label: {$acc['fieldlabel']}</li>";
        echo "<li>Read-only: " . ($acc['readonly'] == 0 ? 'No (Editable)' : 'Yes') . "</li>";
        echo "<li>Presence: " . ($acc['presence'] == 2 ? 'Visible' : 'Hidden') . "</li>";
        echo "<li>Mandatory: " . (strpos($acc['typeofdata'], 'M') !== false ? 'Yes' : 'No') . "</li>";
        echo "</ul>";
    } else {
        echo "<p class='error'>❌ Field not found in Accounts</p>";
    }
    
    // Verify Potentials field removed
    $potentialsVerify = $adb->pquery(
        "SELECT COUNT(*) as cnt FROM vtiger_field WHERE fieldname = 'cf_855' AND tabid = ?",
        array($potentialsTabid)
    );
    
    echo "<h3>Potentials - Company Code Field:</h3>";
    $potRow = $adb->fetchByAssoc($potentialsVerify);
    if ($potRow['cnt'] == 0) {
        echo "<p class='success'>✅ Field removed from Potentials</p>";
    } else {
        echo "<p class='error'>❌ Field still exists in Potentials</p>";
    }
    
    echo "<h3>✅ Migration Complete!</h3>";
    echo "<p>Next: Update ProjectCodeHandler to read Company Code from Account.</p>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#fee;border:1px solid #f00;padding:10px;'>";
    echo "<h3 class='error'>ERROR</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "</body></html>";

