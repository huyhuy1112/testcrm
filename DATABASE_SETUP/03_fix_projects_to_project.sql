-- ==========================================
-- Fix PROJECTS → PROJECT Migration
-- ==========================================
-- 
-- File: 03_fix_projects_to_project.sql
-- Mục đích: Di chuyển modules từ app "PROJECTS" sang app "PROJECT"
-- 
-- Sử dụng khi:
-- - Bạn muốn dùng app "PROJECT" thay vì "PROJECTS"
-- - Hoặc khi có lỗi với app "PROJECTS"
--
-- LƯU Ý: 
-- - File này sẽ XÓA app "PROJECTS" và chuyển sang "PROJECT"
-- - Chạy file này SAU KHI đã chạy 01_setup_management_menu.sql
-- ==========================================

-- Bước 1: Xóa các mapping cũ trong PROJECT (để tránh duplicate)
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE a.appname = 'PROJECT' 
  AND t.name IN ('Calendar', 'Reports', 'Documents', 'Users');

-- Bước 2: Copy tất cả từ PROJECTS sang PROJECT
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECT', tabid, sequence, visible
FROM vtiger_app2tab
WHERE appname = 'PROJECTS';

-- Bước 3: Cập nhật sequence cho các module chính
UPDATE vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
SET a.sequence = 1, a.visible = 1
WHERE a.appname = 'PROJECT' AND t.name = 'Calendar';

UPDATE vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
SET a.sequence = 2, a.visible = 1
WHERE a.appname = 'PROJECT' AND t.name = 'Reports';

UPDATE vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
SET a.sequence = 3, a.visible = 1
WHERE a.appname = 'PROJECT' AND t.name = 'Documents';

UPDATE vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
SET a.sequence = 4, a.visible = 1
WHERE a.appname = 'PROJECT' AND t.name = 'Users';

-- Bước 4: Đảm bảo các module tồn tại trong PROJECT (nếu chưa có)
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECT', tabid, 1, 1 
FROM vtiger_tab 
WHERE name = 'Calendar'
  AND tabid NOT IN (SELECT tabid FROM vtiger_app2tab WHERE appname = 'PROJECT' AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Calendar'));

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECT', tabid, 2, 1 
FROM vtiger_tab 
WHERE name = 'Reports'
  AND tabid NOT IN (SELECT tabid FROM vtiger_app2tab WHERE appname = 'PROJECT' AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Reports'));

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECT', tabid, 3, 1 
FROM vtiger_tab 
WHERE name = 'Documents'
  AND tabid NOT IN (SELECT tabid FROM vtiger_app2tab WHERE appname = 'PROJECT' AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Documents'));

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECT', tabid, 4, 1 
FROM vtiger_tab 
WHERE name = 'Users'
  AND tabid NOT IN (SELECT tabid FROM vtiger_app2tab WHERE appname = 'PROJECT' AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Users'));

-- Bước 5: Xóa tất cả records của PROJECTS app
DELETE FROM vtiger_app2tab WHERE appname = 'PROJECTS';

-- Bước 6: Cập nhật display labels
UPDATE vtiger_tab SET tablabel = 'Schedule' WHERE name = 'Calendar' AND tablabel != 'Schedule';
UPDATE vtiger_tab SET tablabel = 'Report' WHERE name = 'Reports' AND tablabel != 'Report';
UPDATE vtiger_tab SET tablabel = 'Document' WHERE name = 'Documents' AND tablabel != 'Document';
UPDATE vtiger_tab SET tablabel = 'Team' WHERE name = 'Users' AND tablabel != 'Team';

-- Bước 7: Kiểm tra kết quả
SELECT 
    t.name AS module_name,
    t.tablabel AS display_label,
    a.appname,
    a.sequence,
    a.visible
FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE a.appname = 'PROJECT'
  AND t.name IN ('Calendar', 'Reports', 'Documents', 'Users')
ORDER BY a.sequence;

-- Bước 8: Xác nhận PROJECTS đã bị xóa
SELECT COUNT(*) as remaining_projects_records
FROM vtiger_app2tab 
WHERE appname = 'PROJECTS';
-- Kết quả mong đợi: 0

