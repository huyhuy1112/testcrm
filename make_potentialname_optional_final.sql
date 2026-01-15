-- ============================================
-- Make Opportunity Name (potentialname) Optional
-- ============================================
-- This script updates the field metadata to make potentialname non-mandatory
-- Run: docker exec -i vtiger_db mysql -uroot -p132120 TDB1 < make_potentialname_optional_final.sql

-- Step 1: Update typeofdata from V~M to V~O (mandatory to optional)
UPDATE vtiger_field 
SET typeofdata = REPLACE(typeofdata, '~M', '~O')
WHERE fieldname = 'potentialname' 
AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Potentials')
AND typeofdata LIKE '%~M%';

-- Step 2: Verify the change
SELECT 
    fieldid, 
    fieldname, 
    typeofdata,
    CASE 
        WHEN typeofdata LIKE '%~O%' THEN 'OPTIONAL ✓'
        WHEN typeofdata LIKE '%~M%' THEN 'MANDATORY ✗'
        ELSE 'UNKNOWN'
    END as status
FROM vtiger_field 
WHERE fieldname = 'potentialname' 
AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Potentials');

-- Expected result: typeofdata should contain ~O (optional)


