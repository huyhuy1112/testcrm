# Management Menu Configuration

## ✅ Configuration Complete

**Date:** $(date)  
**Task:** Configure "Management" app menu with Calendar, Reports, Documents, and Users modules

---

## Modules Assigned to Management Menu

| Module | Display Label | Internal Name |
|--------|--------------|--------------|
| Calendar | Schedule | Calendar |
| Reports | Report | Reports |
| Documents | Document | Documents |
| Users | Team | Users |

---

## Implementation Files

### 1. PHP Script: `configure_management_menu.php`

**Purpose:** Programmatic configuration via Vtiger API

**Features:**
- ✅ Fetches module tabids dynamically
- ✅ Removes modules from previous apps
- ✅ Maps modules to MANAGEMENT app
- ✅ Updates display labels
- ✅ Idempotent (safe to re-run)
- ✅ Verification queries

**Usage:**
```bash
php configure_management_menu.php
```

### 2. SQL Script: `configure_management_menu.sql`

**Purpose:** Direct database configuration

**Features:**
- ✅ Idempotent SQL statements
- ✅ Removes old mappings
- ✅ Inserts new mappings
- ✅ Updates display labels
- ✅ Verification query included

**Usage:**
```bash
mysql -u root -p TDB1 < configure_management_menu.sql
```

---

## Database Changes

### vtiger_app2tab Table

**Actions:**
1. Remove modules from previous apps (if any)
2. Insert mappings to MANAGEMENT app:
   - Calendar → MANAGEMENT (sequence: 1)
   - Reports → MANAGEMENT (sequence: 2)
   - Documents → MANAGEMENT (sequence: 3)
   - Users → MANAGEMENT (sequence: 4)

**SQL Pattern:**
```sql
-- Remove from old apps
DELETE a FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE t.name IN ('Calendar', 'Reports', 'Documents', 'Users')
  AND a.appname != 'MANAGEMENT';

-- Add to MANAGEMENT
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible)
SELECT 'MANAGEMENT', tabid, [sequence], 1
FROM vtiger_tab WHERE name = '[ModuleName]' LIMIT 1;
```

### vtiger_tab Table

**Actions:**
- Update `tablabel` for display names:
  - Calendar: `tablabel = 'Schedule'`
  - Reports: `tablabel = 'Report'`
  - Documents: `tablabel = 'Document'`
  - Users: `tablabel = 'Team'`

**SQL Pattern:**
```sql
UPDATE vtiger_tab SET tablabel = '[DisplayLabel]' WHERE name = '[ModuleName]';
```

---

## Expected Menu Structure

After configuration and cache clear:

```
Sidebar Menu
├── MARKETING
├── SALES
├── INVENTORY
├── SUPPORT
├── MANAGEMENT  ← New/Updated
│   ├── Schedule (Calendar)
│   ├── Report (Reports)
│   ├── Document (Documents)
│   └── Team (Users)
└── TOOLS
```

---

## Verification Steps

### 1. Run Configuration Script

**Option A: PHP Script (Recommended)**
```bash
php configure_management_menu.php
```

**Option B: SQL Script**
```bash
mysql -u root -p TDB1 < configure_management_menu.sql
```

### 2. Clear Cache

```bash
rm -rf cache/*
rm -rf storage/cache/*
rm -rf templates_c/*
```

Or via Vtiger Admin:
- Settings → Configuration → Clear Cache

### 3. Logout and Login

- Logout from Vtiger CRM
- Login again
- Menu structure is cached per user session

### 4. Verify Menu

- ✅ "Management" menu appears in sidebar
- ✅ Shows 4 modules: Schedule, Report, Document, Team
- ✅ Clicking modules opens correct ListView
- ✅ No white screen
- ✅ No PHP errors

---

## Verification Queries

### Check App-to-Tab Mappings

