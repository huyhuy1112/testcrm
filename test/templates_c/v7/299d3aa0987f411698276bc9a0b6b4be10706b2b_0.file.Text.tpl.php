<?php
/* Smarty version 4.5.4, created on 2025-03-19 16:25:54
  from '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Vtiger/uitypes/Text.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_67daf012a5cd76_07285061',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '299d3aa0987f411698276bc9a0b6b4be10706b2b' => 
    array (
      0 => '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Vtiger/uitypes/Text.tpl',
      1 => 1742383637,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_67daf012a5cd76_07285061 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_checkPlugins(array(0=>array('file'=>'/home/nhtdbus8/crm.tdbsolution.com/vendor/smarty/smarty/libs/plugins/modifier.regex_replace.php','function'=>'smarty_modifier_regex_replace',),));
$_smarty_tpl->_assignInScope('FIELD_INFO', $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldInfo());
$_smarty_tpl->_assignInScope('SPECIAL_VALIDATOR', $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getValidator());
if (!(isset($_smarty_tpl->tpl_vars['FIELD_NAME']->value)) || !$_smarty_tpl->tpl_vars['FIELD_NAME']->value) {
$_smarty_tpl->_assignInScope('FIELD_NAME', $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldName());
}
$_smarty_tpl->_assignInScope('fieldValue', purifyHtmlEventAttributes($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue'),true));
if ($_smarty_tpl->tpl_vars['fieldValue']->value === null) {
$_smarty_tpl->_assignInScope('fieldValue', '');
}
if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('uitype') == '19' || $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('uitype') == '20') {?><textarea rows="3" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_editView_fieldName_<?php echo $_smarty_tpl->tpl_vars['FIELD_NAME']->value;?>
" class="inputElement textAreaElement col-lg-12 <?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isNameField()) {?>nameField<?php }?>" name="<?php echo $_smarty_tpl->tpl_vars['FIELD_NAME']->value;?>
" <?php if ($_smarty_tpl->tpl_vars['FIELD_NAME']->value == "notecontent") {?>id="<?php echo $_smarty_tpl->tpl_vars['FIELD_NAME']->value;?>
"<?php }?> <?php if (!empty($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value)) {?>data-validator='<?php echo Zend_Json::encode($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value);?>
'<?php }
if ($_smarty_tpl->tpl_vars['FIELD_INFO']->value["mandatory"] == true) {?> data-rule-required="true" <?php }
if (php7_count($_smarty_tpl->tpl_vars['FIELD_INFO']->value['validator'])) {?>data-specific-rules='<?php echo ZEND_JSON::encode($_smarty_tpl->tpl_vars['FIELD_INFO']->value["validator"]);?>
'<?php }?>><?php echo smarty_modifier_regex_replace($_smarty_tpl->tpl_vars['fieldValue']->value,"/(?!\w)\&nbsp;(?=\w)/"," ");?>
</textarea><?php } else { ?><textarea rows="5" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_editView_fieldName_<?php echo $_smarty_tpl->tpl_vars['FIELD_NAME']->value;?>
" class="inputElement <?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isNameField()) {?>nameField<?php }?>" name="<?php echo $_smarty_tpl->tpl_vars['FIELD_NAME']->value;?>
" <?php if (!empty($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value)) {?>data-validator='<?php echo Zend_Json::encode($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value);?>
'<?php }
if ($_smarty_tpl->tpl_vars['FIELD_INFO']->value["mandatory"] == true) {?> data-rule-required="true" <?php }
if (php7_count($_smarty_tpl->tpl_vars['FIELD_INFO']->value['validator'])) {?>data-specific-rules='<?php echo ZEND_JSON::encode($_smarty_tpl->tpl_vars['FIELD_INFO']->value["validator"]);?>
'<?php }?>><?php echo smarty_modifier_regex_replace($_smarty_tpl->tpl_vars['fieldValue']->value,"/(?!\w)\&nbsp;(?=\w)/"," ");?>
</textarea><?php }
}
}
