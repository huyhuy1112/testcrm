<?php
/* Smarty version 4.5.4, created on 2025-03-26 14:30:53
  from '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Settings/Groups/EditView.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_67e40f9d6c3356_49462072',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '635a8d6d397f9d83c2de15cdcd9dfcd1074a2f0d' => 
    array (
      0 => '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Settings/Groups/EditView.tpl',
      1 => 1742383535,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_67e40f9d6c3356_49462072 (Smarty_Internal_Template $_smarty_tpl) {
?>
<div class="editViewPageDiv"><div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"><div class="editViewContainer"><form name="EditGroup" action="index.php" method="post" id="EditView" class="form-horizontal"><input type="hidden" name="module" value="Groups"><input type="hidden" name="action" value="Save"><input type="hidden" name="parent" value="Settings"><input type="hidden" name="record" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getId();?>
"><input type="hidden" name="mode" value="<?php echo $_smarty_tpl->tpl_vars['MODE']->value;?>
"><h4><?php if (!empty($_smarty_tpl->tpl_vars['MODE']->value)) {
echo vtranslate('LBL_EDITING',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 <?php echo vtranslate(('SINGLE_').($_smarty_tpl->tpl_vars['MODULE']->value),$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 - <?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getName();
} else {
echo vtranslate('LBL_CREATING_NEW',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 <?php echo vtranslate(('SINGLE_').($_smarty_tpl->tpl_vars['MODULE']->value),$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);
}?></h4><hr><br><div class="editViewBody"><div class="form-group row"><label class="col-lg-3 col-md-3 col-sm-3 fieldLabel control-label"><?php echo vtranslate('LBL_GROUP_NAME',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
&nbsp;<span class="redColor">*</span></label><div class="fieldValue col-lg-9 col-md-9 col-sm-9"><div class="row"><div class="col-lg-6 col-md-6 col-sm-12"><input class="inputElement" type="text" name="groupname" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getName();?>
" data-rule-required="true"></div></div></div></div><div class="form-group row"><label class="col-lg-3 col-md-3 col-sm-3 fieldLabel control-label"><?php echo vtranslate('LBL_DESCRIPTION',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</label><div class="fieldValue col-lg-9 col-md-9 col-sm-9"><div class="row"><div class="col-lg-6 col-md-6 col-sm-12"><input class="inputElement" type="text" name="description" id="description" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getDescription();?>
" /></div></div></div></div><div class="form-group row"><label class="col-lg-3 col-md-3 col-sm-3 fieldLabel control-label"><?php echo vtranslate('LBL_GROUP_MEMBERS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
&nbsp;<span class="redColor">*</span></label><div class="fieldValue col-lg-9 col-md-9 col-sm-9"><div class="row"><?php $_smarty_tpl->_assignInScope('GROUP_MEMBERS', $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getMembers());?><div class="col-lg-6 col-md-6 col-sm-6"><select id="memberList" class="select2 inputElement" multiple="true" name="members[]" data-rule-required="true" data-placeholder="<?php echo vtranslate('LBL_ADD_USERS_ROLES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
" ><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['MEMBER_GROUPS']->value, 'ALL_GROUP_MEMBERS', false, 'GROUP_LABEL');
$_smarty_tpl->tpl_vars['ALL_GROUP_MEMBERS']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['GROUP_LABEL']->value => $_smarty_tpl->tpl_vars['ALL_GROUP_MEMBERS']->value) {
$_smarty_tpl->tpl_vars['ALL_GROUP_MEMBERS']->do_else = false;
?><optgroup label="<?php ob_start();
echo $_smarty_tpl->tpl_vars['GROUP_LABEL']->value;
$_prefixVariable1 = ob_get_clean();
echo vtranslate($_prefixVariable1,$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
" class="<?php echo $_smarty_tpl->tpl_vars['GROUP_LABEL']->value;?>
"><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['ALL_GROUP_MEMBERS']->value, 'MEMBER');
$_smarty_tpl->tpl_vars['MEMBER']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['MEMBER']->value) {
$_smarty_tpl->tpl_vars['MEMBER']->do_else = false;
if ($_smarty_tpl->tpl_vars['MEMBER']->value->getName() != $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getName()) {?><option value="<?php echo $_smarty_tpl->tpl_vars['MEMBER']->value->getId();?>
" data-member-type="<?php echo $_smarty_tpl->tpl_vars['GROUP_LABEL']->value;?>
" <?php if ((isset($_smarty_tpl->tpl_vars['GROUP_MEMBERS']->value[$_smarty_tpl->tpl_vars['GROUP_LABEL']->value][$_smarty_tpl->tpl_vars['MEMBER']->value->getId()]))) {?> selected="true"<?php }?>><?php echo trim($_smarty_tpl->tpl_vars['MEMBER']->value->getName());?>
</option><?php }
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?></optgroup><?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?></select></div><div class="groupMembersColors col-lg-3 col-md-3 col-sm-6"><ul class="liStyleNone"><li class="Users textAlignCenter"><?php echo vtranslate('LBL_USERS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li><li class="Groups textAlignCenter"><?php echo vtranslate('LBL_GROUPS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li><li class="Roles textAlignCenter"><?php echo vtranslate('LBL_ROLES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li><li class="RoleAndSubordinates textAlignCenter"><?php echo vtranslate('LBL_ROLEANDSUBORDINATE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li></ul></div></div></div></div></div><div class='modal-overlay-footer clearfix'><div class="row clearfix"><div class='textAlignCenter col-lg-12 col-md-12 col-sm-12 '><button type='submit' class='btn btn-success saveButton' type="submit" ><?php echo vtranslate('LBL_SAVE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</button>&nbsp;&nbsp;<a class='cancelLink' data-dismiss="modal" href="javascript:history.back()" type="reset"><?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></div></div></div></form></div></div></div><?php }
}
