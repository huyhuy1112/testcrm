# ✅ Marketing Menu Safe Implementation (Template-Only)

## 🎯 Implementation Strategy

**Option A (Template-Only)** - Safest approach, no backend changes

---

## 📋 Code Changes

### File Modified:
- `layouts/v7/modules/Vtiger/partials/Menubar.tpl`

### Before:
```smarty
<div id="modules-menu" class="modules-menu">
	{foreach key=moduleName item=moduleModel from=$SELECTED_CATEGORY_MENU_LIST}
		{assign var='translatedModuleLabel' value=vtranslate($moduleModel->get('label'),$moduleName )}
		<ul title="{$translatedModuleLabel}" class="module-qtip">
			<li {if $MODULE eq $moduleName}class="active"{else}class=""{/if}>
				<a href="{$moduleModel->getDefaultUrl()}&app={$SELECTED_MENU_CATEGORY}">
					{$moduleModel->getModuleIcon()}
					<span>{$translatedModuleLabel}</span>
				</a>
			</li>
		</ul>
	{/foreach}
</div>
```

### After:
```smarty
<div id="modules-menu" class="modules-menu">
	{foreach key=moduleName item=moduleModel from=$SELECTED_CATEGORY_MENU_LIST}
		{* Hide Leads ONLY in Marketing menu *}
		{if $SELECTED_MENU_CATEGORY eq 'MARKETING' && $moduleName eq 'Leads'}
			{continue}
		{/if}
		
		{assign var='translatedModuleLabel' value=vtranslate($moduleModel->get('label'),$moduleName )}
		<ul title="{$translatedModuleLabel}" class="module-qtip">
			<li {if $MODULE eq $moduleName}class="active"{else}class=""{/if}>
				<a href="{$moduleModel->getDefaultUrl()}&app={$SELECTED_MENU_CATEGORY}">
					{$moduleModel->getModuleIcon()}
					<span>{$translatedModuleLabel}</span>
				</a>
			</li>
		</ul>
	{/foreach}
	
	{* Add temporary placeholder menu items ONLY in Marketing menu *}
	{if $SELECTED_MENU_CATEGORY eq 'MARKETING'}
		{* Evaluate - placeholder only *}
		<ul title="Evaluate" class="module-qtip">
			<li class="">
				<a href="#" onclick="return false;" style="cursor: not-allowed; opacity: 0.7;">
					<i class="fa fa-bar-chart"></i>
					<span>Evaluate</span>
				</a>
			</li>
		</ul>
		
		{* Plans - placeholder only *}
		<ul title="Plans" class="module-qtip">
			<li class="">
				<a href="#" onclick="return false;" style="cursor: not-allowed; opacity: 0.7;">
					<i class="fa fa-calendar"></i>
					<span>Plans</span>
				</a>
			</li>
		</ul>
	{/if}
</div>
```

---

## 🛡️ Why This Will NOT Cause White Screen

### 1. **Pure Template Logic**
- Only Smarty template syntax (`{if}`, `{continue}`, `{foreach}`)
- No PHP code execution
- No database queries
- No module model instantiation

### 2. **Safe Conditional Rendering**
```smarty
{if $SELECTED_MENU_CATEGORY eq 'MARKETING' && $moduleName eq 'Leads'}
    {continue}
{/if}
```
- Simple string comparison
- If condition fails → menu item renders normally
- No exception can be thrown

### 3. **Static HTML for Placeholders**
```smarty
<a href="#" onclick="return false;" style="cursor: not-allowed; opacity: 0.7;">
    <i class="fa fa-bar-chart"></i>
    <span>Evaluate</span>
</a>
```
- Pure HTML/CSS
- No dynamic method calls
- No property access
- If condition fails → items simply don't render

### 4. **No Backend Dependencies**
- ✅ No `Vtiger_Module_Model` usage
- ✅ No `createFakeMenuModel()` method
- ✅ No `is_fake` property
- ✅ No database changes
- ✅ No core file modifications

### 5. **Graceful Fallback**
- If `$SELECTED_MENU_CATEGORY` is undefined → condition is false → normal rendering
- If `$moduleName` is undefined → condition is false → normal rendering
- If template syntax error → Smarty shows error, not white screen
- All existing menu items continue to work

---

## ✅ Safety Features

### Template-Level Only:
- Changes are **purely presentational**
- No business logic
- No data manipulation
- No side effects

### Conditional Scope:
- Only affects `SELECTED_MENU_CATEGORY eq 'MARKETING'`
- Other menus (Sales, Support, etc.) completely unaffected
- Leads still accessible via direct URL

### Simple Logic:
- String comparison: `eq` operator
- Loop control: `{continue}` statement
- Static HTML: No dynamic content

---

## 🔄 Rollback Instructions

### Step 1: Revert Template
```bash
git checkout HEAD -- layouts/v7/modules/Vtiger/partials/Menubar.tpl
```

### Step 2: Clear Cache
```bash
docker exec vtiger_web sh -c "rm -rf cache/* templates_c/*"
```

### Step 3: Reload Page
- Refresh browser
- Menu returns to original state

**Alternative Manual Rollback:**
1. Open `layouts/v7/modules/Vtiger/partials/Menubar.tpl`
2. Remove the `{if $SELECTED_MENU_CATEGORY eq 'MARKETING' && $moduleName eq 'Leads'}` block
3. Remove the `{if $SELECTED_MENU_CATEGORY eq 'MARKETING'}` block with Evaluate/Plans
4. Clear cache

---

## 🧪 Testing Checklist

### ✅ Marketing Menu:
- [ ] Leads is hidden
- [ ] Evaluate appears with `fa-bar-chart` icon
- [ ] Plans appears with `fa-calendar` icon
- [ ] Evaluate/Plans are disabled (not clickable)
- [ ] Other items (Campaigns, Contacts, Organizations) work normally

### ✅ Other Menus:
- [ ] Sales menu unchanged
- [ ] Support menu unchanged
- [ ] Inventory menu unchanged
- [ ] All other menus work normally

### ✅ Leads Module:
- [ ] Still accessible via direct URL: `index.php?module=Leads&view=List`
- [ ] Still functional (not disabled)
- [ ] Permissions unchanged

### ✅ Error Handling:
- [ ] No PHP errors in logs
- [ ] No white screen
- [ ] No JavaScript errors
- [ ] Menu renders even if condition fails

---

## 📝 Summary

**Status:** ✅ **IMPLEMENTED (Template-Only)**

### Changes:
- ✅ Leads hidden in Marketing menu (template-level filtering)
- ✅ Evaluate added (static HTML placeholder)
- ✅ Plans added (static HTML placeholder)
- ✅ No backend changes
- ✅ No module model usage
- ✅ No database changes

### Files Modified:
- `layouts/v7/modules/Vtiger/partials/Menubar.tpl` - Template-only changes

### Why Safe:
1. **Pure template logic** - No PHP execution
2. **Simple conditionals** - String comparison only
3. **Static HTML** - No dynamic method calls
4. **Graceful fallback** - Menu still works if condition fails
5. **No dependencies** - No backend changes required

### Cache Clear:
```bash
docker exec vtiger_web sh -c "rm -rf cache/* templates_c/*"
```

---

## 🎯 Expected Result

**Marketing Menu Shows:**
- Campaigns ✅
- ~~Leads~~ (hidden) ✅
- Contacts ✅
- Organizations ✅
- Evaluate (disabled, placeholder) ✅
- Plans (disabled, placeholder) ✅

**No White Screen:** ✅ Template-only changes cannot cause fatal PHP errors

