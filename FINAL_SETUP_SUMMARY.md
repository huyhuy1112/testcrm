# ✅ Company Code Field Setup - Complete

## 🎯 Summary

**Status:** ✅ **COMPLETE**

Company Code field (`cf_855`) has been created and configured. ProjectCodeHandler has been updated to use this field directly for Project Code generation.

---

## ✅ Configuration

### Custom Fields
| Field | Label | Read-only | Presence | Mandatory | Type |
|-------|-------|-----------|----------|-----------|------|
| **cf_855** | **Company Code** | **No** | **Visible (2)** | **Yes (V~M)** | **Text** |
| cf_857 | Project Name | No | Visible (2) | No (V~O) | Text |
| cf_859 | Project Code | Yes | Visible (2) | No (V~O) | Text |

### Database Columns
- ✅ `cf_855` VARCHAR(255) in `vtiger_potentialscf`
- ✅ `cf_857` VARCHAR(255) in `vtiger_potentialscf`
- ✅ `cf_859` VARCHAR(255) in `vtiger_potentialscf`

---

## 🔄 Project Code Generation

### Format
```
YYYYMMDD-CONTACTNO-COMPANYCODE-PROJECTNAME
```

### Example
```
20260106-CON1-z751-saw
```

### Logic Flow
1. ✅ Check if new record → Exit if not
2. ✅ Check if `cf_859` already exists → Exit if exists
3. ✅ Get Contact ID → Exit if missing
4. ✅ **Get Company Code from `cf_855` → Exit if empty** ⚠️
5. ✅ Get Project Name from `cf_857` or `potentialname`
6. ✅ Sanitize all components
7. ✅ Generate Project Code
8. ✅ Update `vtiger_potentialscf.cf_859` directly

### Text Sanitization
- Lowercase conversion
- Accent removal (Vietnamese, French, etc.)
- Replace non-alphanumeric with "-"
- Trim extra "-" from start/end

---

## ✅ Key Changes

### Handler Updated
- ✅ **Removed Account resolution logic** (no longer needed)
- ✅ **Reads Company Code from `cf_855`** (user input field)
- ✅ **If `cf_855` is empty → handler exits** (does NOT generate code)
- ✅ Added accent removal for Vietnamese characters
- ✅ Improved text normalization

### Field Created
- ✅ Field `cf_855` created in `vtiger_field`
- ✅ Column `cf_855` added to `vtiger_potentialscf`
- ✅ Field configured as mandatory and editable

---

## 🧪 Validation

### SQL Verification
```sql
-- Check fields
SELECT fieldid, fieldname, fieldlabel, readonly, presence, typeofdata 
FROM vtiger_field 
WHERE fieldname IN ('cf_855', 'cf_857', 'cf_859') AND tabid = 2
ORDER BY fieldname;

-- Expected:
-- cf_855: Company Code, readonly=0, presence=2, typeofdata='V~M'
-- cf_857: Project Name, readonly=0, presence=2, typeofdata='V~O'
-- cf_859: Project Code, readonly=1, presence=2, typeofdata='V~O'

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
5. Create Opportunity with:
   - Contact (required)
   - Company Code (required) - type manually
   - Project Name (optional)
6. Save
7. Verify Project Code is generated: `{DATE}-{CONTACT}-{COMPANY_CODE}-{PROJECT}`

---

## 📝 Files Modified

1. **modules/Potentials/ProjectCodeHandler.php**
   - Removed Account resolution logic
   - Reads Company Code from `cf_855`
   - Added accent removal
   - Improved text normalization

2. **Database:**
   - Created field `cf_855` in `vtiger_field` (fieldid: 855)
   - Added column `cf_855` in `vtiger_potentialscf`

---

## ⚠️ Important Rules

1. **Company Code is MANDATORY:**
   - If `cf_855` is empty → Project Code NOT generated
   - Handler exits gracefully

2. **User Input:**
   - Company Code comes from user input (not from Account)
   - User must type Company Code when creating Opportunity

3. **Text Normalization:**
   - All text components are sanitized
   - Accents removed
   - Non-alphanumeric replaced with "-"

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

Then create a new Opportunity and verify Company Code field appears and Project Code is generated.

---

**Completed:** 2026-01-06  
**Status:** ✅ OPERATIONAL