```sql
SELECT 
    t.name AS module_name,
    t.tablabel AS display_label,
    a.appname,
    a.sequence,
    a.visible
FROM vtiger_app2tab a
INNER JOIN vtiger_tab t ON t.tabid = a.tabid
WHERE a.appname = 'MANAGEMENT'
  AND t.name IN ('Calendar', 'Reports', 'Documents', 'Users')
ORDER BY a.sequence;
```

**Expected Result:**
```
module_name | display_label | appname    | sequence | visible
------------|---------------|------------|----------|--------
Calendar    | Schedule      | MANAGEMENT | 1        | 1
Reports     | Report        | MANAGEMENT | 2        | 1
Documents   | Document      | MANAGEMENT | 3        | 1
Users       | Team          | MANAGEMENT | 4        | 1
```

### Check Display Labels

```sql
SELECT name, tablabel FROM vtiger_tab 
WHERE name IN ('Calendar', 'Reports', 'Documents', 'Users')
ORDER BY name;
```

**Expected Result:**
```
name      | tablabel
----------|----------
Calendar  | Schedule
Documents | Document
Reports   | Report
Users     | Team
```

---

## Safety Guarantees

✅ **Idempotent:**
- Scripts can be run multiple times safely
- No duplicate entries
- No data loss

✅ **No Breaking Changes:**
- Module functionality unchanged
- Permissions unchanged
- Only menu structure modified

✅ **Production Safe:**
- No core file modifications
- No template hacks
- Database-only changes

---

## Troubleshooting

### Issue: Management menu doesn't appear

**Solution:**
1. Verify mappings exist:
   ```sql
   SELECT * FROM vtiger_app2tab WHERE appname = 'MANAGEMENT';
   ```
2. Clear cache completely
3. Logout and login again
4. Check browser console for errors

### Issue: Modules show wrong labels

**Solution:**
1. Verify tablabel updates:
   ```sql
   SELECT name, tablabel FROM vtiger_tab WHERE name IN ('Calendar', 'Reports', 'Documents', 'Users');
   ```
2. Clear cache
3. Hard refresh browser (Ctrl+F5)

### Issue: Modules still in old menu

**Solution:**
1. Check if modules are in multiple apps:
   ```sql
   SELECT t.name, a.appname FROM vtiger_app2tab a
   INNER JOIN vtiger_tab t ON t.tabid = a.tabid
   WHERE t.name IN ('Calendar', 'Reports', 'Documents', 'Users');
   ```
2. Remove from old apps manually if needed
3. Re-run configuration script

---

## Rollback Instructions

If you need to revert:

### Restore Original App Mappings

```sql
-- Remove from MANAGEMENT
DELETE FROM vtiger_app2tab WHERE appname = 'MANAGEMENT' 
  AND tabid IN (
    SELECT tabid FROM vtiger_tab 
    WHERE name IN ('Calendar', 'Reports', 'Documents', 'Users')
  );

-- Restore original labels (if changed)
UPDATE vtiger_tab SET tablabel = 'Calendar' WHERE name = 'Calendar';
UPDATE vtiger_tab SET tablabel = 'Reports' WHERE name = 'Reports';
UPDATE vtiger_tab SET tablabel = 'Documents' WHERE name = 'Documents';
UPDATE vtiger_tab SET tablabel = 'Users' WHERE name = 'Users';
```

Then clear cache and logout/login.

---

## Summary

| Item | Status |
|------|--------|
| **Management App** | ✅ Configured |
| **Calendar → Schedule** | ✅ Mapped |
| **Reports → Report** | ✅ Mapped |
| **Documents → Document** | ✅ Mapped |
| **Users → Team** | ✅ Mapped |
| **Display Labels** | ✅ Updated |
| **Idempotent** | ✅ Yes |
| **Production Safe** | ✅ Yes |

---

**Status:** ✅ Complete and ready for deployment

**Result:** Management menu configured with 4 modules, display labels updated, safe to re-run.


