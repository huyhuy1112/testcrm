# ✅ Marketing Menu UI Update (STEP 1)

## 🎯 Objective

Update Marketing menu UI to:
1. **Hide Leads** module (non-destructive)
2. **Add temporary menu items**: Evaluate and Plans

---

## ✅ Implementation

### File Modified:
- `layouts/v7/modules/Vtiger/partials/Menubar.tpl`

### Changes Made:

#### 1. Hide Leads from Marketing Menu
```smarty
{* FILTER: Hide Leads from Marketing menu (non-destructive) *}
{* To re-enable Leads: Remove or comment out the condition below *}
{if $SELECTED_MENU_CATEGORY eq 'MARKETING' && $moduleName eq 'Leads'}
    {continue}
{/if}
```

**How it works:**
- Checks if current menu category is 'MARKETING'
- Checks if module name is 'Leads'
- Skips rendering if both conditions are true
- **Leads module remains installed and functional**
- **Only hidden from Marketing menu display**

#### 2. Add Temporary Menu Items
```smarty
{* ADD TEMPORARY MENU ITEMS: Only show in Marketing category *}
{if $SELECTED_MENU_CATEGORY eq 'MARKETING'}
    {* TODO: Bind Evaluate to real module when ready *}
    <ul title="Evaluate" class="module-qtip">
        <li class="">
            <a href="#" onclick="return false;" style="cursor: not-allowed; opacity: 0.7;">
                <i class="fa fa-bar-chart"></i>
                <span>Evaluate</span>
            </a>
        </li>
    </ul>
    
    {* TODO: Bind Plans to real module when ready *}
    <ul title="Plans" class="module-qtip">
        <li class="">
            <a href="#" onclick="return false;" style="cursor: not-allowed; opacity: 0.7;">
                <i class="fa fa-calendar"></i>
                <span>Plans</span>
            </a>
        </li>
    </ul>
{/if}
```

**Features:**
- **Evaluate**: Icon `fa-bar-chart`, disabled link (`href="#"`, `onclick="return false;"`)
- **Plans**: Icon `fa-calendar`, disabled link
- Only visible in Marketing category
- Visual indication: `cursor: not-allowed`, `opacity: 0.7`
- Clear TODO comments for future module binding

---

## 🔄 How to Re-enable Leads

### Option 1: Remove the Filter (Recommended)
1. Open `layouts/v7/modules/Vtiger/partials/Menubar.tpl`
2. Find this block:
```smarty
{if $SELECTED_MENU_CATEGORY eq 'MARKETING' && $moduleName eq 'Leads'}
    {continue}
{/if}
```
3. **Remove or comment out** the entire block:
```smarty
{* {if $SELECTED_MENU_CATEGORY eq 'MARKETING' && $moduleName eq 'Leads'}
    {continue}
{/if} *}
```
4. Clear cache:
```bash
rm -rf cache/* templates_c/*
```

### Option 2: Modify the Condition
Change the condition to always allow Leads:
```smarty
{if false && $SELECTED_MENU_CATEGORY eq 'MARKETING' && $moduleName eq 'Leads'}
    {continue}
{/if}
```

---

## 🔗 How to Bind Evaluate/Plans to Real Modules

When ready to create real modules for Evaluate and Plans:

### Step 1: Create Modules
1. Create modules in Vtiger (via Settings → Module Manager)
2. Or create custom modules following Vtiger conventions

### Step 2: Update Menu Template
Replace the temporary menu items with real module references:

**Before (Temporary):**
```smarty
<ul title="Evaluate" class="module-qtip">
    <li class="">
        <a href="#" onclick="return false;" style="cursor: not-allowed; opacity: 0.7;">
            <i class="fa fa-bar-chart"></i>
            <span>Evaluate</span>
        </a>
    </li>
</ul>
```

**After (Real Module):**
```smarty
{* Get Evaluate module model if it exists *}
{assign var="evaluateModule" value=$SELECTED_CATEGORY_MENU_LIST['Evaluate']}
{if $evaluateModule}
    <ul title="Evaluate" class="module-qtip">
        <li {if $MODULE eq 'Evaluate'}class="active"{else}class=""{/if}>
            <a href="{$evaluateModule->getDefaultUrl()}&app={$SELECTED_MENU_CATEGORY}">
                {$evaluateModule->getModuleIcon()}
                <span>{vtranslate('LBL_EVALUATE', 'Evaluate')}</span>
            </a>
        </li>
    </ul>
{/if}
```

### Step 3: Register Modules in Marketing Category
Ensure modules are registered in Marketing category:
- Settings → Menu Editor
- Add Evaluate and Plans to Marketing category
- Or update `MenuStructure.php` if needed

---

## 🛡️ Safety Features

### ✅ Non-Destructive:
- **Leads module**: Still installed, still functional
- **Database**: No changes to `vtiger_tab`, `vtiger_field`, etc.
- **Permissions**: Unchanged
- **Core files**: Only template modified (upgrade-safe)

### ✅ Upgrade-Safe:
- Template override can be preserved during upgrades
- Clear comments explain purpose
- Easy to revert or modify

### ✅ UI-Only:
- No business logic changes
- No database modifications
- No module registration changes
- Pure presentation layer

---

## 📋 Testing

### Test Cases:

1. **Marketing Menu Display:**
   - ✅ Leads should NOT appear in Marketing menu
   - ✅ Evaluate should appear with `fa-bar-chart` icon
   - ✅ Plans should appear with `fa-calendar` icon
   - ✅ Both Evaluate and Plans should be disabled (not clickable)

2. **Other Menus:**
   - ✅ Leads should still appear in other menus (if applicable)
   - ✅ Evaluate/Plans should NOT appear in other menus

3. **Leads Module Functionality:**
   - ✅ Leads module should still work if accessed directly
   - ✅ URL: `index.php?module=Leads&view=List`
   - ✅ Permissions unchanged
   - ✅ Data intact

4. **Cache:**
   - ✅ Clear cache after changes
   - ✅ Template should recompile

---

## 📝 Summary

**Status:** ✅ **COMPLETE**

### Changes:
- ✅ Leads hidden from Marketing menu (non-destructive)
- ✅ Evaluate added (temporary, UI-only)
- ✅ Plans added (temporary, UI-only)
- ✅ Clear documentation for re-enabling Leads
- ✅ Clear TODO comments for module binding

### Files Modified:
- `layouts/v7/modules/Vtiger/partials/Menubar.tpl`

### Next Steps (Future):
- Create Evaluate module (when ready)
- Create Plans module (when ready)
- Bind menu items to real modules
- Remove temporary UI placeholders

---

## 🎯 Expected Result

**Marketing Menu Should Show:**
- Campaigns (existing)
- ~~Leads~~ (hidden)
- Evaluate (new, disabled)
- Plans (new, disabled)

**Other Menus:**
- Unchanged
- Leads still accessible via direct URL


