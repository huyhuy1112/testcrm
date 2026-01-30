<?php
/**
 * Restore Project Code Auto-Generation Feature
 * Run via browser: http://localhost:8080/restore_project_code_feature.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(60);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Restore Project Code Feature</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;} .info{color:blue;} table{border-collapse:collapse;width:100%;margin:10px 0;} th,td{border:1px solid #ddd;padding:8px;} th{background:#f0f0f0;} .step{background:#f9f9f9;padding:15px;margin:10px 0;border-left:4px solid #007bff;}</style>";
echo "</head><body>";
echo "<h1>🔧 Restore Project Code Auto-Generation Feature</h1>";
echo "<hr>";

try {
    require_once 'config.php';
    require_once 'include/database/PearDatabase.php';
    require_once 'include/events/include.inc';
    
    global $adb;
    $adb = PearDatabase::getInstance();
    
    if (!$adb) {
        throw new Exception("Database connection failed");
    }
    
    // Step 1: Verify Potentials Module
    echo "<div class='step'>";
    echo "<h2>Step 1: Verify Potentials Module</h2>";
    
    $tabResult = $adb->pquery("SELECT tabid, name FROM vtiger_tab WHERE name = 'Potentials'", array());
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
    
    // Step 2: Check and Create Custom Fields
    echo "<div class='step'>";
    echo "<h2>Step 2: Custom Fields Verification</h2>";
    
    $fieldsToCreate = array(
        'cf_857' => array(
            'label' => 'Project Name',
            'uitype' => 1, // Text
            'typeofdata' => 'V~O', // Optional
            'readonly' => 0,
            'presence' => 2, // Visible
            'maximumlength' => 255
        ),
        'cf_859' => array(
            'label' => 'Project Code',
            'uitype' => 1, // Text
            'typeofdata' => 'V~O', // Optional
            'readonly' => 1, // Read-only
            'presence' => 2, // Visible
            'maximumlength' => 255
        )
    );
    
    $maxFieldId = $adb->getUniqueId('vtiger_field');
    $nextFieldId = $maxFieldId;
    
    foreach ($fieldsToCreate as $fieldname => $config) {
        // Check if field exists
        $fieldCheck = $adb->pquery(
            "SELECT fieldid, fieldname, fieldlabel, presence, readonly FROM vtiger_field WHERE fieldname = ? AND tabid = ?",
            array($fieldname, $potentialsTabid)
        );
        
        if ($adb->num_rows($fieldCheck) > 0) {
            $fieldRow = $adb->fetchByAssoc($fieldCheck);
            echo "<p class='info'>ℹ️ Field '$fieldname' ({$fieldRow['fieldlabel']}) already exists (fieldid: {$fieldRow['fieldid']})</p>";
            
            // Verify it's correct
            if ($fieldRow['readonly'] != $config['readonly'] || $fieldRow['presence'] != $config['presence']) {
                echo "<p class='warning'>⚠️ Field properties don't match expected values. Updating...</p>";
                $adb->pquery(
                    "UPDATE vtiger_field SET readonly = ?, presence = ? WHERE fieldid = ?",
                    array($config['readonly'], $config['presence'], $fieldRow['fieldid'])
                );
                echo "<p class='success'>✅ Updated field properties</p>";
            }
        } else {
            // Get next sequence
            $seqResult = $adb->pquery(
                "SELECT MAX(sequence) as max_seq FROM vtiger_field WHERE tabid = ? AND block = ?",
                array($potentialsTabid, $customBlockId)
            );
            $seqRow = $adb->fetchByAssoc($seqResult);
            $nextSequence = ($seqRow['max_seq'] ? $seqRow['max_seq'] + 1 : 1);
            
            // Create field
            $fieldId = $nextFieldId++;
            $columnName = $fieldname;
            $tableName = 'vtiger_potentialscf';
            
            echo "<p>Creating field '$fieldname' ({$config['label']})...</p>";
            
            // Insert into vtiger_field
            $adb->pquery(
                "INSERT INTO vtiger_field 
                (tabid, fieldid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, 
                 readonly, presence, defaultvalue, maximumlength, sequence, block, displaytype, typeofdata, 
                 quickcreate, quickcreatesequence, info_type, helpinfo, summaryfield, headerfield)
                VALUES (?, ?, ?, ?, 2, ?, ?, ?, ?, ?, '', ?, ?, ?, 1, ?, 1, ?, 'BAS', '', 0, 0)",
                array(
                    $potentialsTabid,      // tabid
                    $fieldId,              // fieldid
                    $columnName,           // columnname
                    $tableName,            // tablename
                    $config['uitype'],      // uitype
                    $fieldname,            // fieldname
                    $config['label'],      // fieldlabel
                    $config['readonly'],   // readonly
                    $config['presence'],   // presence
                    $config['maximumlength'], // maximumlength
                    $nextSequence,         // sequence
                    $customBlockId,        // block
                    $config['typeofdata'], // typeofdata
                    $nextSequence          // quickcreatesequence
                )
            );
            
            echo "<p class='success'>✅ Created field '$fieldname' (fieldid: $fieldId, sequence: $nextSequence)</p>";
        }
    }
    echo "</div>";
    
    // Step 3: Verify/Create Database Columns
    echo "<div class='step'>";
    echo "<h2>Step 3: Database Columns Verification</h2>";
    
    foreach ($fieldsToCreate as $fieldname => $config) {
        $columnCheck = $adb->pquery(
            "SHOW COLUMNS FROM vtiger_potentialscf LIKE ?",
            array($fieldname)
        );
        
        if ($adb->num_rows($columnCheck) == 0) {
            echo "<p>Adding column '$fieldname' to vtiger_potentialscf...</p>";
            
            // Add column
            $adb->pquery(
                "ALTER TABLE vtiger_potentialscf ADD COLUMN $fieldname VARCHAR(255) DEFAULT NULL",
                array()
            );
            
            echo "<p class='success'>✅ Added column '$fieldname'</p>";
        } else {
            echo "<p class='info'>ℹ️ Column '$fieldname' already exists</p>";
        }
    }
    echo "</div>";
    
    // Step 4: Create ProjectCodeHandler
    echo "<div class='step'>";
    echo "<h2>Step 4: ProjectCodeHandler Creation</h2>";
    
    $handlerFile = 'modules/Potentials/ProjectCodeHandler.php';
    
    if (file_exists($handlerFile)) {
        echo "<p class='info'>ℹ️ Handler file already exists: $handlerFile</p>";
        
        // Verify it's correct
        $handlerContent = file_get_contents($handlerFile);
        if (strpos($handlerContent, 'class ProjectCodeHandler') === false) {
            echo "<p class='error'>❌ Handler file exists but doesn't contain ProjectCodeHandler class. Will recreate.</p>";
            $recreate = true;
        } else {
            $recreate = false;
            echo "<p class='success'>✅ Handler file is valid</p>";
        }
    } else {
        $recreate = true;
    }
    
    if ($recreate) {
        $handlerCode = <<<'PHP'
<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

require_once 'data/VTEntityDelta.php';

class ProjectCodeHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $log, $adb;

		try {
			// STRICT: Handle ONLY vtiger.entity.aftersave (NOT final to avoid recursion)
			if ($eventName !== 'vtiger.entity.aftersave') {
				return;
			}

			$moduleName = $entityData->getModuleName();
			if ($moduleName !== 'Potentials') {
				return;
			}

			$recordId = $entityData->getId();
			if (empty($recordId)) {
				return;
			}

			// CRITICAL: Only process NEW records
			if (!$entityData->isNew()) {
				if ($log) {
					$log->debug("[ProjectCodeHandler] Skipping - not a new record (ID: $recordId)");
				}
				return;
			}

			// Check if Project Code already exists
			$codeCheck = $adb->pquery(
				"SELECT cf_859 FROM vtiger_potentialscf WHERE potentialid = ?",
				array($recordId)
			);
			
			if ($adb->num_rows($codeCheck) > 0) {
				$existingCode = $adb->query_result($codeCheck, 0, 'cf_859');
				if (!empty($existingCode)) {
					if ($log) {
						$log->debug("[ProjectCodeHandler] Project Code already exists: $existingCode (ID: $recordId)");
					}
					return; // Already generated, skip
				}
			}

			// Get Opportunity data
			$potentialResult = $adb->pquery(
				"SELECT p.potentialid, p.potentialname, p.contact_id, p.related_to, ce.createdtime
				 FROM vtiger_potential p
				 INNER JOIN vtiger_crmentity ce ON ce.crmid = p.potentialid
				 WHERE p.potentialid = ?",
				array($recordId)
			);
			
			if ($adb->num_rows($potentialResult) == 0) {
				if ($log) {
					$log->debug("[ProjectCodeHandler] Opportunity not found (ID: $recordId)");
				}
				return;
			}
			
			$potentialRow = $adb->fetchByAssoc($potentialResult);
			$contactId = $potentialRow['contact_id'];
			$accountId = $potentialRow['related_to'];
			$createdTime = $potentialRow['createdtime'];
			
			// Validate required fields
			if (empty($contactId) || $contactId == 0) {
				if ($log) {
					$log->debug("[ProjectCodeHandler] No contact linked (ID: $recordId)");
				}
				return;
			}
			
			if (empty($accountId) || $accountId == 0) {
				if ($log) {
					$log->debug("[ProjectCodeHandler] No account linked (ID: $recordId)");
				}
				return;
			}

			// 1. CREATE_DATE: Format createdtime as YYYYMMDD
			$createDate = '';
			if (!empty($createdTime)) {
				$dateObj = new DateTime($createdTime);
				$createDate = $dateObj->format('Ymd');
			} else {
				$createDate = date('Ymd');
			}

			// 2. CONTACT_ID: Get contact_no from vtiger_contactdetails
			$contactResult = $adb->pquery(
				"SELECT contact_no FROM vtiger_contactdetails WHERE contactid = ?",
				array($contactId)
			);
			
			if ($adb->num_rows($contactResult) == 0) {
				if ($log) {
					$log->debug("[ProjectCodeHandler] Contact not found (contactid: $contactId)");
				}
				return;
			}
			
			$contactNo = $adb->query_result($contactResult, 0, 'contact_no');
			if (empty($contactNo)) {
				if ($log) {
					$log->debug("[ProjectCodeHandler] Contact number is empty (contactid: $contactId)");
				}
				return;
			}

			// 3. COMPANY_CODE: Get from vtiger_accountscf.cf_855 or fallback to account_no
			$accountResult = $adb->pquery(
				"SELECT a.account_no, acf.cf_855 
				 FROM vtiger_account a
				 LEFT JOIN vtiger_accountscf acf ON acf.accountid = a.accountid
				 WHERE a.accountid = ?",
				array($accountId)
			);
			
			if ($adb->num_rows($accountResult) == 0) {
				if ($log) {
					$log->debug("[ProjectCodeHandler] Account not found (accountid: $accountId)");
				}
				return;
			}
			
			$accountRow = $adb->fetchByAssoc($accountResult);
			$companyCode = $accountRow['cf_855'];
			if (empty($companyCode)) {
				$companyCode = $accountRow['account_no'];
			}
			if (empty($companyCode)) {
				if ($log) {
					$log->debug("[ProjectCodeHandler] Company code is empty (accountid: $accountId)");
				}
				return;
			}

			// 4. PROJECT_NAME: Get from vtiger_potentialscf.cf_857 or fallback to potentialname
			$projectName = '';
			$projectNameResult = $adb->pquery(
				"SELECT cf_857 FROM vtiger_potentialscf WHERE potentialid = ?",
				array($recordId)
			);
			
			if ($adb->num_rows($projectNameResult) > 0) {
				$projectName = $adb->query_result($projectNameResult, 0, 'cf_857');
			}
			
			if (empty($projectName)) {
				$projectName = $potentialRow['potentialname'];
			}
			
			if (empty($projectName)) {
				if ($log) {
					$log->debug("[ProjectCodeHandler] Project name is empty (ID: $recordId)");
				}
				return;
			}

			// Sanitize project name (remove special chars, spaces -> hyphens)
			$projectName = strtolower($projectName);
			$projectName = preg_replace('/[^a-z0-9]+/', '-', $projectName);
			$projectName = trim($projectName, '-');

			// Generate Project Code: {CREATE_DATE}-{CONTACT_ID}-{COMPANY_CODE}-{PROJECT_NAME}
			$projectCode = "$createDate-$contactNo-$companyCode-$projectName";

			// Update directly in database (no save() to avoid recursion)
			// First ensure row exists in vtiger_potentialscf
			$checkRow = $adb->pquery(
				"SELECT potentialid FROM vtiger_potentialscf WHERE potentialid = ?",
				array($recordId)
			);
			
			if ($adb->num_rows($checkRow) == 0) {
				// Insert row if doesn't exist
				$adb->pquery(
					"INSERT INTO vtiger_potentialscf (potentialid) VALUES (?)",
					array($recordId)
				);
			}
			
			// Update Project Code
			$adb->pquery(
				"UPDATE vtiger_potentialscf SET cf_859 = ? WHERE potentialid = ?",
				array($projectCode, $recordId)
			);

			if ($log) {
				$log->debug("[ProjectCodeHandler] Generated Project Code: $projectCode for Opportunity ID: $recordId");
			}

		} catch (Exception $e) {
			if ($log) {
				$log->error("[ProjectCodeHandler] Error: " . $e->getMessage());
			}
			// Silent failure - don't break save process
		}
	}
}

PHP;
        
        file_put_contents($handlerFile, $handlerCode);
        echo "<p class='success'>✅ Created handler file: $handlerFile</p>";
    }
    echo "</div>";
    
    // Step 5: Register Event Handler
    echo "<div class='step'>";
    echo "<h2>Step 5: Event Handler Registration</h2>";
    
    $handlerCheck = $adb->pquery(
        "SELECT eventhandler_id, event_name, handler_path, handler_class, is_active 
         FROM vtiger_eventhandlers 
         WHERE handler_class = 'ProjectCodeHandler'",
        array()
    );
    
    if ($adb->num_rows($handlerCheck) > 0) {
        $handlerRow = $adb->fetchByAssoc($handlerCheck);
        $handlerId = $handlerRow['eventhandler_id'];
        
        // Check if correct
        if ($handlerRow['event_name'] != 'vtiger.entity.aftersave' || 
            $handlerRow['handler_path'] != 'modules/Potentials/ProjectCodeHandler.php' ||
            $handlerRow['is_active'] != 1) {
            
            echo "<p class='warning'>⚠️ Handler exists but needs update</p>";
            
            // Update handler
            $adb->pquery(
                "UPDATE vtiger_eventhandlers 
                 SET event_name = ?, handler_path = ?, is_active = 1 
                 WHERE eventhandler_id = ?",
                array('vtiger.entity.aftersave', 'modules/Potentials/ProjectCodeHandler.php', $handlerId)
            );
            
            echo "<p class='success'>✅ Updated handler (ID: $handlerId)</p>";
        } else {
            echo "<p class='success'>✅ Handler already registered correctly (ID: $handlerId)</p>";
        }
        
        // Check for duplicates
        $dupCheck = $adb->pquery(
            "SELECT COUNT(*) as cnt FROM vtiger_eventhandlers WHERE handler_class = 'ProjectCodeHandler'",
            array()
        );
        $dupRow = $adb->fetchByAssoc($dupCheck);
        if ($dupRow['cnt'] > 1) {
            echo "<p class='warning'>⚠️ Found {$dupRow['cnt']} handlers. Removing duplicates...</p>";
            // Keep only the first one
            $allHandlers = $adb->pquery(
                "SELECT eventhandler_id FROM vtiger_eventhandlers WHERE handler_class = 'ProjectCodeHandler' ORDER BY eventhandler_id",
                array()
            );
            $first = true;
            while ($hRow = $adb->fetchByAssoc($allHandlers)) {
                if (!$first) {
                    $adb->pquery("DELETE FROM vtiger_eventhandlers WHERE eventhandler_id = ?", array($hRow['eventhandler_id']));
                }
                $first = false;
            }
            echo "<p class='success'>✅ Removed duplicate handlers</p>";
        }
    } else {
        // Register handler
        $em = new VTEventsManager($adb);
        $em->registerHandler(
            'vtiger.entity.aftersave',
            'modules/Potentials/ProjectCodeHandler.php',
            'ProjectCodeHandler',
            '',
            '[]'
        );
        
        echo "<p class='success'>✅ Registered ProjectCodeHandler</p>";
    }
    echo "</div>";
    
    // Step 6: Clear Cache
    echo "<div class='step'>";
    echo "<h2>Step 6: Cache Clear</h2>";
    
    $cacheDirs = array('cache', 'templates_c');
    foreach ($cacheDirs as $dir) {
        if (is_dir($dir)) {
            $files = glob("$dir/*");
            $count = 0;
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                    $count++;
                }
            }
            echo "<p class='success'>✅ Cleared $count files from $dir/</p>";
        } else {
            echo "<p class='info'>ℹ️ Directory $dir/ not found (may not exist yet)</p>";
        }
    }
    echo "</div>";
    
    // Final Summary
    echo "<hr>";
    echo "<h2>📋 Summary</h2>";
    echo "<div class='step'>";
    
    // Verify everything
    $verifyFields = $adb->pquery(
        "SELECT fieldname, fieldlabel, readonly, presence FROM vtiger_field 
         WHERE fieldname IN ('cf_857', 'cf_859') AND tabid = ?",
        array($potentialsTabid)
    );
    
    echo "<h3>Custom Fields Status:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Label</th><th>Read-only</th><th>Presence</th></tr>";
    while ($vf = $adb->fetchByAssoc($verifyFields)) {
        echo "<tr>";
        echo "<td>{$vf['fieldname']}</td>";
        echo "<td>{$vf['fieldlabel']}</td>";
        echo "<td>" . ($vf['readonly'] == 1 ? 'Yes' : 'No') . "</td>";
        echo "<td>" . ($vf['presence'] == 2 ? 'Visible' : 'Hidden') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $verifyHandler = $adb->pquery(
        "SELECT eventhandler_id, event_name, handler_path, handler_class, is_active 
         FROM vtiger_eventhandlers 
         WHERE handler_class = 'ProjectCodeHandler'",
        array()
    );
    
    echo "<h3>Event Handler Status:</h3>";
    if ($adb->num_rows($verifyHandler) > 0) {
        $vh = $adb->fetchByAssoc($verifyHandler);
        echo "<p class='success'>✅ Handler registered</p>";
        echo "<ul>";
        echo "<li>ID: {$vh['eventhandler_id']}</li>";
        echo "<li>Event: {$vh['event_name']}</li>";
        echo "<li>Path: {$vh['handler_path']}</li>";
        echo "<li>Class: {$vh['handler_class']}</li>";
        echo "<li>Active: " . ($vh['is_active'] == 1 ? 'Yes' : 'No') . "</li>";
        echo "</ul>";
    } else {
        echo "<p class='error'>❌ Handler not found</p>";
    }
    
    echo "<h3>✅ Feature Restored!</h3>";
    echo "<p>Project Code will now auto-generate when creating new Opportunities.</p>";
    echo "<p><strong>Format:</strong> {CREATE_DATE}-{CONTACT_ID}-{COMPANY_CODE}-{PROJECT_NAME}</p>";
    echo "<p><strong>Example:</strong> 20260106-CON13-z751-my-project</p>";
    
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


