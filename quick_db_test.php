<?php
/**
 * Quick Database Test - Minimal test to avoid hanging
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(10);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Quick DB Test</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;}</style>";
echo "</head><body>";
echo "<h2>Quick Database Test</h2>";

try {
    echo "<p>Step 1: Loading config.php...</p>";
    flush();
    require_once 'config.php';
    echo "<p class='success'>✅ config.php loaded</p>";
    
    echo "<p>Step 2: Loading config.inc.php...</p>";
    flush();
    require_once 'config.inc.php';
    echo "<p class='success'>✅ config.inc.php loaded</p>";
    
    echo "<p>Step 3: Database config...</p>";
    echo "<p>Server: " . htmlspecialchars($dbconfig['db_server']) . "</p>";
    echo "<p>Port: " . htmlspecialchars($dbconfig['db_port']) . "</p>";
    echo "<p>Database: " . htmlspecialchars($dbconfig['db_name']) . "</p>";
    echo "<p>User: " . htmlspecialchars($dbconfig['db_username']) . "</p>";
    
    echo "<p>Step 4: Testing direct MySQL connection...</p>";
    flush();
    
    $host = $dbconfig['db_server'];
    $port = str_replace(':', '', $dbconfig['db_port']);
    $dbname = $dbconfig['db_name'];
    $user = $dbconfig['db_username'];
    $pass = $dbconfig['db_password'];
    
    $mysqli = new mysqli($host, $user, $pass, $dbname, $port ?: 3306);
    
    if ($mysqli->connect_error) {
        echo "<p class='error'>❌ MySQL Connection failed: " . $mysqli->connect_error . "</p>";
    } else {
        echo "<p class='success'>✅ Direct MySQL connection: OK</p>";
        
        $result = $mysqli->query("SELECT COUNT(*) as cnt FROM vtiger_notifications");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p>Notifications in DB: " . $row['cnt'] . "</p>";
        }
        
        $mysqli->close();
    }
    
    echo "<p>Step 5: Testing PearDatabase (with timeout)...</p>";
    flush();
    
    // Try to load PearDatabase with error handling
    if (file_exists('include/database/PearDatabase.php')) {
        echo "<p>PearDatabase.php exists, attempting to load...</p>";
        flush();
        
        // Use include instead of require to catch fatal errors
        @include_once 'include/database/PearDatabase.php';
        
        if (class_exists('PearDatabase')) {
            echo "<p class='success'>✅ PearDatabase class loaded</p>";
            
            try {
                global $adb;
                $adb = @PearDatabase::getInstance();
                
                if ($adb) {
                    echo "<p class='success'>✅ PearDatabase instance created</p>";
                } else {
                    echo "<p class='error'>❌ PearDatabase::getInstance() returned null</p>";
                }
            } catch (Exception $e) {
                echo "<p class='error'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
            } catch (Error $e) {
                echo "<p class='error'>❌ Fatal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p class='error'>❌ PearDatabase class not found after include</p>";
        }
    } else {
        echo "<p class='error'>❌ PearDatabase.php not found</p>";
    }
    
    echo "<hr>";
    echo "<h3>Summary</h3>";
    echo "<p>If direct MySQL connection works but PearDatabase hangs, there may be an issue with PearDatabase initialization.</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
} catch (Error $e) {
    echo "<p class='error'>❌ Fatal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

echo "</body></html>";


