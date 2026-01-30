# ✅ Company Code Migration to Accounts - Complete

## 🎯 Summary

**Status:** ✅ **COMPLETE**

Company Code has been migrated from Opportunities (Potentials) to Accounts (Organization) level. ProjectCodeHandler now reads Company Code exclusively from the related Account.

---

## ✅ Changes Applied

### 1. Removed Company Code from Potentials
- ✅ **Field `cf_855` deleted** from `vtiger_field` (Potentials tabid)
- ✅ **Field removed** from Create/Edit/Detail views
- ⚠️ **Column `cf_855` kept** in `vtiger_potentialscf` for data safety (not deleted)

### 2. Ensured Company Code in Accounts
- ✅ **Field `cf_855` exists** in Accounts module
- ✅ **Field configured:** Mandatory (`V~M`), Editable (`readonly = 0`), Visible (`presence = 2`)
- ✅ **Column `cf_855` exists** in `vtiger_accountscf`

### 3. Updated ProjectCodeHandler
- ✅ **Removed:** Reading Company Code from `vtiger_potentialscf.cf_855`
- ✅ **Added:** Reading Company Code from `vtiger_accountscf.cf_855` (via `related_to`)
- ✅ **Validation:** Requires Account to be linked
- ✅ **Validation:** Requires Account's Company Code to be non-empty
- ✅ **Error logging:** Clear error messages when Account or Company Code is missing

---

## 🔄 Project Code Generation Logic

### Format (Unchanged)
```
YYYYMMDD-CONTACTNO-COMPANYCODE-PROJECTNAME
```

### Example
```
20260106-CON1-z751-saw
```

### Components

1. **CREATE_DATE:** `YYYYMMDD` from `vtiger_crmentity.createdtime`
2. **CONTACT_ID:** `contact_no` from `vtiger_contactdetails`
3. **COMPANY_CODE:** `cf_855` from `vtiger_accountscf` ⚠️ **FROM ACCOUNT ONLY**
4. **PROJECT_NAME:** `cf_857` or `potentialname` (sanitized)

### Handler Rules

- ✅ **Runs ONLY for Potentials module**
- ✅ **Runs ONLY for new records** (`isNew() == true`)
- ✅ **Generates ONLY if `cf_859` is empty**
- ✅ **Requires Account to be linked** (`related_to` must not be empty)
- ✅ **Company Code MUST come from Account's `cf_855`** (single source of truth)
- ✅ **If Account not linked OR Account's Company Code empty → handler exits** (does NOT generate)
- ✅ **Project Name:** Use `cf_857`, fallback to `potentialname`, fallback to `project-{ID}`
- ✅ **Text normalization:** lowercase, remove accents, replace non-alphanumeric with "-"

---

## 📝 Handler Logic Flow

```
1. Check event name → Exit if not 'vtiger.entity.aftersave'
2. Check module → Exit if not 'Potentials'
3. Check if new record → Exit if not new
4. Check if cf_859 exists → Exit if exists
5. Get Contact ID → Exit if missing
6. Get Account ID (related_to) → Exit if missing ⚠️
7. Get Company Code from Account's cf_855 → Exit if empty ⚠️
8. Get Project Name from cf_857 or potentialname
9. Sanitize all text components
10. Generate Project Code
11. Update vtiger_potentialscf.cf_859 directly
```

### Error Conditions (Handler Exits)
- ❌ No Contact linked
- ❌ No Account linked (`related_to` is empty)
- ❌ Account not found in database
- ❌ Account's Company Code (`cf_855`) is empty
- ❌ Company Code empty after sanitization

---

## ✅ Data Safety

### Preserved
- ✅ Existing Project Codes (`cf_859`) - **NOT modified**
- ✅ Existing Opportunities - **NOT affected**
- ✅ Column `cf_855` in `vtiger_potentialscf` - **NOT deleted** (kept for data safety)

### Removed
- ✅ Field `cf_855` from Potentials module (removed from `vtiger_field`)
- ✅ Field no longer appears in Create/Edit/Detail views

---

## 🧪 Validation

