# ✅ Marketing Menu Backend Update (STEP 1)

## 🎯 Objective

Implement **NON-DESTRUCTIVE** Marketing menu customization at the **backend/model level** instead of template level.

**Why template-level failed:**
- Template filtering happens too late in the rendering pipeline
- Menu data structure is already finalized by the time template receives it
- Backend filtering ensures menu structure is correct before template rendering

---

## ✅ Implementation

### Files Modified:

1. **`modules/Vtiger/views/Basic.php`** - Backend menu filtering and injection
2. **`layouts/v7/modules/Vtiger/partials/Menubar.tpl`** - Template updated to handle fake menu items

---

## 🔧 Code Changes

### 1. Backend Filtering (Basic.php)

**Location:** `modules/Vtiger/views/Basic.php` - `preProcess()` method (lines 55-76)

**Code Snippet:**
```php
// ============================================
// CUSTOM MARKETING MENU OVERRIDE (STEP 1)
// ============================================
// NON-DESTRUCTIVE: Filter Leads from Marketing menu
// Add temporary UI-only menu items: Evaluate and Plans
// ============================================
if (isset($menuGroupedByParent['MARKETING'])) {
    // FILTER: Hide Leads from Marketing menu (non-destructive)
    // To re-enable Leads: Remove or comment out the unset() line below
    if (isset($menuGroupedByParent['MARKETING']['Leads'])) {
        unset($menuGroupedByParent['MARKETING']['Leads']);
    }
    
    // INJECT: Add temporary UI-only menu items
    // TODO: Bind Evaluate/Plans to real modules when ready
    $evaluateMenuModel = self::createFakeMenuModel('Evaluate', 'fa-bar-chart', 'MARKETING');
    $plansMenuModel = self::createFakeMenuModel('Plans', 'fa-calendar', 'MARKETING');
    
    // Insert at the end of Marketing menu
    $menuGroupedByParent['MARKETING']['Evaluate'] = $evaluateMenuModel;
    $menuGroupedByParent['MARKETING']['Plans'] = $plansMenuModel;
}
```

### 2. Fake Menu Model Helper (Basic.php)

**Location:** `modules/Vtiger/views/Basic.php` - `createFakeMenuModel()` method (lines 222-243)

**Code Snippet:**
```php
/**
 * Creates a fake menu model for UI-only placeholder items
 * These are NOT real modules - they're temporary UI placeholders
 */
private static function createFakeMenuModel($moduleName, $iconClass, $parentCategory) {
    $fakeModel = new Vtiger_Module_Model();
    
    // Set basic properties that Module Model expects
    $fakeModel->set('name', $moduleName);
    $fakeModel->set('label', $moduleName);
    $fakeModel->set('parent', $parentCategory);
    $fakeModel->set('presence', 2); // Visible
    $fakeModel->set('tabsequence', 999); // Place at end
    $fakeModel->set('id', 0); // Fake tabid
    
    // Mark as fake for template handling
    $fakeModel->set('custom_icon', $iconClass);
    $fakeModel->set('is_fake', true);
    $fakeModel->set('fake_default_url', '#');
    
    return $fakeModel;
}
```

### 3. Template Handling (Menubar.tpl)

**Location:** `layouts/v7/modules/Vtiger/partials/Menubar.tpl`

**Code Snippet:**
```smarty
{foreach key=moduleName item=moduleModel from=$SELECTED_CATEGORY_MENU_LIST}
    {assign var='isFake' value=$moduleModel->get('is_fake')}
    {assign var='customIcon' value=$moduleModel->get('custom_icon')}
    
    {if $isFake}
        {* FAKE MENU ITEM: UI-only placeholder *}
        <a href="#" onclick="return false;" style="cursor: not-allowed; opacity: 0.7;">
            <i class="fa {$customIcon}"></i>
            <span>{$translatedModuleLabel}</span>
        </a>
    {else}
        {* REAL MENU ITEM: Normal module link *}
        <a href="{$moduleModel->getDefaultUrl()}&app={$SELECTED_MENU_CATEGORY}">
            {$moduleModel->getModuleIcon()}
            <span>{$translatedModuleLabel}</span>
        </a>
    {/if}
{/foreach}
```

