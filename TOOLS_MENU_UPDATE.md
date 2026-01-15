# TOOLS Menu Update - Invoices, Orders, Document Templates, and History

## Summary

Added **Invoices**, **Orders** (SalesOrder), **Documents** (Document Templates), and **History** modules to the TOOLS menu.

## Module Information

### Invoice
- **Module**: Invoice
- **tabid**: 23
- **presence**: 0 (visible)
- **isentitytype**: 1 (entity module)
- **customized**: 0 (standard module)
- **App mapping**: TOOLS (sequence: 6)

### SalesOrder (Orders)
- **Module**: SalesOrder
- **tabid**: 22
- **presence**: 0 (visible)
- **isentitytype**: 1 (entity module)
- **customized**: 0 (standard module)
- **App mapping**: TOOLS (sequence: 7)
- **Note**: Appears as "Orders" in menu

### Documents (Document Templates)
- **Module**: Documents
- **tabid**: 8
- **presence**: 0 (visible)
- **isentitytype**: 1 (entity module)
- **customized**: 0 (standard module)
- **App mapping**: TOOLS (sequence: 8)
- **Note**: Used as "Document Templates" in menu

### History (Placeholder)
- **Module**: History
- **tabid**: 54
- **presence**: 0 (visible)
- **isentitytype**: 0 (no database tables)
- **customized**: 1 (custom placeholder module)
- **Icon**: fa-history
- **App mapping**: TOOLS (sequence: 9)

## Database Changes

### vtiger_app2tab
```sql
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible) VALUES
('TOOLS', 23, 6, 1),  -- Invoice
('TOOLS', 22, 7, 1),  -- SalesOrder (Orders)
('TOOLS', 8, 8, 1),   -- Documents (Document Templates)
('TOOLS', 54, 9, 1);  -- History
```

### vtiger_parenttabrel
```sql
INSERT INTO vtiger_parenttabrel (parenttabid, tabid, sequence) VALUES
(7, 23, 17),  -- Invoice -> TOOLS
(7, 22, 18),  -- SalesOrder -> TOOLS
(7, 8, 3),    -- Documents -> TOOLS (already existed)
(7, 54, 16);  -- History -> TOOLS
```

## Files Created

### History Module (Placeholder)
- `modules/History/History.php` - Main module class
- `modules/History/manifest.xml` - Module manifest
- `modules/History/models/Module.php` - Module model with icon
- `modules/History/views/List.php` - List view controller
- `layouts/v7/modules/History/ListViewContents.tpl` - List view template

## Verification

### App-to-Tab Mappings
- ✓ Invoice -> TOOLS (sequence: 6, visible: 1)
- ✓ SalesOrder -> TOOLS (sequence: 7, visible: 1)
- ✓ Documents -> TOOLS (sequence: 8, visible: 1)
- ✓ History -> TOOLS (sequence: 9, visible: 1)

### Parent Tab Relations
- ✓ Documents -> Tools (sequence: 3)
- ✓ History -> Tools (sequence: 16)
- ✓ Invoice -> Tools (sequence: 17)
- ✓ SalesOrder -> Tools (sequence: 18)

## Expected TOOLS Menu Structure

After logout/login, TOOLS menu should show:
1. **Invoices** - opens real Invoice ListView
2. **Orders** (SalesOrder) - opens real SalesOrder ListView
3. **Document Templates** (Documents) - opens real Documents ListView
4. **History** - opens placeholder empty page

## Next Steps

1. ✅ Cache cleared
2. ⏳ **Logout and login again**
3. ⏳ **Check TOOLS menu** - Invoices, Orders, Document Templates, and History should appear
4. ⏳ **Click Invoices/Orders/Documents** - Should open real ListView (no white screen)
5. ⏳ **Click History** - Should open placeholder empty page (no white screen)

## Notes

- Invoice, SalesOrder, and Documents are standard Vtiger modules (real modules with data)
- History is a placeholder module (no database tables, no business logic)
- All modules are menu-level bindings only
- No template edits
- No core logic changes
- Production-safe


