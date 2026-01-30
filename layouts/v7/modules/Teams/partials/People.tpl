{strip}
<div class="table-responsive">
	<table class="table listViewEntriesTable">
		<thead>
			<tr>
				<th>Name</th>
				<th>Email</th>
				<th>Groups</th>
				<th>Projects</th>
				<th>Status</th>
				<th class="actions">{vtranslate('LBL_ACTIONS','Vtiger')}</th>
			</tr>
		</thead>
		<tbody>
			{foreach item=ROW from=$PEOPLE}
				<tr data-userid="{$ROW.id}">
					<td>
						<strong>{$ROW.full_name|decode_html}</strong><br/>
						<span class="text-muted">@{$ROW.user_name|decode_html}</span>
					</td>
					<td>{$ROW.email|decode_html}</td>
					<td>
						{if $ROW.groups|@count > 0}
							{implode(', ', $ROW.groups)|decode_html}
						{else}
							—
						{/if}
					</td>
					<td>
						{if $ROW.project_count > 0}
							<span class="teams-project-count" 
								  data-toggle="popover" 
								  data-trigger="hover" 
								  data-placement="left"
								  data-html="true"
								  data-userid="{$ROW.id}"
								  style="cursor: pointer; color: #337ab7; text-decoration: underline; font-weight: 500;">
								{$ROW.project_count}
							</span>
							{* Store projects data in hidden script tag *}
							<script type="text/template" class="teams-projects-data" data-userid="{$ROW.id}">
							{literal}{{/literal}
								"projects": [
									{foreach item=PROJ from=$ROW.projects name=projloop}
										{literal}{{/literal}"id":{$PROJ.id},"name":"{$PROJ.name|escape:'javascript'}","status":"{$PROJ.status|escape:'javascript'}"{literal}}{/literal}{if not $smarty.foreach.projloop.last},{/if}
									{/foreach}
								],
								"count": {$ROW.project_count}
							{literal}}{/literal}
							</script>
						{else}
							0
						{/if}
					</td>
					<td>
						{if $ROW.is_inactive}
							<span class="label label-danger">Inactive</span>
						{elseif $ROW.is_online}
							<span class="label label-success" style="display: inline-flex; align-items: center; gap: 6px;">
								<span class="dot-online"></span>
								<span>Online</span>
							</span>
						{elseif $ROW.status_label eq 'Never logged in'}
							<span class="text-muted"><em>Never logged in</em></span>
						{else}
							<span class="text-muted" style="display: inline-flex; align-items: center; gap: 6px;">
								<span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #999; margin-right: 2px;"></span>
								<span>{$ROW.status_label|decode_html}</span>
							</span>
						{/if}
					</td>
					<td>
						<div class="btn-group">
							<button class="btn btn-default btn-sm dropdown-toggle teams-action-btn" data-toggle="dropdown" title="{vtranslate('LBL_ACTIONS','Vtiger')}">
								<i class="fa fa-ellipsis-v"></i>
							</button>
							<ul class="dropdown-menu dropdown-menu-right">
								<li><a href="index.php?module=Users&parent=Settings&view=Edit&record={$ROW.id}">{vtranslate('LBL_EDIT','Vtiger')}</a></li>
								<li><a href="index.php?module=Users&view=EditAjax&mode=changePassword&recordId={$ROW.id}" target="_blank">{vtranslate('LBL_CHANGE_PASSWORD','Users')}</a></li>
								<li><a href="index.php?module=Teams&view=AddToGroup&app=Management&userid={$ROW.id}">{vtranslate('LBL_ADD_GROUP','Vtiger')}</a></li>
								{if $CAN_DEACTIVATE}
									<li><a href="#" class="js-delete-person dropdown-item" data-userid="{$ROW.id|escape}">Delete</a></li>
								{/if}
							</ul>
						</div>
					</td>
				</tr>
			{foreachelse}
				<tr><td colspan="6" class="text-center text-muted">No people found.</td></tr>
			{/foreach}
		</tbody>
	</table>
</div>
{/strip}
