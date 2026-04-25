{* Calendar Year view (lightweight, non-FullCalendar) *}
{strip}
<input type="hidden" id="currentView" value="{$REQ->get('view')}" />
<input type="hidden" id="start_day" value="{$CURRENT_USER->get('dayoftheweek')}" />
<input type="hidden" id="activity_view" value="{$CURRENT_USER->get('activity_view')}" />
<input type="hidden" id="time_format" value="{$CURRENT_USER->get('hour_format')}" />
<input type="hidden" id="start_hour" value="{$CURRENT_USER->get('start_hour')}" />
<input type="hidden" id="date_format" value="{$CURRENT_USER->get('date_format')}" />
<div id="mycalendar" class="calendarview col-lg-12 calendar-yearview" data-year="{$YEAR|escape}">
	{assign var=LEFTPANELHIDE value=(isset($CURRENT_USER_MODEL) && $CURRENT_USER_MODEL) ? $CURRENT_USER_MODEL->get('leftpanelhide') : 0}
	<div class="essentials-toggle" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Vtiger')}">
		<span class="essentials-toggle-marker fa {if $LEFTPANELHIDE eq '1'}fa-chevron-right{else}fa-chevron-left{/if} cursorPointer"></span>
	</div>

	<div class="calendar-yearview-wrapper">
		<div class="calendar-yearview-header cyv-toolbar">
			<div class="cyv-toolbar-left">
				<div class="cyv-title">
					<span class="cyv-title-kicker">{vtranslate('LBL_CALENDAR_VIEW','Calendar')|default:'Calendar'}</span>
					<span class="cyv-title-main">{vtranslate('LBL_YEAR','Calendar')|default:'Year'} {$YEAR|escape}</span>
				</div>
			</div>
			<div class="cyv-toolbar-right">
				<a class="btn btn-default cyv-btn" href="index.php?module=Calendar&view=Year&year={$YEAR-1}{if $smarty.get.app neq ''}&app={$smarty.get.app}{/if}">
					<i class="fa fa-chevron-left"></i>
				</a>
				<a class="btn btn-default cyv-btn" href="index.php?module=Calendar&view=Year{if $smarty.get.app neq ''}&app={$smarty.get.app}{/if}">
					{vtranslate('LBL_TODAY')|default:'Today'}
				</a>
				<a class="btn btn-default cyv-btn" href="index.php?module=Calendar&view=Year&year={$YEAR+1}{if $smarty.get.app neq ''}&app={$smarty.get.app}{/if}">
					<i class="fa fa-chevron-right"></i>
				</a>
				<div class="dropdown cyv-dropdown">
					<button class="btn btn-default cyv-btn dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fa fa-th-large"></i>&nbsp;View&nbsp;<span class="caret"></span>
					</button>
					<ul class="dropdown-menu dropdown-menu-right cyv-dropdown-menu">
						<li><a href="index.php?module=Calendar&view=Calendar{if $smarty.get.app neq ''}&app={$smarty.get.app}{/if}"><i class="fa fa-calendar"></i>&nbsp;Calendar</a></li>
						<li><a href="index.php?module=Calendar&view=Year&year={$YEAR}{if $smarty.get.app neq ''}&app={$smarty.get.app}{/if}"><i class="fa fa-th"></i>&nbsp;Year</a></li>
						<li class="divider"></li>
						<li><a href="javascript:void(0)" onclick="Calendar_Calendar_Js.showCalendarSettings();"><i class="fa fa-wrench"></i>&nbsp;Calendar Settings</a></li>
					</ul>
				</div>
			</div>
		</div>

		<div class="cyv-body">
			<div class="cyv-side">
				<div class="cyv-panel">
					<div class="cyv-panel-title">Quick filters</div>
					<label class="cyv-toggle">
						<input type="checkbox" class="cyv-filter" data-filter="events" checked>
						<span class="cyv-toggle-ui"></span>
						<span class="cyv-toggle-text"><span class="cyv-swatch is-event"></span> Events</span>
						<span class="cyv-badge" id="cyv-count-events">0</span>
					</label>
					<label class="cyv-toggle">
						<input type="checkbox" class="cyv-filter" data-filter="tasks" checked>
						<span class="cyv-toggle-ui"></span>
						<span class="cyv-toggle-text"><span class="cyv-swatch is-task"></span> Tasks</span>
						<span class="cyv-badge" id="cyv-count-tasks">0</span>
					</label>
					<div class="cyv-hint">Toggle to focus the year overview.</div>
				</div>

				<div class="cyv-panel">
					<div class="cyv-panel-title">Legend</div>
					<div class="cyv-legend">
						<div class="cyv-legend-row"><span class="cyv-swatch is-event"></span><span>Event day</span></div>
						<div class="cyv-legend-row"><span class="cyv-swatch is-task"></span><span>Task day</span></div>
						<div class="cyv-legend-row"><span class="cyv-swatch is-multi"></span><span>Mixed</span></div>
					</div>
				</div>
			</div>

			<div class="cyv-main">
				<div class="calendar-yearview-grid">
					{assign var=MONTHS value=[1,2,3,4,5,6,7,8,9,10,11,12]}
					{foreach item=M from=$MONTHS}
						{assign var=MG value=$MONTH_GRIDS[$M]}
						<div class="calendar-yearview-month">
							<div class="calendar-yearview-month-head">
								<span class="calendar-yearview-month-name" data-month="{$M|escape}" data-year="{$YEAR|escape}">
									{assign var=MONTH_NAMES value=[
										'01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June',
										'07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'
									]}
									<span class="cyv-month-title">{$MONTH_NAMES[$M|string_format:"%02d"]}</span>
									<span class="cyv-month-sub">{$YEAR|escape}</span>
								</span>
							</div>
							<div class="calendar-yearview-weekdays">
								<div>T2</div><div>T3</div><div>T4</div><div>T5</div><div>T6</div><div>T7</div><div>CN</div>
							</div>
							<div class="calendar-yearview-days calendar-yearview-month-body" data-month="{$M|escape}" data-year="{$YEAR|escape}">
								{foreach item=C from=$MG.cells}
									{if $C.blank}
										<div class="calendar-yearview-day is-blank" aria-hidden="true"></div>
									{else}
										<div class="calendar-yearview-day" data-date="{$C.date|escape}">
											<span class="d">{$C.day|escape}</span>
											<span class="dots" aria-hidden="true"></span>
											<span class="m" aria-hidden="true"></span>
										</div>
									{/if}
								{/foreach}
							</div>
						</div>
					{/foreach}
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}