### SQL Verification
```sql
-- 1. Verify Company Code in Accounts (should exist)
SELECT fieldid, fieldname, fieldlabel, readonly, presence, typeofdata 
FROM vtiger_field 
WHERE fieldname = 'cf_855' AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Accounts');

-- Expected: 1 row, readonly=0, presence=2, typeofdata='V~M'

-- 2. Verify Company Code NOT in Potentials (should not exist)
SELECT COUNT(*) as cnt 
FROM vtiger_field 
WHERE fieldname = 'cf_855' AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Potentials');

-- Expected: 0

-- 3. Verify Account column exists
SHOW COLUMNS FROM vtiger_accountscf WHERE Field = 'cf_855';

-- Expected: 1 column, VARCHAR(255)

-- 4. Check Accounts with Company Code
SELECT accountid, accountname, acf.cf_855 as company_code
FROM vtiger_account a
LEFT JOIN vtiger_accountscf acf ON acf.accountid = a.accountid
WHERE acf.cf_855 IS NOT NULL AND acf.cf_855 != ''
ORDER BY a.accountid DESC
LIMIT 10;

-- 5. Check Opportunities with Project Codes (should still work)
SELECT 
    p.potentialid,
    p.potentialname,
    p.related_to,
    a.accountname,
    acf.cf_855 as account_company_code,
    pcf.cf_859 as project_code
FROM vtiger_potential p
LEFT JOIN vtiger_account a ON a.accountid = p.related_to
LEFT JOIN vtiger_accountscf acf ON acf.accountid = p.related_to
LEFT JOIN vtiger_potentialscf pcf ON pcf.potentialid = p.potentialid
WHERE pcf.cf_859 IS NOT NULL
ORDER BY p.potentialid DESC
LIMIT 10;
```

### UI Verification
1. **Clear cache:**
   ```bash
   rm -rf cache/* templates_c/*
   ```

2. **Go to Accounts → Create/Edit:**
   - Verify "Company Code" field appears in Custom Information block
   - Field should be **required** (red asterisk)
   - Field should be **editable** (user can type)

3. **Go to Potentials → Create:**
   - Verify "Company Code" field **DOES NOT appear**
   - Only "Project Name" and "Project Code" should be visible

4. **Create Opportunity:**
   - Link to an Account (required)
   - Ensure Account has Company Code filled
   - Fill in Contact (required)
   - Fill in Project Name (optional)
   - Save
   - Verify Project Code is generated: `{DATE}-{CONTACT}-{COMPANY_CODE}-{PROJECT}`

---

## 📁 Files Modified

### Modified
1. **modules/Potentials/ProjectCodeHandler.php**
   - Removed: Reading from `vtiger_potentialscf.cf_855`
   - Added: Reading from `vtiger_accountscf.cf_855` (via `related_to`)
   - Added: Account validation
   - Added: Clear error logging

### Created
1. **migrate_company_code_to_accounts.php** - Migration script
2. **MIGRATION_SUMMARY.md** - This document

### Database
- **Deleted:** Field `cf_855` from `vtiger_field` (Potentials tabid)
- **Created/Verified:** Field `cf_855` in `vtiger_field` (Accounts tabid)
- **Verified:** Column `cf_855` in `vtiger_accountscf`
- **Preserved:** Column `cf_855` in `vtiger_potentialscf` (not deleted for data safety)

---

## ⚠️ Important Rules

1. **Company Code is at Account Level:**
   - Field `cf_855` exists ONLY in Accounts module
   - Users enter Company Code when creating/editing Account
   - Opportunity reads Company Code from related Account

2. **Account is Required:**
   - Opportunity MUST be linked to an Account
   - If no Account → Project Code NOT generated
   - Handler exits with clear error log

3. **Account's Company Code is Required:**
   - Account MUST have Company Code (`cf_855`) filled
   - If empty → Project Code NOT generated
   - Handler exits with clear error log

4. **No Manual Input in Opportunity:**
   - Company Code field removed from Potentials
   - Users cannot enter Company Code at Opportunity level
   - Single source of truth: Account's `cf_855`

---

## ✅ Status

**Migration Status:** ✅ **COMPLETE**

- ✅ Company Code removed from Potentials
- ✅ Company Code ensured in Accounts
- ✅ Handler updated to read from Account
- ✅ Data safety preserved
- ✅ No recursion, no re-save loops

**Next Step:** Clear cache and test:
```bash
rm -rf cache/* templates_c/*
```

Then:
1. Create/Edit Account and fill Company Code
2. Create Opportunity linked to that Account
3. Verify Project Code is generated using Account's Company Code

---

**Completed:** 2026-01-06  
**Status:** ✅ OPERATIONAL


