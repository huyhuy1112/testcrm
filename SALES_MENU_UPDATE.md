# SALES Menu Update - Contracts and Orders

## Summary

Added **ServiceContracts** (Contracts) and **SalesOrder** (Orders) modules to the SALES menu.

## Module Information

- **ServiceContracts** (Contracts)
  - tabid: 35
  - presence: 0 (visible)
  - isentitytype: 1
  - customized: 0

- **SalesOrder** (Orders)
  - tabid: 22
  - presence: 0 (visible)
  - isentitytype: 1
  - customized: 0

## Database Changes

### vtiger_app2tab
```sql
INSERT INTO vtiger_app2tab (appname, tabid, sequence, visible) VALUES
('SALES', 35, 8, 1),  -- ServiceContracts
('SALES', 22, 9, 1);  -- SalesOrder
```

### vtiger_parenttabrel
```sql
INSERT INTO vtiger_parenttabrel (parenttabid, tabid, sequence) VALUES
(3, 35, 14),  -- ServiceContracts -> SALES
(3, 22, 6);   -- SalesOrder -> SALES (already existed)
```

## Verification

### App-to-Tab Mappings
- ✓ ServiceContracts -> SALES (sequence: 8, visible: 1)
- ✓ SalesOrder -> SALES (sequence: 9, visible: 1)

### Parent Tab Relations
- ✓ ServiceContracts -> Sales (sequence: 14)
- ✓ SalesOrder -> Sales (sequence: 6)

## Expected SALES Menu Structure

After logout/login, SALES menu should show:
1. Leads
2. Opportunities
3. Accounts
4. Contacts
5. **Contracts** (newly added)
6. **Orders** (newly added)

## Next Steps

1. ✅ Cache cleared
2. ⏳ **Logout and login again**
3. ⏳ **Check SALES menu** - Contracts and Orders should appear
4. ⏳ **Click modules** - Should open normal ListView (no white screen)

## Notes

- No template changes
- No core logic changes
- Menu-level binding only
- Safe for production
- Both modules are standard Vtiger modules (not custom)


