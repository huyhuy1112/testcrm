# ✅ Make Opportunity Name Optional

## 🎯 Objective

Remove the **required** validation for Opportunity Name (`potentialname`) field so users can create Opportunities without filling this field.

---

## ✅ Changes Made

### 1. Code Update: `modules/Potentials/Potentials.php`

**Before:**
```php
var $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime', 'potentialname');
```

**After:**
```php
// NOTE: potentialname removed from mandatory - it will be auto-synced with Project Code
var $mandatory_fields = Array('assigned_user_id', 'createdtime', 'modifiedtime');
```

✅ **Status:** Already updated

---

### 2. Database Update: `vtiger_field.typeofdata`

**Current:** `V~M` (Mandatory)  
**Target:** `V~O` (Optional)

**SQL Script:** `make_potentialname_optional_final.sql`

---

## 🔧 How to Apply

### Option 1: Run SQL Script (Recommended)

```bash
# Run SQL script in Docker container
docker exec -i vtiger_db mysql -uroot -p132120 TDB1 < make_potentialname_optional_final.sql
```

### Option 2: Manual SQL Update

```sql
-- Connect to database
docker exec -it vtiger_db mysql -uroot -p132120 TDB1

-- Run update
UPDATE vtiger_field 
SET typeofdata = REPLACE(typeofdata, '~M', '~O')
WHERE fieldname = 'potentialname' 
AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Potentials')
AND typeofdata LIKE '%~M%';

-- Verify
SELECT fieldid, fieldname, typeofdata 
FROM vtiger_field 
WHERE fieldname = 'potentialname' 
AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Potentials');
```

---

## 🧹 Clear Cache

After updating database, clear Vtiger cache:

```bash
# Clear cache directories
docker exec vtiger_web rm -rf cache/* templates_c/*

# Or from host
cd /Users/dangquochuy/Backup
rm -rf cache/* templates_c/*
```

---

## ✅ Verification

### 1. Check Database
```sql
SELECT fieldid, fieldname, typeofdata 
FROM vtiger_field 
WHERE fieldname = 'potentialname' 
AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = 'Potentials');
```

**Expected:** `typeofdata` should contain `~O` (not `~M`)

### 2. Test in UI
1. Go to Potentials → Create Opportunity
2. **DO NOT** fill Opportunity Name field
3. Fill other required fields (Organization, etc.)
4. Click Save
5. **Expected:** Should save successfully without validation error

### 3. Verify Auto-Sync
- After save, Project Code should be generated
- Opportunity Name should be automatically synced with Project Code
- Header title should show Project Code

---

## 📋 Summary

### ✅ Completed:
- [x] Removed `potentialname` from `$mandatory_fields` in `Potentials.php`
- [ ] Update `vtiger_field.typeofdata` in database (run SQL script)

### 🔄 Next Steps:
1. Run SQL script to update database
2. Clear cache
3. Test creating Opportunity without Opportunity Name
4. Verify auto-sync with Project Code works

---

## 🎯 Expected Behavior

### Before:
- ❌ Opportunity Name is required
- ❌ Cannot save without filling Opportunity Name
- ❌ Validation error if empty

### After:
- ✅ Opportunity Name is optional
- ✅ Can save without filling Opportunity Name
- ✅ Opportunity Name auto-synced with Project Code after save
- ✅ No validation error if empty

---

## 📝 Notes

- **Why make it optional?** Because Opportunity Name is now auto-synced with Project Code, users don't need to manually fill it.
- **Auto-sync:** The `ProjectCodeHandler` will automatically set `potentialname = Project Code` after save.
- **Backward compatibility:** Existing Opportunities with manual names will continue to work.

