<?php
/**
 * Test index.php execution
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

ob_start();

echo "<h2>Testing index.php execution</h2>";
echo "<hr>";

try {
    if (!file_exists("vendor/autoload.php")) {
        throw new Exception("vendor/autoload.php not found");
    }
    
    require_once 'vendor/autoload.php';
    echo "✅ Composer loaded<br>";
    
    require_once 'config.php';
    echo "✅ config.php loaded<br>";
    
    require_once 'config.inc.php';
    echo "✅ config.inc.php loaded<br>";
    
    require_once 'include/Webservices/Relation.php';
    echo "✅ Relation.php loaded<br>";
    
    require_once 'vtlib/Vtiger/Module.php';
    echo "✅ Module.php loaded<br>";
    
    require_once 'includes/main/WebUI.php';
    echo "✅ WebUI.php loaded<br>";
    
    echo "<hr>";
    echo "<h3>Creating WebUI instance...</h3>";
    
    $webUI = new Vtiger_WebUI();
    echo "✅ WebUI created<br>";
    
    echo "<h3>Creating Vtiger_Request...</h3>";
    $request = new Vtiger_Request($_REQUEST, $_REQUEST);
    echo "✅ Request created<br>";
    
    echo "<h3>Processing request...</h3>";
    echo "<p>This may take a moment...</p>";
    
    ob_end_flush();
    flush();
    
    $webUI->process($request);
    
} catch (Exception $e) {
    $output = ob_get_clean();
    echo "<div style='background:#fee;border:1px solid #f00;padding:10px;'>";
    echo "<h3 style='color:red;'>ERROR</h3>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
    
    if (!empty($output)) {
        echo "<h3>Output before error:</h3>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
}


