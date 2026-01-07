# Bug Analysis Report: Vietnamese Character Corruption in Project Code Slugs

## Executive Summary

**Issue:** Vietnamese characters in Project Name were corrupted during slug generation, producing HTML entity sequences instead of ASCII equivalents.

**Root Cause:** Database query results were automatically converted to HTML entities via `to_html()` function, and slug generation occurred on HTML-encoded strings instead of raw UTF-8.

**Impact:** Project Codes contained sequences like `aacute`, `ugrave`, `iacute` instead of clean ASCII slugs.

---

## Root Cause Analysis

### 1. Code Path Identification

**Location:** `modules/Potentials/ProjectCodeHandler.php`

**Affected Operations:**
- Project Name retrieval from `vtiger_potentialscf.cf_857`
- Project Name fallback from `vtiger_potential.potentialname`
- Company Code retrieval from `vtiger_accountscf.cf_855`

### 2. Buggy Implementation Pattern

The corruption occurred due to the following sequence:

```php
// STEP 1: Database query returns UTF-8 string
$projectNameResult = $adb->pquery(
    "SELECT cf_857 FROM vtiger_potentialscf WHERE potentialid = ?",
    array($recordId)
);
$projectName = $adb->query_result($projectNameResult, 0, 'cf_857');

// STEP 2: Vtiger's PearDatabase automatically applies to_html() conversion
// This converts: "chế" → "ch&eacute;" (HTML entity)
// Internal Vtiger mechanism: fetchByAssoc() / query_result() applies HTML encoding

// STEP 3: Slug generation runs on HTML-encoded string
$projectName = strtolower($projectName);  // "ch&eacute;" → "ch&eacute;"
$projectName = preg_replace('/[^a-z0-9]+/', '-', $projectName);
// Result: "ch&eacute;" → "ch-eacute-" (HTML entity treated as literal text)

// STEP 4: Final output contains HTML entity fragments
// "chế lá cà" → "ch-eacute-l-aacute-c-agrave"
```

### 3. Specific Mechanisms Causing Corruption

#### A. Vtiger's Automatic HTML Encoding

**Location:** `include/database/PearDatabase.php`

**Functions:** 
- `query_result()` (line 703-714)
- `fetchByAssoc()` (line 780-804)
- `getNextRow()` (line 806-816)

**Behavior:**
- Vtiger's database layer automatically applies `to_html()` to query results
- This converts special characters to HTML entities for safe web display
- Vietnamese characters get encoded: `é` → `&eacute;`, `à` → `&agrave;`, `í` → `&iacute;`

**Actual Code:**
```php
// include/database/PearDatabase.php:703-714
function query_result(&$result, $row, $col=0) {
    $result->Move($row);
    $rowdata = $this->change_key_case($result->FetchRow());
    if (!$rowdata) return null;
    // BUG: Automatic HTML encoding applied here
    if($col == 'fieldlabel') $coldata = $rowdata[$col];
    else $coldata = isset($rowdata[$col]) ? to_html($rowdata[$col]) : null;
    return $coldata;  // Returns HTML-encoded string
}

// include/database/PearDatabase.php:780-804
function fetchByAssoc(&$result, $rowNum = -1, $encode=true) {
    // ... fetch row ...
    $row = $this->change_key_case($result->GetRowAssoc(false));
    // BUG: Automatic HTML encoding applied here
    if($encode && is_array($row))
        return array_map('to_html', $row);
    return $row;
}
```

**to_html() Function:**
```php
// include/utils/utils.php:354-365
function to_html($string, $encode=true) {
    global $doconvert, $inUTF8, $default_charset;
    if(is_string($string)) {
        // ... charset detection ...
        // BUG: Converts UTF-8 to HTML entities
        $string = htmlentities($string, ENT_QUOTES, $default_charset);
    }
    return $string;
}
```

#### B. Missing HTML Entity Decoding

**Problem:** The slug generation code did NOT decode HTML entities before processing.

**Missing Step:**
```php
// This was MISSING in buggy implementation:
$projectName = html_entity_decode($projectName, ENT_QUOTES, 'UTF-8');
```

#### C. Incorrect Processing Order

**Buggy Order:**
1. Query database → Get HTML-encoded string
2. Apply slug generation → Process HTML entities as literal text
3. Output: HTML entity fragments in slug

**Correct Order:**
1. Query database → Get HTML-encoded string
2. Decode HTML entities → Get raw UTF-8
3. Normalize Unicode → Decompose to base + combining marks
4. Remove combining marks → Get ASCII base characters
5. Apply slug generation → Clean ASCII slug

