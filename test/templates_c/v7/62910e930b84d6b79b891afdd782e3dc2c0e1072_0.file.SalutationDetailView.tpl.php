<?php
/* Smarty version 4.5.4, created on 2025-12-29 04:12:22
  from '/var/www/html/layouts/v7/modules/Vtiger/uitypes/SalutationDetailView.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_6951ffa6873ff5_36369207',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '62910e930b84d6b79b891afdd782e3dc2c0e1072' => 
    array (
      0 => '/var/www/html/layouts/v7/modules/Vtiger/uitypes/SalutationDetailView.tpl',
      1 => 1766628497,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_6951ffa6873ff5_36369207 (Smarty_Internal_Template $_smarty_tpl) {
echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('salutationtype');?>


<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getDisplayValue($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue'),$_smarty_tpl->tpl_vars['RECORD']->value->getId(),$_smarty_tpl->tpl_vars['RECORD']->value);
}
}
