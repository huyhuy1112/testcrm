<?php
/* Smarty version 4.5.4, created on 2025-03-24 16:28:12
  from '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Vtiger/ProjectTaskSummaryWidgetContents.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_67e1881c2b2692_11828693',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '9a2e992302de80098358a835c349222c7ae331f0' => 
    array (
      0 => '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Vtiger/ProjectTaskSummaryWidgetContents.tpl',
      1 => 1742383610,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_67e1881c2b2692_11828693 (Smarty_Internal_Template $_smarty_tpl) {
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['RELATED_HEADERS']->value, 'HEADER');
$_smarty_tpl->tpl_vars['HEADER']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['HEADER']->value) {
$_smarty_tpl->tpl_vars['HEADER']->do_else = false;
if ($_smarty_tpl->tpl_vars['HEADER']->value->get('label') == "Project Task Name") {
ob_start();
echo vtranslate($_smarty_tpl->tpl_vars['HEADER']->value->get('label'),$_smarty_tpl->tpl_vars['MODULE_NAME']->value);
$_prefixVariable1 = ob_get_clean();
$_smarty_tpl->_assignInScope('TASK_NAME_HEADER', $_prefixVariable1);
} elseif ($_smarty_tpl->tpl_vars['HEADER']->value->get('label') == "Progress") {
$_smarty_tpl->_assignInScope('TASK_PROGRESS_HEADER', vtranslate($_smarty_tpl->tpl_vars['HEADER']->value->get('label'),$_smarty_tpl->tpl_vars['MODULE_NAME']->value));
} elseif ($_smarty_tpl->tpl_vars['HEADER']->value->get('label') == "Status") {
$_smarty_tpl->_assignInScope('TASK_STATUS_HEADER', vtranslate($_smarty_tpl->tpl_vars['HEADER']->value->get('label'),$_smarty_tpl->tpl_vars['MODULE_NAME']->value));
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['RELATED_RECORDS']->value, 'RELATED_RECORD');
$_smarty_tpl->tpl_vars['RELATED_RECORD']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['RELATED_RECORD']->value) {
$_smarty_tpl->tpl_vars['RELATED_RECORD']->do_else = false;
$_smarty_tpl->_assignInScope('PERMISSIONS', Users_Privileges_Model::isPermitted($_smarty_tpl->tpl_vars['RELATED_MODULE']->value,'EditView',$_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id')));?><div class="recentActivitiesContainer"><ul class="unstyled"><li><div><div class="textOverflowEllipsis width27em"><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
_Related_Record_<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id');?>
" title="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('projecttaskname');?>
"><strong><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('projecttaskname');?>
</strong></a></div><div class="row"><?php $_smarty_tpl->_assignInScope('RELATED_MODULE_MODEL', Vtiger_Module_Model::getInstance('ProjectTask'));
$_smarty_tpl->_assignInScope('FIELD_MODEL', $_smarty_tpl->tpl_vars['RELATED_MODULE_MODEL']->value->getField('projecttaskprogress'));
if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isViewableInDetailView()) {?><div class="col-lg-6"><div class="row"><span class="col-lg-6"><?php echo $_smarty_tpl->tpl_vars['TASK_PROGRESS_HEADER']->value;?>
 :</span><?php if ($_smarty_tpl->tpl_vars['PERMISSIONS']->value && $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isEditable()) {?><span class="col-lg-6"><div class="dropdown pull-left"><a href="#" data-toggle="dropdown" class="dropdown-toggle"><span class="fieldValue"><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('projecttaskprogress');?>
</span>&nbsp;<b class="caret"></b></a><ul class="dropdown-menu widgetsList" data-recordid="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getId();?>
" data-fieldname="projecttaskprogress"data-old-value="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('projecttaskprogress');?>
" data-mandatory="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory();?>
"><?php $_smarty_tpl->_assignInScope('PICKLIST_VALUES', $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getPicklistValues());?><li class="editTaskDetails emptyOption"><a><?php echo vtranslate('LBL_SELECT_OPTION',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</a></li><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value, 'PICKLIST_VALUE', false, 'PICKLIST_NAME');
$_smarty_tpl->tpl_vars['PICKLIST_VALUE']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['PICKLIST_NAME']->value => $_smarty_tpl->tpl_vars['PICKLIST_VALUE']->value) {
$_smarty_tpl->tpl_vars['PICKLIST_VALUE']->do_else = false;
?><li class="editTaskDetails"><a><?php echo $_smarty_tpl->tpl_vars['PICKLIST_VALUE']->value;?>
</a></li><?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?></ul></div></span><?php } else { ?><span class="col-lg-7"><strong>&nbsp;<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('projecttaskprogress');?>
</strong></span><?php }?></div></div><?php }
$_smarty_tpl->_assignInScope('FIELD_MODEL', $_smarty_tpl->tpl_vars['RELATED_MODULE_MODEL']->value->getField('projecttaskstatus'));
if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isViewableInDetailView()) {?><div class="col-lg-6"><div class="row"><span class="col-lg-6"><?php echo $_smarty_tpl->tpl_vars['TASK_STATUS_HEADER']->value;?>
 :</span><?php if ($_smarty_tpl->tpl_vars['PERMISSIONS']->value && $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isEditable()) {?><span class="col-lg-6 nav nav-pills"><div class="dropdown pull-left"><a href="#" data-toggle="dropdown" class="dropdown-toggle"><span class="fieldValue"><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('projecttaskstatus');?>
</span>&nbsp;<b class="caret"></b></a><ul class="dropdown-menu widgetsList pull-right" data-recordid="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getId();?>
" data-fieldname="projecttaskstatus"data-old-value="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('projecttaskstatus');?>
" data-mandatory="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory();?>
" style="max-height: 200px; left: -64px;"><?php $_smarty_tpl->_assignInScope('PICKLIST_VALUES', $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getPicklistValues());?><li class="editTaskDetails emptyOption" value=""><a><?php echo vtranslate('LBL_SELECT_OPTION',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</a></li><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value, 'PICKLIST_VALUE', false, 'PICKLIST_NAME');
$_smarty_tpl->tpl_vars['PICKLIST_VALUE']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['PICKLIST_NAME']->value => $_smarty_tpl->tpl_vars['PICKLIST_VALUE']->value) {
$_smarty_tpl->tpl_vars['PICKLIST_VALUE']->do_else = false;
?><li class="editTaskDetails" value="<?php echo $_smarty_tpl->tpl_vars['PICKLIST_VALUE']->value;?>
"><a><?php echo $_smarty_tpl->tpl_vars['PICKLIST_VALUE']->value;?>
</a></li><?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?></ul></div></span><?php } else { ?><span class="col-lg-7"><strong>&nbsp;<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('projecttaskstatus');?>
</strong></span><?php }?></div></div><?php }?></div></div></li></ul></div><?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
$_smarty_tpl->_assignInScope('NUMBER_OF_RECORDS', php7_count($_smarty_tpl->tpl_vars['RELATED_RECORDS']->value));
if ($_smarty_tpl->tpl_vars['NUMBER_OF_RECORDS']->value == 5) {?><div class=""><div class="pull-right"><a class="moreRecentTasks cursorPointer"><?php echo vtranslate('LBL_MORE',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</a></div></div><?php }
}
}
