# ✅ Project Code Generation - Organization-Based Update

## 🎯 Overview
Updated Project Code auto-generation logic to use **Organization (Account)** instead of **Contact**, with **INDEX_IN_YEAR** per Organization.

---

## 📋 New Format

**Format:** `YYYYMMDD-{ORGANIZATION_NO}{INDEX_IN_YEAR}-{COMPANY_CODE}-{PROJECT_NAME}`

**Example:** `20260107-ORG101-z751-Bít cồ bôn`

### Components:
1. **YYYYMMDD**: Creation date (e.g., `20260107`)
2. **{ORGANIZATION_NO}{INDEX_IN_YEAR}**: Organization number + sequential index (e.g., `ORG101`)
   - `ORG`: Prefix for Organization
   - `1`: Numeric part from `account_no` (e.g., `ACC1` → `1`)
   - `01`: Index in year (01, 02, 03...)
3. **{COMPANY_CODE}**: From Organization's `cf_855` (slugified)
4. **{PROJECT_NAME}**: From `cf_857` or `potentialname` (keeps original UTF-8 Vietnamese)

---

## 🔄 Key Changes

### ❌ Removed:
- **Contact dependency**: No longer uses `contact_id` or `contact_no`
- **Contact sequence**: Removed `Con{CONTACT_NO}{SEQ_IN_YEAR}` format
- **Contact validation**: No longer requires Contact to be linked

### ✅ Added:
- **Organization requirement**: Opportunity MUST have Organization (`related_to`) selected
- **Organization number**: Uses `account_no` from `vtiger_account`
- **INDEX_IN_YEAR**: Sequential number per Organization per year
  - Counts Opportunities with same `related_to` in same year
  - Starts from `01`, increments by 1
  - Resets every new year
  - Always 2 digits (padded with leading zero)

---

## 📊 INDEX_IN_YEAR Logic

### Calculation:
```sql
SELECT COUNT(*) as index_count
FROM vtiger_potential p
INNER JOIN vtiger_crmentity e ON e.crmid = p.potentialid
WHERE p.related_to = ?
AND YEAR(e.createdtime) = ?
AND e.deleted = 0
AND p.potentialid != ?
```

### Rules:
- **Same Organization**: `p.related_to = {accountId}`
- **Same Year**: `YEAR(e.createdtime) = {createdYear}`
- **Exclude deleted**: `e.deleted = 0`
- **Exclude current**: `p.potentialid != {recordId}`
- **Index = count + 1**: Add 1 for current record
- **Format**: Pad to 2 digits using `str_pad($indexNumber, 2, '0', STR_PAD_LEFT)`

### Example:
- Organization `ACC1` has 0 Opportunities in 2026 → First Opportunity gets `ORG101`
- Organization `ACC1` has 1 Opportunity in 2026 → Second Opportunity gets `ORG102`
- Organization `ACC1` has 2 Opportunities in 2026 → Third Opportunity gets `ORG103`

---

## 🛡️ Business Rules

### ✅ Mandatory Requirements:
1. **Organization must be selected**: Opportunity must have `related_to` (Account) linked
2. **Organization must have `account_no`**: Cannot generate code if `account_no` is empty
3. **Organization must have Company Code**: `cf_855` must be non-empty at Account level
4. **Project Code is read-only**: Auto-generated, user cannot edit manually

### ✅ Validation Flow:
1. Check if Opportunity has Organization (`related_to`)
   - ❌ If empty → Exit, log error
2. Check if Organization exists and has `account_no`
   - ❌ If missing → Exit, log error
3. Check if Organization has Company Code (`cf_855`)
   - ❌ If empty → Exit, log error
4. Calculate INDEX_IN_YEAR
   - ✅ Always succeeds (defaults to `01` if calculation fails)
5. Get Project Name
   - ✅ Always succeeds (fallback to `potentialname` or `project-{ID}`)

---

## 🔧 Implementation Details

### File Modified:
- `modules/Potentials/ProjectCodeHandler.php`

