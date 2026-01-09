<?php
/**
 * Check White Screen Issues
 * Run via browser: http://localhost:8080/check_white_screen.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>White Screen Check</title>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;} .warning{color:orange;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;} th{background:#f0f0f0;}</style>";
echo "</head><body>";
echo "<h2>🔍 White Screen Diagnostic</h2>";
echo "<hr>";

try {
    // 1. Check config.php
    echo "<h3>1. Config Files</h3>";
    if (file_exists('config.php')) {
        echo "<p class='success'>✅ config.php exists</p>";
        require_once 'config.php';
    } else {
        echo "<p class='error'>❌ config.php NOT FOUND</p>";
        exit;
    }
    
    if (file_exists('config.inc.php')) {
        echo "<p class='success'>✅ config.inc.php exists</p>";
        $configSize = filesize('config.inc.php');
        echo "<p>Size: $configSize bytes</p>";
        
        if ($configSize < 100) {
            echo "<p class='error'>⚠️ config.inc.php is too small! May be empty or corrupted.</p>";
        }
    } else {
        echo "<p class='error'>❌ config.inc.php NOT FOUND</p>";
    }
    
    // 2. Check database connection
    echo "<hr><h3>2. Database Connection</h3>";
    echo "<p>Loading PearDatabase...</p>";
    flush();
    ob_flush();
    
    try {
        require_once 'include/database/PearDatabase.php';
        echo "<p class='success'>✅ PearDatabase.php loaded</p>";
        flush();
        ob_flush();
        
        global $adb, $dbconfig;
        
        echo "<p>Initializing PearDatabase instance...</p>";
        flush();
        ob_flush();
        
        $adb = PearDatabase::getInstance();
        
        if ($adb) {
            echo "<p class='success'>✅ PearDatabase initialized</p>";
            flush();
            ob_flush();
            
            // Test connection
            echo "<p>Testing database connection...</p>";
            flush();
            ob_flush();
            
            try {
                $result = $adb->query("SELECT 1 as test");
                if ($result) {
                    $row = $adb->fetchByAssoc($result);
                    echo "<p class='success'>✅ Database connection: OK</p>";
                    echo "<p>Database: " . htmlspecialchars($dbconfig['db_name']) . "</p>";
                    echo "<p>Host: " . htmlspecialchars($dbconfig['db_server']) . "</p>";
                    echo "<p>Port: " . htmlspecialchars($dbconfig['db_port']) . "</p>";
                    echo "<p>User: " . htmlspecialchars($dbconfig['db_username']) . "</p>";
                } else {
                    echo "<p class='error'>❌ Database query failed</p>";
                }
            } catch (Exception $e) {
                echo "<p class='error'>❌ Database query error: " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
                echo "<p>Line: " . $e->getLine() . "</p>";
            }
        } else {
            echo "<p class='error'>❌ Failed to initialize PearDatabase</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error loading PearDatabase: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p>Line: " . $e->getLine() . "</p>";
    } catch (Error $e) {
        echo "<p class='error'>❌ Fatal error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>File: " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p>Line: " . $e->getLine() . "</p>";
    }
    
    // 3. Check critical tables
    echo "<hr><h3>3. Critical Database Tables</h3>";
    $criticalTables = array(
        'vtiger_crmentity' => 'Core entity table',
        'vtiger_users' => 'Users table',
        'vtiger_organizationdetails' => 'Organization details',
        'vtiger_tab' => 'Modules table',
        'vtiger_notifications' => 'Notifications table'
    );
    
    echo "<table>";
    echo "<tr><th>Table</th><th>Status</th><th>Row Count</th><th>Description</th></tr>";
    
    foreach ($criticalTables as $table => $desc) {
        try {
            $check = $adb->query("SHOW TABLES LIKE '$table'");
            if ($adb->num_rows($check) > 0) {
                $countResult = $adb->query("SELECT COUNT(*) as cnt FROM $table");
                $row = $adb->fetchByAssoc($countResult);
                $count = $row['cnt'];
                
                echo "<tr>";
                echo "<td><strong>$table</strong></td>";
                echo "<td class='success'>✅ Exists</td>";
                echo "<td>" . number_format($count) . "</td>";
                echo "<td>$desc</td>";
                echo "</tr>";
            } else {
                echo "<tr>";
                echo "<td><strong>$table</strong></td>";
                echo "<td class='error'>❌ Missing</td>";
                echo "<td>-</td>";
                echo "<td class='error'>$desc - CRITICAL!</td>";
                echo "</tr>";
            }
        } catch (Exception $e) {
            echo "<tr>";
            echo "<td><strong>$table</strong></td>";
            echo "<td class='error'>❌ Error</td>";
            echo "<td>-</td>";
            echo "<td class='error'>" . htmlspecialchars($e->getMessage()) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    // 4. Check PHP errors
    echo "<hr><h3>4. PHP Configuration</h3>";
    echo "<p>PHP Version: " . PHP_VERSION . "</p>";
    echo "<p>Error Reporting: " . (ini_get('error_reporting') ? 'Enabled' : 'Disabled') . "</p>";
    echo "<p>Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "</p>";
    echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>";
    echo "<p>Max Execution Time: " . ini_get('max_execution_time') . "</p>";
    
    // 5. Check file permissions
    echo "<hr><h3>5. File Permissions</h3>";
    $importantFiles = array(
        'config.php',
        'config.inc.php',
        'index.php',
        'include/database/PearDatabase.php'
    );
    
    foreach ($importantFiles as $file) {
        if (file_exists($file)) {
            $perms = substr(sprintf('%o', fileperms($file)), -4);
            $readable = is_readable($file) ? '✅' : '❌';
            echo "<p>$readable $file (perms: $perms)</p>";
        } else {
            echo "<p class='error'>❌ $file NOT FOUND</p>";
        }
    }
    
    // 6. Check for common issues
    echo "<hr><h3>6. Common Issues Check</h3>";
    
    // Check if organizationdetails table is empty
    try {
        $orgCheck = $adb->query("SELECT COUNT(*) as cnt FROM vtiger_organizationdetails");
        $orgRow = $adb->fetchByAssoc($orgCheck);
        if ($orgRow['cnt'] == 0) {
            echo "<p class='warning'>⚠️ vtiger_organizationdetails is empty - may cause installation check issues</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Cannot check vtiger_organizationdetails: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Check if users table has admin user
    try {
        $userCheck = $adb->query("SELECT COUNT(*) as cnt FROM vtiger_users WHERE id = 1");
        $userRow = $adb->fetchByAssoc($userCheck);
        if ($userRow['cnt'] == 0) {
            echo "<p class='error'>❌ No admin user (ID=1) found in vtiger_users</p>";
        } else {
            echo "<p class='success'>✅ Admin user exists</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Cannot check users: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // 7. Test index.php
    echo "<hr><h3>7. Test index.php</h3>";
    if (file_exists('index.php')) {
        echo "<p class='success'>✅ index.php exists</p>";
        echo "<p><a href='index.php' target='_blank'>Try accessing index.php</a></p>";
    } else {
        echo "<p class='error'>❌ index.php NOT FOUND</p>";
    }
    
    echo "<hr>";
    echo "<h3>📋 Summary</h3>";
    echo "<p>If you see white screen, check:</p>";
    echo "<ol>";
    echo "<li>Browser console (F12) for JavaScript errors</li>";
    echo "<li>Apache error logs: <code>docker logs vtiger_web</code></li>";
    echo "<li>PHP error logs in container</li>";
    echo "<li>Check if config.inc.php is properly configured</li>";
    echo "<li>Verify database connection settings</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<div style='background:#fee;border:1px solid #f00;padding:10px;'>";
    echo "<h3 class='error'>FATAL ERROR</h3>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "</body></html>";

