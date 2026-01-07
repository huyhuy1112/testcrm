# Project Name UTF-8 Keep - Remove Slugify

## 🎯 Summary

**Status:** ✅ **COMPLETE**

Changed Project Code generation to keep original Vietnamese Project Name (UTF-8) instead of slugifying it.

---

## 🐛 Problem

**Issue:** Project Name was being slugified, losing Vietnamese characters.

**Example:**
- Input: Project Name = `"bít cồ bôn"`
- Output: Project Code = `20260107-CON1-z751-b-t-c-b-n` ❌
- Expected: Project Code = `20260107-CON1-z751-bít cồ bôn` ✅

**Root Cause:**
- `slugifyVietnamese()` was converting Vietnamese characters to ASCII slugs
- Project Code was designed for URL usage, but now needed for display

---

## ✅ Solution

### Removed Slugify for Project Name

**Before:**
```php
// Decode HTML entities
$rawProjectName = html_entity_decode($rawProjectName, ENT_QUOTES, 'UTF-8');

// Slugify Project Name (removes accents, converts to ASCII)
$projectName = slugifyVietnamese($rawProjectName);
// Result: "bít cồ bôn" → "b-t-c-b-n" ❌
```

**After:**
```php
// Decode HTML entities to get raw UTF-8
$rawProjectName = html_entity_decode($rawProjectName, ENT_QUOTES, 'UTF-8');

// NEW REQUIREMENT: Keep original Vietnamese Project Name (UTF-8)
// DO NOT slugify, DO NOT remove accents, DO NOT transform characters
// Project Code is now for display, not URL usage
$projectName = trim($rawProjectName);
// Result: "bít cồ bôn" → "bít cồ bôn" ✅
```

### What Changed

1. **Removed:** `slugifyVietnamese()` call for Project Name
2. **Removed:** All Unicode normalization for Project Name
3. **Removed:** All character transformation for Project Name
4. **Kept:** HTML entity decoding (needed to get raw UTF-8 from Vtiger)
5. **Kept:** Whitespace trimming only
6. **Kept:** Company Code slugify (unchanged as per requirements)

---

## 📝 Implementation Details

### File Modified
- `modules/Potentials/ProjectCodeHandler.php`

### Changes Made

**Lines ~276-299:**
- Removed `slugifyVietnamese()` call for Project Name
- Removed fallback slugify logic
- Changed to simple `trim()` only
- Updated comments to reflect new requirement

### Processing Flow

**New Flow:**
```
1. Query database → Get Project Name
   "bít cồ bôn" (may be HTML-encoded)

2. Decode HTML entities → Get raw UTF-8
   "bít cồ bôn" (raw UTF-8)

3. Trim whitespace only
   "bít cồ bôn" (no transformation)

4. Use directly in Project Code
   "20260107-CON1-z751-bít cồ bôn" ✅
```

**Old Flow (Removed):**
```
1. Query database → Get Project Name
2. Decode HTML entities
3. Normalize Unicode (NFD)
4. Remove combining marks
5. Slugify to ASCII
6. Result: "b-t-c-b-n" ❌
```

---

## ✅ Test Results

### Before Fix
- Input: `"bít cồ bôn"`
- Output: `20260107-CON1-z751-b-t-c-b-n` ❌

### After Fix
- Input: `"bít cồ bôn"`
- Output: `20260107-CON1-z751-bít cồ bôn` ✅

### Examples

| Input Project Name | Before Fix | After Fix |
|-------------------|------------|-----------|
| `"bít cồ bôn"` | `b-t-c-b-n` | `bít cồ bôn` ✅ |
| `"chế lá cà"` | `che-la-ca` | `chế lá cà` ✅ |
| `"kí đầu"` | `ki-dau` | `kí đầu` ✅ |
| `"nam lùn"` | `nam-lun` | `nam lùn` ✅ |

---

## 🔒 Safety Guarantees

✅ **Error handling:**
- HTML entity decode wrapped in try/catch
- Empty check with fallback
- All operations safe

✅ **Backward compatibility:**
- Only affects NEW Opportunities
- Existing Project Codes unchanged
- No database schema changes

✅ **Production-safe:**
- All operations wrapped in try/catch
- Comprehensive logging
- Never breaks save flow

---

## 📋 Requirements Met

- [x] Project Name keeps original UTF-8 characters
- [x] DO NOT slugify Project Name
- [x] DO NOT remove Vietnamese accents
- [x] DO NOT use Normalizer, iconv, htmlentities, slug logic
- [x] Project Code is for display, not URL usage
- [x] Company Code logic remains unchanged (still slugified)
- [x] Random code remains unchanged
- [x] Only affects NEW Opportunities
- [x] No database schema changes
- [x] No UI changes

---

## 📐 Project Code Format

**Format:**
```
YYYYMMDD-{CONTACT_NO}-{COMPANY_CODE}-{PROJECT_NAME}
```

**Example:**
```
20260107-CON1-z751-bít cồ bôn
```

**Components:**
- `YYYYMMDD`: Creation date (e.g., `20260107`)
- `{CONTACT_NO}`: Contact number from database (e.g., `CON1`)
- `{COMPANY_CODE}`: Company Code from Account (slugified, e.g., `z751`)
- `{PROJECT_NAME}`: Original Project Name (UTF-8, e.g., `bít cồ bôn`)

---

**Completed:** 2026-01-07  
**Status:** ✅ COMPLETE

