{*+**********************************************************************************
 * Accounts List (Sales app): close split shell opened in Accounts ListViewPreProcess.tpl
 ************************************************************************************}
{if (isset($SELECTED_MENU_CATEGORY) && ($SELECTED_MENU_CATEGORY eq 'SALES' || $SELECTED_MENU_CATEGORY eq 'MARKETING')) || (isset($smarty.get.app) && ($smarty.get.app eq 'SALES' || $smarty.get.app eq 'MARKETING'))}
	</div>
</div>
</main>
</div>
</div>
{else}
{include file="ListViewPostProcess.tpl"|@vtemplate_path:'Vtiger'}
{/if}
