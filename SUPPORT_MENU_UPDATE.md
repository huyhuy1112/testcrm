# SUPPORT Menu Update - Activities, Schedule, and Rules

## Summary

Added **Activities** (Calendar), **Schedule**, and **Rules** modules to the SUPPORT menu.

## Module Information

### Activities (Calendar)
- **Module**: Calendar
- **tabid**: 9
- **presence**: 0 (visible)
- **isentitytype**: 1 (entity module)
- **customized**: 0 (standard module)
- **Note**: Activities maps to Calendar backend

### Schedule (Placeholder)
- **Module**: Schedule
- **tabid**: 52
- **presence**: 0 (visible)
- **isentitytype**: 0 (no database tables)
- **customized**: 1 (custom placeholder module)
- **Icon**: fa-calendar-check-o

### Rules (Placeholder)
- **Module**: Rules
- **tabid**: 53
- **presence**: 0 (visible)
- **isentitytype**: 0 (no database tables)
- **customized**: 1 (custom placeholder module)
- **Icon**: fa-gavel

## Database Changes

### vtiger_app2tab
```sql
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible) VALUES
('SUPPORT', 9, 8, 1),   -- Activities (Calendar)
('SUPPORT', 52, 9, 1),  -- Schedule
('SUPPORT', 53, 10, 1); -- Rules
```

### vtiger_parenttabrel
```sql
INSERT INTO vtiger_parenttabrel (parenttabid, tabid, sequence) VALUES
(4, 9, 8),   -- Activities (Calendar) -> SUPPORT
(4, 52, 17), -- Schedule -> SUPPORT
(4, 53, 18); -- Rules -> SUPPORT
```

## Files Created

### Schedule Module
- `modules/Schedule/Schedule.php` - Main module class
- `modules/Schedule/manifest.xml` - Module manifest
- `modules/Schedule/models/Module.php` - Module model with icon
- `modules/Schedule/views/List.php` - List view controller
- `layouts/v7/modules/Schedule/ListViewContents.tpl` - List view template

### Rules Module
- `modules/Rules/Rules.php` - Main module class
- `modules/Rules/manifest.xml` - Module manifest
- `modules/Rules/models/Module.php` - Module model with icon
- `modules/Rules/views/List.php` - List view controller
- `layouts/v7/modules/Rules/ListViewContents.tpl` - List view template

## Verification

### App-to-Tab Mappings
- ✓ Activities (Calendar) -> SUPPORT (sequence: 8, visible: 1)
- ✓ Schedule -> SUPPORT (sequence: 9, visible: 1)
- ✓ Rules -> SUPPORT (sequence: 10, visible: 1)

### Parent Tab Relations
- ✓ Activities (Calendar) -> Support (sequence: 8)
- ✓ Schedule -> Support (sequence: 17)
- ✓ Rules -> Support (sequence: 18)

## Expected SUPPORT Menu Structure

After logout/login, SUPPORT menu should show:
1. Tickets (HelpDesk)
2. **Activities** (Calendar) - opens normal Calendar/ListView
3. **Schedule** - opens placeholder empty page
4. **Rules** - opens placeholder empty page

## Next Steps

1. ✅ Cache cleared
2. ⏳ **Logout and login again**
3. ⏳ **Check SUPPORT menu** - Activities, Schedule, and Rules should appear
4. ⏳ **Click Activities** - Should open Calendar/ListView normally
5. ⏳ **Click Schedule/Rules** - Should open placeholder empty page (no white screen)

## Notes

- Activities uses existing Calendar module (no new module created)
- Schedule and Rules are placeholder modules (no database tables, no business logic)
- All modules are menu-level bindings only
- No template edits
- No core logic changes
- Production-safe


