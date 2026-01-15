-- ==========================================
-- Setup Management Menu (PROJECTS App)
-- ==========================================
-- 
-- File: 01_setup_management_menu.sql
-- Mục đích: Cấu hình menu Management với các module:
--   - Calendar → Schedule
--   - Reports → Report  
--   - Documents → Document
--   - Users → Team
--
-- LƯU Ý: 
-- - Chạy file này SAU KHI đã cài đặt Vtiger CRM (database đã có sẵn)
-- - File này an toàn, có thể chạy nhiều lần (idempotent)
-- ==========================================

-- Bước 1: Xóa các mapping cũ (nếu có) từ các app khác
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE t.name IN ('Calendar', 'Reports', 'Documents', 'Users')
  AND a.appname != 'PROJECTS';

-- Bước 2: Lấy sequence tiếp theo cho PROJECTS app
SET @max_seq = COALESCE((SELECT MAX(sequence) FROM vtiger_app2tab WHERE appname = 'PROJECTS'), 0);
SET @next_seq = @max_seq + 1;

-- Bước 3: Thêm Calendar → Schedule (sequence 1)
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE t.name = 'Calendar' AND a.appname = 'PROJECTS';

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, @next_seq, 1
FROM vtiger_tab WHERE name = 'Calendar' LIMIT 1;
SET @next_seq = @next_seq + 1;

-- Bước 4: Thêm Reports → Report (sequence 2)
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE t.name = 'Reports' AND a.appname = 'PROJECTS';

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, @next_seq, 1
FROM vtiger_tab WHERE name = 'Reports' LIMIT 1;
SET @next_seq = @next_seq + 1;

-- Bước 5: Thêm Documents → Document (sequence 3)
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE t.name = 'Documents' AND a.appname = 'PROJECTS';

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, @next_seq, 1
FROM vtiger_tab WHERE name = 'Documents' LIMIT 1;
SET @next_seq = @next_seq + 1;

-- Bước 6: Thêm Users → Team (sequence 4)
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE t.name = 'Users' AND a.appname = 'PROJECTS';

INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, @next_seq, 1
FROM vtiger_tab WHERE name = 'Users' LIMIT 1;

-- Bước 7: Cập nhật display labels
UPDATE vtiger_tab SET tablabel = 'Schedule' WHERE name = 'Calendar' AND tablabel != 'Schedule';
UPDATE vtiger_tab SET tablabel = 'Report' WHERE name = 'Reports' AND tablabel != 'Report';
UPDATE vtiger_tab SET tablabel = 'Document' WHERE name = 'Documents' AND tablabel != 'Document';
UPDATE vtiger_tab SET tablabel = 'Team' WHERE name = 'Users' AND tablabel != 'Team';

-- Bước 8: Kiểm tra kết quả
SELECT 
    t.name AS module_name,
    t.tablabel AS display_label,
    a.appname,
    a.sequence,
    a.visible
FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE a.appname = 'PROJECTS'
  AND t.name IN ('Calendar', 'Reports', 'Documents', 'Users')
ORDER BY a.sequence;

-- Kết quả mong đợi:
-- Calendar    | Schedule | PROJECTS | 1 | 1
-- Reports     | Report   | PROJECTS | 2 | 1
-- Documents   | Document | PROJECTS | 3 | 1
-- Users       | Team     | PROJECTS | 4 | 1

