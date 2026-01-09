<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "PHP is working!<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";

if (file_exists('config.php')) {
    echo "✅ config.php exists<br>";
    require_once 'config.php';
    echo "✅ config.php loaded<br>";
} else {
    echo "❌ config.php NOT FOUND<br>";
}

if (file_exists('vendor/autoload.php')) {
    echo "✅ vendor/autoload.php exists<br>";
    require_once 'vendor/autoload.php';
    echo "✅ Composer loaded<br>";
} else {
    echo "❌ vendor/autoload.php NOT FOUND<br>";
}

if (file_exists('config.inc.php')) {
    echo "✅ config.inc.php exists<br>";
    $size = filesize('config.inc.php');
    echo "Size: $size bytes<br>";
} else {
    echo "❌ config.inc.php NOT FOUND<br>";
}

echo "<br><strong>If you see this, PHP is working correctly!</strong>";

