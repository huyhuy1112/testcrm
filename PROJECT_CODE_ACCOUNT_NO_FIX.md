# ✅ Project Code - Account Number Fix

## 🐛 Bug Report

**Issue:** Project Code was using internal Organization ID (`accountid`) instead of Organization Number (`account_no`).

**Wrong Output:**
```
20260108-ORG403-abc-chế lá cà
```

**Expected Output:**
```
20260108-ACC403-abc-chế lá cà
```

---

## 🔍 Root Cause

The code was:
1. ✅ Correctly querying `vtiger_account.account_no`
2. ❌ **WRONG**: Extracting only digits from `account_no` using `preg_replace('/[^0-9]/', '', $accountNo)`
3. ❌ **WRONG**: Prefixing with "ORG" and using those digits: `ORG{$accountNumber}{$indexInYear}`
4. ❌ **WRONG**: Fallback to `accountid` if no digits found

**Example:**
- `account_no` = "ACC1"
- Extracted digits = "1"
- Generated = "ORG101" ❌
- **Should be** = "ACC101" ✅

---

## ✅ Solution

### Changes Made:

1. **Removed digit extraction**: No longer uses `preg_replace('/[^0-9]/', '', $accountNo)`
2. **Removed ORG prefix**: No longer hardcodes "ORG" prefix
3. **Use account_no directly**: Uses `account_no` value as-is from database
4. **Removed accountid fallback**: Never uses `accountid` in Project Code

### Code Changes:

**Before (WRONG):**
```php
// Extract numeric part from account_no
$accountNumber = preg_replace('/[^0-9]/', '', $accountNo); // Extract only digits
if (empty($accountNumber)) {
    $accountNumber = $accountId; // Fallback to account ID if no number found
}
$organizationWithIndex = "ORG{$accountNumber}{$indexInYear}";
```

**After (CORRECT):**
```php
// CRITICAL: Use account_no directly - DO NOT extract digits, DO NOT prefix ORG, DO NOT use accountid
// Format: {ACCOUNT_NO}{INDEX_IN_YEAR} (e.g., "ACC101" if account_no is "ACC1" and index is "01")
$organizationWithIndex = "{$accountNo}{$indexInYear}";
```

---

## 📋 Final Format

**Format:** `YYYYMMDD-{ACCOUNT_NO}{INDEX_IN_YEAR}-{COMPANY_CODE}-{PROJECT_NAME}`

**Components:**
1. **YYYYMMDD**: Creation date (e.g., `20260108`)
2. **{ACCOUNT_NO}{INDEX_IN_YEAR}**: Organization number + sequential index
   - `ACCOUNT_NO`: Direct value from `vtiger_account.account_no` (e.g., `ACC1`, `ACC403`)
   - `INDEX_IN_YEAR`: Sequential number per Organization per year (e.g., `01`, `02`, `03...`)
3. **{COMPANY_CODE}**: From Organization's `cf_855` (slugified)
4. **{PROJECT_NAME}**: From `cf_857` or `potentialname` (keeps original UTF-8 Vietnamese)

**Examples:**
- `20260108-ACC403-abc-chế lá cà` (Organization ACC403, first project in 2026)
- `20260108-ACC40302-abc-chế lá cà` (Organization ACC403, second project in 2026)
- `20260108-ACC101-xyz-Bít cồ bôn` (Organization ACC1, first project in 2026)

---

## 🛡️ Business Rules

### ✅ Correct Implementation:
1. **Use `account_no` directly**: No transformation, no extraction, no prefix
2. **Never use `accountid`**: Internal ID is never exposed in Project Code
3. **Preserve Organization Number format**: If `account_no` is "ACC1", use "ACC1" (not "1" or "ORG1")
4. **Append index**: Index is appended directly to `account_no` (e.g., "ACC1" + "01" = "ACC101")

### ❌ What Was Wrong:
- Extracting digits from `account_no` (lost prefix like "ACC")
- Hardcoding "ORG" prefix
- Using `accountid` as fallback
- Not preserving original Organization Number format

---

## 🔧 Technical Details

### Database Query:
```php
// Get account_no from Organization (Account) - this is the Organization Number
$accountResult = $adb->pquery(
    "SELECT account_no FROM vtiger_account WHERE accountid = ?",
    array($accountId)
);

$accountNo = $adb->query_result($accountResult, 0, 'account_no');
```

### Index Calculation (Unchanged):
```php
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

$indexNumber = intval($existingCount) + 1;
$indexInYear = str_pad($indexNumber, 2, '0', STR_PAD_LEFT); // 01, 02, 03...
```

### Final Formatting:
```php
// Use account_no directly - DO NOT extract digits, DO NOT prefix ORG, DO NOT use accountid
$organizationWithIndex = "{$accountNo}{$indexInYear}";
```

---

## ✅ Validation

### Test Cases:
1. **Organization with account_no = "ACC1"**:
   - First Opportunity in 2026 → `ACC101` ✅
   - Second Opportunity in 2026 → `ACC102` ✅

2. **Organization with account_no = "ACC403"**:
   - First Opportunity in 2026 → `ACC40301` ✅
   - Second Opportunity in 2026 → `ACC40302` ✅

3. **Organization with account_no = "ORG5"**:
   - First Opportunity in 2026 → `ORG501` ✅ (preserves "ORG" prefix from account_no)

4. **No account_no**:
   - Handler exits with error log ✅

---

## 📝 Files Modified

- `modules/Potentials/ProjectCodeHandler.php`
  - Removed digit extraction logic
  - Removed "ORG" prefix hardcoding
  - Removed `accountid` fallback
  - Use `account_no` directly

---

## 🎯 Summary

**Status:** ✅ **FIXED**

The Project Code now correctly uses `vtiger_account.account_no` directly without any transformation, prefix, or fallback to `accountid`. The Organization Number format is preserved exactly as stored in the database.

**Key Changes:**
- ✅ Use `account_no` directly (no extraction)
- ✅ No hardcoded "ORG" prefix
- ✅ Never use `accountid` in Project Code
- ✅ Preserve original Organization Number format

