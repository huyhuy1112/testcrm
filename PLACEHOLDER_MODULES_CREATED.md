# Placeholder Modules Created

## Summary

Two lightweight placeholder modules have been created for the Marketing menu:

1. **Evaluate** (tabid: 50)
   - Icon: `fa-bar-chart`
   - Parent: MARKETING

2. **Plans** (tabid: 51)
   - Icon: `fa-calendar`
   - Parent: MARKETING

## Module Structure

Both modules follow the same minimal structure:

```
modules/
  Evaluate/
    Evaluate.php          # Main module class (extends CRMEntity)
    manifest.xml           # Module manifest
    models/
      Module.php         # Module model with icon override
  Plans/
    Plans.php
    manifest.xml
    models/
      Module.php
```

## Key Features

- **No database tables**: `isentitytype = 0` in `vtiger_tab`
- **No business logic**: Minimal CRMEntity class
- **Menu integration**: Registered in `vtiger_tab` and `vtiger_parenttabrel`
- **Custom icons**: Override `getModuleIcon()` in Module model
- **Production safe**: No side effects, no data usage

## Database Registration

### vtiger_tab
- `tabid`: 50 (Evaluate), 51 (Plans)
- `name`: Evaluate, Plans
- `parent`: MARKETING
- `isentitytype`: 0 (no database tables)
- `customized`: 1 (custom modules)

### vtiger_parenttabrel
- Linked to MARKETING parent tab (parenttabid: 2)
- Sequence: 9 (Evaluate), 10 (Plans)

## Next Steps

1. **Clear cache** (already done):
   ```bash
   rm -rf cache/* templates_c/*
   ```

2. **Reload Vtiger UI**

3. **Go to Menu Editor**:
   - Settings > Menu Editor
   - Evaluate and Plans should appear in available modules
   - Drag them into MARKETING category

4. **Verify**:
   - Modules appear in Marketing menu
   - Icons display correctly (bar-chart, calendar)
   - Clicking modules shows empty page (acceptable for placeholders)

## Notes

- These modules are **UI-only placeholders**
- They do NOT have:
  - Database tables
  - Workflows
  - Related lists
  - Reports
  - Custom fields
- They are safe to use in production
- Can be removed later if needed (use uninstall script)

## Uninstall (if needed)

To remove these modules:

```sql
-- Remove from vtiger_parenttabrel
DELETE FROM vtiger_parenttabrel WHERE tabid IN (50, 51);

-- Remove from vtiger_tab
DELETE FROM vtiger_tab WHERE tabid IN (50, 51);

-- Remove files
rm -rf modules/Evaluate modules/Plans
```