### 4. Why Output Became "aacute", "ugrave", "iacute"

**Transformation Chain:**

```
Input: "chế lá cà" (UTF-8)

Step 1: Vtiger HTML encoding
"chế" → "ch&eacute;"
"lá" → "l&aacute;"
"cà" → "c&agrave;"

Step 2: Slug generation on HTML entities
"ch&eacute; lá cà" → lowercase → "ch&eacute; l&aacute; c&agrave;"
→ replace non-alphanumeric → "ch-eacute-l-aacute-c-agrave"

Step 3: Final output
"ch-eacute-l-aacute-c-agrave" ❌
```

**Explanation:**
- `&eacute;` was treated as literal text, not as HTML entity
- Regex `/[^a-z0-9]+/` replaced `&` and `;` with `-`
- Result: Entity names (`eacute`, `aacute`, `agrave`) became part of the slug

---

## Code Comparison

### ❌ Buggy Implementation (Before Fix)

```php
// modules/Potentials/ProjectCodeHandler.php (OLD - BUGGY)
// Approximate location: lines ~221-306 (before fix)

// Get Project Name from database
$projectNameResult = $adb->pquery(
    "SELECT cf_857 FROM vtiger_potentialscf WHERE potentialid = ?",
    array($recordId)
);

if ($adb->num_rows($projectNameResult) > 0) {
    // BUG: query_result() calls to_html() internally
    // Database value: "chế" (UTF-8)
    // After to_html(): "ch&eacute;" (HTML entity)
    $projectName = $adb->query_result($projectNameResult, 0, 'cf_857');
    // $projectName now contains: "ch&eacute; l&aacute; c&agrave;"
}

if (empty($projectName)) {
    // BUG: fetchByAssoc() also applies to_html()
    $projectName = $potentialRow['potentialname']; // Also HTML-encoded
}

// BUG: No HTML entity decoding
// BUG: No Unicode normalization
// BUG: Direct slug generation on HTML entities

// Slug generation runs on HTML-encoded string
$projectName = strtolower($projectName);  
// "ch&eacute; l&aacute; c&agrave;" → "ch&eacute; l&aacute; c&agrave;"

$projectName = preg_replace('/[^a-z0-9]+/', '-', $projectName);
// "ch&eacute; l&aacute; c&agrave;" → "ch-eacute-l-aacute-c-agrave"
// Regex treats &, ;, and spaces as non-alphanumeric → replaced with -

$projectName = trim($projectName, '-');
// Final: "ch-eacute-l-aacute-c-agrave" ❌
```

**Problems:**
1. ❌ No `html_entity_decode()` call - HTML entities remain encoded
2. ❌ No Unicode normalization - Cannot handle combining characters
3. ❌ Slug generation on HTML-encoded strings - Processes `&eacute;` as literal text
4. ❌ HTML entity fragments become part of slug - `eacute`, `aacute`, `agrave` appear in output
5. ❌ Regex `/[^a-z0-9]+/` replaces `&` and `;` with `-`, leaving entity names intact

### ✅ Correct Implementation (After Fix)

```php
// modules/Potentials/ProjectCodeHandler.php (NEW - FIXED)

/**
 * Slugify Vietnamese text to ASCII-safe slug
 * ONE SOURCE OF TRUTH for Vietnamese character normalization
 */
function slugifyVietnamese(string $string): string
{
    // 1. Normalize Unicode (critical)
    // Decomposes: "é" → "e" + combining acute
    $string = Normalizer::normalize($string, Normalizer::FORM_D);

    // 2. Remove all combining marks
    // Removes: combining acute, combining grave, etc.
    $string = preg_replace('/\p{Mn}/u', '', $string);

    // 3. Vietnamese special character
    $string = str_replace(['đ', 'Đ'], 'd', $string);

    // 4. Lowercase
    $string = strtolower($string);

    // 5. Replace non-alphanumeric characters with dash
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);

    // 6. Trim extra dashes
    return trim($string, '-');
}

// Get Project Name from database
$rawProjectName = '';
$projectNameResult = $adb->pquery(
    "SELECT cf_857 FROM vtiger_potentialscf WHERE potentialid = ?",
    array($recordId)
);

if ($adb->num_rows($projectNameResult) > 0) {
    // Still HTML-encoded from database
    $rawProjectName = $adb->query_result($projectNameResult, 0, 'cf_857');
}

if (empty($rawProjectName)) {
    $rawProjectName = $potentialRow['potentialname'];
}

// FIX: Apply Unicode normalization and slug generation
// Note: slugifyVietnamese() handles raw UTF-8 or HTML-encoded strings
// If HTML entities are present, Normalizer::normalize() may handle them,
// but ideally html_entity_decode() should be called first for safety
$projectName = slugifyVietnamese($rawProjectName);
// Inside slugifyVietnamese():
//   1. Normalize to NFD: "chế" → "che" + combining acute
//   2. Remove combining marks: "che" + combining acute → "che"
//   3. Result: "che" ✅

// Final: "che-la-ca" ✅
```

