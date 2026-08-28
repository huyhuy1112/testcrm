<?php
/**
 * Sửa menu MANAGEMENT không hiện menu con
 *
 * 1. Chuẩn hóa appname trong vtiger_app2tab: đổi "Management", "Managment" ... thành "MANAGEMENT"
 * 2. Nếu MANAGEMENT vẫn không có module nào → thêm các module mặc định (Calendar, Teams, Reports, Documents, Project, ProjectTask, Contacts)
 *
 * Chạy: php fix_management_menu_display.php
 * Sau đó: Clear cache (Settings → Configuration → Clear Cache), logout rồi login lại.
 */

require_once 'config.inc.php';
require_once 'include/database/PearDatabase.php';
require_once 'include/utils/utils.php';

$adb = PearDatabase::getInstance();
$appName = 'MANAGEMENT';

echo "==========================================\n";
echo "Fix MANAGEMENT menu - hiện đủ menu con\n";
echo "==========================================\n\n";

// Bước 1: Chuẩn hóa appname thành MANAGEMENT (nếu DB lưu "Management", "Managment" ...)
echo "Bước 1: Chuẩn hóa appname trong vtiger_app2tab...\n";
$adb->pquery(
	"UPDATE vtiger_app2tab SET appname = ? WHERE UPPER(TRIM(appname)) = ? AND TRIM(appname) != ?",
	array($appName, $appName, $appName)
);
echo "  ✓ Đã chạy chuẩn hóa appname (nếu có bản ghi 'Management' → 'MANAGEMENT').\n";

// Bước 2: Kiểm tra MANAGEMENT có ít nhất 1 module không
$countResult = $adb->pquery("SELECT COUNT(*) AS cnt FROM vtiger_app2tab WHERE appname = ? AND visible = 1", array($appName));
$count = (int) $adb->query_result($countResult, 0, 'cnt');

if ($count > 0) {
	echo "\nBước 2: MANAGEMENT đã có $count module.\n";
	echo "Bước 2b: Đảm bảo Home (Main Page) có trong MANAGEMENT...\n";
	$homeResult = $adb->pquery("SELECT tabid FROM vtiger_tab WHERE name = ?", array('Home'));
	if ($adb->num_rows($homeResult) > 0) {
		$homeTabId = $adb->query_result($homeResult, 0, 'tabid');
		$homeCheck = $adb->pquery("SELECT 1 FROM vtiger_app2tab WHERE appname = ? AND tabid = ?", array($appName, $homeTabId));
		if ($adb->num_rows($homeCheck) == 0) {
			$seqResult = $adb->pquery("SELECT MAX(sequence) AS max_seq FROM vtiger_app2tab WHERE appname = ?", array($appName));
			$nextSeq = ($adb->num_rows($seqResult) > 0 && $adb->query_result($seqResult, 0, 'max_seq') !== null)
				? (int) $adb->query_result($seqResult, 0, 'max_seq') + 1 : 1;
			$adb->pquery("INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible) VALUES (?, ?, ?, ?)",
				array($appName, $homeTabId, $nextSeq, 1));
			echo "  ✓ Đã thêm Home (Main Page) vào MANAGEMENT (sequence=$nextSeq).\n";
		} else {
			echo "  - Home đã có trong MANAGEMENT.\n";
		}
	} else {
		echo "  - Module Home không tồn tại trong vtiger_tab, bỏ qua.\n";
	}
	echo "\nNếu menu con vẫn không hiện: Clear cache (Settings → Configuration → Clear Cache), logout và login lại.\n";
	exit(0);
}

echo "\nBước 2: MANAGEMENT chưa có module nào. Thêm các module mặc định...\n";

$defaultModules = array('Home', 'Calendar', 'Teams', 'Reports', 'Documents', 'Project', 'ProjectTask', 'Contacts');
$moduleTabIds = array();

foreach ($defaultModules as $moduleName) {
	$result = $adb->pquery("SELECT tabid, tablabel FROM vtiger_tab WHERE name = ?", array($moduleName));
	if ($adb->num_rows($result) > 0) {
		$tabid = $adb->query_result($result, 0, 'tabid');
		$moduleTabIds[$moduleName] = $tabid;
		echo "  ✓ $moduleName (tabid=$tabid)\n";
	} else {
		echo "  - $moduleName: không tồn tại, bỏ qua.\n";
	}
}

if (empty($moduleTabIds)) {
	echo "\nKhông có module nào để thêm. Chạy configure_management_menu_backup.php để cấu hình MANAGEMENT.\n";
	exit(1);
}

$seqResult = $adb->pquery("SELECT MAX(sequence) AS max_seq FROM vtiger_app2tab WHERE appname = ?", array($appName));
$nextSeq = ($adb->num_rows($seqResult) > 0 && $adb->query_result($seqResult, 0, 'max_seq') !== null)
	? (int) $adb->query_result($seqResult, 0, 'max_seq') + 1 : 1;

foreach ($moduleTabIds as $moduleName => $tabid) {
	$check = $adb->pquery("SELECT 1 FROM vtiger_app2tab WHERE appname = ? AND tabid = ?", array($appName, $tabid));
	if ($adb->num_rows($check) == 0) {
		$adb->pquery(
			"INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible) VALUES (?, ?, ?, ?)",
			array($appName, $tabid, $nextSeq, 1)
		);
		echo "  ✓ Đã thêm $moduleName vào MANAGEMENT (sequence=$nextSeq)\n";
	} else {
		$adb->pquery(
			"UPDATE vtiger_app2tab SET sequence = ?, visible = 1 WHERE appname = ? AND tabid = ?",
			array($nextSeq, $appName, $tabid)
		);
		echo "  ✓ Đã cập nhật $moduleName (sequence=$nextSeq)\n";
	}
	$nextSeq++;
}

echo "\n==========================================\n";
echo "Xong. Tiếp theo:\n";
echo "  1. Clear cache: Settings → Configuration → Clear Cache\n";
echo "  2. Logout và login lại\n";
echo "  3. Mở menu hamburger, hover vào MANAGEMENT → menu con sẽ hiện.\n";
echo "==========================================\n";
