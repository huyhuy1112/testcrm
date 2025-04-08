<?php
/* Smarty version 4.5.4, created on 2025-03-24 16:08:41
  from '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Vtiger/DetailViewHeader.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_67e183898c8cf7_46994068',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '3269dd6865500a21ef1f1e172a02c93ffc026661' => 
    array (
      0 => '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Vtiger/DetailViewHeader.tpl',
      1 => 1742383596,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_67e183898c8cf7_46994068 (Smarty_Internal_Template $_smarty_tpl) {
?><div class=" detailview-header-block"><div class="detailview-header"><div class="row"><?php $_smarty_tpl->_subTemplateRender(call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'vtemplate_path' ][ 0 ], array( "DetailViewHeaderTitle.tpl",$_smarty_tpl->tpl_vars['MODULE']->value )), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
$_smarty_tpl->_subTemplateRender(call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'vtemplate_path' ][ 0 ], array( "DetailViewActions.tpl",$_smarty_tpl->tpl_vars['MODULE']->value )), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?></div></div><?php }
}
