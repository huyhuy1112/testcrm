<?php
/* Smarty version 4.5.4, created on 2025-12-26 01:45:24
  from '/var/www/html/layouts/v7/modules/Vtiger/dashboards/KeyMetricsContents.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_694de8b4d6b055_43547673',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '18e4d5492790b2c643596df2b442840272491298' => 
    array (
      0 => '/var/www/html/layouts/v7/modules/Vtiger/dashboards/KeyMetricsContents.tpl',
      1 => 1766628497,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_694de8b4d6b055_43547673 (Smarty_Internal_Template $_smarty_tpl) {
?><div><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['KEYMETRICS']->value, 'KEYMETRIC');
$_smarty_tpl->tpl_vars['KEYMETRIC']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['KEYMETRIC']->value) {
$_smarty_tpl->tpl_vars['KEYMETRIC']->do_else = false;
?><div style="padding-bottom:6px;"><span class="pull-right"><?php echo $_smarty_tpl->tpl_vars['KEYMETRIC']->value['count'];?>
</span><a href="?module=<?php echo $_smarty_tpl->tpl_vars['KEYMETRIC']->value['module'];?>
&view=List&viewname=<?php echo $_smarty_tpl->tpl_vars['KEYMETRIC']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['KEYMETRIC']->value['name'];?>
</a></div><?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?></div>
<?php }
}
