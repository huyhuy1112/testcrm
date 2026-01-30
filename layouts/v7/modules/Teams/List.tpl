{strip}
<link rel="stylesheet" type="text/css" href="layouts/v7/modules/Teams/resources/teams.css" media="screen" />

<div class="teams-page-container">
	<!-- Modern Header Section -->
	<div class="teams-modern-header">
		<div class="teams-header-content">
			<div class="teams-header-left">
				<h1 class="teams-main-title">
					<i class="fa fa-users" aria-hidden="true"></i>
					<span>Teams</span>
				</h1>
				<p class="teams-subtitle">Manage people and groups</p>
			</div>
			<div class="teams-header-right">
				{if $CAN_ADD_GROUP}
					<button type="button" class="btn btn-default teams-action-btn js-add-group" data-url="index.php?module=Teams&view=AddGroup&app=Management">
						<i class="fa fa-plus" aria-hidden="true"></i>
						{vtranslate('LBL_ADD_GROUP','Vtiger')}
					</button>
				{/if}
				{if $CAN_ADD_PERSON}
					<button type="button" class="btn btn-primary teams-action-btn js-add-person" data-url="index.php?module=Teams&view=People&app=Management&mode=modal">
						<i class="fa fa-user-plus" aria-hidden="true"></i>
						{vtranslate('LBL_ADD','Vtiger')} {vtranslate('LBL_PERSON','Vtiger')}
					</button>
				{/if}
			</div>
		</div>
	</div>

	<!-- Modern Tabs Navigation -->
	<div class="teams-modern-tabs">
		<ul class="nav nav-tabs teams-tabs-nav" role="tablist">
			<li role="presentation" class="{if $ACTIVE_TAB eq 'people'}active{/if}">
				<a href="index.php?module=Teams&view=List&tab=people&app=Management" aria-controls="people" role="tab">
					<i class="fa fa-users" aria-hidden="true"></i>
					<span>People</span>
				</a>
			</li>
			<li role="presentation" class="{if $ACTIVE_TAB eq 'groups'}active{/if}">
				<a href="index.php?module=Teams&view=List&tab=groups&app=Management" aria-controls="groups" role="tab">
					<i class="fa fa-object-group" aria-hidden="true"></i>
					<span>Groups</span>
				</a>
			</li>
			<li role="presentation" class="{if $ACTIVE_TAB eq 'settings'}active{/if}">
				<a href="index.php?module=Teams&view=List&tab=settings&app=Management" aria-controls="settings" role="tab">
					<i class="fa fa-cog" aria-hidden="true"></i>
					<span>Settings</span>
				</a>
			</li>
		</ul>
	</div>

	<!-- Content Area -->
	<div class="teams-content-wrapper">
		<div class="teams-content">
			{if $ACTIVE_TAB eq 'groups'}
				{include file='partials/Groups.tpl'|@vtemplate_path:$MODULE}
			{elseif $ACTIVE_TAB eq 'settings'}
				{include file='partials/Settings.tpl'|@vtemplate_path:$MODULE}
			{else}
				{include file='partials/People.tpl'|@vtemplate_path:$MODULE}
			{/if}
		</div>
	</div>
</div>
{/strip}
<script type="text/javascript" src="{vresource_url('layouts/v7/modules/Teams/resources/TeamsModal.js')}"></script>
<script type="text/javascript" src="{vresource_url('layouts/v7/modules/Teams/resources/Group.js')}"></script>
<script type="text/javascript" src="{vresource_url('layouts/v7/modules/Teams/resources/Person.js')}"></script>
<script type="text/javascript">
{literal}
jQuery(document).ready(function($) {
	// Initialize Bootstrap popover for project count
	$('.teams-project-count').each(function() {
		var $el = $(this);
		var userId = $el.data('userid');
		
		// Find corresponding script tag with projects data
		var $script = $('script.teams-projects-data[data-userid="' + userId + '"]');
		if ($script.length > 0) {
			try {
				var projectsData = JSON.parse($script.html());
				var projects = projectsData.projects || [];
				var count = projectsData.count || projects.length;
				
				if (projects.length > 0) {
					var html = '<div style="max-width: 300px;"><strong>Projects (' + count + '):</strong><ul style="margin: 8px 0 0 0; padding-left: 20px;">';
					
					$.each(projects, function(i, proj) {
						html += '<li style="margin: 4px 0;">' + $('<div>').text(proj.name || '').html();
						if (proj.status) {
							html += ' <span class="label label-default" style="font-size: 10px; margin-left: 6px;">' + $('<div>').text(proj.status).html() + '</span>';
						}
						html += '</li>';
					});
					
					html += '</ul></div>';
					
					$el.attr('data-content', html);
				}
			} catch(e) {
				console.error('[Teams] Error parsing projects data for user', userId, e);
			}
		}
	});
	
	$('.teams-project-count').popover({
		container: 'body',
		html: true
	});
	
	// Clean up popovers when page unloads
	$(window).on('beforeunload', function() {
		$('.teams-project-count').popover('destroy');
	});
	
	// Auto-refresh status every 5 seconds (only on People tab) - more frequent for better real-time updates
	if (window.location.href.indexOf('tab=people') !== -1 || window.location.href.indexOf('tab=') === -1) {
		var statusRefreshInterval = setInterval(function() {
			// Get latest status from server
			AppConnector.request({
				module: 'Teams',
				action: 'PersonAjax',
				mode: 'getStatus'
			}).done(function(response) {
				if (response && response.success && response.result && response.result.status_map) {
					var statusMap = response.result.status_map;
					
					// Update status for each user row
					$('tbody tr[data-userid]').each(function() {
						var $row = $(this);
						var $statusCell = $row.find('td:nth-child(5)'); // Status column
						
						// Get user ID from data attribute
						var userId = parseInt($row.attr('data-userid'));
						
						if (userId && statusMap[userId]) {
							var status = statusMap[userId];
							var newHtml = '';
							
							if (status.is_inactive) {
								newHtml = '<span class="label label-danger">Inactive</span>';
							} else if (status.is_online) {
								newHtml = '<span class="label label-success" style="display: inline-flex; align-items: center; gap: 6px;"><span class="dot-online"></span><span>Online</span></span>';
							} else if (status.status_label === 'Never logged in') {
								newHtml = '<span class="text-muted"><em>Never logged in</em></span>';
							} else {
								newHtml = '<span class="text-muted" style="display: inline-flex; align-items: center; gap: 6px;"><span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #999; margin-right: 2px;"></span><span>' + $('<div>').text(status.status_label).html() + '</span></span>';
							}
							
							$statusCell.html(newHtml);
						}
					});
				}
			}).fail(function(err) {
				console.error('[Teams] Status refresh failed', err);
			});
		}, 5000); // Every 5 seconds - more frequent for better real-time updates
		
		// Clear interval when leaving page
		$(window).on('beforeunload', function() {
			clearInterval(statusRefreshInterval);
		});
	}
});
{/literal}
</script>