---

## 🔄 How to Re-enable Leads

### Step 1: Open Basic.php
```bash
modules/Vtiger/views/Basic.php
```

### Step 2: Find the Filter Block
Look for lines 64-66:
```php
if (isset($menuGroupedByParent['MARKETING']['Leads'])) {
    unset($menuGroupedByParent['MARKETING']['Leads']);
}
```

### Step 3: Remove or Comment Out
```php
// Commented out to re-enable Leads
// if (isset($menuGroupedByParent['MARKETING']['Leads'])) {
//     unset($menuGroupedByParent['MARKETING']['Leads']);
// }
```

### Step 4: Clear Cache
```bash
rm -rf cache/* templates_c/*
```

**Result:** Leads will reappear in Marketing menu immediately.

---

## 🔗 How to Bind Evaluate/Plans to Real Modules

### Step 1: Create Modules
1. Create modules in Vtiger (Settings → Module Manager)
2. Or create custom modules following Vtiger conventions
3. Register them in Marketing category (Settings → Menu Editor)

### Step 2: Update Basic.php
Replace fake menu creation with real module instances:

**Before:**
```php
$evaluateMenuModel = self::createFakeMenuModel('Evaluate', 'fa-bar-chart', 'MARKETING');
$plansMenuModel = self::createFakeMenuModel('Plans', 'fa-calendar', 'MARKETING');
```

**After:**
```php
// Get real module models if they exist
if (Vtiger_Module_Model::getInstance('Evaluate')) {
    $evaluateMenuModel = Vtiger_Module_Model::getInstance('Evaluate');
} else {
    $evaluateMenuModel = self::createFakeMenuModel('Evaluate', 'fa-bar-chart', 'MARKETING');
}

if (Vtiger_Module_Model::getInstance('Plans')) {
    $plansMenuModel = Vtiger_Module_Model::getInstance('Plans');
} else {
    $plansMenuModel = self::createFakeMenuModel('Plans', 'fa-calendar', 'MARKETING');
}
```

### Step 3: Remove Fake Flag Check
Once modules are real, the template will automatically use normal rendering (no `is_fake` check needed).

---

## 🛡️ Safety Features

### ✅ Non-Destructive:
- **Leads module**: Still installed, still functional, only hidden from Marketing menu
- **Database**: No changes to `vtiger_tab`, `vtiger_app2tab`, etc.
- **Permissions**: Unchanged
- **Core files**: Only view file modified (upgrade-safe)

### ✅ Backend-Level Filtering:
- Menu structure modified **before** template rendering
- Template receives already-filtered menu array
- No template-level hacks needed
- Works with all menu rendering mechanisms

### ✅ Upgrade-Safe:
- Changes in view layer (Basic.php)
- Clear comments explain purpose
- Easy to revert or modify
- No database dependencies

---

## 📋 Summary

**Status:** ✅ **COMPLETE**

### Changes:
- ✅ Leads filtered from Marketing menu at backend level
- ✅ Evaluate added as fake menu item (UI-only)
- ✅ Plans added as fake menu item (UI-only)
- ✅ Template updated to handle fake menu items
- ✅ Clear documentation for re-enabling Leads
- ✅ Clear TODO comments for module binding

### Files Modified:
- `modules/Vtiger/views/Basic.php` - Backend filtering and fake menu creation
- `layouts/v7/modules/Vtiger/partials/Menubar.tpl` - Template handling for fake items

### Why This Works:
- **Backend filtering**: Menu structure is modified before template receives it
- **Model-level**: Uses Vtiger's menu model system
- **Template-aware**: Template checks `is_fake` flag to render appropriately
- **Non-destructive**: Leads remains functional, just hidden from Marketing menu

---

## 🎯 Expected Result

**Marketing Menu Should Show:**
- Campaigns (existing)
- ~~Leads~~ (hidden, but still functional)
- Evaluate (new, disabled, UI-only)
- Plans (new, disabled, UI-only)

**Other Menus:**
- Unchanged
- Leads still accessible via direct URL: `index.php?module=Leads&view=List`


