<?php
/* Smarty version 4.5.4, created on 2025-03-20 04:36:35
  from '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Vtiger/AddDashBoardTabForm.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_67db9b535e2db9_64705093',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'eff0a00fb6da57be437df6258996c8fae2091b3c' => 
    array (
      0 => '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Vtiger/AddDashBoardTabForm.tpl',
      1 => 1742383592,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_67db9b535e2db9_64705093 (Smarty_Internal_Template $_smarty_tpl) {
?><div class="modal-dialog modelContainer"><?php ob_start();
echo vtranslate('LBL_ADD_DASHBOARD');
$_prefixVariable1=ob_get_clean();
$_smarty_tpl->_subTemplateRender(call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'vtemplate_path' ][ 0 ], array( "ModalHeader.tpl",$_smarty_tpl->tpl_vars['MODULE']->value )), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('TITLE'=>$_prefixVariable1), 0, true);
?><div class="modal-content"><form id="AddDashBoardTab" name="AddDashBoardTab" method="post" action="index.php"><input type="hidden" name="module" value="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
"/><input type="hidden" name="action" value="DashBoardTab"/><input type="hidden" name="mode" value="addTab"/><div class="modal-body clearfix"><div class="col-lg-5"><label class="control-label pull-right marginTop5px"><?php echo vtranslate('LBL_TAB_NAME',$_smarty_tpl->tpl_vars['MODULE']->value);?>
&nbsp;<span class="redColor">*</span></label></div><div class="col-lg-6"><input type="text" name="tabName" data-rule-required="true" size="25" class="inputElement" maxlength='30'/></div><div class="col-lg-12" style='margin-top: 10px; padding: 5px;'><div class="alert-info"><center><i class="fa fa-info-circle"></i>&nbsp;&nbsp;<?php echo vtranslate('LBL_MAX_CHARACTERS_ALLOWED_DASHBOARD',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</center></div></div></div><?php $_smarty_tpl->_subTemplateRender(call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'vtemplate_path' ][ 0 ], array( "ModalFooter.tpl",$_smarty_tpl->tpl_vars['MODULE']->value )), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?></form></div></div>
<?php }
}
