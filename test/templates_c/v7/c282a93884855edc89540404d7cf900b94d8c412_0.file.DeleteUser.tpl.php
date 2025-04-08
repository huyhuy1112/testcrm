<?php
/* Smarty version 4.5.4, created on 2025-03-26 14:36:39
  from '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Users/DeleteUser.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_67e410f7e49f74_95777384',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c282a93884855edc89540404d7cf900b94d8c412' => 
    array (
      0 => '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Users/DeleteUser.tpl',
      1 => 1742383580,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_67e410f7e49f74_95777384 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div class="modal-dialog modelContainer"><?php ob_start();
echo vtranslate('Transfer records to user',$_smarty_tpl->tpl_vars['MODULE']->value);
$_prefixVariable1 = ob_get_clean();
$_smarty_tpl->_assignInScope('HEADER_TITLE', $_prefixVariable1);
$_smarty_tpl->_subTemplateRender(call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'vtemplate_path' ][ 0 ], array( "ModalHeader.tpl",$_smarty_tpl->tpl_vars['MODULE']->value )), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array('TITLE'=>$_smarty_tpl->tpl_vars['HEADER_TITLE']->value), 0, true);
?><div class="modal-content"><form class="form-horizontal" id="deleteUser" name="deleteUser" method="post" action="index.php"><input type="hidden" name="module" value="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
" /><input type="hidden" name="userid" value="<?php echo $_smarty_tpl->tpl_vars['USERID']->value;?>
" /><div name='massEditContent'><div class="modal-body"><div class="form-group"><label class="control-label fieldLabel col-sm-5"><?php echo vtranslate('User to be deleted',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label><label class="control fieldValue col-sm-5" style="padding-top: 6PX;"><?php echo $_smarty_tpl->tpl_vars['DELETE_USER_NAME']->value;?>
</label></div><div class="form-group"><label class="control-label fieldLabel col-sm-5"><?php echo vtranslate('Transfer records to user',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label><div class="controls fieldValue col-xs-6"><select class="select2 <?php if ($_smarty_tpl->tpl_vars['OCCUPY_COMPLETE_WIDTH']->value) {?> row-fluid <?php }?>" name="tranfer_owner_id" data-validation-engine="validate[ required]" ><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['USER_LIST']->value, 'USER_MODEL', false, 'USER_ID');
$_smarty_tpl->tpl_vars['USER_MODEL']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['USER_ID']->value => $_smarty_tpl->tpl_vars['USER_MODEL']->value) {
$_smarty_tpl->tpl_vars['USER_MODEL']->do_else = false;
?><option value="<?php echo $_smarty_tpl->tpl_vars['USER_ID']->value;?>
" ><?php echo $_smarty_tpl->tpl_vars['USER_MODEL']->value->getName();?>
</option><?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?></select></div></div><?php if (!(isset($_smarty_tpl->tpl_vars['PERMANENT']->value)) || !$_smarty_tpl->tpl_vars['PERMANENT']->value) {?><div class="form-group"><label class="control-label fieldLabel col-sm-4"></label><div class="controls fieldValue col-sm-8"><input type="checkbox" name="deleteUserPermanent" value="1" >&nbsp;&nbsp;<?php echo vtranslate('LBL_DELETE_USER_PERMANENTLY',$_smarty_tpl->tpl_vars['MODULE']->value);?>
&nbsp;&nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="<?php echo vtranslate('LBL_DELETE_USER_PERMANENTLY_INFO',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"></i></div></div><?php }?></div></div><?php $_smarty_tpl->_subTemplateRender(call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'vtemplate_path' ][ 0 ], array( 'ModalFooter.tpl',$_smarty_tpl->tpl_vars['MODULE']->value )), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
?></form></div></div>

<?php }
}
