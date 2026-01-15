-- ==========================================
-- CODE ĐƠN GIẢN - COPY VÀ PASTE VÀO phpMyAdmin
-- ==========================================
-- 
-- BƯỚC 1: Copy toàn bộ code bên dưới
-- BƯỚC 2: Vào cPanel → phpMyAdmin → Chọn database → Tab SQL
-- BƯỚC 3: Paste code vào và click "Go"
-- ==========================================

-- Xóa các mapping cũ (nếu có)
DELETE FROM vtiger_app2tab 
WHERE tabid IN (
    SELECT tabid FROM vtiger_tab 
    WHERE name IN ('Calendar', 'Reports', 'Documents', 'Users')
)
AND appname != 'PROJECTS';

-- Thêm Calendar vào PROJECTS (hiển thị là Schedule)
DELETE FROM vtiger_app2tab 
WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Calendar' LIMIT 1)
AND appname = 'PROJECTS';

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, 1, 1
FROM vtiger_tab WHERE name = 'Calendar' LIMIT 1;

-- Thêm Reports vào PROJECTS (hiển thị là Report)
DELETE FROM vtiger_app2tab 
WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Reports' LIMIT 1)
AND appname = 'PROJECTS';

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, 2, 1
FROM vtiger_tab WHERE name = 'Reports' LIMIT 1;

-- Thêm Documents vào PROJECTS (hiển thị là Document)
DELETE FROM vtiger_app2tab 
WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Documents' LIMIT 1)
AND appname = 'PROJECTS';

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, 3, 1
FROM vtiger_tab WHERE name = 'Documents' LIMIT 1;

-- Thêm Users vào PROJECTS (hiển thị là Team)
DELETE FROM vtiger_app2tab 
WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Users' LIMIT 1)
AND appname = 'PROJECTS';

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, 4, 1
FROM vtiger_tab WHERE name = 'Users' LIMIT 1;

-- Cập nhật tên hiển thị
UPDATE vtiger_tab SET tablabel = 'Schedule' WHERE name = 'Calendar';
UPDATE vtiger_tab SET tablabel = 'Report' WHERE name = 'Reports';
UPDATE vtiger_tab SET tablabel = 'Document' WHERE name = 'Documents';
UPDATE vtiger_tab SET tablabel = 'Team' WHERE name = 'Users';

-- Xong! Kiểm tra kết quả:
SELECT 
    t.name AS 'Module',
    t.tablabel AS 'Tên hiển thị',
    a.sequence AS 'Thứ tự'
FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE a.appname = 'PROJECTS'
  AND t.name IN ('Calendar', 'Reports', 'Documents', 'Users')
ORDER BY a.sequence;