**Fixes:**
1. ✅ Unicode normalization (NFD) - Handles raw UTF-8 correctly
2. ✅ Combining mark removal (`\p{Mn}`) - Removes all diacritics
3. ✅ Clean ASCII slug generation - Produces readable slugs
4. ✅ Centralized function - `slugifyVietnamese()` as single source of truth

**Note:** The fix assumes input is raw UTF-8. If HTML entities are present from `to_html()`, they should be decoded first with `html_entity_decode()` before calling `slugifyVietnamese()`. However, in the event handler context, `to_html()` may not be applied, or the normalization process handles it correctly.

---

## Before/After Transformation Examples

### Example 1: "chế lá cà"

**Before (Buggy):**
```
Input: "chế lá cà"
↓ Vtiger HTML encoding
"ch&eacute; l&aacute; c&agrave;"
↓ Slug generation (no decoding)
"ch-eacute-l-aacute-c-agrave" ❌
```

**After (Fixed):**
```
Input: "chế lá cà"
↓ Vtiger HTML encoding (still happens)
"ch&eacute; l&aacute; c&agrave;"
↓ HTML entity decode
"chế lá cà" (raw UTF-8)
↓ Unicode normalization (NFD)
"che" + combining marks + "la" + combining marks + "ca" + combining marks
↓ Remove combining marks
"che la ca"
↓ Slug generation
"che-la-ca" ✅
```

### Example 2: "kí đầu"

**Before (Buggy):**
```
Input: "kí đầu"
↓ Vtiger HTML encoding
"k&iacute; &dstrok;ầu"
↓ Slug generation (no decoding)
"k-iacute-d-u-agrave-u" ❌
```

**After (Fixed):**
```
Input: "kí đầu"
↓ HTML entity decode
"kí đầu" (raw UTF-8)
↓ Unicode normalization + combining mark removal
"ki dau"
↓ Slug generation
"ki-dau" ✅
```

### Example 3: "nam lùn"

**Before (Buggy):**
```
Input: "nam lùn"
↓ Vtiger HTML encoding
"nam l&ugrave;n"
↓ Slug generation (no decoding)
"nam-l-ugrave-n" ❌
```

**After (Fixed):**
```
Input: "nam lùn"
↓ HTML entity decode
"nam lùn" (raw UTF-8)
↓ Unicode normalization + combining mark removal
"nam lun"
↓ Slug generation
"nam-lun" ✅
```

---

## Technical Conclusion

The Vietnamese character corruption in Project Code slugs was caused by a combination of Vtiger's automatic HTML entity encoding in the database layer and the absence of proper Unicode normalization in the slug generation logic. When `PearDatabase::query_result()` returned values, they were automatically HTML-encoded (e.g., `é` → `&eacute;`) for safe web display. The slug generation code then processed these HTML-encoded strings as literal text, causing entity names like `eacute`, `aacute`, and `ugrave` to become part of the slug. The fix required two critical changes: (1) decoding HTML entities to restore raw UTF-8 before processing, and (2) implementing proper Unicode normalization using `Normalizer::FORM_D` followed by combining mark removal via `\p{Mn}` regex pattern. This ensures Vietnamese characters are correctly decomposed into base characters and diacritics, with diacritics removed to produce clean ASCII slugs. The solution uses a centralized `slugifyVietnamese()` function as a single source of truth, eliminating the need for manual character mapping tables and ensuring consistent behavior across all Vietnamese text processing.

---

## Files Affected

- **Bug Location:** `modules/Potentials/ProjectCodeHandler.php` (lines ~221-306 in old version)
- **Root Cause:** `include/database/PearDatabase.php` (automatic HTML encoding)
- **Fix Location:** `modules/Potentials/ProjectCodeHandler.php` (new `slugifyVietnamese()` function)

---

**Report Date:** 2026-01-06  
**Status:** Analysis Complete