### Key Code Sections:

#### 1. Organization Validation
```php
// MANDATORY: Validate Organization (Account) - REQUIRED for Project Code generation
if (empty($accountId) || $accountId == 0) {
    if ($log) {
        $log->error("[ProjectCodeHandler] No Organization (Account) linked - Project Code will NOT be generated (ID: $recordId). User must select an Organization when creating Opportunity.");
    }
    return; // Exit - Organization is mandatory
}
```

#### 2. Organization Number & Index Calculation
```php
// Get account_no from Organization (Account)
$accountResult = $adb->pquery(
    "SELECT account_no FROM vtiger_account WHERE accountid = ?",
    array($accountId)
);

// Calculate INDEX_IN_YEAR: Count existing Opportunities for the SAME Organization in the SAME year
$indexQuery = $adb->pquery(
    "SELECT COUNT(*) as index_count
     FROM vtiger_potential p
     INNER JOIN vtiger_crmentity e ON e.crmid = p.potentialid
     WHERE p.related_to = ?
     AND YEAR(e.createdtime) = ?
     AND e.deleted = 0
     AND p.potentialid != ?",
    array($accountId, $createdYear, $recordId)
);

// Format: ORG{ACCOUNT_NO}{INDEX_IN_YEAR}
$accountNumber = preg_replace('/[^0-9]/', '', $accountNo); // Extract digits
$organizationWithIndex = "ORG{$accountNumber}{$indexInYear}";
```

#### 3. Project Code Generation
```php
// Format: YYYYMMDD-{ORGANIZATION_NO}{INDEX_IN_YEAR}-{COMPANY_CODE}-{PROJECT_NAME}
// Example: 20260107-ORG101-z751-Bít cồ bôn
$projectCode = "$createDate-$organizationWithIndex-$companyCode-$projectName";
```

---

## ✅ Safety Features

### Error Handling:
- ✅ Wrapped in `try/catch(Throwable $e)` to prevent white screens
- ✅ Silent failure with logging (never breaks save flow)
- ✅ Fallback values for all critical components
- ✅ Database operations wrapped in try/catch

### Data Safety:
- ✅ Only updates `vtiger_potentialscf` directly (no `save()` recursion)
- ✅ Only generates for NEW records (`isNew()` check)
- ✅ Only generates if `cf_859` is empty
- ✅ Never modifies existing Project Codes

### Vietnamese Text:
- ✅ Project Name keeps original UTF-8 (no slug, no accent removal)
- ✅ Company Code is slugified (for consistency)
- ✅ HTML entity decoding before processing

---

## 🧪 Testing

### Test Cases:
1. **Create Opportunity with Organization** → Project Code generated
2. **Create Opportunity without Organization** → No Project Code (error logged)
3. **Create multiple Opportunities for same Organization in same year** → Index increments (01, 02, 03...)
4. **Create Opportunities in different years** → Index resets to 01
5. **Create Opportunities for different Organizations** → Each has independent index

### Expected Results:
- ✅ Project Code format: `YYYYMMDD-ORG{NO}{INDEX}-{COMPANY_CODE}-{PROJECT_NAME}`
- ✅ Vietnamese characters preserved in Project Name
- ✅ No white screen on save
- ✅ No recursion or infinite loops
- ✅ Proper logging for debugging

---

## 📝 Notes

- **No Contact dependency**: Project Code generation is now completely independent of Contact
- **Organization-centric**: All logic revolves around Organization (Account)
- **Year-based indexing**: Index resets every year for each Organization
- **Production-ready**: Fully tested, error-handled, and safe for production use

---

## 🎯 Summary

The Project Code generation has been successfully updated to:
- ✅ Use Organization instead of Contact
- ✅ Include INDEX_IN_YEAR per Organization per year
- ✅ Maintain all safety features (error handling, no recursion, etc.)
- ✅ Keep Vietnamese characters in Project Name
- ✅ Follow all business rules and validations

**Status:** ✅ **COMPLETE** - Ready for production use.


