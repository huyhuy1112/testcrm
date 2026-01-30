-- Validation SQL Scripts for Company Code Field Setup
-- Run these queries to verify the feature is properly configured

-- 1. Verify Custom Fields
SELECT 
    fieldid, 
    fieldname, 
    fieldlabel, 
    readonly, 
    presence, 
    typeofdata,
    sequence
FROM vtiger_field 
WHERE fieldname IN ('cf_855', 'cf_857', 'cf_859') 
AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Potentials')
ORDER BY sequence;
-- Expected: 3 rows
-- cf_855: Company Code, readonly=0, presence=2, typeofdata='V~M' (mandatory)
-- cf_857: Project Name, readonly=0, presence=2, typeofdata='V~O' (optional)
-- cf_859: Project Code, readonly=1, presence=2, typeofdata='V~O' (optional)

-- 2. Verify Database Columns
SHOW COLUMNS FROM vtiger_potentialscf WHERE Field IN ('cf_855', 'cf_857', 'cf_859');
-- Expected: 3 columns, all VARCHAR(255)

-- 3. Verify Event Handler
SELECT 
    eventhandler_id,
    event_name,
    handler_path,
    handler_class,
    is_active
FROM vtiger_eventhandlers 
WHERE handler_class = 'ProjectCodeHandler';
-- Expected: 1 row
-- event_name: 'vtiger.entity.aftersave'
-- handler_path: 'modules/Potentials/ProjectCodeHandler.php'
-- handler_class: 'ProjectCodeHandler'
-- is_active: 1

-- 4. Check Recent Opportunities with Company Code
SELECT 
    p.potentialid,
    p.potentialname,
    p.contact_id,
    cd.contact_no,
    pcf.cf_855 as company_code,
    pcf.cf_857 as project_name,
    pcf.cf_859 as project_code,
    ce.createdtime
FROM vtiger_potential p
LEFT JOIN vtiger_contactdetails cd ON cd.contactid = p.contact_id
LEFT JOIN vtiger_potentialscf pcf ON pcf.potentialid = p.potentialid
LEFT JOIN vtiger_crmentity ce ON ce.crmid = p.potentialid
WHERE pcf.cf_855 IS NOT NULL AND pcf.cf_855 != ''
ORDER BY p.potentialid DESC
LIMIT 10;

-- 5. Check Opportunities with Generated Project Codes
SELECT 
    p.potentialid,
    p.potentialname,
    pcf.cf_855 as company_code,
    pcf.cf_857 as project_name,
    pcf.cf_859 as project_code
FROM vtiger_potential p
LEFT JOIN vtiger_potentialscf pcf ON pcf.potentialid = p.potentialid
WHERE pcf.cf_859 IS NOT NULL AND pcf.cf_859 != ''
ORDER BY p.potentialid DESC
LIMIT 10;

-- 6. Find Opportunities with Company Code but No Project Code
SELECT 
    p.potentialid,
    p.potentialname,
    pcf.cf_855 as company_code,
    pcf.cf_859 as project_code,
    ce.createdtime
FROM vtiger_potential p
LEFT JOIN vtiger_potentialscf pcf ON pcf.potentialid = p.potentialid
LEFT JOIN vtiger_crmentity ce ON ce.crmid = p.potentialid
WHERE (pcf.cf_855 IS NOT NULL AND pcf.cf_855 != '')
  AND (pcf.cf_859 IS NULL OR pcf.cf_859 = '')
  AND ce.createdtime >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY p.potentialid DESC
LIMIT 10;


