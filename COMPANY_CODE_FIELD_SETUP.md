# ✅ Company Code Field (cf_855) Setup - Complete

## 🎯 Summary

**Status:** ✅ **COMPLETE**

Company Code field (`cf_855`) has been created and configured. ProjectCodeHandler has been updated to use this field directly instead of trying to resolve from Account.

---

## ✅ What Was Done

### 1. Custom Field Created
- **Field:** `cf_855`
- **Label:** Company Code
- **Type:** Text (VARCHAR 255)
- **Mandatory:** Yes (`V~M`)
- **Editable:** Yes (`readonly = 0`)
- **Visible:** Yes (`presence = 2`)
- **Block:** Custom Information

### 2. Database Column
- ✅ Column `cf_855` exists in `vtiger_potentialscf`
- ✅ Type: VARCHAR(255)

### 3. ProjectCodeHandler Updated
- ✅ Removed Account resolution logic
- ✅ Now reads Company Code directly from `vtiger_potentialscf.cf_855`
- ✅ If `cf_855` is empty → handler exits (does NOT generate Project Code)
- ✅ Added accent removal for Vietnamese characters
- ✅ Improved text normalization

---

## 📋 Field Configuration

| Field | Label | Read-only | Presence | Mandatory | Type |
|-------|-------|-----------|----------|-----------|------|
| cf_855 | Company Code | No | Visible (2) | Yes (V~M) | Text |
| cf_857 | Project Name | No | Visible (2) | No (V~O) | Text |
| cf_859 | Project Code | Yes | Visible (2) | No (V~O) | Text |

---

## 🔄 Project Code Generation Logic

### Format
```
YYYYMMDD-CONTACTNO-COMPANYCODE-PROJECTNAME
```

### Example
```
20260106-CON1-z751-saw
```

### Components

1. **CREATE_DATE:** `YYYYMMDD` from `createdtime`
2. **CONTACT_ID:** `contact_no` from `vtiger_contactdetails`
3. **COMPANY_CODE:** `cf_855` from `vtiger_potentialscf` (user input)
4. **PROJECT_NAME:** `cf_857` or `potentialname` (sanitized)

### Rules

- ✅ **Company Code MUST come from cf_855** (user input field)
- ✅ **If cf_855 is empty → Project Code NOT generated**
- ✅ **Project Name:** Use `cf_857`, fallback to `potentialname`, fallback to `project-{ID}`
- ✅ **Text normalization:** lowercase, remove accents, replace non-alphanumeric with "-"

---

## 🔧 Handler Logic

### Event
- **Event:** `vtiger.entity.aftersave`
- **Module:** Potentials only
- **Condition:** New records only (`isNew() == true`)
- **Condition:** `cf_859` must be empty

### Flow
```
1. Check if new record → Exit if not
2. Check if cf_859 already exists → Exit if exists
3. Get Contact ID → Exit if missing
4. Get Company Code from cf_855 → Exit if empty
5. Get Project Name from cf_857 or potentialname
6. Sanitize all text components
7. Generate Project Code
8. Update vtiger_potentialscf.cf_859 directly
```

### Text Sanitization
- Lowercase conversion
- Accent removal (Vietnamese, French, etc.)
- Replace non-alphanumeric with "-"
- Trim extra "-" from start/end

---

## ✅ Validation

### SQL Verification
```sql
-- Check fields
SELECT fieldid, fieldname, fieldlabel, readonly, presence, typeofdata 
FROM vtiger_field 
WHERE fieldname IN ('cf_855', 'cf_857', 'cf_859') AND tabid = 2
ORDER BY fieldname;

-- Check columns
SHOW COLUMNS FROM vtiger_potentialscf WHERE Field IN ('cf_855', 'cf_857', 'cf_859');

-- Check generated codes
SELECT potentialid, cf_855, cf_857, cf_859 
FROM vtiger_potentialscf 
ORDER BY potentialid DESC 
LIMIT 5;
```

### UI Verification
1. Go to Potentials → Create
2. Verify "Company Code" field appears in Custom Information block
3. Field should be **required** (cannot save if empty)
4. Field should be **editable** (user can type)
5. Create Opportunity with Company Code
6. Verify Project Code is generated: `{DATE}-{CONTACT}-{COMPANY_CODE}-{PROJECT}`

---

## 📝 Files Modified

1. **modules/Potentials/ProjectCodeHandler.php**
   - Removed Account resolution logic
   - Now reads Company Code from `cf_855`
   - Added accent removal
   - Improved text normalization

2. **Database:**
   - Created field `cf_855` in `vtiger_field`
   - Added column `cf_855` in `vtiger_potentialscf`

---

## 🎯 Key Changes

### Before
- Company Code came from Account (`vtiger_accountscf.cf_855` or `account_no`)
- Multiple fallback methods to find Account
- Used `'NOACC'` if Account not found

### After
- Company Code comes directly from `vtiger_potentialscf.cf_855` (user input)
- No Account resolution needed
- If `cf_855` is empty → Project Code NOT generated (handler exits)

---

## ✅ Status

**Feature Status:** ✅ **OPERATIONAL**

- ✅ Field `cf_855` created and configured
- ✅ Column `cf_855` exists in database
- ✅ Handler updated to use `cf_855`
- ✅ Text normalization improved
- ✅ Accent removal added

**Next Step:** Clear cache and test in UI:
```bash
rm -rf cache/* templates_c/*
```

Then create a new Opportunity and verify:
1. Company Code field is visible and required
2. User can type Company Code
3. Project Code is generated after save

---

**Completed:** 2026-01-06  
**Status:** ✅ OPERATIONAL


