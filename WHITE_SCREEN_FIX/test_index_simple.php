<?php
/**
 * TEST INDEX.PHP SIMPLE - Exact copy with error display
 * 
 * File này là bản copy của index.php nhưng có enable errors
 * để xem lỗi thực sự
 * 
 * SỬ DỤNG:
 *   1. Upload file này lên hosting
 *   2. Truy cập: https://supertestcrm.tdbsolution.com/test_index_simple.php
 *   3. Xem errors
 *   4. XÓA file này sau khi fix xong
 */

// Enable ALL errors
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
ini_set('max_execution_time', '60');

// Start output buffering to catch everything
ob_start();

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

if (!file_exists("vendor/autoload.php")) {
    echo "Please install composer dependencies.";
    exit;
}

//Overrides GetRelatedList : used to get related query
//TODO : Eliminate below hacking solution
include_once 'config.php';
require_once 'vendor/autoload.php';
include_once 'include/Webservices/Relation.php';

include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

try {
    $webUI = new Vtiger_WebUI();
    $webUI->process(new Vtiger_Request($_REQUEST, $_REQUEST));
} catch (Error $e) {
    echo "<h1 style='color:red'>FATAL ERROR</h1>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h2>Stack Trace:</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Exception $e) {
    echo "<h1 style='color:red'>EXCEPTION</h1>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h2>Stack Trace:</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Get any output
$output = ob_get_contents();
ob_end_clean();

// Display output
if (!empty($output)) {
    echo "<h2>Output Captured:</h2>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
} else {
    echo "<p style='color:orange'><strong>⚠️ No output captured</strong></p>";
    echo "<p>This could mean:</p>";
    echo "<ul>";
    echo "<li>Request was redirected (check headers)</li>";
    echo "<li>Output was sent directly (not buffered)</li>";
    echo "<li>Process completed but no output</li>";
    echo "</ul>";
}

// Check headers
if (headers_sent($file, $line)) {
    echo "<h2>Headers Sent:</h2>";
    echo "<p>File: $file</p>";
    echo "<p>Line: $line</p>";
    echo "<h3>Headers:</h3>";
    echo "<pre>";
    foreach (headers_list() as $header) {
        echo htmlspecialchars($header) . "\n";
    }
    echo "</pre>";
}

echo "<hr>";
echo "<p><strong>Lưu ý:</strong> Xóa file này sau khi fix xong!</p>";
?>

