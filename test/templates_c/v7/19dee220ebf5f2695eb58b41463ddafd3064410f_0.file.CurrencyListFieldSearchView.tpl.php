<?php
/* Smarty version 4.5.4, created on 2025-03-27 07:33:15
  from '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Vtiger/uitypes/CurrencyListFieldSearchView.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_67e4ff3b5f20f2_63520691',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '19dee220ebf5f2695eb58b41463ddafd3064410f' => 
    array (
      0 => '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Vtiger/uitypes/CurrencyListFieldSearchView.tpl',
      1 => 1742383630,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_67e4ff3b5f20f2_63520691 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_assignInScope('FIELD_INFO', Zend_Json::encode($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldInfo()));
$_smarty_tpl->_assignInScope('CURRENCY_LIST', $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getCurrencyList());?><div class="select2_search_div"><input type="text" class="listSearchContributor inputElement select2_input_element"/><select class="select2 listSearchContributor" name="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('name');?>
" data-fieldinfo='<?php echo htmlspecialchars((string)$_smarty_tpl->tpl_vars['FIELD_INFO']->value, ENT_QUOTES, 'UTF-8', true);?>
' style="display:none"><option value=""><?php echo vtranslate('LBL_SELECT_OPTION','Vtiger');?>
</option><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['CURRENCY_LIST']->value, 'CURRENCY_NAME', false, 'CURRENCY_ID');
$_smarty_tpl->tpl_vars['CURRENCY_NAME']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['CURRENCY_ID']->value => $_smarty_tpl->tpl_vars['CURRENCY_NAME']->value) {
$_smarty_tpl->tpl_vars['CURRENCY_NAME']->do_else = false;
?><option value="<?php echo $_smarty_tpl->tpl_vars['CURRENCY_NAME']->value;?>
" <?php if (($_smarty_tpl->tpl_vars['CURRENCY_NAME']->value == $_smarty_tpl->tpl_vars['SEARCH_INFO']->value['searchValue']) && ($_smarty_tpl->tpl_vars['CURRENCY_NAME']->value != '')) {?> selected<?php }?>><?php echo vtranslate($_smarty_tpl->tpl_vars['CURRENCY_NAME']->value,$_smarty_tpl->tpl_vars['MODULE']->value);?>
</option><?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?></select></div><?php }
}
