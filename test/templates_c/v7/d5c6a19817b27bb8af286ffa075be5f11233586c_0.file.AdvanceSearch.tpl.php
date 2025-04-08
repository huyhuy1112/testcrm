<?php
/* Smarty version 4.5.4, created on 2025-03-25 07:43:08
  from '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Vtiger/AdvanceSearch.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_67e25e8c9688a1_31013133',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'd5c6a19817b27bb8af286ffa075be5f11233586c' => 
    array (
      0 => '/home/nhtdbus8/crm.tdbsolution.com/layouts/v7/modules/Vtiger/AdvanceSearch.tpl',
      1 => 1742383593,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_67e25e8c9688a1_31013133 (Smarty_Internal_Template $_smarty_tpl) {
?><div id="searchResults-container" class="advanceFilterContainer"><div class="modal-content"><div class="modal-header"><div class="row"><div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><h4 class="pull-left m-xy-0" data-result="<?php echo vtranslate('LBL_SEARCH_RESULTS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" data-modify="<?php echo vtranslate('LBL_SAVE_MODIFY_FILTER',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"><?php echo vtranslate('LBL_ADVANCE_SEARCH',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 <?php echo vtranslate('LBL_SEARCH',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</h4></div><div class="search-action-container col-lg-6 col-md-6 col-sm-6 col-xs-6"><div class=" p-r-0"><button type="button" class="close" aria-label="Close" data-dismiss="modal"><span aria-hidden="true" class='fa fa-close'></span></button></div></div></div></div><div class="container-fluid modal-body"><div id="advanceSearchHolder"><div id="advanceSearchContainer"><div class="searchModuleComponent"><div class="pull-left" style="margin-right:10px;font-size:18px;"><?php echo vtranslate('LBL_SEARCH_IN',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</div><select class="select2 col-lg-3" id="searchModuleList" data-placeholder="<?php echo vtranslate('LBL_SELECT_MODULE');?>
"><option></option><?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['SEARCHABLE_MODULES']->value, 'fieldObject', false, 'MODULE_NAME');
$_smarty_tpl->tpl_vars['fieldObject']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['MODULE_NAME']->value => $_smarty_tpl->tpl_vars['fieldObject']->value) {
$_smarty_tpl->tpl_vars['fieldObject']->do_else = false;
?><option value="<?php echo $_smarty_tpl->tpl_vars['MODULE_NAME']->value;?>
" <?php if ($_smarty_tpl->tpl_vars['MODULE_NAME']->value == $_smarty_tpl->tpl_vars['SOURCE_MODULE']->value) {?>selected="selected"<?php }?>><?php echo vtranslate($_smarty_tpl->tpl_vars['MODULE_NAME']->value,$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</option><?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?></select></div><div class="clearfix"></div><div class="filterElements well filterConditionContainer" id="searchContainer" style="height: auto;"><form name="advanceFilterForm" method="POST"><?php if ($_smarty_tpl->tpl_vars['SOURCE_MODULE']->value == 'Home') {?><div class="textAlignCenter well contentsBackground"><?php echo vtranslate('LBL_PLEASE_SELECT_MODULE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</div><?php } else { ?><input type="hidden" name="labelFields" <?php if (!empty($_smarty_tpl->tpl_vars['SOURCE_MODULE_MODEL']->value)) {?>  data-value='<?php echo ZEND_JSON::encode($_smarty_tpl->tpl_vars['SOURCE_MODULE_MODEL']->value->getNameFields());?>
' <?php }?> /><?php $_smarty_tpl->_subTemplateRender(call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'vtemplate_path' ][ 0 ], array( 'AdvanceFilter.tpl' )), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
}?></form></div></div></div><div class="searchResults"></div></div><div class="modal-overlay-footer clearfix padding0px border0"><div class="row clearfix"><div class="col-lg-5 col-md-5 col-sm-5">&nbsp;</div><div class="actions  col-lg-7 col-md-7 col-sm-7 p-xy-8"><div class="row"><div class="col-lg-2"><button class="btn btn-success" id="advanceSearchButton" <?php if ($_smarty_tpl->tpl_vars['SOURCE_MODULE']->value == 'Home') {?> disabled="" <?php }?>  type="submit"><strong><?php echo vtranslate('LBL_SEARCH',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></div><div class="col-lg-10"><div class="row"><div class="col-lg-12"><div class="row"><?php if ($_smarty_tpl->tpl_vars['SAVE_FILTER_PERMITTED']->value) {?><button class="btn btn-success col-lg-2 marginLeft10px" <?php if ($_smarty_tpl->tpl_vars['SOURCE_MODULE']->value == 'Home') {?> disabled="" <?php }?> id="advanceIntiateSave"><strong><?php echo vtranslate('LBL_SAVE_AS_FILTER',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button><input class="hide col-lg-3 marginLeft10px" type="text" value="" name="viewname"/><button class="btn btn-success hide col-lg-2 marginLeft10px" <?php if ($_smarty_tpl->tpl_vars['SOURCE_MODULE']->value == 'Home') {?> disabled="" <?php }?> id="advanceSave"><strong><?php echo vtranslate('LBL_SAVE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button><?php }?></div></div></div></div></div></div></div></div><div>&nbsp;</div></div><div class="col-lg-2 col-md-1 hidden-xs hidden-sm">&nbsp;</div></div></div>

<?php }
}
