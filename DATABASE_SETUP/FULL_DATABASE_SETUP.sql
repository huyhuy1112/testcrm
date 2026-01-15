-- ==========================================
-- Vtiger CRM - Full Database Setup
-- ==========================================
-- 
-- File: FULL_DATABASE_SETUP.sql
-- Mục đích: Setup menu Management sau khi import database Vtiger CRM
-- 
-- HƯỚNG DẪN:
-- 1. Import database Vtiger CRM từ Installation Wizard trước
-- 2. Sau đó chạy file SQL này để setup menu Management
-- ==========================================

-- Bước 1: Xóa các mapping cũ (nếu có)
DELETE FROM vtiger_app2tab 
WHERE tabid IN (
    SELECT tabid FROM vtiger_tab 
    WHERE name IN ('Calendar', 'Reports', 'Documents', 'Users')
)
AND appname != 'PROJECTS';

-- Bước 2: Thêm Calendar vào PROJECTS (hiển thị là Schedule)
DELETE FROM vtiger_app2tab 
WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Calendar' LIMIT 1)
AND appname = 'PROJECTS';

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, 1, 1
FROM vtiger_tab WHERE name = 'Calendar' LIMIT 1;

-- Bước 3: Thêm Reports vào PROJECTS (hiển thị là Report)
DELETE FROM vtiger_app2tab 
WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Reports' LIMIT 1)
AND appname = 'PROJECTS';

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, 2, 1
FROM vtiger_tab WHERE name = 'Reports' LIMIT 1;

-- Bước 4: Thêm Documents vào PROJECTS (hiển thị là Document)
DELETE FROM vtiger_app2tab 
WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Documents' LIMIT 1)
AND appname = 'PROJECTS';

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, 3, 1
FROM vtiger_tab WHERE name = 'Documents' LIMIT 1;

-- Bước 5: Thêm Users vào PROJECTS (hiển thị là Team)
DELETE FROM vtiger_app2tab 
WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Users' LIMIT 1)
AND appname = 'PROJECTS';

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, 4, 1
FROM vtiger_tab WHERE name = 'Users' LIMIT 1;

-- Bước 6: Cập nhật tên hiển thị
UPDATE vtiger_tab SET tablabel = 'Schedule' WHERE name = 'Calendar';
UPDATE vtiger_tab SET tablabel = 'Report' WHERE name = 'Reports';
UPDATE vtiger_tab SET tablabel = 'Document' WHERE name = 'Documents';
UPDATE vtiger_tab SET tablabel = 'Team' WHERE name = 'Users';

-- Xong! Kiểm tra kết quả:
SELECT 
    t.name AS 'Module',
    t.tablabel AS 'Tên hiển thị',
    a.sequence AS 'Thứ tự',
    a.visible AS 'Hiển thị'
FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE a.appname = 'PROJECTS'
  AND t.name IN ('Calendar', 'Reports', 'Documents', 'Users')
ORDER BY a.sequence;

