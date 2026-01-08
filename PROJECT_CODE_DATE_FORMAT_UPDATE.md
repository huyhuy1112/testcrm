# ✅ Project Code Date Format Update

## 🎯 Objective

Change Project Code date format from **4-digit year** (`YYYYMMDD`) to **2-digit year** (`YYMMDD`).

---

## 📋 Format Change

### Before:
- **Format:** `YYYYMMDD-{ACCOUNT_NO}{INDEX_IN_YEAR}-{COMPANY_CODE}-{PROJECT_NAME}`
- **Example:** `20260108-ACC406-abc-cầu lông`

### After:
- **Format:** `YYMMDD-{ACCOUNT_NO}{INDEX_IN_YEAR}-{COMPANY_CODE}-{PROJECT_NAME}`
- **Example:** `260108-ACC406-abc-cầu lông`

---

## 🔧 Implementation

### Code Changes

**File:** `modules/Potentials/ProjectCodeHandler.php`

**Location:** Date generation logic (lines 142-157)

**Changes:**
1. Changed `DateTime::format('Ymd')` → `DateTime::format('ymd')`
2. Changed `date('Ymd')` → `date('ymd')` (in 2 fallback locations)
3. Updated comments to reflect new format

### Code Snippet

**Before:**
```php
// 1. CREATE_DATE: Format createdtime as YYYYMMDD
$createDate = '';
try {
    if (!empty($createdTime)) {
        $dateObj = new DateTime($createdTime);
        $createDate = $dateObj->format('Ymd');
    } else {
        $createDate = date('Ymd');
    }
} catch (Throwable $e) {
    // Fallback to current date if DateTime fails
    $createDate = date('Ymd');
    // ...
}
```

**After:**
```php
// 1. CREATE_DATE: Format createdtime as YYMMDD (2-digit year)
$createDate = '';
try {
    if (!empty($createdTime)) {
        $dateObj = new DateTime($createdTime);
        $createDate = $dateObj->format('ymd'); // y = 2-digit year (e.g., 26 for 2026)
    } else {
        $createDate = date('ymd'); // y = 2-digit year
    }
} catch (Throwable $e) {
    // Fallback to current date if DateTime fails
    $createDate = date('ymd'); // y = 2-digit year
    // ...
}
```

---

## ✅ What Changed

### Date Format:
- **Before:** `'Ymd'` → `20260108` (4-digit year)
- **After:** `'ymd'` → `260108` (2-digit year)

### PHP Date Format Codes:
- `Y` = 4-digit year (e.g., 2026)
- `y` = 2-digit year (e.g., 26)

---

## 🛡️ What Did NOT Change

### ✅ Preserved:
1. **Month and Day:** Remain unchanged (still 2 digits each)
2. **INDEX_IN_YEAR calculation:** Still uses 4-digit year (`'Y'`) for SQL `YEAR()` function
3. **Organization Number logic:** Unchanged
4. **Company Code logic:** Unchanged
5. **Project Name logic:** Unchanged
6. **Database structure:** No changes
7. **Event handler flow:** No changes

### Year Calculation for INDEX_IN_YEAR:
```php
// This remains unchanged - uses 4-digit year for SQL YEAR() function
$createdYear = $dateObj->format('Y'); // Still 'Y' (4-digit) for SQL compatibility
```

---

## 📊 Impact

### ✅ Affects:
1. **New Project Codes:** Will use 2-digit year format
2. **Opportunity Name:** Auto-synced from Project Code (also uses 2-digit year)

### ❌ Does NOT Affect:
1. **Existing records:** Old Project Codes remain unchanged
2. **INDEX_IN_YEAR calculation:** Still uses 4-digit year for SQL queries
3. **Database schema:** No changes
4. **Other modules:** No impact

---

## 🧪 Testing

### Test Cases:

1. **Create new Opportunity:**
   - Date: 2026-01-08
   - Expected Project Code: `260108-ACC406-abc-cầu lông` ✅

2. **Create Opportunity on different date:**
   - Date: 2026-12-25
   - Expected Project Code: `261225-ACC406-abc-cầu lông` ✅

3. **Verify Opportunity Name sync:**
   - Opportunity Name should match Project Code exactly ✅

4. **Verify old records unchanged:**
   - Existing records with `20260108-...` format remain unchanged ✅

---

## 📝 Summary

**Status:** ✅ **COMPLETE**

### Changes Made:
- ✅ Updated date format from `'Ymd'` to `'ymd'` (3 locations)
- ✅ Updated comments to reflect new format
- ✅ Updated example in comments

### No Side Effects:
- ✅ No database changes
- ✅ No recursion risk
- ✅ No white screen risk
- ✅ INDEX_IN_YEAR calculation unchanged (still uses 4-digit year for SQL)

### Result:
- ✅ New Project Codes use 2-digit year: `260108-ACC406-abc-cầu lông`
- ✅ Opportunity Name auto-syncs with new format
- ✅ Old records remain unchanged
- ✅ Save works normally, no blank screen

---

## 🎯 Expected Behavior

**Before:**
- Project Code: `20260108-ACC406-abc-cầu lông`
- Opportunity Name: `20260108-ACC406-abc-cầu lông`

**After:**
- Project Code: `260108-ACC406-abc-cầu lông`
- Opportunity Name: `260108-ACC406-abc-cầu lông`

**Old Records:**
- Remain unchanged: `20260108-ACC406-abc-cầu lông` (still valid)

