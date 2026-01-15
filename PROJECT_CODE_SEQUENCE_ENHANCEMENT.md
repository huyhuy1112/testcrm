# Project Code Sequence Enhancement - Per Contact Per Year

## 🎯 Summary

**Status:** ✅ **COMPLETE**

Enhanced Project Code generation to include sequence number per contact per year.

---

## 📋 Requirement

**Goal:** Add "Project Sequence Per Contact Per Year" into Project Code.

**Final Format:**
```
YYYYMMDD-Con{CONTACT_NO}{SEQ_IN_YEAR}-{COMPANY_CODE}-{PROJECT_NAME}
```

**Example:**
```
20260107-Con101-z751-Bít cồ bôn
```

**Definition:**
- `SEQ_IN_YEAR`: Sequence number of Opportunities for the SAME Contact, created within the SAME year
- Starts from 01 and increments by 1
- Resets every new year
- Always 2 digits (01, 02, 03...)

---

## ✅ Implementation

### 1. Sequence Calculation Logic

**Location:** `modules/Potentials/ProjectCodeHandler.php` (lines ~189-241)

**Steps:**
1. Get year from `createdtime` (from `vtiger_crmentity`)
2. Count existing opportunities for the same contact in the same year
3. Exclude current record and deleted records
4. Add 1 to get sequence number
5. Pad to 2 digits using `str_pad()`

**SQL Query:**
```sql
SELECT COUNT(*) as seq_count
FROM vtiger_potential p
INNER JOIN vtiger_crmentity e ON e.crmid = p.potentialid
WHERE p.contact_id = ?
AND YEAR(e.createdtime) = ?
AND e.deleted = 0
AND p.potentialid != ?
```

### 2. Contact Number Formatting

**Logic:**
- Extract numeric part from `contact_no` (handles formats like "CON1", "Con1", "1", etc.)
- Format as `Con{number}{sequence}` (e.g., "Con101")
- Example: Contact "CON1" + Sequence "01" = "Con101"

**Code:**
```php
// Extract numeric part from contact_no
$contactNumber = preg_replace('/[^0-9]/', '', $contactNo);
if (empty($contactNumber)) {
    $contactNumber = $contactId; // Fallback to contact ID
}
$contactWithSequence = "Con{$contactNumber}{$sequenceInYear}";
```

### 3. Project Code Format

**Before:**
```
YYYYMMDD-{CONTACT_NO}-{COMPANY_CODE}-{PROJECT_NAME}
Example: 20260107-CON1-z751-Bít cồ bôn
```

**After:**
```
YYYYMMDD-Con{CONTACT_NO}{SEQ_IN_YEAR}-{COMPANY_CODE}-{PROJECT_NAME}
Example: 20260107-Con101-z751-Bít cồ bôn
```

---

## 📝 Code Changes

### File Modified
- `modules/Potentials/ProjectCodeHandler.php`

### Changes Made

1. **Added Sequence Calculation** (lines ~189-229):
   - Get year from `createdtime`
   - Count existing opportunities for same contact in same year
   - Calculate sequence number (count + 1)
   - Pad to 2 digits

2. **Added Contact Formatting** (lines ~231-241):
   - Extract numeric part from `contact_no`
   - Format as `Con{number}{sequence}`

3. **Updated Project Code Generation** (line ~358):
   - Changed from `$contactNo` to `$contactWithSequence`
   - Updated format comment

---

## 🔒 Safety Guarantees

✅ **Error handling:**
- Sequence calculation wrapped in try/catch
- Defaults to '01' if calculation fails
- Comprehensive logging

✅ **Data integrity:**
- Excludes current record from count
- Excludes deleted records
- Only counts non-deleted opportunities

✅ **Production-safe:**
- No database schema changes
- No new fields
- Computed dynamically via SQL
- No recursion (direct database update)

---

## 📊 Examples

### Example 1: First Opportunity for Contact in Year
- Contact: CON1
- Year: 2026
- Existing count: 0
- Sequence: 01
- Result: `Con101`
- Project Code: `20260107-Con101-z751-Bít cồ bôn`

### Example 2: Second Opportunity for Same Contact in Same Year
- Contact: CON1
- Year: 2026
- Existing count: 1
- Sequence: 02
- Result: `Con102`
- Project Code: `20260107-Con102-z751-Dự án mới`

### Example 3: First Opportunity in New Year
- Contact: CON1
- Year: 2027
- Existing count: 0 (reset for new year)
- Sequence: 01
- Result: `Con101`
- Project Code: `20270107-Con101-z751-Dự án 2027`

### Example 4: Different Contact
- Contact: CON2
- Year: 2026
- Existing count: 0 (different contact, separate sequence)
- Sequence: 01
- Result: `Con201`
- Project Code: `20260107-Con201-z751-Dự án khác`

---

## ✅ Requirements Met

- [x] Format: `YYYYMMDD-Con{CONTACT_NO}{SEQ_IN_YEAR}-{COMPANY_CODE}-{PROJECT_NAME}`
- [x] Sequence per contact per year
- [x] Starts from 01, increments by 1
- [x] Resets every new year
- [x] Always 2 digits
- [x] No new database tables
- [x] No new fields
- [x] Computed dynamically via SQL
- [x] Excludes current record
- [x] Excludes deleted records
- [x] Only for Potentials module
- [x] Only on record creation
- [x] Only if cf_859 is empty
- [x] Direct database update (no save())
- [x] No recursion
- [x] Comprehensive error handling

---

## 🔍 SQL Query Details

**Query:**
```sql
SELECT COUNT(*) as seq_count
FROM vtiger_potential p
INNER JOIN vtiger_crmentity e ON e.crmid = p.potentialid
WHERE p.contact_id = ?
AND YEAR(e.createdtime) = ?
AND e.deleted = 0
AND p.potentialid != ?
```

**Parameters:**
1. `contact_id`: Contact ID of the opportunity
2. `YEAR(createdtime)`: Year from createdtime
3. `potentialid != ?`: Exclude current record

**Result:**
- Returns count of existing opportunities
- Sequence = count + 1
- Padded to 2 digits

---

**Completed:** 2026-01-07  
**Status:** ✅ COMPLETE


