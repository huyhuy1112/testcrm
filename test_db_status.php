<?php
/**
 * Vtiger CRM Database Status Diagnostic Script
 * 
 * This script verifies database connection and checks if Vtiger is initialized.
 * READ-ONLY - Does not modify any data.
 * 
 * Access via: http://your-domain/test_db_status.php
 */

// Suppress errors for clean output
error_reporting(E_ALL);
ini_set('display_errors', 'On');

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Vtiger Database Status</title>\n";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
h2 { color: #666; margin-top: 20px; }
.status { padding: 10px; margin: 10px 0; border-radius: 3px; }
.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
th { background-color: #4CAF50; color: white; }
pre { background: #f4f4f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style></head><body>\n";
echo "<div class='container'>\n";
echo "<h1>DATABASE STATUS REPORT</h1>\n";

// Step 1: Read config.inc.php
echo "<h2>Step 1: Reading Configuration</h2>\n";

if (!file_exists('config.inc.php')) {
    echo "<div class='status error'>ERROR: config.inc.php not found!</div>\n";
    echo "</div></body></html>\n";
    exit;
}

require_once 'config.inc.php';

// Extract database configuration
$db_host = isset($dbconfig['db_server']) ? $dbconfig['db_server'] : 'localhost';
$db_port = isset($dbconfig['db_port']) ? $dbconfig['db_port'] : '';
$db_name = isset($dbconfig['db_name']) ? $dbconfig['db_name'] : '';
$db_user = isset($dbconfig['db_username']) ? $dbconfig['db_username'] : '';
$db_pass = isset($dbconfig['db_password']) ? $dbconfig['db_password'] : '';

// Build hostname with port if specified
$db_hostname = $db_host . $db_port;

echo "<div class='status info'>\n";
echo "<strong>Configuration Loaded:</strong><br>\n";
echo "DB Host: " . htmlspecialchars($db_hostname) . "<br>\n";
echo "DB Name: " . htmlspecialchars($db_name ?: 'NOT SET') . "<br>\n";
echo "DB User: " . htmlspecialchars($db_user ?: 'NOT SET') . "<br>\n";
echo "DB Password: " . ($db_pass ? '***SET***' : 'NOT SET') . "<br>\n";
echo "</div>\n";

// Check if credentials are set
if (empty($db_name) || empty($db_user) || empty($db_pass)) {
    echo "<div class='status error'>\n";
    echo "<strong>ERROR:</strong> Database credentials are not fully configured in config.inc.php<br>\n";
    echo "Please update config.inc.php with database credentials before testing.\n";
    echo "</div>\n";
    echo "</div></body></html>\n";
    exit;
}

// Step 2: Test database connection
echo "<h2>Step 2: Testing Database Connection</h2>\n";

$connection = null;
$connection_error = null;

try {
    // Try mysqli connection
    if (function_exists('mysqli_connect')) {
        $connection = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
        
        if (!$connection) {
            $connection_error = mysqli_connect_error();
        } else {
            // Set charset
            mysqli_set_charset($connection, 'utf8');
        }
    } else {
        throw new Exception("mysqli extension not available");
    }
} catch (Exception $e) {
    $connection_error = $e->getMessage();
}

if ($connection && !mysqli_connect_errno()) {
    echo "<div class='status success'>\n";
    echo "<strong>✓ Connection: SUCCESS</strong><br>\n";
    echo "Connected to database server successfully.\n";
    echo "</div>\n";
} else {
    echo "<div class='status error'>\n";
    echo "<strong>✗ Connection: FAILED</strong><br>\n";
    echo "Error: " . htmlspecialchars($connection_error ?: 'Unknown error') . "<br>\n";
    echo "Please verify database credentials in config.inc.php\n";
    echo "</div>\n";
    echo "</div></body></html>\n";
    exit;
}

// Step 3: Verify database and tables
echo "<h2>Step 3: Verifying Database Structure</h2>\n";

// Get current database name
$current_db = mysqli_fetch_row(mysqli_query($connection, "SELECT DATABASE()"))[0];
echo "<div class='status info'>\n";
echo "<strong>Current Database:</strong> " . htmlspecialchars($current_db) . "<br>\n";
echo "</div>\n";

// Get list of tables
$tables_result = mysqli_query($connection, "SHOW TABLES");
if (!$tables_result) {
    echo "<div class='status error'>\n";
    echo "ERROR: Cannot query tables. " . mysqli_error($connection) . "\n";
    echo "</div>\n";
    mysqli_close($connection);
    echo "</div></body></html>\n";
    exit;
}

$tables = array();
while ($row = mysqli_fetch_row($tables_result)) {
    $tables[] = $row[0];
}

$table_count = count($tables);
echo "<div class='status info'>\n";
echo "<strong>Total Tables Found:</strong> " . $table_count . "<br>\n";
echo "</div>\n";

// Step 4: Check for core Vtiger tables
echo "<h2>Step 4: Checking Core Vtiger Tables</h2>\n";

$core_tables = array(
    'vtiger_users' => 'User management',
    'vtiger_tab' => 'Module registry',
    'vtiger_crmentity' => 'Entity tracking',
    'vtiger_field' => 'Field definitions',
    'vtiger_potential' => 'Opportunities',
    'vtiger_account' => 'Accounts',
    'vtiger_contactdetails' => 'Contacts',
    'vtiger_eventhandlers' => 'Event handlers',
    'vtiger_notifications' => 'Notifications',
    'vtiger_app2tab' => 'Menu structure'
);

$found_tables = array();
$missing_tables = array();

foreach ($core_tables as $table => $description) {
    if (in_array($table, $tables)) {
        $found_tables[$table] = $description;
    } else {
        $missing_tables[$table] = $description;
    }
}

echo "<table>\n";
echo "<tr><th>Table Name</th><th>Status</th><th>Description</th></tr>\n";

foreach ($found_tables as $table => $desc) {
    echo "<tr><td><strong>" . htmlspecialchars($table) . "</strong></td>";
    echo "<td><span style='color: green;'>✓ EXISTS</span></td>";
    echo "<td>" . htmlspecialchars($desc) . "</td></tr>\n";
}

foreach ($missing_tables as $table => $desc) {
    echo "<tr><td><strong>" . htmlspecialchars($table) . "</strong></td>";
    echo "<td><span style='color: red;'>✗ MISSING</span></td>";
    echo "<td>" . htmlspecialchars($desc) . "</td></tr>\n";
}

echo "</table>\n";

// Check if Vtiger is initialized
$vtiger_initialized = false;
$vtiger_tables_count = 0;

foreach ($tables as $table) {
    if (strpos($table, 'vtiger_') === 0) {
        $vtiger_tables_count++;
    }
}

// Vtiger is considered initialized if we have core tables
if (count($found_tables) >= 5 && $vtiger_tables_count > 50) {
    $vtiger_initialized = true;
}

// Step 5: Get sample data counts
echo "<h2>Step 5: Sample Data Verification</h2>\n";

$sample_data = array();

if (in_array('vtiger_users', $tables)) {
    $result = mysqli_query($connection, "SELECT COUNT(*) as count FROM vtiger_users WHERE deleted = 0");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $sample_data['Users'] = $row['count'];
    }
}

if (in_array('vtiger_tab', $tables)) {
    $result = mysqli_query($connection, "SELECT COUNT(*) as count FROM vtiger_tab WHERE presence = 0");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $sample_data['Active Modules'] = $row['count'];
    }
}

if (in_array('vtiger_potential', $tables)) {
    $result = mysqli_query($connection, "SELECT COUNT(*) as count FROM vtiger_potential p INNER JOIN vtiger_crmentity e ON e.crmid = p.potentialid WHERE e.deleted = 0");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $sample_data['Opportunities'] = $row['count'];
    }
}

