-- ==========================================
-- Fix PROJECTS → PROJECT Menu Migration
-- ==========================================
-- 
-- CRITICAL FIX:
-- - Move all modules from invalid "PROJECTS" app to correct "PROJECT" app
-- - Remove all PROJECTS app records
-- - Update display labels
-- - Ensure correct sequence
--
-- This script is IDEMPOTENT - safe to re-run
-- ==========================================

-- Step 1: Move all modules from PROJECTS to PROJECT
-- Note: vtiger_app2tab has no PRIMARY KEY, so we use DELETE + INSERT pattern

-- First, delete any existing PROJECT mappings for target modules (to avoid duplicates)
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE a.appname = 'PROJECT' 
  AND t.name IN ('Calendar', 'Reports', 'Documents', 'Users');

-- Then insert all PROJECTS mappings into PROJECT
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECT', tabid, sequence, visible
FROM vtiger_app2tab
WHERE appname = 'PROJECTS';

-- Step 2: Update sequence for target modules in PROJECT app
-- Calendar -> Schedule (sequence 1)
UPDATE vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
SET a.sequence = 1, a.visible = 1
WHERE a.appname = 'PROJECT' AND t.name = 'Calendar';

-- Reports -> Report (sequence 2)
UPDATE vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
SET a.sequence = 2, a.visible = 1
WHERE a.appname = 'PROJECT' AND t.name = 'Reports';

-- Documents -> Document (sequence 3)
UPDATE vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
SET a.sequence = 3, a.visible = 1
WHERE a.appname = 'PROJECT' AND t.name = 'Documents';

-- Users -> Team (sequence 4)
UPDATE vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
SET a.sequence = 4, a.visible = 1
WHERE a.appname = 'PROJECT' AND t.name = 'Users';

-- Step 3: Ensure modules exist in PROJECT (insert if missing)
-- Note: Step 1 already moved all PROJECTS records, Step 2 updated sequences
-- This step only inserts if modules were never in PROJECTS (edge case)

-- Calendar
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECT', tabid, 1, 1 
FROM vtiger_tab 
WHERE name = 'Calendar'
  AND tabid NOT IN (SELECT tabid FROM vtiger_app2tab WHERE appname = 'PROJECT');

-- Reports
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECT', tabid, 2, 1 
FROM vtiger_tab 
WHERE name = 'Reports'
  AND tabid NOT IN (SELECT tabid FROM vtiger_app2tab WHERE appname = 'PROJECT');

-- Documents
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECT', tabid, 3, 1 
FROM vtiger_tab 
WHERE name = 'Documents'
  AND tabid NOT IN (SELECT tabid FROM vtiger_app2tab WHERE appname = 'PROJECT');

-- Users
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'PROJECT', tabid, 4, 1 
FROM vtiger_tab 
WHERE name = 'Users'
  AND tabid NOT IN (SELECT tabid FROM vtiger_app2tab WHERE appname = 'PROJECT');

-- Step 4: Remove all PROJECTS app records
DELETE FROM vtiger_app2tab WHERE appname = 'PROJECTS';

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
WHERE a.appname = 'PROJECT'
  AND t.name IN ('Calendar', 'Reports', 'Documents', 'Users')
ORDER BY a.sequence;

-- Step 7: Verify PROJECTS is completely removed
SELECT COUNT(*) as remaining_projects_records
FROM vtiger_app2tab 
WHERE appname = 'PROJECTS';

-- Expected: 0
