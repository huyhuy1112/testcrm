-- Validation SQL Scripts for Project Code Feature
-- Run these queries to verify the feature is properly configured

-- 1. Verify Potentials Module
SELECT tabid, name FROM vtiger_tab WHERE name = 'Potentials';
-- Expected: 1 row with tabid = 2

-- 2. Verify Custom Fields Exist
SELECT 
    fieldid, 
    fieldname, 
    fieldlabel, 
    tabid, 
    presence, 
    readonly, 
    typeofdata,
    uitype
FROM vtiger_field 
WHERE fieldname IN ('cf_857', 'cf_859') 
AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Potentials')
ORDER BY fieldname;
-- Expected: 2 rows
-- cf_857: Project Name, readonly=0, presence=2
-- cf_859: Project Code, readonly=1, presence=2

-- 3. Verify Database Columns Exist
SHOW COLUMNS FROM vtiger_potentialscf LIKE 'cf_857';
SHOW COLUMNS FROM vtiger_potentialscf LIKE 'cf_859';
-- Expected: Both columns exist with VARCHAR(255)

-- 4. Verify Event Handler Registration
SELECT 
    eventhandler_id,
    event_name,
    handler_path,
    handler_class,
    is_active,
    dependent_on
FROM vtiger_eventhandlers 
WHERE handler_class = 'ProjectCodeHandler';
-- Expected: 1 row
-- event_name: 'vtiger.entity.aftersave'
-- handler_path: 'modules/Potentials/ProjectCodeHandler.php'
-- handler_class: 'ProjectCodeHandler'
-- is_active: 1

-- 5. Check for Duplicate Handlers
SELECT COUNT(*) as handler_count
FROM vtiger_eventhandlers 
WHERE handler_class = 'ProjectCodeHandler';
-- Expected: 1 (no duplicates)

-- 6. Verify Handler File Exists (check via filesystem, not SQL)
-- File should exist: modules/Potentials/ProjectCodeHandler.php

-- 7. Check Recent Project Codes Generated
SELECT 
    p.potentialid,
    p.potentialname,
    p.contact_id,
    cd.contact_no,
    a.accountname,
    acf.cf_855 as company_code,
    pcf.cf_857 as project_name,
    pcf.cf_859 as project_code,
    ce.createdtime
FROM vtiger_potential p
LEFT JOIN vtiger_contactdetails cd ON cd.contactid = p.contact_id
LEFT JOIN vtiger_account a ON a.accountid = p.related_to
LEFT JOIN vtiger_accountscf acf ON acf.accountid = p.related_to
LEFT JOIN vtiger_potentialscf pcf ON pcf.potentialid = p.potentialid
LEFT JOIN vtiger_crmentity ce ON ce.crmid = p.potentialid
WHERE pcf.cf_859 IS NOT NULL AND pcf.cf_859 != ''
ORDER BY p.potentialid DESC
LIMIT 10;

-- 8. Find Opportunities Without Project Code (should be old records or records without required data)
SELECT 
    p.potentialid,
    p.potentialname,
    p.contact_id,
    p.related_to,
    pcf.cf_857 as project_name,
    pcf.cf_859 as project_code,
    ce.createdtime
FROM vtiger_potential p
LEFT JOIN vtiger_potentialscf pcf ON pcf.potentialid = p.potentialid
LEFT JOIN vtiger_crmentity ce ON ce.crmid = p.potentialid
WHERE (pcf.cf_859 IS NULL OR pcf.cf_859 = '')
AND p.contact_id IS NOT NULL
AND p.contact_id != 0
AND p.related_to IS NOT NULL
AND p.related_to != 0
ORDER BY p.potentialid DESC
LIMIT 10;


