-- Script to make Opportunity Name (potentialname) field optional
-- Run this in MySQL to update the field metadata

-- Step 1: Find the fieldid for potentialname in Potentials module
-- SELECT fieldid, fieldname, typeofdata FROM vtiger_field 
-- WHERE fieldname = 'potentialname' AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Potentials');

-- Step 2: Update typeofdata from V~M to V~O (mandatory to optional)
-- Replace {FIELDID} with the actual fieldid from Step 1

UPDATE vtiger_field 
SET typeofdata = REPLACE(typeofdata, '~M', '~O')
WHERE fieldname = 'potentialname' 
AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Potentials')
AND typeofdata LIKE '%~M%';

-- Verify the change
SELECT fieldid, fieldname, typeofdata, presence 
FROM vtiger_field 
WHERE fieldname = 'potentialname' 
AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Potentials');


