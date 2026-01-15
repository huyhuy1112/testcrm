<?php
/**
 * Fix Git Conflict on Hosting
 * Script này sẽ giúp xử lý conflict khi pull code trên hosting
 * 
 * Chạy trên hosting: php fix_git_conflict_on_hosting.php
 */

echo "🔧 Fix Git Conflict on Hosting\n";
echo "==============================\n\n";

$config_file = 'config.inc.php';
$backup_file = 'config.inc.php.backup.' . date('YmdHis');

// 1. Backup current config.inc.php
if (file_exists($config_file)) {
    copy($config_file, $backup_file);
    echo "✅ Backed up current config.inc.php to: $backup_file\n";
} else {
    echo "⚠️ config.inc.php not found\n";
}

// 2. Stash local changes
echo "\n📦 Stashing local changes...\n";
$output = [];
$return_var = 0;
exec("git stash 2>&1", $output, $return_var);

if ($return_var === 0) {
    echo "✅ Local changes stashed\n";
    if (!empty($output)) {
        echo implode("\n", $output) . "\n";
    }
} else {
    echo "⚠️ Stash failed or no changes to stash\n";
    echo implode("\n", $output) . "\n";
}

// 3. Pull latest code
echo "\n⬇️ Pulling latest code from GitHub...\n";
$output = [];
$return_var = 0;
exec("git pull origin main 2>&1", $output, $return_var);

if ($return_var === 0) {
    echo "✅ Pull successful\n";
    if (!empty($output)) {
        echo implode("\n", $output) . "\n";
    }
} else {
    echo "❌ Pull failed\n";
    echo implode("\n", $output) . "\n";
    echo "\n⚠️ Please check the error above\n";
    exit(1);
}

// 4. Restore production config if needed
if (file_exists($backup_file)) {
    echo "\n🔄 Checking if production config needs to be restored...\n";
    
    // Read backup to get production database config
    $backup_content = file_get_contents($backup_file);
    
    // Check if backup has production database config
    if (preg_match("/db_username.*=.*['\"](nhtdbus8_[^'\"]+)['\"]/", $backup_content, $matches)) {
        $prod_db_user = $matches[1];
        echo "📝 Found production database user: $prod_db_user\n";
        
        // Read current config
        $current_content = file_get_contents($config_file);
        
        // Check if current config has production settings
        if (strpos($current_content, $prod_db_user) === false) {
            echo "⚠️ Production config not found in pulled file\n";
            echo "💡 You may need to manually update config.inc.php with production settings\n";
            echo "📁 Backup saved at: $backup_file\n";
        } else {
            echo "✅ Production config already in pulled file\n";
        }
    }
}

echo "\n✅ Process complete!\n";
echo "📁 Backup file: $backup_file\n";
echo "\n💡 Next steps:\n";
echo "1. Check if config.inc.php has correct production database settings\n";
echo "2. If not, copy database settings from backup file\n";
echo "3. Test your application\n";
