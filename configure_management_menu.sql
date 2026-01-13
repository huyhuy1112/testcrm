-- ==========================================
-- Configure PROJECTS App Menu (displayed as "Management")
-- ==========================================
-- 
-- Assigns modules to PROJECTS app menu (which displays as "Management"):
-- - Calendar (display: Schedule)
-- - Reports (display: Report)
-- - Documents (display: Document)
-- - Users (display: Team)
--
-- NOTE: PROJECTS app is already renamed to "Management" in UI via LBL_PROJECT
-- This script configures which modules appear under it.
--
-- This script is IDEMPOTENT - safe to re-run
-- ==========================================

-- Step 1: Get module tabids (for reference)
-- Calendar: tabid 9
-- Reports: tabid 1
-- Documents: tabid 8
-- Users: tabid 4

-- Step 2: Remove modules from previous apps (if they exist elsewhere)
-- Note: Keep PROJECTS app mappings, remove from all other apps
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE t.name IN ('Calendar', 'Reports', 'Documents', 'Users')
  AND a.appname != 'PROJECTS';

-- Step 3: Get max sequence for PROJECTS app
SET @max_seq = COALESCE((SELECT MAX(sequence) FROM vtiger_app2tab WHERE appname = 'PROJECTS'), 0);
SET @next_seq = @max_seq + 1;

-- Step 4: Insert/Update mappings to PROJECTS app
-- Note: vtiger_app2tab has no PRIMARY KEY, so we use DELETE + INSERT pattern

-- Calendar -> Schedule (sequence 1)
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE t.name = 'Calendar' AND a.appname = 'PROJECTS';
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, @next_seq, 1
FROM vtiger_tab WHERE name = 'Calendar' LIMIT 1;
SET @next_seq = @next_seq + 1;

-- Reports -> Report (sequence 2)
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE t.name = 'Reports' AND a.appname = 'PROJECTS';
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, @next_seq, 1
FROM vtiger_tab WHERE name = 'Reports' LIMIT 1;
SET @next_seq = @next_seq + 1;

-- Documents -> Document (sequence 3)
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE t.name = 'Documents' AND a.appname = 'PROJECTS';
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, @next_seq, 1
FROM vtiger_tab WHERE name = 'Documents' LIMIT 1;
SET @next_seq = @next_seq + 1;

-- Users -> Team (sequence 4)
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE t.name = 'Users' AND a.appname = 'PROJECTS';
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECTS', tabid, @next_seq, 1
FROM vtiger_tab WHERE name = 'Users' LIMIT 1;

-- Step 5: Update display labels
UPDATE vtiger_tab SET tablabel = 'Schedule' WHERE name = 'Calendar' AND tablabel != 'Schedule';
UPDATE vtiger_tab SET tablabel = 'Report' WHERE name = 'Reports' AND tablabel != 'Report';
UPDATE vtiger_tab SET tablabel = 'Document' WHERE name = 'Documents' AND tablabel != 'Document';
UPDATE vtiger_tab SET tablabel = 'Team' WHERE name = 'Users' AND tablabel != 'Team';

-- Step 6: Verification query
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

