<?php
/**
 * Test Project Code Generation
 * Run via browser: http://localhost:8080/test_project_code_generation.php
 * 
 * This script creates a test Opportunity and verifies Project Code is generated
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test Project Code Generation</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;}</style>";
echo "</head><body>";
echo "<h2>🧪 Test Project Code Generation</h2>";
echo "<hr>";

try {
    require_once 'config.php';
    require_once 'include/database/PearDatabase.php';
    
    global $adb;
    $adb = PearDatabase::getInstance();
    
    echo "<h3>Step 1: Verify Prerequisites</h3>";
    
    // Check handler
    $handlerCheck = $adb->pquery(
        "SELECT eventhandler_id, is_active FROM vtiger_eventhandlers WHERE handler_class = 'ProjectCodeHandler'",
        array()
    );
    
    if ($adb->num_rows($handlerCheck) == 0) {
        echo "<p class='error'>❌ ProjectCodeHandler not registered. Please run restore_project_code_feature.php first.</p>";
        exit;
    }
    
    $handlerRow = $adb->fetchByAssoc($handlerCheck);
    if ($handlerRow['is_active'] != 1) {
        echo "<p class='error'>❌ ProjectCodeHandler is not active. Please activate it.</p>";
        exit;
    }
    
    echo "<p class='success'>✅ ProjectCodeHandler is registered and active</p>";
    
    // Check fields
    $fieldCheck = $adb->pquery(
        "SELECT fieldname FROM vtiger_field WHERE fieldname IN ('cf_857', 'cf_859') AND tabid = 2",
        array()
    );
    
    if ($adb->num_rows($fieldCheck) < 2) {
        echo "<p class='error'>❌ Custom fields missing. Please run restore_project_code_feature.php first.</p>";
        exit;
    }
    
    echo "<p class='success'>✅ Custom fields exist</p>";
    
    echo "<h3>Step 2: Find Test Data</h3>";
    
    // Find a contact with contact_no
    $contactResult = $adb->pquery(
        "SELECT contactid, contact_no, firstname, lastname 
         FROM vtiger_contactdetails 
         WHERE contact_no IS NOT NULL AND contact_no != '' 
         LIMIT 1",
        array()
    );
    
    if ($adb->num_rows($contactResult) == 0) {
        echo "<p class='error'>❌ No contacts found with contact_no. Please create a contact first.</p>";
        exit;
    }
    
    $contactRow = $adb->fetchByAssoc($contactResult);
    $contactId = $contactRow['contactid'];
    $contactNo = $contactRow['contact_no'];
    echo "<p class='info'>ℹ️ Using Contact: {$contactRow['firstname']} {$contactRow['lastname']} (ID: $contactId, No: $contactNo)</p>";
    
    // Find an account with company code
    $accountResult = $adb->pquery(
        "SELECT a.accountid, a.accountname, a.account_no, acf.cf_855 
         FROM vtiger_account a
         LEFT JOIN vtiger_accountscf acf ON acf.accountid = a.accountid
         WHERE (acf.cf_855 IS NOT NULL AND acf.cf_855 != '') 
            OR (a.account_no IS NOT NULL AND a.account_no != '')
         LIMIT 1",
        array()
    );
    
    if ($adb->num_rows($accountResult) == 0) {
        echo "<p class='error'>❌ No accounts found with company code. Please create an account with cf_855 or account_no first.</p>";
        exit;
    }
    
    $accountRow = $adb->fetchByAssoc($accountResult);
    $accountId = $accountRow['accountid'];
    $companyCode = !empty($accountRow['cf_855']) ? $accountRow['cf_855'] : $accountRow['account_no'];
    echo "<p class='info'>ℹ️ Using Account: {$accountRow['accountname']} (ID: $accountId, Code: $companyCode)</p>";
    
    echo "<h3>Step 3: Create Test Opportunity</h3>";
    
    // Get current user
    global $current_user;
    if (!$current_user) {
        $userResult = $adb->pquery("SELECT id FROM vtiger_users WHERE status = 'Active' LIMIT 1", array());
        if ($adb->num_rows($userResult) > 0) {
            $userId = $adb->query_result($userResult, 0, 'id');
        } else {
            throw new Exception("No active user found");
        }
    } else {
        $userId = $current_user->id;
    }
    
    // Create Opportunity using CRMEntity
    require_once 'modules/Potentials/Potentials.php';
    require_once 'data/CRMEntity.php';
    
    $focus = CRMEntity::getInstance('Potentials');
    $focus->column_fields['potentialname'] = 'Test Project Code ' . date('Y-m-d H:i:s');
    $focus->column_fields['contact_id'] = $contactId;
    $focus->column_fields['related_to'] = $accountId;
    $focus->column_fields['cf_857'] = 'test-project-' . time(); // Project Name
    $focus->column_fields['assigned_user_id'] = $userId;
    $focus->column_fields['sales_stage'] = 'Prospecting';
    $focus->column_fields['amount'] = 1000;
    
    // Save
    $focus->save('Potentials');
    $opportunityId = $focus->id;
    
    echo "<p class='success'>✅ Created Opportunity (ID: $opportunityId)</p>";
    echo "<p>Potential Name: {$focus->column_fields['potentialname']}</p>";
    echo "<p>Project Name (cf_857): {$focus->column_fields['cf_857']}</p>";
    
    echo "<h3>Step 4: Verify Project Code Generation</h3>";
    
    // Wait a moment for event to fire
    sleep(1);
    
    // Check Project Code
    $codeResult = $adb->pquery(
        "SELECT cf_859, cf_857 FROM vtiger_potentialscf WHERE potentialid = ?",
        array($opportunityId)
    );
    
    if ($adb->num_rows($codeResult) > 0) {
        $codeRow = $adb->fetchByAssoc($codeResult);
        $projectCode = $codeRow['cf_859'];
        $projectName = $codeRow['cf_857'];
        
        if (!empty($projectCode)) {
            echo "<p class='success'>✅ Project Code Generated: <strong>$projectCode</strong></p>";
            
            // Verify format
            $expectedFormat = date('Ymd') . "-$contactNo-$companyCode-" . strtolower(preg_replace('/[^a-z0-9]+/', '-', $projectName));
            $expectedFormat = trim($expectedFormat, '-');
            
            echo "<p class='info'>ℹ️ Expected format: {YYYYMMDD}-{CONTACT_NO}-{COMPANY_CODE}-{PROJECT_NAME}</p>";
            echo "<p class='info'>ℹ️ Expected value: $expectedFormat</p>";
            
            if (strpos($projectCode, $contactNo) !== false && 
                strpos($projectCode, $companyCode) !== false) {
                echo "<p class='success'>✅ Project Code format is correct!</p>";
            } else {
                echo "<p class='error'>⚠️ Project Code format may be incorrect. Check components.</p>";
            }
        } else {
            echo "<p class='error'>❌ Project Code is empty. Handler may not have fired.</p>";
            echo "<p>Check:</p>";
            echo "<ul>";
            echo "<li>Handler is registered and active</li>";
            echo "<li>Event 'vtiger.entity.aftersave' is firing</li>";
            echo "<li>All required data (contact, account, project name) is present</li>";
            echo "</ul>";
        }
    } else {
        echo "<p class='error'>❌ No record found in vtiger_potentialscf</p>";
    }
    
    echo "<h3>Step 5: View Opportunity</h3>";
    echo "<p><a href='index.php?module=Potentials&view=Detail&record=$opportunityId' target='_blank'>View Opportunity Detail</a></p>";
    
    echo "<hr>";
    echo "<h3>📋 Test Summary</h3>";
    echo "<p>Opportunity ID: <strong>$opportunityId</strong></p>";
    if (!empty($projectCode)) {
        echo "<p>Project Code: <strong>$projectCode</strong></p>";
        echo "<p class='success'><strong>✅ Test PASSED - Project Code was generated successfully!</strong></p>";
    } else {
        echo "<p class='error'><strong>❌ Test FAILED - Project Code was not generated</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

echo "</body></html>";