{literal}
<script type="text/javascript">
(function(){
	function escapeHtml(str){
		return String(str || "")
			.replace(/&/g,"&amp;")
			.replace(/</g,"&lt;")
			.replace(/>/g,"&gt;")
			.replace(/"/g,"&quot;")
			.replace(/'/g,"&#39;");
	}

	function parseEventRange(ev){
		var s = ev.start || ev.date_start || ev._start || "";
		var e = ev.end || ev.due_date || ev._end || "";
		var ms = (window.moment && s) ? moment(s) : null;
		var me = (window.moment && e) ? moment(e) : null;
		if (!(ms && ms.isValid())) ms = null;
		if (!(me && me.isValid())) me = null;
		return { start: ms, end: me || ms };
	}

	function buildDayIndex(events){
		var index = {};
		var counts = {};
		var typeCounts = {}; // ymd -> {events:n,tasks:n}
		(events || []).forEach(function(ev){
			var r = parseEventRange(ev);
			if (!r.start) return;
			var startDay = r.start.clone().startOf("day");
			var endDay = (r.end ? r.end.clone().startOf("day") : startDay.clone());
			var span = Math.min(370, Math.max(0, endDay.diff(startDay, "days")));
			for (var i=0;i<=span;i++){
				var dayKey = startDay.clone().add(i,"days").format("YYYY-MM-DD");
				if (!index[dayKey]) index[dayKey] = [];
				index[dayKey].push(ev);
				counts[dayKey] = (counts[dayKey] || 0) + 1;
				if (!typeCounts[dayKey]) typeCounts[dayKey] = { events: 0, tasks: 0 };
				var mod = ev.module || ev.sourceModule || ev.calendarModule || "";
				if (mod === "Events") typeCounts[dayKey].events += 1;
				else typeCounts[dayKey].tasks += 1;
			}
		});
		return { index:index, counts:counts, typeCounts:typeCounts };
	}

	function closeDayPopover(){
		try { jQuery('.cyv-day-popover').remove(); } catch (e) {}
		try { jQuery(document).off('mousedown.cyvPopover'); } catch (e2) {}
		try { jQuery(document).off('keydown.cyvPopover'); } catch (e3) {}
	}

	function formatDateLabel(dateKey){
		try {
			if (window.moment) {
				var m = moment(dateKey, "YYYY-MM-DD", true);
				if (m && m.isValid()) return m.format("MMMM D, YYYY");
			}
		} catch (e) {}
		return dateKey;
	}

	function formatTimeRange(ev){
		try {
			if (!window.moment) return "";
			var r = parseEventRange(ev);
			if (!r.start) return "";
			// show time only if input looks like datetime/time
			var hasTime = (String(ev.start || ev.date_start || "").indexOf(":") !== -1) || (String(ev.time_start || "").indexOf(":") !== -1);
			if (!hasTime) return "";
			var s = r.start.format("HH:mm");
			var e = (r.end && r.end.isValid()) ? r.end.format("HH:mm") : "";
			if (e && e !== s) return s + "–" + e;
			return s;
		} catch (e) { return ""; }
	}

	function positionPopover($pop, anchorEl){
		try {
			var rect = anchorEl.getBoundingClientRect();
			var scrollX = window.pageXOffset || document.documentElement.scrollLeft || 0;
			var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;

			var left = rect.left + scrollX;
			var top = rect.bottom + scrollY + 8;
			$pop.css({ left: left + "px", top: top + "px" });

			var popRect = $pop.get(0).getBoundingClientRect();
			var vw = window.innerWidth || document.documentElement.clientWidth;
			var vh = window.innerHeight || document.documentElement.clientHeight;

			if (popRect.right > vw - 12) {
				left = rect.right + scrollX - popRect.width;
			}
			if (popRect.bottom > vh - 12) {
				top = rect.top + scrollY - popRect.height - 8;
			}

			left = Math.max(scrollX + 12, Math.min(left, scrollX + vw - popRect.width - 12));
			top = Math.max(scrollY + 12, Math.min(top, scrollY + vh - popRect.height - 12));

			$pop.css({ left: left + "px", top: top + "px" });
		} catch (e) {}
	}

	function showDayPopover(dateKey, items, anchorEl){
		closeDayPopover();

		var titleLabel = formatDateLabel(dateKey);
		var list = items || [];

		var events = [];
		var tasks = [];
		list.forEach(function(ev){
			var mod = ev.module || ev.sourceModule || ev.calendarModule || "";
			if (mod === "Events") events.push(ev);
			else tasks.push(ev);
		});

		var html = "";
		html += "<div class='cyv-day-popover' role='dialog' aria-label='Day details'>";
		html += "  <div class='cyv-pop-head'>";
		html += "    <div class='cyv-pop-title'>" + escapeHtml(titleLabel) + "</div>";
		html += "    <button type='button' class='cyv-pop-close' aria-label='Close'>&times;</button>";
		html += "  </div>";
		html += "  <div class='cyv-pop-body'>";

		function renderSection(label, arr, badgeClass, badgeText){
			html += "    <div class='cyv-pop-sec'>";
			html += "      <div class='cyv-pop-sec-title'>" + escapeHtml(label) + "<span class='cyv-pop-sec-count'>" + arr.length + "</span></div>";
			if (!arr.length) {
				html += "      <div class='cyv-pop-empty'>No " + escapeHtml(label.toLowerCase()) + " for this day.</div>";
			} else {
				html += "      <ul class='cyv-pop-list'>";
				arr.forEach(function(ev){
					var subject = ev.title || ev.subject || ev.name || "(No subject)";
					var id = ev.id || ev.activityid || "";
					var url = id ? ("index.php?module=Calendar&view=Detail&record=" + encodeURIComponent(id)) : "javascript:void(0)";
					var time = formatTimeRange(ev);
					html += "        <li class='cyv-pop-item'>";
					html += "          <a class='cyv-pop-link' href='" + url + "' target='_self'>" + escapeHtml(subject) + "</a>";
					if (time) html += "          <div class='cyv-pop-meta'>" + escapeHtml(time) + "</div>";
					html += "          <span class='cyv-pop-badge " + badgeClass + "'>" + escapeHtml(badgeText) + "</span>";
					html += "        </li>";
				});
				html += "      </ul>";
			}
			html += "    </div>";
		}

		if (!events.length && !tasks.length) {
			html += "    <div class='cyv-pop-empty-state'>No tasks or events for this day.</div>";
		} else {
			renderSection("Events", events, "is-event", "Event");
			renderSection("Tasks", tasks, "is-task", "Task");
		}

		html += "    <div class='cyv-pop-actions'>";
		html += "      <a class='btn btn-default btn-sm' href='index.php?module=Calendar&view=Calendar&date=" + encodeURIComponent(dateKey) + "'>Open Calendar</a>";
		html += "    </div>";
		html += "  </div>";
		html += "</div>";

		var $pop = jQuery(html).appendTo(document.body);
		positionPopover($pop, anchorEl);

		$pop.on('click', '.cyv-pop-close', function(e){ e.preventDefault(); closeDayPopover(); });
		jQuery(document).on('mousedown.cyvPopover', function(e){
			var $t = jQuery(e.target);
			if ($t.closest('.cyv-day-popover').length) return;
			closeDayPopover();
		});
		jQuery(document).on('keydown.cyvPopover', function(e){
			if (e && (e.key === 'Escape' || e.keyCode === 27)) closeDayPopover();
		});
	}

	jQuery(function(){
		// Ensure global app-menu hamburger binding is active on Year view (PJAX/Calendar view can skip global init)
		try {
			if (window.Vtiger_Index_Js && Vtiger_Index_Js.getInstance) {
				Vtiger_Index_Js.getInstance().registerAppTriggerEvent();
			}
		} catch (e) {}

		// Progressive enhancement: Fetch events for the year (Events + Tasks)
		try {
			var year = parseInt(jQuery("#mycalendar").data("year"),10);
			var userFmt = (window.vtUtils && vtUtils.getMomentDateFormat) ? vtUtils.getMomentDateFormat() : "YYYY-MM-DD";
			var start = (window.moment ? moment(year + "-01-01", "YYYY-MM-DD").format(userFmt) : (year + "-01-01"));
			var end = (window.moment ? moment(year + "-12-31", "YYYY-MM-DD").format(userFmt) : (year + "-12-31"));

			var merged = [];
			var totals = { events: 0, tasks: 0 };
			var fetchOne = function(type, cb){
				app.request.post({
					data: {
						module: "Calendar",
						action: "Feed",
						start: start,
						end: end,
						type: type,
						userid: (app.getUserId ? app.getUserId() : ""),
						color: "",
						textColor: "",
						targetModule: type,
						fieldname: "",
						group: 0,
						mapping: "",
						conditions: ""
					}
				}).then(function(err, data){
					if (err) return cb(null);
					try { return cb(JSON.parse(data || "[]")); } catch (e) { return cb(null); }
				});
			};

			fetchOne("Events", function(list1){
				if (list1 && list1.length) merged = merged.concat(list1);
				totals.events += (list1 && list1.length) ? list1.length : 0;
				fetchOne("Calendar", function(list2){
					if (list2 && list2.length) merged = merged.concat(list2);
					totals.tasks += (list2 && list2.length) ? list2.length : 0;
					jQuery("#cyv-count-events").text(totals.events);
					jQuery("#cyv-count-tasks").text(totals.tasks);
					var idx = buildDayIndex(merged);
					jQuery(".calendar-yearview-day[data-date]").each(function(){
						var $d = jQuery(this);
						var k = String($d.data("date"));
						var c = idx.counts[k] || 0;
						if (c > 0){
							$d.addClass("has-events");
							$d.find(".m").text(c > 9 ? "9+" : String(c));
							$d.attr("data-count", c);
							var tc = idx.typeCounts[k] || { events: 0, tasks: 0 };
							$d.attr("data-ev", tc.events || 0);
							$d.attr("data-tk", tc.tasks || 0);
							// Up to 3 dots: blue for events, green for tasks
							var dots = "";
							var evDots = Math.min(3, tc.events);
							var tkDots = Math.min(3 - evDots, tc.tasks);
							for (var i=0;i<evDots;i++) dots += "<span class='dot is-event'></span>";
							for (var j=0;j<tkDots;j++) dots += "<span class='dot is-task'></span>";
							$d.find(".dots").html(dots);
						}
					});

					var applyFilters = function(){
						var showEvents = jQuery('.cyv-filter[data-filter="events"]').is(':checked');
						var showTasks = jQuery('.cyv-filter[data-filter="tasks"]').is(':checked');
						jQuery(".calendar-yearview-day.has-events").each(function(){
							var $d = jQuery(this);
							var ev = parseInt($d.attr("data-ev") || "0", 10);
							var tk = parseInt($d.attr("data-tk") || "0", 10);
							var visible = (showEvents && ev > 0) || (showTasks && tk > 0);
							$d.toggleClass("cyv-filtered-out", !visible);
						});
					};
					jQuery(document).off("change.cyvFilter").on("change.cyvFilter", ".cyv-filter", applyFilters);
					applyFilters();

					jQuery(document).off("click.yearViewDay").on("click.yearViewDay", ".calendar-yearview-day[data-date]", function(e){
						e.preventDefault();
						e.stopPropagation();
						var $cell = jQuery(this);
						if ($cell.hasClass("cyv-filtered-out")) return;
						var k = String($cell.data("date"));
						var all = idx.index[k] || [];
						var showEvents = jQuery('.cyv-filter[data-filter="events"]').is(':checked');
						var showTasks = jQuery('.cyv-filter[data-filter="tasks"]').is(':checked');
						var filtered = all.filter(function(ev){
							var mod = ev.module || ev.sourceModule || ev.calendarModule || "";
							if (mod === "Events") return !!showEvents;
							return !!showTasks;
						});
						showDayPopover(k, filtered, this);
					});
				});
			});
		} catch (e) {}
	});
})();
</script>
{/literal}