if (in_array('vtiger_account', $tables)) {
    $result = mysqli_query($connection, "SELECT COUNT(*) as count FROM vtiger_account a INNER JOIN vtiger_crmentity e ON e.crmid = a.accountid WHERE e.deleted = 0");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $sample_data['Accounts'] = $row['count'];
    }
}

if (!empty($sample_data)) {
    echo "<table>\n";
    echo "<tr><th>Entity Type</th><th>Count</th></tr>\n";
    foreach ($sample_data as $type => $count) {
        echo "<tr><td>" . htmlspecialchars($type) . "</td><td><strong>" . $count . "</strong></td></tr>\n";
    }
    echo "</table>\n";
}

// Final Summary
echo "<h2>Final Summary</h2>\n";
echo "<div class='status " . ($vtiger_initialized ? 'success' : 'error') . "'>\n";
echo "<strong>=========================</strong><br>\n";
echo "<strong>DATABASE STATUS REPORT</strong><br>\n";
echo "<strong>=========================</strong><br><br>\n";
echo "<strong>DB Host:</strong> " . htmlspecialchars($db_hostname) . "<br>\n";
echo "<strong>DB Name:</strong> " . htmlspecialchars($db_name) . "<br>\n";
echo "<strong>DB User:</strong> " . htmlspecialchars($db_user) . "<br>\n";
echo "<strong>Connection:</strong> <span style='color: green;'>SUCCESS</span><br><br>\n";
echo "<strong>Tables Found:</strong> " . $table_count . "<br>\n";
echo "<strong>Vtiger Tables:</strong> " . $vtiger_tables_count . "<br>\n";
echo "<strong>Core Tables Detected:</strong> " . count($found_tables) . " / " . count($core_tables) . "<br>\n";
echo "<strong>Vtiger Initialized:</strong> " . ($vtiger_initialized ? '<span style="color: green;">YES</span>' : '<span style="color: red;">NO</span>') . "<br>\n";
echo "<strong>System Status:</strong> " . ($vtiger_initialized ? '<span style="color: green;">READY</span>' : '<span style="color: orange;">NOT READY</span>') . "<br>\n";
echo "</div>\n";

// Additional information
if ($vtiger_initialized) {
    echo "<div class='status success'>\n";
    echo "<strong>✓ System is ready for use.</strong><br>\n";
    echo "Database is properly configured and Vtiger tables are present.\n";
    echo "</div>\n";
} else {
    echo "<div class='status error'>\n";
    echo "<strong>⚠ System may not be fully initialized.</strong><br>\n";
    if ($table_count == 0) {
        echo "No tables found. Database may be empty. You may need to run the Vtiger installer or restore a database backup.\n";
    } elseif ($vtiger_tables_count < 50) {
        echo "Only " . $vtiger_tables_count . " Vtiger tables found. Expected 100+ tables. Database may be incomplete.\n";
    } else {
        echo "Some core tables are missing. Please verify database backup was restored correctly.\n";
    }
    echo "</div>\n";
}

// Close connection
mysqli_close($connection);

echo "</div></body></html>\n";
?>

