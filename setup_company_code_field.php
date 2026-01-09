<?php
/**
 * Setup Company Code Field (cf_855) for Potentials Module
 * Run via browser: http://localhost:8080/setup_company_code_field.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(60);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Setup Company Code Field</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;} .info{color:blue;} table{border-collapse:collapse;width:100%;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;} th{background:#f0f0f0;} .step{background:#f9f9f9;padding:15px;margin:10px 0;border-left:4px solid #007bff;}</style>";
echo "</head><body>";
echo "<h1>🔧 Setup Company Code Field (cf_855)</h1>";
echo "<hr>";

try {
    require_once 'config.php';
    require_once 'include/database/PearDatabase.php';
    
    global $adb;
    $adb = PearDatabase::getInstance();
    
    // Step 1: Verify Potentials Module
    echo "<div class='step'>";
    echo "<h2>Step 1: Verify Potentials Module</h2>";
    
    $tabResult = $adb->pquery("SELECT tabid FROM vtiger_tab WHERE name = 'Potentials'", array());
    if ($adb->num_rows($tabResult) == 0) {
        throw new Exception("Potentials module not found!");
    }
    $tabRow = $adb->fetchByAssoc($tabResult);
    $potentialsTabid = $tabRow['tabid'];
    echo "<p class='success'>✅ Potentials module found (tabid: $potentialsTabid)</p>";
    
    // Get Custom Information block
    $blockResult = $adb->pquery("SELECT blockid FROM vtiger_blocks WHERE tabid = ? AND blocklabel = 'LBL_CUSTOM_INFORMATION'", array($potentialsTabid));
    if ($adb->num_rows($blockResult) == 0) {
        throw new Exception("Custom Information block not found!");
    }
    $blockRow = $adb->fetchByAssoc($blockResult);
    $customBlockId = $blockRow['blockid'];
    echo "<p class='success'>✅ Custom Information block found (blockid: $customBlockId)</p>";
    echo "</div>";
    
    // Step 2: Check if cf_855 exists
    echo "<div class='step'>";
    echo "<h2>Step 2: Check Company Code Field (cf_855)</h2>";
    
    $fieldCheck = $adb->pquery(
        "SELECT fieldid, fieldname, fieldlabel, readonly, presence, typeofdata, mandatory 
         FROM vtiger_field 
         WHERE fieldname = 'cf_855' AND tabid = ?",
        array($potentialsTabid)
    );
    
    if ($adb->num_rows($fieldCheck) > 0) {
        $fieldRow = $adb->fetchByAssoc($fieldCheck);
        $fieldId = $fieldRow['fieldid'];
        echo "<p class='info'>ℹ️ Field cf_855 already exists (fieldid: $fieldId)</p>";
        
        // Check if it needs updates
        $needsUpdate = false;
        $updates = array();
        
        if ($fieldRow['fieldlabel'] != 'Company Code') {
            $needsUpdate = true;
            $updates[] = "fieldlabel = 'Company Code'";
        }
        
        if ($fieldRow['readonly'] != 0) {
            $needsUpdate = true;
            $updates[] = "readonly = 0";
        }
        
        if ($fieldRow['presence'] != 2) {
            $needsUpdate = true;
            $updates[] = "presence = 2";
        }
        
        // Check typeofdata for mandatory (should be V~M for mandatory)
        if (strpos($fieldRow['typeofdata'], 'M') === false) {
            $needsUpdate = true;
            $newTypeofData = str_replace('~O', '~M', $fieldRow['typeofdata']);
            $newTypeofData = str_replace('V~O', 'V~M', $newTypeofData);
            if ($newTypeofData == $fieldRow['typeofdata']) {
                $newTypeofData = 'V~M';
            }
            $updates[] = "typeofdata = '$newTypeofData'";
        }
        
        if ($needsUpdate) {
            echo "<p class='warning'>⚠️ Field exists but needs updates</p>";
            $updateSql = "UPDATE vtiger_field SET " . implode(', ', $updates) . " WHERE fieldid = ?";
            $adb->pquery($updateSql, array($fieldId));
            echo "<p class='success'>✅ Updated field properties</p>";
        } else {
            echo "<p class='success'>✅ Field is correctly configured</p>";
        }
    } else {
        // Create field
        echo "<p>Creating field cf_855 (Company Code)...</p>";
        
        // Get next fieldid and sequence
        $maxFieldIdResult = $adb->pquery("SELECT MAX(fieldid) as max_id FROM vtiger_field", array());
        $maxFieldIdRow = $adb->fetchByAssoc($maxFieldIdResult);
        $nextFieldId = $maxFieldIdRow['max_id'] + 1;
        
        $seqResult = $adb->pquery(
            "SELECT MAX(sequence) as max_seq FROM vtiger_field WHERE tabid = ? AND block = ?",
            array($potentialsTabid, $customBlockId)
        );
        $seqRow = $adb->fetchByAssoc($seqResult);
        $nextSequence = ($seqRow['max_seq'] ? $seqRow['max_seq'] + 1 : 1);
        
        // Insert into vtiger_field
        $adb->pquery(
            "INSERT INTO vtiger_field 
            (tabid, fieldid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, 
             readonly, presence, defaultvalue, maximumlength, sequence, block, displaytype, typeofdata, 
             quickcreate, quickcreatesequence, info_type, helpinfo, summaryfield, headerfield, mandatory)
            VALUES (?, ?, ?, ?, 2, 1, ?, ?, 0, 2, '', 255, ?, ?, 1, 'V~M', 1, ?, 'BAS', '', 0, 0, 1)",
            array(
                $potentialsTabid,      // tabid
                $nextFieldId,           // fieldid
                'cf_855',               // columnname
                'vtiger_potentialscf',  // tablename
                'cf_855',               // fieldname
                'Company Code',         // fieldlabel
                $nextSequence,          // sequence
                $customBlockId,         // block
                $nextSequence,          // quickcreatesequence
            )
        );
        
        echo "<p class='success'>✅ Created field cf_855 (fieldid: $nextFieldId, sequence: $nextSequence)</p>";
    }
    echo "</div>";
    
    // Step 3: Verify/Create Database Column
    echo "<div class='step'>";
    echo "<h2>Step 3: Database Column Verification</h2>";
    
    $columnCheck = $adb->pquery(
        "SHOW COLUMNS FROM vtiger_potentialscf LIKE 'cf_855'",
        array()
    );
    
    if ($adb->num_rows($columnCheck) == 0) {
        echo "<p>Adding column cf_855 to vtiger_potentialscf...</p>";
        $adb->pquery(
            "ALTER TABLE vtiger_potentialscf ADD COLUMN cf_855 VARCHAR(255) DEFAULT NULL",
            array()
        );
        echo "<p class='success'>✅ Added column cf_855</p>";
    } else {
        echo "<p class='info'>ℹ️ Column cf_855 already exists</p>";
    }
    echo "</div>";
    
    // Step 4: Verify Field in Layout
    echo "<div class='step'>";
    echo "<h2>Step 4: Field Layout Verification</h2>";
    
    // Check if field is in any block
    $layoutCheck = $adb->pquery(
        "SELECT fieldid FROM vtiger_field WHERE fieldname = 'cf_855' AND tabid = ? AND block = ?",
        array($potentialsTabid, $customBlockId)
    );
    
    if ($adb->num_rows($layoutCheck) > 0) {
        echo "<p class='success'>✅ Field is in Custom Information block</p>";
    } else {
        echo "<p class='warning'>⚠️ Field may not be in correct block</p>";
    }
    
    // Verify field properties
    $fieldProps = $adb->pquery(
        "SELECT fieldlabel, readonly, presence, typeofdata, mandatory 
         FROM vtiger_field 
         WHERE fieldname = 'cf_855' AND tabid = ?",
        array($potentialsTabid)
    );
    
    if ($adb->num_rows($fieldProps) > 0) {
        $props = $adb->fetchByAssoc($fieldProps);
        echo "<table>";
        echo "<tr><th>Property</th><th>Value</th><th>Expected</th><th>Status</th></tr>";
        
        $checks = array(
            'fieldlabel' => array('value' => $props['fieldlabel'], 'expected' => 'Company Code', 'ok' => $props['fieldlabel'] == 'Company Code'),
            'readonly' => array('value' => $props['readonly'], 'expected' => '0 (Editable)', 'ok' => $props['readonly'] == 0),
            'presence' => array('value' => $props['presence'], 'expected' => '2 (Visible)', 'ok' => $props['presence'] == 2),
            'mandatory' => array('value' => $props['mandatory'], 'expected' => '1 (Required)', 'ok' => $props['mandatory'] == 1),
            'typeofdata' => array('value' => $props['typeofdata'], 'expected' => 'V~M (Mandatory)', 'ok' => strpos($props['typeofdata'], 'M') !== false),
        );
        
        foreach ($checks as $prop => $check) {
            $status = $check['ok'] ? '<span class="success">✅</span>' : '<span class="error">❌</span>';
            echo "<tr>";
            echo "<td><strong>$prop</strong></td>";
            echo "<td>{$check['value']}</td>";
            echo "<td>{$check['expected']}</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // Step 5: Verify Other Fields
    echo "<div class='step'>";
    echo "<h2>Step 5: Verify Other Fields</h2>";
    
    $otherFields = array(
        'cf_857' => 'Project Name',
        'cf_859' => 'Project Code'
    );
    
    echo "<table>";
    echo "<tr><th>Field</th><th>Label</th><th>Status</th></tr>";
    
    foreach ($otherFields as $fieldname => $label) {
        $check = $adb->pquery(
            "SELECT fieldid FROM vtiger_field WHERE fieldname = ? AND tabid = ?",
            array($fieldname, $potentialsTabid)
        );
        
        $status = $adb->num_rows($check) > 0 ? '<span class="success">✅ Exists</span>' : '<span class="error">❌ Missing</span>';
        echo "<tr>";
        echo "<td>$fieldname</td>";
        echo "<td>$label</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Final Summary
    echo "<hr>";
    echo "<h2>📋 Summary</h2>";
    echo "<div class='step'>";
    
    $finalCheck = $adb->pquery(
        "SELECT fieldid, fieldlabel, readonly, presence, typeofdata, mandatory 
         FROM vtiger_field 
         WHERE fieldname = 'cf_855' AND tabid = ?",
        array($potentialsTabid)
    );
    
    if ($adb->num_rows($finalCheck) > 0) {
        $final = $adb->fetchByAssoc($finalCheck);
        echo "<h3>✅ Company Code Field (cf_855) Status:</h3>";
        echo "<ul>";
        echo "<li>Field ID: {$final['fieldid']}</li>";
        echo "<li>Label: {$final['fieldlabel']}</li>";
        echo "<li>Read-only: " . ($final['readonly'] == 0 ? 'No (Editable)' : 'Yes') . "</li>";
        echo "<li>Presence: " . ($final['presence'] == 2 ? 'Visible' : 'Hidden') . "</li>";
        echo "<li>Mandatory: " . ($final['mandatory'] == 1 ? 'Yes (Required)' : 'No') . "</li>";
        echo "<li>Type: {$final['typeofdata']}</li>";
        echo "</ul>";
        
        echo "<h3>✅ Next Steps:</h3>";
        echo "<ol>";
        echo "<li>Clear cache: <code>rm -rf cache/* templates_c/*</code></li>";
        echo "<li>Refresh browser and go to Potentials → Create</li>";
        echo "<li>Verify 'Company Code' field appears in Custom Information block</li>";
        echo "<li>Field should be required (cannot save if empty)</li>";
        echo "<li>Create Opportunity with Company Code</li>";
        echo "<li>Verify Project Code is generated: <code>{DATE}-{CONTACT}-{COMPANY_CODE}-{PROJECT}</code></li>";
        echo "</ol>";
    } else {
        echo "<p class='error'>❌ Field creation failed</p>";
    }
    
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

