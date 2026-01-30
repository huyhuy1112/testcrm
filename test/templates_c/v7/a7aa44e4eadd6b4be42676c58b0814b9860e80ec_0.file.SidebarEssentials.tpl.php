<?php
/* Smarty version 4.5.4, created on 2026-01-30 07:49:23
  from '/var/www/html/layouts/v7/modules/Calendar/partials/SidebarEssentials.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.4',
  'unifunc' => 'content_697c628322d521_68836826',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'a7aa44e4eadd6b4be42676c58b0814b9860e80ec' => 
    array (
      0 => '/var/www/html/layouts/v7/modules/Calendar/partials/SidebarEssentials.tpl',
      1 => 1769759073,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_697c628322d521_68836826 (Smarty_Internal_Template $_smarty_tpl) {
if ($_GET['view'] == 'Calendar' || $_GET['view'] == 'SharedCalendar') {?>
<div class="calendar-sidebar-google">
    <div class="calendar-mini-wrap" id="calendar-mini-wrap" title="<?php echo vtranslate('LBL_MINI_CALENDAR','Calendar');?>
">
        <div id="calendar-mini"></div>
    </div>
</div>
<div class="sidebar-menu">
    <div class="module-filters" id="module-filters">
        <div class="sidebar-container lists-menu-container">
            <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['QUICK_LINKS']->value['SIDEBARWIDGET'], 'SIDEBARWIDGET');
$_smarty_tpl->tpl_vars['SIDEBARWIDGET']->do_else = true;
if ($_from !== null) foreach ($_from as $_smarty_tpl->tpl_vars['SIDEBARWIDGET']->value) {
$_smarty_tpl->tpl_vars['SIDEBARWIDGET']->do_else = false;
?>
            <?php if ($_smarty_tpl->tpl_vars['SIDEBARWIDGET']->value->get('linklabel') == 'LBL_ACTIVITY_TYPES' || $_smarty_tpl->tpl_vars['SIDEBARWIDGET']->value->get('linklabel') == 'LBL_ADDED_CALENDARS') {?>
            <div class="calendar-sidebar-tabs sidebar-widget" id="<?php echo $_smarty_tpl->tpl_vars['SIDEBARWIDGET']->value->get('linklabel');?>
-accordion" role="tablist" aria-multiselectable="true" data-widget-name="<?php echo $_smarty_tpl->tpl_vars['SIDEBARWIDGET']->value->get('linklabel');?>
">
                <div class="calendar-sidebar-tab">
                    <div class="sidebar-widget-header" role="tab" data-url="<?php echo $_smarty_tpl->tpl_vars['SIDEBARWIDGET']->value->getUrl();?>
">
                        <div class="sidebar-header clearfix">
                                                        <h5 class="pull-left"><?php echo vtranslate($_smarty_tpl->tpl_vars['SIDEBARWIDGET']->value->get('linklabel'),$_smarty_tpl->tpl_vars['MODULE']->value);?>
</h5>
                            <button class="btn btn-default pull-right sidebar-btn add-calendar-feed">
                                <div class="fa fa-plus" aria-hidden="true"></div>
                            </button> 
                        </div>
                    </div>
                    <hr style="margin: 5px 0;">
                    <div class="list-menu-content">
                        <div id="<?php echo $_smarty_tpl->tpl_vars['SIDEBARWIDGET']->value->get('linklabel');?>
" class="sidebar-widget-body activitytypes">
                            <div style="text-align:center;"><img src="layouts/v7/skins/images/loading.gif"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php }?>
            <?php
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);?>    
        </div>
    </div>
</div>
<?php } else { ?>
    <?php $_smarty_tpl->_subTemplateRender(call_user_func_array($_smarty_tpl->registered_plugins[ 'modifier' ][ 'vtemplate_path' ][ 0 ], array( "partials/SidebarEssentials.tpl",'Vtiger' )), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, true);
}
}
}
