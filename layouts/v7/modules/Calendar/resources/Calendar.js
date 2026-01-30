/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Calendar_Calendar_Js", {
	calendarViewContainer: false,
	feedsWidgetPostLoadEvent: 'Calendar.Viewtypes.PostLoad.Event',
	disabledFeedsStorageKey: 'calendar.feeds.disabled',
	calendarInstance: false,
	numberOfDaysInAgendaView: 7,
	userPreferenceCache: [],
	sideBarEssentialsState: '',
	getInstance: function () {
		if (!Calendar_Calendar_Js.calendarInstance) {
			if (app.view() == 'SharedCalendar') {
				Calendar_Calendar_Js.calendarInstance = new Calendar_SharedCalendar_Js();
			} else {
				Calendar_Calendar_Js.calendarInstance = new Calendar_Calendar_Js();
			}
		}
		return Calendar_Calendar_Js.calendarInstance;
	},
	showCreateEventModal: function () {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.showCreateEventModal();
	},
	showCreateTaskModal: function () {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.showCreateTaskModal();
	},
	showCalendarSettings: function () {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.showCalendarSettings();
	},
	deleteCalendarEvent: function (eventId, sourceModule, isRecurring) {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.deleteCalendarEvent(eventId, sourceModule, isRecurring);
	},
	editCalendarEvent: function (eventId, isRecurring) {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.editCalendarEvent(eventId, isRecurring);
	},
	editCalendarTask: function (taskId) {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.editCalendarTask(taskId);
	},
	markAsHeld: function (recordId) {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.markAsHeld(recordId);
	},
	holdFollowUp: function (eventId) {
		var instance = Calendar_Calendar_Js.getInstance();
		instance.holdFollowUp(eventId);
	}

}, {
	init: function () {
		this.addComponents();
	},
	addComponents: function () {
		this.addIndexComponent();
	},
	addIndexComponent: function () {
		this.addModuleSpecificComponent('Index', 'Vtiger', app.getParentModuleName());
	},
	registerCreateFollowUpEvent: function (modalContainer) {
		var thisInstance = this;
		var params = {
			submitHandler: function (form) {
				form = jQuery(form);
				form.find('[type="submit"]').attr('disabled', 'disabled');
				var formData = form.serializeFormData();
				app.helper.showProgress();
				app.request.post({'data': formData}).then(function (err, res) {
					app.helper.hideProgress();
					app.helper.hideModal();
					if (!err && res['created']) {
						jQuery('.vt-notification').remove();
						thisInstance.updateListView();
						thisInstance.updateCalendarView("Event");
					} else {
						app.event.trigger('post.save.failed', err);
					}
				});
			}
		};
		modalContainer.find('form#followupQuickCreate').vtValidate(params);
	},
	holdFollowUp: function (eventId) {
		var thisInstance = this;
		var requestParams = {
			'module': 'Calendar',
			'view': 'QuickCreateFollowupAjax',
			'record': eventId
		};
		app.helper.showProgress();
		app.request.get({'data': requestParams}).then(function (err, resp) {
			app.helper.hideProgress();
			if (!err && resp) {
				app.helper.showModal(resp, {
					'cb': function (modalContainer) {
						thisInstance.registerCreateFollowUpEvent(modalContainer);
					}
				});
			}
		});
	},
	updateListView: function () {
		if (app.view() === 'List') {
			var listInstance = Vtiger_List_Js.getInstance();
			listInstance.loadListViewRecords();
		}
	},
	updateCalendarView: function (activitytype) {
		if (app.view() === 'Calendar' || app.view() === 'SharedCalendar') {
			if (activitytype === 'Event') {
				this.updateAllEventsOnCalendar();
			} else {
				this.updateAllTasksOnCalendar();
			}
		}
	},
	markAsHeld: function (recordId) {
		var thisInstance = this;
		app.helper.showConfirmationBox({
			message: app.vtranslate('JS_CONFIRM_MARK_AS_HELD')
		}).then(function () {
			var requestParams = {
				module: "Calendar",
				action: "SaveFollowupAjax",
				mode: "markAsHeldCompleted",
				record: recordId
			};

			app.request.post({'data': requestParams}).then(function (e, res) {
				jQuery('.vt-notification').remove();
				if (e) {
					app.event.trigger('post.save.failed', e);
				} else if (res && res['valid'] === true && res['markedascompleted'] === true) {
					thisInstance.updateListView();
					thisInstance.updateCalendarView(res.activitytype);
				} else {
					app.helper.showAlertNotification({
						'message': app.vtranslate('JS_FUTURE_EVENT_CANNOT_BE_MARKED_AS_HELD')
					});
				}
			});
		});
	},
	registerCalendarSharingTypeChangeEvent: function (modalContainer) {
		var selectedUsersContainer = app.helper.getSelect2FromSelect(
				jQuery('#selectedUsers', modalContainer)
				);
		jQuery('[name="sharedtype"]').on('change', function () {
			var sharingType = jQuery(this).data('sharingtype');

			if (sharingType === 'selectedusers') {
				selectedUsersContainer.show();
				selectedUsersContainer.attr('style', 'display:block;width:90%;');
			} else {
				selectedUsersContainer.hide();
			}
		});
		jQuery('[name="sharedtype"]:checked').trigger('change');
	},
	registerHourFormatChangeEvent: function (modalContainer) {
		var hourFormatConditionMapping = jQuery('input[name="timeFormatOptions"]', modalContainer).data('value');
		var form = modalContainer.find('form');
		form.find('input[name="hour_format"]').on('click', function () {
			var hourFormatVal = jQuery(this).val();
			var startHourElement = jQuery('select[name="start_hour"]', form);
			var conditionSelected = startHourElement.val();
			var list = hourFormatConditionMapping['hour_format'][hourFormatVal]['start_hour'];
			var options = '';
			for (var key in list) {
				if (list.hasOwnProperty(key)) {
					var conditionValue = list[key];
					options += '<option value="' + key + '"';
					if (key === conditionSelected) {
						options += ' selected="selected" ';
					}
					options += '>' + conditionValue + '</option>';
				}
			}
			startHourElement.html(options).trigger("change");
		});
	},
	registerCalendarSettingsShownEvents: function (modalContainer) {
		this.registerCalendarSharingTypeChangeEvent(modalContainer);
		this.registerHourFormatChangeEvent(modalContainer);
		app.helper.showVerticalScroll(jQuery('.modal-body'), {setHeight: '400px'});
		vtUtils.enableTooltips();
		modalContainer.find('button[name="saveButton"]').on('click', function () {
			jQuery(this).attr('disabled', 'disabled');
			modalContainer.find('form').find('[name="sourceView"]').val(app.view());
			modalContainer.find('form').submit();
		});
	},
	showCalendarSettings: function () {
		var thisInstance = this;
		var params = {
			'module': app.getModuleName(),
			'view': 'Calendar',
			'mode': 'Settings'
		};
		app.helper.showProgress();
		app.request.post({'data': params}).then(function (e, data) {
			app.helper.hideProgress();
			if (!e) {
				app.helper.showModal(data, {
					'cb': function (modalContainer) {
						thisInstance.registerCalendarSettingsShownEvents(modalContainer);
					}
				});
			} else {
				console.log("network error : ", e);
			}
		});
	},
	getDisabledFeeds: function () {
		return app.storage.get(Calendar_Calendar_Js.disabledFeedsStorageKey, []);
	},
	disableFeed: function (sourceKey) {
		var disabledFeeds = this.getDisabledFeeds();
		if (disabledFeeds.indexOf(sourceKey) === -1) {
			disabledFeeds.push(sourceKey);
			app.storage.set(Calendar_Calendar_Js.disabledFeedsStorageKey, disabledFeeds);
		}
	},
	enableFeed: function (sourceKey) {
		var disabledFeeds = this.getDisabledFeeds();
		if (disabledFeeds.indexOf(sourceKey) !== -1) {
			disabledFeeds = jQuery.grep(disabledFeeds, function (value) {
				return value !== sourceKey;
			});
			app.storage.set(Calendar_Calendar_Js.disabledFeedsStorageKey, disabledFeeds);
		}
	},
	getFeedRequestParams: function (start, end, feedCheckbox) {
		var userFormat = jQuery('body').data('userDateformat').toUpperCase();
		var dateFormat = userFormat;
		var startDate = start.format(dateFormat);
		var endDate = end.format(dateFormat);
		return {
			'start': startDate,
			'end': endDate,
			'type': feedCheckbox.data('calendarFeed'),
			'fieldname': feedCheckbox.data('calendarFieldname'),
			'color': feedCheckbox.data('calendarFeedColor'),
			'textColor': feedCheckbox.data('calendarFeedTextcolor'),
			'conditions': feedCheckbox.data('calendarFeedConditions')
		};
	},
	renderEvents: function () {
		var thisInstance = this;
		this.getCalendarViewContainer().fullCalendar('addEventSource',
				function (start, end, timezone, render) {
					thisInstance.getCalendarViewContainer().fullCalendar('removeEvents');
					var activeFeeds = jQuery('input[data-calendar-feed]:checked');
					var activeFeedsRequestParams = {};
					activeFeeds.each(function () {
						var feedCheckbox = jQuery(this);
						var feedRequestParams = thisInstance.getFeedRequestParams(start, end, feedCheckbox);
						activeFeedsRequestParams[feedCheckbox.data('calendarSourcekey')] = feedRequestParams;
					});

					var requestParams = {
						'module': app.getModuleName(),
						'action': 'Feed',
						'mode': 'batch',
						'feedsRequest': activeFeedsRequestParams
					};
					var events = [];
					app.helper.showProgress();
					activeFeeds.attr('disabled', 'disabled');
					app.request.post({'data': requestParams}).then(function (e, data) {
						if (!e) {
							data = JSON.parse(data);
							for (var feedType in data) {
								var feed = JSON.parse(data[feedType]);
								feed.forEach(function (entry) {
									events.push(entry);
								});
							}
						} else {
							console.log("error in response : ", e);
						}
						render(events);
						activeFeeds.removeAttr('disabled');
						app.helper.hideProgress();
					});
				});
	},
	assignFeedTextColor: function (feedCheckbox) {
		var color = feedCheckbox.data('calendarFeedColor');
		var contrast = app.helper.getColorContrast(color);
		var textColor = (contrast === 'dark') ? 'white' : 'black';
		feedCheckbox.data('calendarFeedTextcolor', textColor);
		feedCheckbox.closest('.calendar-feed-indicator').css({'color': textColor});
	},
	colorizeFeed: function (feedCheckbox) {
		this.assignFeedTextColor(feedCheckbox);
	},
	restoreFeedsState: function (widgetContainer) {
		var thisInstance = this;
		var disabledFeeds = this.getDisabledFeeds();
		var feedsList = widgetContainer.find('#calendarview-feeds > ul.feedslist');
		var calendarfeeds = feedsList.find('[data-calendar-feed]');
		calendarfeeds.each(function () {
			var feedCheckbox = jQuery(this);
			var sourceKey = feedCheckbox.data('calendarSourcekey');
			if (disabledFeeds.indexOf(sourceKey) === -1) {
				feedCheckbox.attr('checked', true);
			}
			thisInstance.colorizeFeed(feedCheckbox);
		});
	},
	fetchEvents: function (feedCheckbox) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var view = thisInstance.getCalendarViewContainer().fullCalendar('getView');

		var feedRequestParams = thisInstance.getFeedRequestParams(view.start, view.end, feedCheckbox);
		feedRequestParams.module = app.getModuleName();
		feedRequestParams.action = 'Feed';

		var events = [];
		app.request.post({'data': feedRequestParams}).then(function (e, data) {
			if (!e) {
				events = JSON.parse(data);
				aDeferred.resolve(events);
			} else {
				aDeferred.reject(e);
			}
		});
		return aDeferred.promise();
	},
	addEvents: function (feedCheckbox) {
		var thisInstance = this;
		if (feedCheckbox.is(':checked')) {
			app.helper.showProgress();
			feedCheckbox.attr('disabled', 'disabled');
			thisInstance.fetchEvents(feedCheckbox).then(function (events) {
				thisInstance.getCalendarViewContainer().fullCalendar('addEventSource', events);
				feedCheckbox.removeAttr('disabled');
				app.helper.hideProgress();
			}, function (e) {
				console.log("error while fetching events : ", feedCheckbox, e);
			});
		}
	},
	removeEvents: function (feedCheckbox) {
		var module = feedCheckbox.data('calendarFeed');
		var conditions = feedCheckbox.data('calendarFeedConditions');
		var fieldName = feedCheckbox.data('calendarFieldname');
		this.getCalendarViewContainer().fullCalendar('removeEvents',
				function (eventObj) {
					return module === eventObj.module && eventObj.conditions === conditions && fieldName === eventObj.fieldName;
				});
	},
	registerFeedChangeEvent: function () {
		var thisInstance = this;
		jQuery('#calendarview-feeds').on('change',
				'input[type="checkbox"].toggleCalendarFeed',
				function () {
					var curentTarget = jQuery(this);
					var sourceKey = curentTarget.data('calendarSourcekey');
					if (curentTarget.is(':checked')) {
						thisInstance.enableFeed(sourceKey);
						thisInstance.addEvents(curentTarget);
					} else {
						thisInstance.disableFeed(sourceKey);
						thisInstance.removeEvents(curentTarget);
					}
				});
	},
	updateRangeFields: function (container, options) {
		var moduleName = container.find('select[name="modulesList"]').val();
		var fieldSelectElement = container.find('select[name="fieldsList"]');

		var sourceFieldSelect = container.find('select[name="sourceFieldsList"]');
		var targetFieldSelect = container.find('select[name="targetFieldsList"]');
		fieldSelectElement.removeAttr('disabled');

		var optionsCount = fieldSelectElement.find('option').not('option[value="birthday"]');

		if (moduleName === 'Events' || moduleName === 'Calendar') {
			optionsCount = fieldSelectElement.find('option').not('option[value="date_start,due_date"]');
		}

		if (optionsCount.length > 1) {
			container.find('[name="rangeFields"]').removeAttr('disabled').trigger('change');
		} else {
			container.find('[name="rangeFields"]').attr('disabled', true).attr('checked', false).trigger('change');
		}

		var selectedValue = fieldSelectElement.find('option:selected').val();
		sourceFieldSelect.select2('destroy').html(options).select2();
		targetFieldSelect.select2('destroy').html(options).select2();

		if (moduleName === 'Events' || moduleName === 'Calendar') {
			sourceFieldSelect.find('option[value="date_start,due_date"]').remove();
			targetFieldSelect.find('option[value="date_start,due_date"]').remove();
		}
		sourceFieldSelect.find('option[value="birthday"]').remove();
		targetFieldSelect.find('option[value="birthday"]').remove();
		if (selectedValue === 'birthday') {
			selectedValue = fieldSelectElement.find('option:selected').next().val();
		}
		var otherOption = targetFieldSelect.find('option').not('option[value="' + selectedValue + '"]');
		sourceFieldSelect.select2('val', selectedValue);
		if (otherOption.length > 0) {
			targetFieldSelect.select2('val', otherOption.val());
		} else {
			targetFieldSelect.select2('destroy').html('').select2();
		}
	},
	updateDateFields: function (container) {
		var fieldMeta = container.find('[name="moduleDateFields"]').data('value');
		var moduleSelectElement = container.find('select[name="modulesList"]');
		var moduleName = moduleSelectElement.val();

		var fieldSelectElement = container.find('select[name="fieldsList"]');

		var options = '';
		for (var key in fieldMeta) {
			if (fieldMeta.hasOwnProperty(key) && key === moduleName) {
				var moduleSpecificFields = fieldMeta[key];
				for (var fieldName in moduleSpecificFields) {
					if (moduleSpecificFields.hasOwnProperty(fieldName)) {
						options += '<option value="' + fieldName + '" data-viewfieldname="' + fieldName + '">' +
								moduleSpecificFields[fieldName] + '</option>';
					}
				}
			}
		}
		if (options === '')
			options = '<option value="">NONE</option>';

		fieldSelectElement.select2('destroy').html(options).select2().trigger('change');

		var editorMode = container.find('.editorMode').val();
		if (editorMode === 'create') {
			this.updateRangeFields(container, options);
		}
	},
	initializeColorPicker: function (element, customParams, onChangeFunc) {
		var params = {
			flat: true,
			onChange: onChangeFunc
		};
		if (typeof customParams !== 'undefined') {
			params = jQuery.extend(params, customParams);
		}
		element.ColorPicker(params);
	},
	getRandomColor: function () {
		return '#' + (0x1000000 + (Math.random()) * 0xffffff).toString(16).substr(1, 6);
	},
	registerDateFieldChangeEvent: function (modalContainer) {
		var thisInstance = this;
		var parentElement = jQuery('#calendarview-feeds');
		var fieldsSelect = modalContainer.find('[name="fieldsList"]');

		fieldsSelect.on('change', function () {
			var moduleName = modalContainer.find('[name="modulesList"]').find('option:selected').val();
			var selectedOption = jQuery(this).find('option:selected');
			var fieldName = selectedOption.val();
			var currentColor = thisInstance.getRandomColor();

			var calendarSourceKey = moduleName + '_' + fieldName;
			if (moduleName === 'Events') {
				var conditions = modalContainer.find('#calendarviewconditions').val();
				conditions = thisInstance._getParsedConditions(conditions);
				if (conditions.hasOwnProperty('value')) {
					calendarSourceKey += '_' + conditions.value;
				}
			}

			var feedCheckbox = jQuery('[data-calendar-sourcekey="' + calendarSourceKey + '"]', parentElement);
			if (feedCheckbox.length) {
				currentColor = feedCheckbox.data('calendarFeedColor');
			}
			modalContainer.find('.selectedColor').val(currentColor);
			modalContainer.find('.calendarColorPicker').ColorPickerSetColor(currentColor);
		});
		modalContainer.find('#calendarviewconditions').on('change', function () {
			fieldsSelect.trigger('change');
		});
	},
	_getParsedConditions: function (conditions) {
		var parsedConditions = {};
		if (conditions !== '') {
			parsedConditions = JSON.parse(conditions);
			if (typeof parsedConditions !== 'object') {
				parsedConditions = JSON.parse(parsedConditions);
			}
		}
		return parsedConditions;
	},
	saveFeedSettings: function (modalContainer, feedIndicator) {
		var thisInstance = this;
		var modulesList = modalContainer.find('select[name="modulesList"]');
		var moduleName = modulesList.val();
		var fieldName = modalContainer.find('select[name="fieldsList"]').val();
		var selectedColor = modalContainer.find('input.selectedColor').val();
		var conditions = '';
		if (moduleName === 'Events') {
			conditions = modalContainer.find('[name="conditions"]').val();
			if (conditions !== '') {
				conditions = JSON.stringify(conditions);
			}
		}

		var editorMode = modalContainer.find('.editorMode').val();
		if (editorMode === 'create') {
			var translatedFieldName = modalContainer.find('.selectedType').data('typename');
			if (modalContainer.find('[name="rangeFields"]').is(':checked')) {
				var sourceValue = modalContainer.find('[name="sourceFieldsList"]').val();
				var targetValue = modalContainer.find('[name="targetFieldsList"]').val();
				fieldName = sourceValue + ',' + targetValue;
				translatedFieldName = modalContainer.find('[name="sourceFieldsList"] option:selected').text() + ',' + modalContainer.find('[name="targetFieldsList"] option:selected').text();
			}
		}

		var params = {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode: 'addCalendarView',
			viewmodule: moduleName,
			viewfieldname: fieldName,
			viewColor: selectedColor,
			viewConditions: conditions
		};

		app.helper.showProgress();
		app.request.post({'data': params}).then(function (e, data) {
			if (!e) {
				var contrast = app.helper.getColorContrast(selectedColor);
				var textColor = (contrast === 'dark') ? 'white' : 'black';
				var message = app.vtranslate('JS_CALENDAR_VIEW_COLOR_UPDATED_SUCCESSFULLY');
				var parsedConditions = thisInstance._getParsedConditions(conditions);
				var feedIndicatorTitle = moduleName + '-' + translatedFieldName;
				var calendarSourceKey = moduleName + '_' + fieldName;

				if (parsedConditions.hasOwnProperty('value')) {
					calendarSourceKey += '_' + parsedConditions.value;
					feedIndicatorTitle = moduleName + '(' + app.vtranslate(parsedConditions.value) + ') -' + translatedFieldName;
				}

				if (editorMode === 'create') {
					var translatedModuleName = modulesList.find('option:selected').text();
					var feedIndicatorTemplate = jQuery('#calendarview-feeds').find('ul.dummy > li.feed-indicator-template');
					feedIndicatorTemplate.removeClass('.feed-indicator-template');
					var newFeedIndicator = feedIndicatorTemplate.clone(true, true);
					//replacing module name prefix with translated module name and concatinating with field name
					var feedIndicatorModuleEndIndex = feedIndicatorTitle.indexOf('('); // Events (ActivityType) - title...
					if (feedIndicatorModuleEndIndex == -1) { // ModuleName - title...
							feedIndicatorModuleEndIndex = feedIndicatorTitle.indexOf('-');
					}
					feedIndicatorTitle = translatedModuleName + feedIndicatorTitle.substr(feedIndicatorModuleEndIndex);
					newFeedIndicator.find('span:first').text(feedIndicatorTitle);
					var newFeedCheckbox = newFeedIndicator.find('.toggleCalendarFeed');
					newFeedCheckbox.attr('data-calendar-sourcekey', calendarSourceKey).
							attr('data-calendar-feed', moduleName).
							attr('data-calendar-fieldlabel', translatedFieldName).
							attr('data-calendar-fieldname', fieldName).
							attr('title', translatedModuleName).
							attr('checked', 'checked');
					if (data['type']) {
						newFeedCheckbox.attr('data-calendar-type', data['type']);
					}
					feedIndicator = newFeedIndicator;
					jQuery('#calendarview-feeds').find('ul:first').append(feedIndicator);
					message = app.vtranslate('JS_CALENDAR_VIEW_ADDED_SUCCESSFULLY');
				} else {
					feedIndicator = jQuery('#calendarview-feeds')
							.find('[data-calendar-sourcekey="' + calendarSourceKey + '"]')
							.closest('.calendar-feed-indicator');
				}

				feedIndicator.css({'background-color': selectedColor, 'color': textColor});
				var feedCheckbox = feedIndicator.find('.toggleCalendarFeed');
				feedCheckbox.data('calendarFeedColor', selectedColor).
						data('calendarFeedTextcolor', textColor).
						data('calendarFeedConditions', conditions);
				thisInstance.refreshFeed(feedCheckbox);

				app.helper.hideProgress();
				app.helper.hideModal();
				app.helper.showSuccessNotification({'message': message});
			} else {
				console.log("error occured while saving : ", params, e);
			}
		});
	},
	registerColorEditorSaveEvent: function (modalContainer, feedIndicator) {
		var thisInstance = this;
		modalContainer.find('[name="saveButton"]').on('click', function () {
			var currentTarget = jQuery(this);
			currentTarget.attr('disabled', 'disabled');
			var modulesSelect = modalContainer.find('select[name="modulesList"]');
			var fieldsSelect = modalContainer.find('select[name="fieldsList"]');
			var selectedType = modalContainer.find('.selectedType');

			var moduleName = modulesSelect.val();
			var fieldName = fieldsSelect.val();

			selectedType.val(fieldName).data(
					'typename',
					fieldsSelect.find('option:selected').text()
					);

			var selectedColor = modalContainer.find('.selectedColor').val(),
					conditions = '';
			if (moduleName === 'Events') {
				conditions = modalContainer.find('[name="conditions"]').val();
				if (conditions !== '') {
					conditions = JSON.stringify(conditions);
				}
			}

			thisInstance.checkDuplicateFeed(moduleName, fieldName, selectedColor, conditions).then(
					function (result) {
						thisInstance.saveFeedSettings(modalContainer, feedIndicator);
					},
					function () {
						app.helper.showErrorNotification({'message': app.vtranslate('JS_CALENDAR_VIEW_YOU_ARE_EDITING_NOT_FOUND')});
						currentTarget.removeAttr('disabled');
					});
		});
	},
	registerColorEditorEvents: function (modalContainer, feedIndicator) {
		var thisInstance = this;
		var feedCheckbox = feedIndicator.find('input[type="checkbox"].toggleCalendarFeed');

		var colorPickerHost = modalContainer.find('.calendarColorPicker');
		var selectedColor = modalContainer.find('.selectedColor');
		thisInstance.initializeColorPicker(colorPickerHost, {}, function (hsb, hex, rgb) {
			var selectedColorCode = '#' + hex;
			selectedColor.val(selectedColorCode);
		});

		thisInstance.registerDateFieldChangeEvent(modalContainer);

		var modulesSelect = modalContainer.find('[name="modulesList"]');
		modulesSelect.on('change', function () {
			thisInstance.updateDateFields(modalContainer);
//handling eventtype condition element display
			var module = jQuery(this).val();
			if (module === 'Events') {
				modalContainer.find('#js-eventtype-condition').removeClass('hide');
				var feedConditions = feedCheckbox.data('calendarFeedConditions');
				if (feedConditions !== '') {
					modalContainer.find('[name="conditions"]').val(JSON.parse(feedConditions)).trigger('change');
				}
			} else {
				modalContainer.find('#js-eventtype-condition').addClass('hide');
			}
		}).select2('val', feedCheckbox.data('calendarFeed')).trigger('change');

		var fieldSelectElement = modalContainer.find('[name="fieldsList"]');
		fieldSelectElement.select2('val', feedCheckbox.data('calendarFieldname')).trigger('change');

		thisInstance.registerColorEditorSaveEvent(modalContainer, feedIndicator);
	},
	showColorEditor: function (feedIndicator) {
		var thisInstance = this;
		var params = {
			module: app.getModuleName(),
			view: 'ActivityTypeViews',
			mode: 'editActivityType'
		};
		app.helper.showProgress();
		app.request.post({'data': params}).then(function (e, data) {
			app.helper.hideProgress();
			if (!e) {
				app.helper.showModal(data, {
					'cb': function (modalContainer) {
						thisInstance.registerColorEditorEvents(modalContainer, feedIndicator);
					}
				});
			} else {
				console.log("network error : ", e);
			}
		});
	},
	registerFeedsColorEditEvent: function () {
		var thisInstance = this;
		jQuery('#calendarview-feeds').on('click', '.editCalendarFeedColor',
				function () {
					var feedIndicator = jQuery(this).closest('li.calendar-feed-indicator');
					thisInstance.showColorEditor(feedIndicator);
				});
	},
	getFeedDeleteParameters: function (feedCheckbox) {
		return {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode: 'deleteCalendarView',
			viewmodule: feedCheckbox.data('calendarFeed'),
			viewfieldname: feedCheckbox.data('calendarFieldname'),
			viewfieldlabel: feedCheckbox.data('calendarFieldlabel'),
			viewConditions: feedCheckbox.data('calendarFeedConditions')
		};
	},
	deleteFeed: function (feedIndicator) {
		var thisInstance = this;
		var feedCheckbox = feedIndicator.find('input[type="checkbox"].toggleCalendarFeed');
		var params = thisInstance.getFeedDeleteParameters(feedCheckbox);

		app.helper.showProgress();
		app.request.post({'data': params}).then(function (e) {
			if (!e) {
				thisInstance.removeEvents(feedCheckbox);
				feedIndicator.remove();
				app.helper.showSuccessNotification({
					message: app.vtranslate('JS_CALENDAR_VIEW_DELETED_SUCCESSFULLY')
				});
			} else {
				console.log("error : ", e);
			}
			app.helper.hideProgress();
		});
	},
	registerFeedDeleteEvent: function () {
		var thisInstance = this;
		jQuery('#calendarview-feeds').on('click', '.deleteCalendarFeed',
				function () {
					var feedIndicator = jQuery(this).closest('.calendar-feed-indicator');
					app.helper.showConfirmationBox({
						message: app.vtranslate('JS_CALENDAR_VIEW_DELETE_CONFIRMATION')
					}).then(function () {
						thisInstance.deleteFeed(feedIndicator);
					});
				});
	},
	checkDuplicateFeed: function (moduleName, fieldName, selectedColor, conditions) {
		var aDeferred = jQuery.Deferred();
		var params = {
			'module': 'Calendar',
			'action': 'CalendarUserActions',
			'mode': 'checkDuplicateView',
			'viewmodule': moduleName,
			'viewfieldname': fieldName,
			'viewColor': selectedColor,
			'viewConditions': conditions
		};
		app.request.post({'data': params}).then(function (e, result) {
			if (!e) {
				if (result['success']) {
					aDeferred.resolve(result);
				} else {
					aDeferred.reject(result);
				}
			} else {
				console.log("error : ", e);
			}
		});
		return aDeferred.promise();
	},
	registerAddActivityTypeEvent: function (modalContainer) {
		var thisInstance = this;
		modalContainer.find('[name="saveButton"]').on('click', function () {
			var currentTarget = jQuery(this);
			currentTarget.attr('disabled', 'disabled');
			var fieldSelect = modalContainer.find('select[name="fieldsList"]');
			var selectedType = modalContainer.find('.selectedType');
			selectedType.val(fieldSelect.val()).data(
					'typename',
					fieldSelect.find('option:selected').text()
					);
			var moduleName = modalContainer.find('select[name="modulesList"]').val();
			var fieldName = fieldSelect.val();
			if (modalContainer.find('[name="rangeFields"]').is(':checked')) {
				var sourceValue = modalContainer.find('[name="sourceFieldsList"]').val();
				var targetValue = modalContainer.find('[name="targetFieldsList"]').val();
				fieldName = sourceValue + ',' + targetValue;
			}
			var selectedColor = modalContainer.find('.selectedUserColor').val(),
					conditions = '';
			if (moduleName === 'Events') {
				conditions = modalContainer.find('[name="conditions"]').val();
				if (conditions !== '') {
					conditions = JSON.stringify(conditions);
				}
			}

			thisInstance.checkDuplicateFeed(moduleName, fieldName, selectedColor, conditions).then(
					function(result) {
					    app.helper.showErrorNotification({'message':result['message']});
					    currentTarget.removeAttr('disabled');
					},
					function() {
					    thisInstance.saveFeedSettings(modalContainer);
					});
		});
	},
	registerAddActivityTypeFeedActions: function (modalContainer) {
		var thisInstance = this;
		var colorPickerHost = modalContainer.find('.calendarColorPicker');
		var selectedColor = modalContainer.find('.selectedColor');
		thisInstance.initializeColorPicker(colorPickerHost, {}, function (hsb, hex, rgb) {
			var selectedColorCode = '#' + hex;
			selectedColor.val(selectedColorCode);
		});

		thisInstance.registerDateFieldChangeEvent(modalContainer);

		var modulesSelect = modalContainer.find('[name="modulesList"]');
		modulesSelect.on('change', function () {
			thisInstance.updateDateFields(modalContainer);
//handling eventtype condition element display
			var module = jQuery(this).val();
			if (module === 'Events') {
				modalContainer.find('#js-eventtype-condition').removeClass('hide');
			} else {
				modalContainer.find('#js-eventtype-condition').addClass('hide');
			}
		}).trigger('change');

		var sourceFieldsSelect = modalContainer.find('select[name="sourceFieldsList"]');
		sourceFieldsSelect.on('change', function () {
			var selectedValue = sourceFieldsSelect.find('option:selected').val();
			if (selectedValue === targetFieldsSelect.find('option:selected').val()) {
				var otherOption = targetFieldsSelect.find('option').not('option[value="' + selectedValue + '"]');
				targetFieldsSelect.select2('val', otherOption.val());
			}
		});

		var targetFieldsSelect = modalContainer.find('select[name="targetFieldsList"]');
		targetFieldsSelect.on('change', function () {
			var selectedValue = targetFieldsSelect.find('option:selected').val();
			if (selectedValue === sourceFieldsSelect.find('option:selected').val()) {
				var otherOption = sourceFieldsSelect.find('option').not('option[value="' + selectedValue + '"]');
				sourceFieldsSelect.select2('val', otherOption.val());
			}
		});

		var rangeFieldsOption = modalContainer.find('[name="rangeFields"]');
		rangeFieldsOption.on('change', function () {
			var fieldSelectEle = modalContainer.find('select[name="fieldsList"]');
			var sourceFieldsSelect = modalContainer.find('select[name="sourceFieldsList"]');
			var targetFieldsSelect = modalContainer.find('select[name="targetFieldsList"]');
			if (rangeFieldsOption.is(':checked')) {
				fieldSelectEle.attr('disabled', true);
				sourceFieldsSelect.removeAttr('disabled');
				targetFieldsSelect.removeAttr('disabled');
			} else {
				fieldSelectEle.removeAttr('disabled');
				sourceFieldsSelect.attr('disabled', true);
				targetFieldsSelect.attr('disabled', true);
			}

			//after disabling or enabling, set the options and selected value for select2 elements
			var fieldSelectedValue = fieldSelectEle.find('option:selected').val();
			var fieldOptions = fieldSelectEle.find('option');
			fieldSelectEle.select2('destroy').html(fieldOptions).select2();
			fieldSelectEle.select2('val', fieldSelectedValue);

			var sourceOptions = sourceFieldsSelect.find('option');
			sourceFieldsSelect.select2('destroy').html(sourceOptions).select2();
			sourceFieldsSelect.select2('val', fieldSelectedValue);

			var sourceSelectValue = sourceFieldsSelect.find('option:selected').val();
			var otherOption = targetFieldsSelect.find('option').not('option[value="' + sourceSelectValue + '"]');
			var targetOptions = targetFieldsSelect.find('option');
			targetFieldsSelect.select2('destroy').html(targetOptions).select2();
			targetFieldsSelect.select2('val', otherOption.val());
		});

		thisInstance.registerAddActivityTypeEvent(modalContainer);
	},
	showAddActivityTypeFeedView: function () {
		var thisInstance = this;
		var params = {
			module: app.getModuleName(),
			view: 'ActivityTypeViews',
			mode: 'addActivityType'
		};
		app.helper.showProgress();
		app.request.post({'data': params}).then(function (e, data) {
			app.helper.hideProgress();
			if (!e) {
				app.helper.showModal(data, {
					'cb': function (modalContainer) {
						thisInstance.registerAddActivityTypeFeedActions(modalContainer);
					}
				});
			} else {
				console.log("network error : ", e);
			}
		});
	},
	showAddCalendarFeedEditor: function () {
		this.showAddActivityTypeFeedView();
	},
	registerFeedAddEvent: function (widgetContainer) {
		var thisInstance = this;
		widgetContainer.find('.add-calendar-feed').on('click', function () {
			thisInstance.showAddCalendarFeedEditor();
		});
	},
	registerWidgetPostLoadEvent: function () {
		var thisInstance = this;
		app.event.on(Calendar_Calendar_Js.feedsWidgetPostLoadEvent,
				function (e, widgetContainer) {
					thisInstance.restoreFeedsState(widgetContainer);
					thisInstance.renderEvents();
					thisInstance.registerFeedAddEvent(widgetContainer);
					thisInstance.registerFeedChangeEvent();
					thisInstance.registerFeedsColorEditEvent();
					thisInstance.registerFeedDeleteEvent();
					thisInstance.registerFeedMassSelectEvent();
				});
	},

	/**
	 * Event listener for change on mass select checkbox.
	 * Click/set true/false on all non-matching checkboxes & 
	 * trigger change event. Contributed by Libertus Solutions
	**/
	registerFeedMassSelectEvent : function() {
		var container = jQuery('#calendarview-feeds');
		var calendarFeeds = jQuery('ul.feedslist input.toggleCalendarFeed', container);
		jQuery('input.mass-select', container).on('change', function() {
			var massSelectchecked = this.checked;
			calendarFeeds.each(function(i) {
				// Only trigger change where necessary
				if(this.checked != massSelectchecked) {
					this.checked = massSelectchecked;
					jQuery(this).change();
				}
			});
		});
	},

	changeWidgetDisplayState: function (widget, state) {
		var key = widget.data('widgetName') + '_WIDGET_DISPLAY_STATE';
		app.storage.set(key, state);
	},
	registerCollapseEvents: function (widget) {
		var thisInstance = this;
		widget.on('show.bs.collapse hide.bs.collapse', function (e) {
			var widgetStateIndicator = widget.find('i.widget-state-indicator');
			if (e.type === 'hide') {
				widgetStateIndicator.removeClass('fa-chevron-down').addClass('fa-chevron-right');
				thisInstance.changeWidgetDisplayState(widget, 'hide');
			} else {
				widgetStateIndicator.removeClass('fa-chevron-right').addClass('fa-chevron-down');
				thisInstance.changeWidgetDisplayState(widget, 'show');
			}
		});
	},
	getWidgetDisplayState: function (widget) {
		var key = widget.data('widgetName') + '_WIDGET_DISPLAY_STATE';
		var value = app.storage.get(key);
		return (value !== null) ? value : 'show';
	},
	restoreWidgetState: function (widget) {
		if (this.getWidgetDisplayState(widget) === 'show') {
			widget.find('.sidebar-widget-header > a').trigger('click');
		}
	},
	initializeWidgets: function () {
		var thisInstance = this;
		var widgets = jQuery('.sidebar-widget');
		jQuery.each(widgets, function () {
			var widget = jQuery(this);
			var widgetHeader = widget.find('.sidebar-widget-header');
			var dataUrl = widgetHeader.data('url');
			var dataParams = app.convertUrlToDataParams(dataUrl);
			var widgetBody = widget.find('.sidebar-widget-body');
			app.request.post({data: dataParams}).then(function (e, data) {
				if (!e) {
					widgetBody.html(data);
                                        let fullCalendarViewHeight = $('.fc-view-container').height();
                                        widgetBody.css('max-height', (fullCalendarViewHeight - 10) + 'px');
					app.helper.showVerticalScroll(
							widgetBody,
							{
								'autoHideScrollbar': true,
								'scrollbarPosition': 'outside'
							}
					);
//thisInstance.registerCollapseEvents(widget);
//thisInstance.restoreWidgetState(widget);
					app.event.trigger(Calendar_Calendar_Js.feedsWidgetPostLoadEvent, widget);
				} else {
					console.log("error in response : ", e);
				}
			});
		});
	},
	getCalendarViewContainer: function () {
		if (!Calendar_Calendar_Js.calendarViewContainer.length) {
			Calendar_Calendar_Js.calendarViewContainer = jQuery('#mycalendar');
		}
		return Calendar_Calendar_Js.calendarViewContainer;
	},
	getUserPrefered: function (setting) {
		if (typeof Calendar_Calendar_Js.userPreferenceCache[setting] === 'undefined') {
			Calendar_Calendar_Js.userPreferenceCache[setting] = jQuery('#' + setting).val();
		}
		return Calendar_Calendar_Js.userPreferenceCache[setting];
	},
	transformToEventObject: function (eventData, feedCheckbox) {
		var eventObject = {};
		eventObject.id = eventData._recordId;
		eventObject.title = eventData.subject.display_value;

		eventObject.start = eventData.date_start.calendar_display_value;
		eventObject.end = eventData.due_date.calendar_display_value;

		eventObject.url = 'index.php?module=Calendar&view=Detail&record=' + eventData._recordId;

		var module = feedCheckbox.data('calendarFeed');
		var color = feedCheckbox.data('calendarFeedColor');
		var textColor = feedCheckbox.data('calendarFeedTextcolor');

		eventObject.activitytype = eventData.activitytype.value;
		eventObject.status = eventData.eventstatus.value;
		eventObject.allDay = false;
		eventObject.module = module;

		eventObject.color = color;
		eventObject.textColor = textColor;
		return eventObject;
	},
	updateAgendaListView: function () {
		var calendarView = this.getCalendarViewContainer().fullCalendar('getView');
		if (calendarView.name === 'vtAgendaList') {
			this.getCalendarViewContainer().fullCalendar('rerenderEvents');
		}
	},
	updateAllEventsOnCalendar: function () {
		this._updateAllOnCalendar("Events");
		this.updateAgendaListView();
	},
	showEventOnCalendar: function (eventData) {
//method 1
//var feedCheckbox = jQuery('[data-calendar-type="Events_1"]');
//var eventObject = this.transformToEventObject(eventData,feedCheckbox);
//this.getCalendarViewContainer().fullCalendar('renderEvent',eventObject);

//method 2
//var thisInstance = this;
//var eventFeeds = jQuery('[data-calendar-feed="Events"]');
//eventFeeds.each(function(i, eventFeed) {
//thisInstance.refreshFeed(jQuery(eventFeed));
//});

//method 3 - Need to update all events, 
//since support for multiple calendar views for events is enabled
		this.updateAllEventsOnCalendar();
	},
	validateAndSaveEvent: function (modalContainer) {
		var thisInstance = this;
		var params = {
			submitHandler: function (form) {
				jQuery("button[name='saveButton']").attr("disabled", "disabled");
				if (this.numberOfInvalids() > 0) {
					return false;
				}
				var e = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
				app.event.trigger(e);
				if (e.isDefaultPrevented()) {
					return false;
				}
				var formData = jQuery(form).serialize();
				app.helper.showProgress();
				app.request.post({data: formData}).then(function (err, data) {
					app.helper.hideProgress();
					if (!err) {
						jQuery('.vt-notification').remove();
						app.helper.hideModal();
						var message = typeof formData.record !== 'undefined' ? app.vtranslate('JS_EVENT_UPDATED') : app.vtranslate('JS_RECORD_CREATED');
						app.helper.showSuccessNotification({"message": message});
						thisInstance.showEventOnCalendar(data);
					} else {
						app.event.trigger('post.save.failed', err);
						jQuery("button[name='saveButton']").removeAttr('disabled');
					}
				});
			}
		};
		modalContainer.find('form').vtValidate(params);
	},
	registerCreateEventModalEvents: function (modalContainer) {
		this.validateAndSaveEvent(modalContainer);
	},
	setStartDateTime: function (modalContainer, startDateTime) {
		var startDateElement = modalContainer.find('input[name="date_start"]');
		var startTimeElement = modalContainer.find('input[name="time_start"]');
		startDateElement.val(startDateTime.format(vtUtils.getMomentDateFormat()));
		startTimeElement.val(startDateTime.format(vtUtils.getMomentTimeFormat()));
		vtUtils.registerEventForDateFields(startDateElement);
		vtUtils.registerEventForTimeFields(startTimeElement);
		startDateElement.trigger('change');
	},
	showCreateModal: function (moduleName, startDateTime) {
		var isAllowed = jQuery('#is_record_creation_allowed').val();
		if (isAllowed) {
			var thisInstance = this;
			var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="' + moduleName + '"]');
			if (quickCreateNode.length <= 0) {
				app.helper.showAlertNotification({
					'message': app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED')
				});
			} else {
				quickCreateNode.trigger('click');
			}

			app.event.one('post.QuickCreateForm.show', function (e, form) {
				thisInstance.performingDayClickOperation = false;
				var modalContainer = form.closest('.modal');
				if (typeof startDateTime !== 'undefined' && startDateTime) {
					thisInstance.setStartDateTime(modalContainer, startDateTime);
				}
				if (moduleName === 'Events') {
					thisInstance.registerCreateEventModalEvents(form.closest('.modal'));
				}
			});
		}
	},
	_updateAllOnCalendar: function (calendarModule) {
		var thisInstance = this;
		this.getCalendarViewContainer().fullCalendar('addEventSource',
				function (start, end, timezone, render) {
					var activeFeeds = jQuery('[data-calendar-feed="' + calendarModule + '"]:checked');

					var activeFeedsRequestParams = {};
					activeFeeds.each(function () {
						var feedCheckbox = jQuery(this);
						var feedRequestParams = thisInstance.getFeedRequestParams(start, end, feedCheckbox);
						activeFeedsRequestParams[feedCheckbox.data('calendarSourcekey')] = feedRequestParams;
					});

					if (activeFeeds.length) {
						var requestParams = {
							'module': app.getModuleName(),
							'action': 'Feed',
							'mode': 'batch',
							'feedsRequest': activeFeedsRequestParams
						};
						var events = [];
						app.helper.showProgress();
						activeFeeds.attr('disabled', 'disabled');
						app.request.post({'data': requestParams}).then(function (e, data) {
							if (!e) {
								data = JSON.parse(data);
								for (var feedType in data) {
									var feed = JSON.parse(data[feedType]);
									feed.forEach(function (entry) {
										events.push(entry);
									});
								}
							} else {
								console.log("error in response : ", e);
							}
							activeFeeds.each(function () {
								var feedCheckbox = jQuery(this);
								thisInstance.removeEvents(feedCheckbox);
							});
							render(events);
							activeFeeds.removeAttr('disabled');
							app.helper.hideProgress();
						});
					}
				});
	},
	showCreateTaskModal: function () {
		this.showCreateModal('Calendar');
	},
	showCreateEventModal: function (startDateTime) {
		this.showCreateModal('Events', startDateTime);
	},
	updateAllTasksOnCalendar: function () {
		this._updateAllOnCalendar("Calendar");
	},
	showTaskOnCalendar: function (data) {
		this.updateAllTasksOnCalendar();
	},
	updateCalendar: function (calendarModule, data) {
		if (calendarModule === 'Events') {
			this.showEventOnCalendar(data);
		} else if (calendarModule === 'Calendar') {
			this.showTaskOnCalendar(data);
		}
	},
	registerPostQuickCreateSaveEvent: function () {
		var thisInstance = this;
		app.event.on("post.QuickCreateForm.save", function (e, data, formData) {
			if (formData.module === 'Calendar' || formData.module === 'Events') {
				thisInstance.updateCalendar(formData.calendarModule, data);
			}
		});
	},
	performingDayClickOperation: false,
	performDayClickAction: function (date, jsEvent, view) {
		if (!this.performingDayClickOperation) {
			this.performingDayClickOperation = true;
			if (date.hasTime() || view.type == 'month') {
				this.showCreateEventModal(date);
			} else {
				this.showCreateTaskModal();
			}
		}
	},
	daysOfWeek: {
		Sunday: 0,
		Monday: 1,
		Tuesday: 2,
		Wednesday: 3,
		Thursday: 4,
		Friday: 5,
		Saturday: 6
	},
	refreshFeed: function (feedCheckbox) {
		var thisInstance = this;
		if (feedCheckbox.is(':checked')) {
			feedCheckbox.attr('disabled', 'disabled');
			thisInstance.fetchEvents(feedCheckbox).then(function (events) {
				thisInstance.removeEvents(feedCheckbox);
				thisInstance.getCalendarViewContainer().fullCalendar('addEventSource', events);
				feedCheckbox.removeAttr('disabled');
			}, function (e) {
				console.log("error while fetching events : ", feedCheckbox, e);
			});
		}
	},
	_updateEventOnResize: function (postData, revertFunc) {
		var thisInstance = this;
		app.helper.showProgress();
		app.request.post({'data': postData}).then(function (e, resp) {
			app.helper.hideProgress();
			if (!e) {
				jQuery('.vt-notification').remove();
				if (!resp['ispermitted']) {
					revertFunc();
					app.helper.showErrorNotification({
						'message': app.vtranslate('JS_NO_EDIT_PERMISSION')
					});
				} else if (resp['error']) {
					revertFunc();
				} else {
					if (resp['recurringRecords'] === true) {
						thisInstance.updateAllEventsOnCalendar();
					}
					app.helper.showSuccessNotification({
						'message': app.vtranslate('JS_EVENT_UPDATED')
					});
				}
			} else {
				app.event.trigger('post.save.failed', e);
				thisInstance.updateAllEventsOnCalendar();
			}
		});
	},
	updateEventOnResize: function (event, delta, revertFunc, jsEvent, ui, view) {
		var thisInstance = this;
		if (event.module !== 'Calendar' && event.module !== 'Events') {
			revertFunc();
			return;
		}

		var postData = {
			'module': app.getModuleName(),
			'action': 'DragDropAjax',
			'mode': 'updateDeltaOnResize',
			'id': event.id,
			'activitytype': event.activitytype,
			'secondsDelta': delta.asSeconds(),
			'view': view.name,
			'userid': event.userid
		};

		if (event.recurringcheck) {
			app.helper.showConfirmationForRepeatEvents().then(function (recurringData) {
				jQuery.extend(postData, recurringData);
				thisInstance._updateEventOnResize(postData, revertFunc);
			});
		} else {
			thisInstance._updateEventOnResize(postData, revertFunc);
		}
	},
	updateEventOnDrop: function (event, delta, revertFunc, jsEvent, ui, view) {
		var thisInstance = this;
		if (event.module !== 'Calendar' && event.module !== 'Events') {
			revertFunc();
			return;
		}

		var postData = {
			'module': 'Calendar',
			'action': 'DragDropAjax',
			'mode': 'updateDeltaOnDrop',
			'id': event.id,
			'activitytype': event.activitytype,
			'secondsDelta': delta.asSeconds(),
			'view': view.name,
			'userid': event.userid
		};

		if (event.recurringcheck) {
			app.helper.showConfirmationForRepeatEvents().then(function (recurringData) {
				jQuery.extend(postData, recurringData);
				thisInstance._updateEventOnResize(postData, revertFunc);
			});
		} else {
			thisInstance._updateEventOnResize(postData, revertFunc);
		}
	},
	getActivityTypeClassName: function (activitytype) {
		var className = 'fa fa-calendar';
		switch (activitytype) {
			case 'Meeting' :
				className = 'vicon-meeting';
				break;
			case 'Call' :
				className = 'fa fa-phone';
				break;
			case 'Mobile Call' :
				className = 'fa fa-mobile';
				break;
		}
		return className;
	},
	addActivityTypeIcons: function (event, element) {
		element.find('.fc-content > .fc-time').prepend(
				'<span>' +
				'<i class="' + this.getActivityTypeClassName(event.activitytype) + '"></i>' +
				'</span>&nbsp;'
				);
	},
	_deleteCalendarEvent: function (eventId, sourceModule, extraParams) {
		var thisInstance = this;
		if (typeof extraParams === 'undefined') {
			extraParams = {};
		}
		var params = {
			"module": "Calendar",
			"action": "DeleteAjax",
			"record": eventId,
			"sourceModule": sourceModule
		};
		jQuery.extend(params, extraParams);

		app.helper.showProgress();
		app.request.post({'data': params}).then(function (e, res) {
			app.helper.hideProgress();
			if (!e) {
				var deletedRecords = res['deletedRecords'];
				for (var key in deletedRecords) {
					var eventId = deletedRecords[key];
					thisInstance.getCalendarViewContainer().fullCalendar('removeEvents', eventId);
				}
				app.helper.showSuccessNotification({
					'message': app.vtranslate('JS_RECORD_DELETED')
				});
			} else {
				app.helper.showErrorNotification({
					'message': app.vtranslate('JS_NO_DELETE_PERMISSION')
				});
			}
		});
	},
	deleteCalendarEvent: function (eventId, sourceModule, isRecurring) {
		var thisInstance = this;
		if (isRecurring) {
			app.helper.showConfirmationForRepeatEvents().then(function (postData) {
				thisInstance._deleteCalendarEvent(eventId, sourceModule, postData);
			});
		} else {
			app.helper.showConfirmationBox({
				message: app.vtranslate('LBL_DELETE_CONFIRMATION')
			}).then(function () {
				thisInstance._deleteCalendarEvent(eventId, sourceModule);
			});
		}
	},
	updateEventOnCalendar: function (eventData) {
		this.updateAllEventsOnCalendar();
	},
	_updateEvent: function (form, extraParams) {
		var formData = jQuery(form).serializeFormData();
		extraParams = extraParams || {};
		jQuery.extend(formData, extraParams);
		app.helper.showProgress();
		app.request.post({data: formData}).then(function (err, data) {
			app.helper.hideProgress();
			if (!err) {
				jQuery('.vt-notification').remove();
				app.helper.showSuccessNotification({"message": ''});
				app.event.trigger("post.QuickCreateForm.save", data, jQuery(form).serializeFormData());
				app.helper.hideModal();
			} else {
				app.event.trigger('post.save.failed', err);
				jQuery("button[name='saveButton']").removeAttr("disabled");
			}
		});
	},
	validateAndUpdateEvent: function (modalContainer, isRecurring) {
		var thisInstance = this;
		var params = {
			submitHandler: function (form) {
				jQuery("button[name='saveButton']").attr("disabled", "disabled");
				if (this.numberOfInvalids() > 0) {
					jQuery("button[name='saveButton']").removeAttr("disabled");
					return false;
				}
				var e = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
				app.event.trigger(e);
				if (e.isDefaultPrevented()) {
					return false;
				}
				if (isRecurring) {
					app.helper.showConfirmationForRepeatEvents().then(function (postData) {
						thisInstance._updateEvent(form, postData);
					});
				} else {
					thisInstance._updateEvent(form);
				}
			}
		};
		modalContainer.find('form').vtValidate(params);
	},
	registerEditEventModalEvents: function (modalContainer, isRecurring) {
		this.validateAndUpdateEvent(modalContainer, isRecurring);
	},
	showEditModal: function (moduleName, record, isRecurring) {
		var thisInstance = this;
		var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="' + moduleName + '"]');
		if (quickCreateNode.length <= 0) {
			app.helper.showAlertNotification({
				'message': app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED')
			});
		} else {
			var quickCreateUrl = quickCreateNode.data('url');
			var quickCreateEditUrl = quickCreateUrl + '&mode=edit&record=' + record;
			quickCreateNode.data('url', quickCreateEditUrl);
			quickCreateNode.trigger('click');
			quickCreateNode.data('url', quickCreateUrl);

			if (moduleName === 'Events') {
				app.event.one('post.QuickCreateForm.show', function (e, form) {
					thisInstance.registerEditEventModalEvents(form.closest('.modal'), isRecurring);
				});
			}
		}
	},
	showEditTaskModal: function (taskId) {
		this.showEditModal('Calendar', taskId);
	},
	editCalendarTask: function (taskId) {
		this.showEditTaskModal(taskId);
	},
	showEditEventModal: function (eventId, isRecurring) {
		this.showEditModal('Events', eventId, isRecurring);
	},
	editCalendarEvent: function (eventId, isRecurring) {
		this.showEditEventModal(eventId, isRecurring);
	},
	registerPopoverEvent: function (event, element, calendarView) {
		var dateFormat = this.getUserPrefered('date_format');
		dateFormat = dateFormat.toUpperCase();
		var hourFormat = this.getUserPrefered('time_format');
		var timeFormat = 'HH:mm';
		if (hourFormat === '12') {
			timeFormat = 'hh:mm a';
		}

		var generatePopoverContentHTML = function (eventObj) {
			var timeString = '';
			if (eventObj.activitytype === 'Task') {
				timeString = moment(eventObj._start._i, eventObj._start._f).format(timeFormat);
			} else if (eventObj.module === "Events") {
				if (eventObj._start && typeof eventObj._start != 'undefined') {
					timeString = eventObj._start.format(timeFormat);
				}
				if (eventObj._end && typeof eventObj._end != 'undefined') {
					timeString += ' - ' + eventObj._end.format(timeFormat);
				}
			} else {
				timeString = eventObj._start.format(dateFormat);
			}
			var sourceModule = eventObj.module;
			if (!sourceModule) {
				sourceModule = 'Calendar';
			}
			var popOverHTML = '' +
			'<span>' +
				timeString +
			'</span>';

			if (sourceModule === 'Calendar' || sourceModule == 'Events') {
				popOverHTML += '' +
						'<span class="pull-right cursorPointer" ' +
						'onClick="Calendar_Calendar_Js.deleteCalendarEvent(\'' + eventObj.id +
						'\',\'' + sourceModule + '\',' + eventObj.recurringcheck + ');" title="' + app.vtranslate('JS_DELETE') + '">' +
						'&nbsp;&nbsp;<i class="fa fa-trash"></i>' +
						'</span> &nbsp;&nbsp;';

				if (sourceModule === 'Events') {
					popOverHTML += '' +
							'<span class="pull-right cursorPointer" ' +
							'onClick="Calendar_Calendar_Js.editCalendarEvent(\'' + eventObj.id +
							'\',' + eventObj.recurringcheck + ');" title="' + app.vtranslate('JS_EDIT') + '">' +
							'&nbsp;&nbsp;<i class="fa fa-pencil"></i>' +
							'</span>';
				} else if (sourceModule === 'Calendar') {
					popOverHTML += '' +
							'<span class="pull-right cursorPointer" ' +
							'onClick="Calendar_Calendar_Js.editCalendarTask(\'' + eventObj.id + '\');" title="' + app.vtranslate('JS_EDIT') + '">' +
							'&nbsp;&nbsp;<i class="fa fa-pencil"></i>' +
							'</span>';
				}

				if (eventObj.status !== 'Held' && eventObj.status !== 'Completed') {
					popOverHTML += '' +
							'<span class="pull-right cursorPointer"' +
							'onClick="Calendar_Calendar_Js.markAsHeld(\'' + eventObj.id + '\');" title="' + app.vtranslate('JS_MARK_AS_HELD') + '">' +
							'<i class="fa fa-check"></i>' +
							'</span>';
				} else if (eventObj.status === 'Held') {
					popOverHTML += '' +
							'<span class="pull-right cursorPointer" ' +
							'onClick="Calendar_Calendar_Js.holdFollowUp(\'' + eventObj.id + '\');" title="' + app.vtranslate('JS_CREATE_FOLLOW_UP') + '">' +
							'<i class="fa fa-flag"></i>' +
							'</span>';
				}
			}
			return popOverHTML;
		};

		var params = {
			'title': event.title,
			'content': generatePopoverContentHTML(event),
			'trigger': 'hover',
			'closeable': true,
			'placement': 'auto',
			'animation': 'fade'
		};
		if (calendarView.name === 'agendaDay') {
			params.constrains = 'vertical';
		}
		element.webuiPopover(params);
	},
	performPreEventRenderActions: function (event, element) {
		var calendarView = this.getCalendarViewContainer().fullCalendar('getView');
		this.addActivityTypeIcons(event, element);
		this.registerPopoverEvent(event, element, calendarView);
	},
	performMouseOutActions: function (event, jsEvent, view) {
//var currentTarget = jQuery(jsEvent.currentTarget);
	},
	performMouseOverActions: function (event, jsEvent, view) {
//var currentTarget = jQuery(jsEvent.currentTarget);
	},
	getCalendarHeight: function (view) {
		var portion = 0.86;
		if (typeof view !== 'undefined') {
			if (view === 'AgendaList') {
				portion = 1;
			}
		}
//calendar-height is 86% of window height
		return jQuery(window).height() * portion;
	},
	getDefaultCalendarView: function () {
		var userDefaultActivityView = this.getUserPrefered('activity_view');
		if (userDefaultActivityView === 'Today') {
			userDefaultActivityView = 'agendaDay';
		} else if (userDefaultActivityView === 'This Week') {
			userDefaultActivityView = 'agendaWeek';
		} else if (userDefaultActivityView === 'Agenda') {
			userDefaultActivityView = 'vtAgendaList';
		} else {
			userDefaultActivityView = 'month';
		}
		return userDefaultActivityView;
	},
	getDefaultCalendarTimeFormat: function () {
		var userDefaultTimeFormat = this.getUserPrefered('time_format');
		if (parseInt(userDefaultTimeFormat) === 24) {
			userDefaultTimeFormat = 'H(:mm)';
		} else {
			userDefaultTimeFormat = 'h(:mm)a';
		}
		return userDefaultTimeFormat;
	},
	getCalendarConfigs: function () {
		var thisInstance = this;
		var userDefaultActivityView = thisInstance.getDefaultCalendarView();
		var userDefaultTimeFormat = thisInstance.getDefaultCalendarTimeFormat();
                
                var dateFormat = app.getDateFormat();
                //Converting to fullcalendar accepting date format
                var monthPos = dateFormat.search("mm");
                var datePos = dateFormat.search("dd");
                if (monthPos < datePos) {
                    dateFormat = "M/D";
                } else {
                    dateFormat = "D/M";
                }
            
		var calenderConfigs = {
			header: {
				left: 'month,agendaWeek,agendaDay,vtAgendaList',
				center: 'title',
				right: 'today prev,next',
			},
                        columnFormat: {
                            month: 'ddd',
                            week: 'ddd '+dateFormat,
                            day: 'dddd '+dateFormat
                        },
			views: {
                            vtAgendaList: {
                                    duration: {days: Calendar_Calendar_Js.numberOfDaysInAgendaView}
                            },
                            month:{
                                columnFormat:'ddd'
                            },
                            agendaWeek: {
                                columnFormat: 'ddd ' + dateFormat,
                                // Enable drag-to-create selection for Week view
                                selectable: true,
                                selectMirror: true
                            },
                            agendaDay: {
                                columnFormat: 'dddd '+dateFormat,
                                // Enable drag-to-create selection for Day view
                                selectable: true,
                                selectMirror: true
                            },
                            // FullCalendar v4+ view names (if supported)
                            timeGridDay: {
                                selectable: true,
                                selectMirror: true
                            },
                            timeGridWeek: {
                                selectable: true,
                                selectMirror: true
                            }
			},
			fixedWeekCount: false,
			// Month view: auto height so page scrolls; Week/Day: fixed height with internal scroll
			contentHeight: function (view) {
				if (view && view.name === 'month') return 'auto';
				return thisInstance.getCalendarHeight();
			},
			firstDay: thisInstance.daysOfWeek[thisInstance.getUserPrefered('start_day')],
			scrollTime: thisInstance.getUserPrefered('start_hour'),
			editable: true,
			eventLimit: true,
			defaultView: userDefaultActivityView,
			slotLabelFormat: userDefaultTimeFormat,
			timeFormat: userDefaultTimeFormat,
			minTime: thisInstance.getUserPrefered('start_hour')+':00',//angelo
			events: [],
			monthNames: [
				app.vtranslate('LBL_JANUARY'),
				app.vtranslate('LBL_FEBRUARY'),
				app.vtranslate('LBL_MARCH'),
				app.vtranslate('LBL_APRIL'),
				app.vtranslate('LBL_MAY'),
				app.vtranslate('LBL_JUNE'),
				app.vtranslate('LBL_JULY'),
				app.vtranslate('LBL_AUGUST'),
				app.vtranslate('LBL_SEPTEMBER'),
				app.vtranslate('LBL_OCTOBER'),
				app.vtranslate('LBL_NOVEMBER'),
				app.vtranslate('LBL_DECEMBER')
			],
			monthNamesShort: [
				app.vtranslate('LBL_JAN'),
				app.vtranslate('LBL_FEB'),
				app.vtranslate('LBL_MAR'),
				app.vtranslate('LBL_APR'),
				app.vtranslate('LBL_MAY'),
				app.vtranslate('LBL_JUN'),
				app.vtranslate('LBL_JUL'),
				app.vtranslate('LBL_AUG'),
				app.vtranslate('LBL_SEP'),
				app.vtranslate('LBL_OCT'),
				app.vtranslate('LBL_NOV'),
				app.vtranslate('LBL_DEC')
			],
			dayNames: [
				app.vtranslate('LBL_SUNDAY'),
				app.vtranslate('LBL_MONDAY'),
				app.vtranslate('LBL_TUESDAY'),
				app.vtranslate('LBL_WEDNESDAY'),
				app.vtranslate('LBL_THURSDAY'),
				app.vtranslate('LBL_FRIDAY'),
				app.vtranslate('LBL_SATURDAY')
			],
			dayNamesShort: [
				app.vtranslate('LBL_SUN'),
				app.vtranslate('LBL_MON'),
				app.vtranslate('LBL_TUE'),
				app.vtranslate('LBL_WED'),
				app.vtranslate('LBL_THU'),
				app.vtranslate('LBL_FRI'),
				app.vtranslate('LBL_SAT')
			],
			buttonText: {
				'today': app.vtranslate('LBL_TODAY'),
				'month': app.vtranslate('LBL_MONTH'),
				'week': app.vtranslate('LBL_WEEK'),
				'day': app.vtranslate('LBL_DAY'),
				'vtAgendaList': app.vtranslate('LBL_AGENDA')
			},
			allDayText: app.vtranslate('LBL_ALL_DAY'),
			dayClick: function (date, jsEvent, view) {
				thisInstance.performDayClickAction(date, jsEvent, view);
			},
			select: function (start, end, jsEvent, view) {
				// Handle drag-to-create selection (Google Calendar-like)
				// FullCalendar v3: select callback receives (start, end, jsEvent, view) as separate parameters
				// NOT as an info object!
				console.log('[Calendar Drag Select] Parameters:', {
					start: start,
					end: end,
					jsEvent: jsEvent,
					view: view
				});
				
				// Check if start and end exist
				if (!start || !end) {
					console.warn('[Calendar Drag Select] Missing start or end');
					thisInstance.getCalendarViewContainer().fullCalendar('unselect');
					return;
				}
				
				// Check if creation is allowed
				var isAllowed = jQuery('#is_record_creation_allowed').val();
				if (!isAllowed) {
					console.warn('[Calendar Drag Select] Creation not allowed');
					thisInstance.getCalendarViewContainer().fullCalendar('unselect');
					return;
				}
				
				// Convert to moment objects if needed (FullCalendar v3 passes moment objects)
				var startMoment = moment.isMoment(start) ? start : moment(start);
				var endMoment = moment.isMoment(end) ? end : moment(end);
				
				if (!startMoment.isValid() || !endMoment.isValid()) {
					console.warn('[Calendar Drag Select] Invalid dates:', {
						start: start,
						end: end,
						startValid: startMoment.isValid(),
						endValid: endMoment.isValid()
					});
					thisInstance.getCalendarViewContainer().fullCalendar('unselect');
					return;
				}
				
				var viewType = (view && view.type) ? view.type : 'unknown';
				console.log('[Calendar Drag Select] Valid selection:', {
					start: startMoment.format(),
					end: endMoment.format(),
					viewType: viewType
				});
				
				// Always open Event form (not Task)
				var moduleName = 'Events';
				console.log('[Calendar Drag Select] Opening Event form');
				
				// Store selection info (store as moment objects to preserve timezone and format)
				// Check if it's all-day: if start and end are at midnight and span full days
				var isAllDay = false;
				if (startMoment.hours() === 0 && startMoment.minutes() === 0 && 
				    endMoment.hours() === 0 && endMoment.minutes() === 0 &&
				    endMoment.diff(startMoment, 'days') >= 1) {
					isAllDay = true;
				}
				
				// Store as moment objects (clone to avoid mutation)
				thisInstance._dragSelection = {
					start: startMoment.clone(),
					end: endMoment.clone(),
					allDay: isAllDay
				};
				
				console.log('[Calendar Drag Select] Stored selection:', {
					start: startMoment.format('YYYY-MM-DD HH:mm'),
					end: endMoment.format('YYYY-MM-DD HH:mm'),
					allDay: isAllDay
				});
				
				// Auto-open QuickCreate (no chooser popup)
				console.log('[Calendar Drag Select] Opening QuickCreate for:', moduleName);
				thisInstance.openQuickCreateFromDrag(moduleName);
			},
			eventResize: function (event, delta, revertFunc, jsEvent, ui, view) {
				thisInstance.updateEventOnResize(event, delta, revertFunc, jsEvent, ui, view);
			},
			eventDrop: function (event, delta, revertFunc, jsEvent, ui, view) {
				thisInstance.updateEventOnDrop(event, delta, revertFunc, jsEvent, ui, view);
			},
			eventRender: function (event, element) {
				thisInstance.performPreEventRenderActions(event, element);
			},
			eventMouseover: function (event, jsEvent, view) {
				thisInstance.performMouseOverActions(event, jsEvent, view);
			},
			eventMouseout: function (event, jsEvent, view) {
				thisInstance.performMouseOutActions(event, jsEvent, view);
			},
			viewRender: function (view, element) {
				if (view.name === 'vtAgendaList') {
					jQuery(".sidebar-essentials").addClass("hide");
					jQuery(".content-area").addClass("full-width");
					jQuery(".essentials-toggle").addClass("hide");
				} else {
					jQuery(".essentials-toggle").removeClass("hide");
					if (Calendar_Calendar_Js.sideBarEssentialsState === 'show') {
						jQuery(".sidebar-essentials").removeClass("hide");
						jQuery(".content-area").removeClass("full-width");
					} else if (Calendar_Calendar_Js.sideBarEssentialsState === 'hidden') {
						jQuery(".sidebar-essentials").addClass("hide");
						jQuery(".content-area").addClass("full-width");
					}
				}
			}
		};
		return calenderConfigs;
	},
	fetchAgendaEvents: function (date) {
		var aDeferred = jQuery.Deferred();

		var dateFormat = this.getUserPrefered('date_format');
		dateFormat = dateFormat.toUpperCase();
		var startDate = date.format(dateFormat);

		var requestParams = {
			'module': app.getModuleName(),
			'action': 'FetchAgendaEvents',
			'startDate': startDate,
			'numOfDays': Calendar_Calendar_Js.numberOfDaysInAgendaView
		};

		app.helper.showProgress();
		app.request.post({'data': requestParams}).then(function (e, res) {
			app.helper.hideProgress();
			if (!e) {
				aDeferred.resolve(res);
			} else {
				aDeferred.reject(e);
			}
		});

		return aDeferred.promise();
	},
	fetchEventDetails: function (eventId) {
		var aDeferred = jQuery.Deferred();

		var requestParams = {
			'module': app.getModuleName(),
			'action': 'CalendarActions',
			'mode': 'fetchAgendaViewEventDetails',
			'id': eventId
		};

		app.helper.showProgress();
		app.request.post({'data': requestParams}).then(function (e, res) {
			app.helper.hideProgress();
			if (!e) {
				aDeferred.resolve(res);
			} else {
				aDeferred.reject(e);
			}
		});

		return aDeferred.promise();
	},
	registerAgendaListView: function () {
		var thisInstance = this;
		var FC = jQuery.fullCalendar;
		var view = FC.View;
		var agendaListView;

		agendaListView = view.extend({
			initialize: function () {
				var dateFormat = thisInstance.getUserPrefered('date_format');
				this.vtDateFormat = dateFormat.toUpperCase();
			},
			getCourseDay: function (date) {
				var today = moment();
				var dateFormat = this.vtDateFormat;
				var todayDate = moment().format(dateFormat);
				if (todayDate === date.format(dateFormat)) {
					return app.vtranslate('LBL_TODAY').toUpperCase();
				} else {
					var tomorrow = today.add(1, 'days');
					if (tomorrow.format(dateFormat) === date.format(dateFormat)) {
						return app.vtranslate('LBL_TOMORROW').toUpperCase();
					}
				}
				return date.format('LL');
			},
			getWeekDay: function (date) {
				var weekDay = date.format('dddd');
				var label = 'LBL_' + weekDay.toUpperCase();
				return app.vtranslate(label).toUpperCase();
			},
			renderHtml: function () {
				var startDate = moment(this.intervalStart);
				var dateFormat = this.vtDateFormat;
				var skeleton = '' +
						'<div class="agendaListView">';
				for (var i = 0; i < Calendar_Calendar_Js.numberOfDaysInAgendaView; i++) {
					var daysToAdd = i ? 1 : 0;
					var date = startDate.add(daysToAdd, 'days').format(dateFormat);
					var day = this.getCourseDay(startDate);
					var weekDay = this.getWeekDay(startDate);
					var part = '' +
							'<div class="agendaListDay" data-date="' + date + '">' +
							'<div class="agendaListViewHeader clearfix">' +
							'<div class="day">' + day + '</div>' +
							'<div class="weekDay">' + weekDay + '</div>' +
							'</div>' +
							'<hr>' +
							'<div class="agendaListViewBody">' +
							'</div>' +
							'</div>';
					skeleton += part;
				}
				skeleton +=
						'</div>';
				return skeleton;
			},
			generateEventDetailsHTML: function (res) {
				var html = '<div class="agenda-table-cell"></div>' +
						'<div class="agenda-table-cell"></div>' +
						'<div class="agenda-table-cell">' +
						'<div class="agenda-table details">';
				for (var fieldLabel in res) {
					var eachItem = '<div class="agenda-details">';
					eachItem += '<span class="detailLabel">' + fieldLabel + '</span>';
					eachItem += '<span class="separator"> : </span>';
					eachItem += '<span class="fieldValue">' + jQuery.trim(res[fieldLabel]) + '</span>';
					eachItem += '</div>';
					html += eachItem;
				}
				html += '</div>' +
						'</div>';
				return html;
			},
			registerToggleMoreDetailsEvent: function (container) {
				var fcInstance = this;
				container.on('click', '.agenda-more-details', function () {
					var target = jQuery(this);
					var indicator = target.find('i');
					var wrapper = target.closest('.agenda-event-wrapper');
					var eventId = wrapper.data('eventId');
					var details = wrapper.find('.agenda-event-details');
					if (indicator.hasClass('fa-plus-square-o')) {
						if (details.data('isDetailsLoaded')) {
							details.removeClass('hide');
						} else {
							thisInstance.fetchEventDetails(eventId).then(function (res) {
								details.append(fcInstance.generateEventDetailsHTML(res));
								details.removeClass('hide');
								details.data('isDetailsLoaded', true)
							});
						}
						indicator.removeClass('fa-plus-square-o').
								addClass('fa-minus-square-o');
					} else {
						details.addClass('hide');
						indicator.removeClass('fa-minus-square-o').
								addClass('fa-plus-square-o');
					}
				});
			},
			registerAgendaViewEvents: function (container) {
				this.registerToggleMoreDetailsEvent(container);
			},
			render: function () {
				this.el.html(this.renderHtml());
				var height = thisInstance.getCalendarHeight('AgendaList') + 'px';
				var agendaListContainer = this.el.find('.agendaListView');
				agendaListContainer.css('max-height', height).css('min-height', height);
				this.registerAgendaViewEvents(agendaListContainer);
			},
			renderEvents: function () {
				this.renderVtAgendaEvents();
			},
			getAgendaActionsHTML: function (event) {
				var actionsMarkup = '' +
						'<div class="agenda-event-actions verticalAlignMiddle">' +
						'<span class="pull-right cursorPointer" ' +
						'onClick="Calendar_Calendar_Js.deleteCalendarEvent(\'' + event.id +
						'\',\'Events\',' + event.recurringcheck + ');" title="' + app.vtranslate('JS_DELETE') + '">' +
						'&nbsp;&nbsp;<i class="fa fa-trash"></i>' +
						'</span>' +
						'<span class="pull-right cursorPointer" ' +
						'onClick="Calendar_Calendar_Js.editCalendarEvent(\'' + event.id +
						'\',' + event.recurringcheck + ');" title="' + app.vtranslate('JS_EDIT') + '">' +
						'&nbsp;&nbsp;<i class="fa fa-pencil"></i>' +
						'</span>';

				if (event.status !== 'Held') {
					actionsMarkup += '' +
							'<span class="pull-right cursorPointer"' +
							'onClick="Calendar_Calendar_Js.markAsHeld(\'' + event.id + '\');" title="' + app.vtranslate('JS_MARK_AS_HELD') + '">' +
							'&nbsp;&nbsp;<i class="fa fa-check"></i>' +
							'</span>';
				} else if (event.status === 'Held') {
					actionsMarkup += '' +
							'<span class="pull-right cursorPointer" ' +
							'onClick="Calendar_Calendar_Js.holdFollowUp(\'' + event.id + '\');" title="' + app.vtranslate('JS_CREATE_FOLLOW_UP') + '">' +
							'&nbsp;&nbsp;<i class="fa fa-flag"></i>' +
							'</span>';
				}
				actionsMarkup +=
						'</div>';
				return actionsMarkup;
			},
			getAgendaEventTitle: function (event) {
				return event.status === 'Held' ?
						'<span><strike>' + event.title + '</strike><span>' :
						'<span>' + event.title + '</span>';
			},
			generateEventHTML: function (event) {
				var html = '' +
						'<div class="agenda-event-wrapper" data-event-id="' + event.id + '">' +
						'<div class="agenda-event-info">' +
						'<div class="agenda-event-time verticalAlignMiddle">' +
						'<div>' + event.startTime + ' - ' + event.endTime + '</div>' +
						'</div>' +
						'<div class="agenda-more-details cursorPointer verticalAlignMiddle">' +
						'<i class="fa fa-plus-square-o" title=' + app.vtranslate('JS_DETAILS') + '></i>' +
						'</div>' +
						'<div class="agenda-event-title verticalAlignMiddle">&nbsp;' +
						'<i class="' + thisInstance.getActivityTypeClassName(event.activitytype) + '" title="' + app.vtranslate(event.activitytype) + '"></i>&nbsp;&nbsp;&nbsp;';
				if (event.recurringcheck) {
					html += '<i class="fa fa-repeat" style="font-size:10px;" title="' + app.vtranslate('JS_RECURRING_EVENT') + '"></i>&nbsp;';
				}
				html += this.getAgendaEventTitle(event) +
						'</div>' +
						'<div class="agenda-event-status verticalAlignMiddle">' + event.status + '</div>' +
						this.getAgendaActionsHTML(event) +
						'</div>' +
						'<div class="agenda-event-details hide verticalAlignMiddle">' +
						'</div>' +
						'</div>';
				return html;
			},
			displayNoEventsMessage: function () {
				jQuery('.agendaListViewBody').each(function (i, element) {
					var currentList = jQuery(element);
					var eventsElements = currentList.find('.agenda-event-wrapper');
					if (!eventsElements.length) {
						currentList.html(
								'<div class="agendaNoEvents">' +
								app.vtranslate('JS_NO_EVENTS_F0R_THE_DAY') +
								'</div>'
								);
					}
				});
			},
			renderVtAgendaEvents: function () {
				var fcInstance = this;
				var currentDate = moment(this.intervalStart);
				thisInstance.fetchAgendaEvents(currentDate).then(function (agendaEvents) {
//cleanup before render
					jQuery('.agendaListViewBody').empty();
					for (var key in agendaEvents) {
						var container = jQuery('[data-date="' + key + '"]');
						var containerBody = container.find('.agendaListViewBody');
						var eventsPerDay = agendaEvents[key];
						jQuery.each(eventsPerDay, function (i, event) {
							containerBody.append(fcInstance.generateEventHTML(event));
						});
					}
					fcInstance.displayNoEventsMessage();
				});
			}
		});

		FC.views.vtAgendaList = agendaListView;
	},
	registerGotoDateButtonAction: function (navigationsContainer) {
		var thisInstance = this;
		var gotoButton = navigationsContainer.find('.vt-goto-date');
		gotoButton.datepicker({
			'autoclose': true,
			'todayBtn': "linked",
			'format': thisInstance.getUserPrefered('date_format'),
		}).on('changeDate', function (e) {
			thisInstance.getCalendarViewContainer().fullCalendar('gotoDate', moment(e.date));
		});
	},
	addGotoDateButton: function () {
		var navigationsContainer = this.getCalendarViewContainer().find(
				'.fc-right > .fc-button-group'
				);
		var buttonHTML = '' +
				'<button type="button" class="vt-goto-date fc-button fc-state-default fc-corner-left">' +
				'<span class="fa fa-calendar"></span>' +
				'</button>';
		navigationsContainer.find('.fc-prev-button').after(buttonHTML);
		this.registerGotoDateButtonAction(navigationsContainer);
	},
	performPostRenderCustomizations: function () {
		this.addGotoDateButton();
	},
	initializeCalendar: function () {
		this.registerAgendaListView();
		var calendarConfigs = this.getCalendarConfigs();
		this.getCalendarViewContainer().fullCalendar(calendarConfigs);
		this.performPostRenderCustomizations();
		this.performSidebarEssentialsRecognition();
	},
	performSidebarEssentialsRecognition: function () {
		app.event.on("Vtiger.Post.MenuToggle", function () {
			var essentialsHidden = jQuery(".sidebar-essentials").hasClass("hide");
			if (essentialsHidden) {
				Calendar_Calendar_Js.sideBarEssentialsState = 'hidden';
			} else {
				Calendar_Calendar_Js.sideBarEssentialsState = 'show';
			}
		});
		var essentialsHidden = jQuery(".sidebar-essentials").hasClass("hide");
		if (essentialsHidden) {
			Calendar_Calendar_Js.sideBarEssentialsState = 'hidden';
		} else {
			Calendar_Calendar_Js.sideBarEssentialsState = 'show';
		}
	},
	registerEvents: function () {
		this._super();
		this.initializeCalendar();
		this.registerWidgetPostLoadEvent();
		this.initializeWidgets();
		this.registerPostQuickCreateSaveEvent();
		this.registerTaskQuickCreateEnhancements();
	},
	/**
	 * Open QuickCreate form from drag selection (Google Calendar-like)
	 * SAFE: Uses existing QuickCreate mechanism, no overlay manipulation
	 */
	openQuickCreateFromSelection: function(start, end) {
		var thisInstance = this;
		var startMoment = moment(start);
		var endMoment = moment(end);
		
		// Calculate duration in days
		var durationDays = endMoment.diff(startMoment, 'days', true);
		
		// Auto-switch to Task if all-day (duration >= 1 day)
		var isAllDay = durationDays >= 1;
		var moduleName = isAllDay ? 'Calendar' : 'Events';
		var activityType = isAllDay ? 'Task' : 'Events';
		
		// Check if creation is allowed
		var isAllowed = jQuery('#is_record_creation_allowed').val();
		if (!isAllowed) {
			// Unselect if not allowed
			thisInstance.getCalendarViewContainer().fullCalendar('unselect');
			return;
		}
		
		// Find QuickCreate node
		var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="' + moduleName + '"]');
		if (quickCreateNode.length <= 0) {
			// Fallback: try Calendar module
			quickCreateNode = jQuery('#quickCreateModules').find('[data-name="Calendar"]');
			if (quickCreateNode.length <= 0) {
				app.helper.showAlertNotification({
					'message': app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED')
				});
				thisInstance.getCalendarViewContainer().fullCalendar('unselect');
				return;
			}
		}
		
		// FIRST: Trigger QuickCreate click to open modal
		quickCreateNode.trigger('click');
		
		// WAIT for modal to render before setting values
		// Use setTimeout as fallback to ensure modal is ready
		setTimeout(function() {
			var container = jQuery('.modal-content, #QuickCreate');
			if (!container.length) {
				console.warn('[Calendar Drag Select] QuickCreate modal not ready');
				// Try again after a short delay
				setTimeout(function() {
					var retryContainer = jQuery('.modal-content, #QuickCreate');
					if (!retryContainer.length) {
						console.warn('[Calendar Drag Select] QuickCreate modal still not ready after retry');
						thisInstance.getCalendarViewContainer().fullCalendar('unselect');
						return;
					}
					thisInstance.populateQuickCreateFields(retryContainer, startMoment, endMoment, isAllDay);
				}, 200);
				return;
			}
			
			// Modal is ready, populate fields
			thisInstance.populateQuickCreateFields(container, startMoment, endMoment, isAllDay);
		}, 300);
		
		// Also listen for post.QuickCreateForm.show event as backup
		app.event.one('post.QuickCreateForm.show', function (e, form) {
			var modalContainer = form.closest('.modal, .modal-content');
			if (modalContainer.length > 0) {
				// Double-check fields aren't already populated
				var startDateElement = modalContainer.find('input[name="date_start"]');
				if (startDateElement.length > 0 && !startDateElement.val()) {
					thisInstance.populateQuickCreateFields(modalContainer, startMoment, endMoment, isAllDay);
				}
			}
		});
	},
	/**
	 * Populate QuickCreate form fields with drag selection values
	 * SAFE: Only sets field values, does not manipulate overlay
	 */
	populateQuickCreateFields: function(container, startMoment, endMoment, isAllDay) {
		var thisInstance = this;
		
		if (!container || !container.length) {
			console.warn('[Calendar Drag Select] Container not found');
			return;
		}
		
		if (isAllDay) {
			// Task: Set date_start and deadline (due_date)
			var startDateElement = container.find('input[name="date_start"]');
			var deadlineElement = container.find('#task_deadline');
			var dueDateElement = container.find('input[name="due_date"]');
			
			if (startDateElement.length > 0) {
				var userDateFormat = vtUtils.getMomentDateFormat();
				startDateElement.val(startMoment.format(userDateFormat));
				vtUtils.registerEventForDateFields(startDateElement);
				startDateElement.trigger('change');
			}
			
			// Set deadline to start date (Task deadline = start date for all-day)
			if (deadlineElement.length > 0) {
				var userDateFormat = vtUtils.getMomentDateFormat();
				deadlineElement.val(startMoment.format(userDateFormat));
				vtUtils.registerEventForDateFields(deadlineElement);
			} else if (dueDateElement.length > 0) {
				var userDateFormat = vtUtils.getMomentDateFormat();
				dueDateElement.val(startMoment.format(userDateFormat));
				vtUtils.registerEventForDateFields(dueDateElement);
			}
			
			// Set activitytype to Task if field exists
			var activityTypeElement = container.find('[name="activitytype"]');
			if (activityTypeElement.length > 0) {
				activityTypeElement.val('Task');
				if (activityTypeElement.is('select')) {
					vtUtils.showSelect2ElementView(activityTypeElement);
				}
			}
		} else {
			// Event: Set date_start, time_start, due_date, time_end
			var startDateElement = container.find('input[name="date_start"]');
			var startTimeElement = container.find('input[name="time_start"]');
			var endDateElement = container.find('input[name="due_date"]');
			var endTimeElement = container.find('input[name="time_end"]');
			
			if (startDateElement.length > 0) {
				var userDateFormat = vtUtils.getMomentDateFormat();
				var startDate = startMoment.format(userDateFormat);
				startDateElement.val(startDate);
				vtUtils.registerEventForDateFields(startDateElement);
				startDateElement.trigger('change');
			}
			
			if (startTimeElement.length > 0) {
				var userTimeFormat = vtUtils.getMomentTimeFormat();
				var startTime = startMoment.format(userTimeFormat);
				startTimeElement.val(startTime);
				vtUtils.registerEventForTimeFields(startTimeElement);
			}
			
			if (endDateElement.length > 0) {
				var userDateFormat = vtUtils.getMomentDateFormat();
				var endDate = endMoment.format(userDateFormat);
				endDateElement.val(endDate);
				vtUtils.registerEventForDateFields(endDateElement);
				endDateElement.trigger('change');
			}
			
			if (endTimeElement.length > 0) {
				var userTimeFormat = vtUtils.getMomentTimeFormat();
				var endTime = endMoment.format(userTimeFormat);
				endTimeElement.val(endTime);
				vtUtils.registerEventForTimeFields(endTimeElement);
			}
		}
		
		// Cleanup: Unselect calendar selection
		thisInstance.getCalendarViewContainer().fullCalendar('unselect');
	},
	/**
	 * Show create chooser popup after drag selection
	 * SAFE: Lightweight popup, no overlay manipulation
	 */
	showCreateChooser: function(mouseEvent) {
		var thisInstance = this;
		
		// Remove any existing chooser
		jQuery('.calendar-create-chooser').remove();
		
		// Create chooser HTML
		var html = '<div class="calendar-create-chooser">' +
			'<div class="chooser-item" data-type="Events">➕ Add Event</div>' +
			'<div class="chooser-item" data-type="Calendar">📝 Add Task</div>' +
			'</div>';
		
		var chooser = jQuery(html).appendTo('body');
		
		// Style chooser
		chooser.css({
			position: 'absolute',
			top: (mouseEvent ? mouseEvent.pageY + 5 : jQuery(window).scrollTop() + 200) + 'px',
			left: (mouseEvent ? mouseEvent.pageX + 5 : jQuery(window).scrollLeft() + 200) + 'px',
			background: '#1f1f1f',
			color: '#fff',
			padding: '8px',
			borderRadius: '6px',
			zIndex: 10000,
			cursor: 'pointer',
			boxShadow: '0 2px 8px rgba(0,0,0,0.3)',
			fontSize: '13px',
			minWidth: '120px'
		});
		
		chooser.find('.chooser-item').css({
			padding: '6px 10px',
			borderRadius: '4px',
			marginBottom: '2px'
		}).hover(
			function() {
				jQuery(this).css('background', '#333');
			},
			function() {
				jQuery(this).css('background', 'transparent');
			}
		);
		
		// Handle chooser item click
		chooser.find('.chooser-item').on('click', function(e) {
			e.stopPropagation();
			var moduleName = jQuery(this).data('type');
			chooser.remove();
			thisInstance.openQuickCreateFromDrag(moduleName);
		});
		
		// Remove chooser on document click
		setTimeout(function() {
			jQuery(document).one('click', function() {
				chooser.remove();
				// Unselect if chooser closed without selection
				if (thisInstance._dragSelection) {
					thisInstance.getCalendarViewContainer().fullCalendar('unselect');
					thisInstance._dragSelection = null;
				}
			});
		}, 100);
	},
	/**
	 * Open QuickCreate from drag selection (Google Calendar-like)
	 * SAFE: Uses existing QuickCreate mechanism
	 * Opens form immediately after drag, populates fields automatically
	 */
	openQuickCreateFromDrag: function(moduleName) {
		var thisInstance = this;
		var data = thisInstance._dragSelection;
		
		console.log('[openQuickCreateFromDrag] Called with module:', moduleName, 'data:', data);
		
		if (!data) {
			console.warn('[openQuickCreateFromDrag] No selection data');
			return;
		}
		
		// Get start and end from stored selection (already moment objects)
		var start = moment.isMoment(data.start) ? data.start.clone() : moment(data.start);
		var end = moment.isMoment(data.end) ? data.end.clone() : moment(data.end);
		
		console.log('[openQuickCreateFromDrag] Original selection:', {
			start: start.format('YYYY-MM-DD HH:mm'),
			end: end.format('YYYY-MM-DD HH:mm')
		});
		
		// Ensure end is always after start (fix for missing end time)
		if (!end.isAfter(start)) {
			console.warn('[openQuickCreateFromDrag] End not after start, adding 30 minutes');
			end = start.clone().add(30, 'minutes');
		}
		
		console.log('[openQuickCreateFromDrag] Final dates:', {
			start: start.format('YYYY-MM-DD HH:mm'),
			end: end.format('YYYY-MM-DD HH:mm')
		});
		
		// Prepare prefill data
		var prefillData = {};
		if (moduleName === 'Events') {
			prefillData = {
				date_start: start.format('YYYY-MM-DD'),
				time_start: start.format('HH:mm'),
				due_date: end.format('YYYY-MM-DD'),
				time_end: end.format('HH:mm'),
				allday: 0
			};
		} else if (moduleName === 'Calendar') {
			prefillData = {
				date_start: start.format('YYYY-MM-DD'),
				task_deadline: end.format('YYYY-MM-DD')
			};
		}
		
		console.log('[openQuickCreateFromDrag] Prefill data:', prefillData);
		
		// Find QuickCreate button
		var quickCreateBtn = jQuery('#quickCreateModules').find('[data-name="' + moduleName + '"]');
		console.log('[openQuickCreateFromDrag] QuickCreate button found:', quickCreateBtn.length);
		
		if (!quickCreateBtn.length) {
			console.error('[openQuickCreateFromDrag] QuickCreate button not found for:', moduleName);
			app.helper.showAlertNotification({
				'message': app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED')
			});
			thisInstance.getCalendarViewContainer().fullCalendar('unselect');
			thisInstance._dragSelection = null;
			return;
		}
		
		// Function to populate fields
		var populateFields = function(form) {
			console.log('[openQuickCreateFromDrag] Populating fields, form:', form.length);
			if (!form || !form.length) {
				console.warn('[openQuickCreateFromDrag] Form not found for population');
				return;
			}
			
			// For Event: Ensure end date/time is always after start
			if (moduleName === 'Events') {
				// Convert to user date format for proper validation
				var userDateFormat = vtUtils.getMomentDateFormat();
				var userTimeFormat = vtUtils.getMomentTimeFormat();
				
				// Set start date/time first
				var $startDate = form.find('[name="date_start"]');
				var $startTime = form.find('[name="time_start"]');
				var $endDate = form.find('[name="due_date"]');
				var $endTime = form.find('[name="time_end"]');
				
				if ($startDate.length) {
					var startDateFormatted = start.format(userDateFormat);
					$startDate.val(startDateFormatted);
					vtUtils.registerEventForDateFields($startDate);
				}
				
				if ($startTime.length) {
					var startTimeFormatted = start.format(userTimeFormat);
					$startTime.val(startTimeFormatted);
					vtUtils.registerEventForTimeFields($startTime);
				}
				
				// IMPORTANT: Prevent auto-calculation of end time from start time
				// Vtiger's registerTimeStartChangeEvent ALWAYS sets end time (line 346 in Edit.js)
				// Solution: Temporarily off the event, set values, then restore
				
				// Step 1: Temporarily off changeTime event for time_start
				var $timeStartInput = $startTime.length ? $startTime : form.find('input[name="time_start"]');
				if ($timeStartInput.length) {
					$timeStartInput.off('changeTime.calendar-drag-populate');
				}
				
				// Step 2: Set ALL values first (without triggering change events)
				var startDateFormatted = start.format(userDateFormat);
				var startTimeFormatted = start.format(userTimeFormat);
				var endDateFormatted = end.format(userDateFormat);
				var endTimeFormatted = end.format(userTimeFormat);
				
				console.log('[openQuickCreateFromDrag] Setting values:', {
					startDate: startDateFormatted,
					startTime: startTimeFormatted,
					endDate: endDateFormatted,
					endTime: endTimeFormatted
				});
				
				// Set start date/time
				if ($startDate.length) {
					$startDate.val(startDateFormatted);
					vtUtils.registerEventForDateFields($startDate);
				}
				if ($startTime.length) {
					$startTime.val(startTimeFormatted);
					vtUtils.registerEventForTimeFields($startTime);
				}
				
				// Set end date/time
				if ($endDate.length) {
					$endDate.val(endDateFormatted);
					vtUtils.registerEventForDateFields($endDate);
				}
				if ($endTime.length) {
					$endTime.val(endTimeFormatted);
					vtUtils.registerEventForTimeFields($endTime);
				}
				
				// Step 3: Mark fields as user-changed to prevent future auto-calculation
				if ($startTime.length) {
					$startTime.data('userChangedDateTime', 1);
				}
				if ($endTime.length) {
					$endTime.data('userChangedDateTime', 1);
				}
				if ($endDate.length) {
					$endDate.data('userChangedDateTime', 1);
				}
				
				// Step 4: Trigger change events AFTER a delay to ensure values are set
				setTimeout(function() {
					// Trigger end fields first
					if ($endDate.length) {
						$endDate.trigger('change');
					}
					if ($endTime.length) {
						$endTime.trigger('change');
					}
					
					// Then trigger start fields (event is off, so auto-calculation won't run)
					setTimeout(function() {
						if ($startDate.length) {
							$startDate.trigger('change');
						}
						if ($startTime.length) {
							// Trigger change (not changeTime) to avoid auto-calculation
							$startTime.trigger('change');
						}
						
						// Step 5: Re-enable changeTime event and re-confirm end values
						setTimeout(function() {
							// Re-enable changeTime event
							if ($timeStartInput.length) {
								$timeStartInput.on('changeTime.calendar-drag-populate', function() {
									// This handler will be called, but end is already set
								});
							}
							
							// Re-confirm end values (in case anything changed them)
							var currentEndDate = $endDate.length ? $endDate.val() : '';
							var currentEndTime = $endTime.length ? $endTime.val() : '';
							
							if ($endDate.length && currentEndDate !== endDateFormatted) {
								console.warn('[openQuickCreateFromDrag] End date was changed, restoring:', endDateFormatted);
								$endDate.val(endDateFormatted);
								$endDate.data('userChangedDateTime', 1);
							}
							if ($endTime.length && currentEndTime !== endTimeFormatted) {
								console.warn('[openQuickCreateFromDrag] End time was changed, restoring:', endTimeFormatted);
								$endTime.val(endTimeFormatted);
								$endTime.data('userChangedDateTime', 1);
							}
						}, 150);
					}, 100);
				}, 50);
			} else {
				// For Task: Just populate normally
				jQuery.each(prefillData, function(name, value) {
					var field = form.find('[name="' + name + '"]');
					if (field.length) {
						console.log('[openQuickCreateFromDrag] Setting field:', name, '=', value);
						field.val(value);
						
						// Register date/time field events
						if (name.indexOf('date') >= 0 || name === 'task_deadline') {
							vtUtils.registerEventForDateFields(field);
							field.trigger('change');
						}
						if (name.indexOf('time') >= 0) {
							vtUtils.registerEventForTimeFields(field);
							field.trigger('change');
						}
					} else {
						console.warn('[openQuickCreateFromDrag] Field not found:', name);
					}
				});
			}
		};
		
		// Listen for QuickCreate form show event (primary method)
		app.event.one('post.QuickCreateForm.show', function(e, form) {
			console.log('[openQuickCreateFromDrag] post.QuickCreateForm.show event fired');
			
			// Set flag to prevent auto-calculation during population
			window._calendarDragPopulating = true;
			
			// Populate fields
			populateFields(form);
			
			// Clear flag after a delay to allow fields to be set
			setTimeout(function() {
				window._calendarDragPopulating = false;
			}, 500);
		});
		
		// Also listen for Bootstrap modal shown event (backup)
		jQuery(document).one('shown.bs.modal', '.modal', function() {
			console.log('[openQuickCreateFromDrag] shown.bs.modal event fired');
			var form = jQuery(this).find('form[name="QuickCreate"]');
			if (!form.length) {
				form = jQuery(this).find('form');
			}
			if (form.length) {
				// Retry with delay to ensure fields are rendered
				setTimeout(function() {
					populateFields(form);
				}, 100);
			}
		});
		
		// Trigger QuickCreate click to open form immediately
		console.log('[openQuickCreateFromDrag] Triggering QuickCreate click');
		quickCreateBtn.trigger('click');
		
		// Cleanup: Unselect calendar selection
		thisInstance.getCalendarViewContainer().fullCalendar('unselect');
		thisInstance._dragSelection = null;
	},
	/**
	 * Register Task-specific QuickCreate enhancements
	 * SAFE: Only enhances Task form, does not affect Event or core logic
	 */
	registerTaskQuickCreateEnhancements: function() {
		var thisInstance = this;
		
		// Listen for QuickCreate form shown
		app.event.on('post.QuickCreateForm.show', function(e, form) {
			// Hide end date/time fields IMMEDIATELY (before any delay) to prevent flash
			thisInstance.hideEventEndFields(form);
			
			setTimeout(function() {
				thisInstance.enhanceTaskQuickCreate(form);
				// Confirm hide again after fields are fully rendered
				thisInstance.hideEventEndFields(form);
			}, 200);
		});
	},
	/**
	 * Hide End Date & End Time fields for Event QuickCreate (UI only)
	 * SAFE: Only hides fields, backend values remain intact
	 * Hides: Only "End Date & Time" field at bottom (in table), keeps top section with "To" label visible
	 * SMOOTH: Uses CSS to hide immediately, then confirms with JS
	 */
	hideEventEndFields: function(form) {
		if (!form || !jQuery(form).length) return;
		
		var $form = jQuery(form);
		var $modal = $form.closest('.modal-content, .modal');
		if (!$modal.length) {
			$modal = jQuery('.modal-content');
		}
		if (!$modal.length) return;
		
		// Detect if this is an Event form (not Task)
		var activityType = $form.find('[name="activitytype"]').val();
		var moduleName = $form.find('[name="module"]').val();
		
		var isEvent = false;
		if (activityType === 'Events' || moduleName === 'Events') {
			isEvent = true;
		} else if (moduleName === 'Calendar' && activityType !== 'Task') {
			isEvent = true;
		}
		
		if (isEvent) {
			// IMMEDIATE: Hide fields using CSS first (no flash)
			// Find and hide end date/time fields in table immediately
			var hideFieldsImmediate = function() {
				var $endDateField = $modal.find('input[name="due_date"]');
				var $endTimeField = $modal.find('input[name="time_end"]');
				
				// Hide due_date field row in table (but NOT in calendar-event-datetime-section)
				$endDateField.each(function() {
					var $field = jQuery(this);
					var $row = $field.closest('tr');
					// Only hide if it's in massEditTable, not in calendar-event-datetime-section
					if ($row.length > 0 && $row.closest('.calendar-event-datetime-section').length === 0) {
						// Hide immediately with CSS
						$row.css('display', 'none');
						// Also hide previous row if it's the label row
						var $prevRow = $row.prev('tr');
						if ($prevRow.length > 0 && $prevRow.find('.fieldLabel').length > 0) {
							var labelText = $prevRow.find('.fieldLabel').text().toLowerCase();
							if (labelText.indexOf('end') >= 0 || labelText.indexOf('due') >= 0) {
								$prevRow.css('display', 'none');
							}
						}
					}
				});
				
				// Hide time_end field row in table (but NOT in calendar-event-datetime-section)
				$endTimeField.each(function() {
					var $field = jQuery(this);
					var $row = $field.closest('tr');
					// Only hide if it's in massEditTable, not in calendar-event-datetime-section
					if ($row.length > 0 && $row.closest('.calendar-event-datetime-section').length === 0) {
						// Hide immediately with CSS
						$row.css('display', 'none');
						// Also hide previous row if it's the label row
						var $prevRow = $row.prev('tr');
						if ($prevRow.length > 0 && $prevRow.find('.fieldLabel').length > 0) {
							var labelText = $prevRow.find('.fieldLabel').text().toLowerCase();
							if (labelText.indexOf('end') >= 0 || labelText.indexOf('time') >= 0) {
								$prevRow.css('display', 'none');
							}
						}
					}
				});
			};
			
			// Try to hide immediately (if fields are already rendered)
			hideFieldsImmediate();
			
			// Also hide after a short delay to catch dynamically rendered fields
			setTimeout(hideFieldsImmediate, 50);
			setTimeout(hideFieldsImmediate, 150);
		}
	},
	/**
	 * Enhance Task QuickCreate form
	 * SAFE: Only modifies Task form UI, defensive checks throughout
	 */
	enhanceTaskQuickCreate: function(form) {
		if (!form || !jQuery(form).length) return;
		
		var $form = jQuery(form);
		var $container = $form.closest('#QuickCreate, .modal-body');
		if (!$container.length) return;
		
		// Detect if this is a Task form
		var activityType = $form.find('[name="activitytype"]').val();
		var moduleName = $form.find('[name="module"]').val();
		
		// Task detection: activitytype === 'Task' OR module === 'Calendar' (defaults to Task)
		var isTask = false;
		if (activityType === 'Task') {
			isTask = true;
		} else if (moduleName === 'Calendar' && (!activityType || activityType === '')) {
			// Calendar module defaults to Task if activitytype not set
			isTask = true;
		}
		
		if (!isTask) {
			// Not a Task form, skip enhancements
			return;
		}
		
		// Enhance time pickers for Task (15-minute step, manual typing)
		this.enhanceTaskTimePickers($container);
		
		// Setup All Day toggle for Task
		this.setupTaskAllDayToggle($container);
		
		// Setup deadline field for Task
		this.setupTaskDeadlineField($container);
		
		// Reorganize time fields layout for Task
		this.reorganizeTaskTimeLayout($container);
	},
	/**
	 * Enhance time pickers for Task: 15-minute step, allow manual typing
	 * SAFE: Only updates UI, does not change form submission
	 */
	enhanceTaskTimePickers: function($container) {
		if (!$container || !$container.length) return;
		
		var $timeFields = $container.find('input[name="time_start"], input[name="time_end"]');
		if ($timeFields.length === 0) return;
		
		setTimeout(function() {
			$timeFields.each(function() {
				var $field = jQuery(this);
				
				// Destroy existing timepicker if it exists
				if ($field.data('timepicker-list')) {
					try {
						$field.timepicker('remove');
					} catch(e) {
						// Ignore errors
					}
				}
				
				var timeFormat = $field.data('format');
				if (timeFormat == '24') {
					timeFormat = 'H:i';
				} else {
					timeFormat = 'h:i A';
				}
				
				// Re-initialize with 15-minute step
				try {
					$field.timepicker({
						timeFormat: timeFormat,
						step: 15, // 15-minute intervals
						disableTextInput: false, // Allow manual typing
						className: 'timePicker calendar-task-timepicker'
					});
				} catch(e) {
					// If timepicker fails, continue without enhancement
					console.warn('Calendar Task: Timepicker enhancement failed', e);
				}
			});
		}, 500);
	},
	/**
	 * Setup All Day toggle for Task
	 * SAFE: Only hides/shows time inputs, does not remove from DOM
	 */
	setupTaskAllDayToggle: function($container) {
		if (!$container || !$container.length) return;
		
		var $allDayCheckbox = $container.find('input[name="allday"], #calendar_allday');
		if ($allDayCheckbox.length === 0) return;
		
		var $timeStart = $container.find('input[name="time_start"]');
		var $timeEnd = $container.find('input[name="time_end"]');
		
		// Find time field containers
		var $timeStartContainer = $timeStart.closest('.input-group, .fieldValue, .calendar-task-time-wrapper').parent();
		var $timeEndContainer = $timeEnd.closest('.input-group, .fieldValue, .calendar-task-time-wrapper').parent();
		
		if ($timeStartContainer.length === 0) {
			$timeStartContainer = $timeStart.closest('div').parent();
		}
		if ($timeEndContainer.length === 0) {
			$timeEndContainer = $timeEnd.closest('div').parent();
		}
		
		$allDayCheckbox.off('change.calendar-task-allday').on('change.calendar-task-allday', function() {
			var isAllDay = jQuery(this).is(':checked');
			
			if (isAllDay) {
				// Hide time fields (but keep in DOM)
				$timeStartContainer.hide();
				$timeEndContainer.hide();
			} else {
				// Show time fields
				$timeStartContainer.show();
				$timeEndContainer.show();
			}
		});
	},
	/**
	 * Setup deadline field for Task
	 * SAFE: Only initializes date picker, does not change form submission
	 */
	setupTaskDeadlineField: function($container) {
		if (!$container || !$container.length) return;
		
		var $deadlineField = $container.find('#task_deadline');
		if ($deadlineField.length === 0) return;
		
		// Initialize date picker if not already initialized
		setTimeout(function() {
			if ($deadlineField.hasClass('dateField') && !$deadlineField.data('datepicker')) {
				try {
					vtUtils.registerEventForDateFields($deadlineField);
				} catch(e) {
					// Silently fail
				}
			}
		}, 300);
	},
	/**
	 * Sync task_deadline to due_date on form submit (Task only)
	 * SAFE: Only updates form field values before submit, does not change submission logic
	 */
	syncTaskDeadlineToDueDate: function($container) {
		if (!$container || !$container.length) return;
		
		var $form = $container.closest('form[name="QuickCreate"]');
		if ($form.length === 0) return;
		
		var $deadlineField = $container.find('#task_deadline');
		var $dueDateField = $container.find('input[name="due_date"]');
		
		if ($deadlineField.length === 0 || $dueDateField.length === 0) return;
		
		// On form submit, sync deadline to due_date
		$form.on('submit.calendar-task-deadline', function() {
			var deadlineValue = $deadlineField.val();
			if (deadlineValue) {
				// Copy deadline value to due_date
				$dueDateField.val(deadlineValue);
			}
		});
	},
	/**
	 * Reorganize time fields layout for Task
	 * SAFE: Only moves DOM elements, does not change form structure
	 */
	reorganizeTaskTimeLayout: function($container) {
		if (!$container || !$container.length) return;
		
		var $taskTimeSection = $container.find('.calendar-task-datetime-section');
		if ($taskTimeSection.length === 0) return;
		
		var $timeStart = $container.find('input[name="time_start"]');
		var $timeEnd = $container.find('input[name="time_end"]');
		
		if ($timeStart.length === 0 || $timeEnd.length === 0) return;
		
		// Find time field containers in RECORD_STRUCTURE loop (massEditTable)
		var $timeStartRow = $timeStart.closest('tr');
		var $timeEndRow = $timeEnd.closest('tr');
		
		// Move time_start to "From" container
		var $fromContainer = $taskTimeSection.find('.calendar-task-time-wrapper').first();
		if ($fromContainer.length > 0 && $timeStartRow.length > 0) {
			// Find the input group or field value container
			var $timeStartField = $timeStart.closest('.input-group, .fieldValue');
			if ($timeStartField.length > 0) {
				// Clone and append to "From" container
				var $cloned = $timeStartField.clone();
				$fromContainer.append($cloned);
				// Hide original row
				$timeStartRow.hide();
			}
		}
		
		// Move time_end to "To" container
		var $toContainer = $taskTimeSection.find('.calendar-task-time-wrapper').last();
		if ($toContainer.length > 0 && $timeEndRow.length > 0) {
			// Find the input group or field value container
			var $timeEndField = $timeEnd.closest('.input-group, .fieldValue');
			if ($timeEndField.length > 0) {
				// Clone and append to "To" container
				var $cloned = $timeEndField.clone();
				$toContainer.append($cloned);
				// Hide original row
				$timeEndRow.hide();
			}
		}
	}
});
