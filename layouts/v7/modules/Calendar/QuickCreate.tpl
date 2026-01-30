{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<form class="form-horizontal recordEditView" id="QuickCreate" name="QuickCreate" method="post" action="index.php">
				{if $MODE eq 'edit' && !empty($RECORD_ID)}
					{assign var=HEADER_TITLE value={vtranslate('LBL_EDITING', $MODULE)}|cat:" "|cat:{vtranslate('SINGLE_'|cat:$MODULE, $MODULE)}}
				{else}
					{assign var=HEADER_TITLE value={vtranslate('LBL_QUICK_CREATE', $MODULE)}|cat:" "|cat:{vtranslate('SINGLE_'|cat:$MODULE, $MODULE)}}
				{/if}
				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

				<div class="modal-body">
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
					{/if}
					<input type="hidden" name="module" value="{$MODULE}">
					<input type="hidden" name="action" value="SaveAjax">
					<input type="hidden" name="calendarModule" value="{$MODULE}">
					<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
					<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
					{if $MODE eq 'edit' && !empty($RECORD_ID)}
						<input type="hidden" name="record" value="{$RECORD_ID}" />
						<input type="hidden" name="mode" value="{$MODE}" />
					{else}
						<input type="hidden" name="record" value="">
					{/if}

					{assign var="RECORD_STRUCTURE_MODEL" value=$QUICK_CREATE_CONTENTS[$MODULE]['recordStructureModel']}
					{assign var="RECORD_STRUCTURE" value=$QUICK_CREATE_CONTENTS[$MODULE]['recordStructure']}
					{assign var="BLOCK_FIELDS" value=$QUICK_CREATE_CONTENTS[$MODULE]['recordStructure']} {* Dependency in Time UiType template *}
					{assign var="MODULE_MODEL" value=$QUICK_CREATE_CONTENTS[$MODULE]['moduleModel']}
					{* Detect Task vs Event *}
					{assign var="ACTIVITY_TYPE_MODEL" value=$RECORD_STRUCTURE['activitytype']}
					{assign var="IS_TASK" value=false}
					{if $ACTIVITY_TYPE_MODEL}
						{assign var="ACTIVITY_TYPE_VALUE" value=$ACTIVITY_TYPE_MODEL->get('fieldvalue')}
						{if $ACTIVITY_TYPE_VALUE eq 'Task'}
							{assign var="IS_TASK" value=true}
						{/if}
					{elseif $MODULE eq 'Calendar'}
						{* Calendar module defaults to Task if activitytype not set *}
						{assign var="IS_TASK" value=true}
					{/if}

					<div class="quickCreateContent calendarQuickCreateContent {if $IS_TASK}calendar-task-quickcreate{else}calendar-event-quickcreate{/if}" style="padding-top:2%;margin-top:5px;">
						{if $MODULE eq 'Calendar'}
							{if !empty($PICKIST_DEPENDENCY_DATASOURCE_TODO)}
								<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE_TODO)}' />
							{/if}
						{else}
							{if !empty($PICKIST_DEPENDENCY_DATASOURCE_EVENT)}
								<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE_EVENT)}' />
							{/if}
						{/if}

						<div>
							{assign var="FIELD_MODEL" value=$RECORD_STRUCTURE['subject']}
							<div style="margin-left: 14px;width: 95%;">
								{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
								{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
								<input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" type="text" class="inputElement {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}" value="{$FIELD_MODEL->get('fieldvalue')}"
									   {if $FIELD_MODEL->get('uitype') eq '3' || $FIELD_MODEL->get('uitype') eq '4'|| $FIELD_MODEL->isReadOnly()} readonly {/if} {if !empty($SPECIAL_VALIDATOR)}data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}"{/if}  
									   {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
									   {foreach item=VALIDATOR from=$FIELD_INFO["validator"]}
										   {assign var=VALIDATOR_NAME value=$VALIDATOR["name"]}
										   data-rule-{$VALIDATOR_NAME} = "true" 
									   {/foreach}
									   placeholder="{vtranslate($FIELD_MODEL->get('label'), $MODULE)} *" style="width: 100%;"/>
							</div>
						</div>

						{* Date & Time Section - Different layout for Task vs Event *}
						{if $IS_TASK}
							{* Task Layout: Single Date picker + All Day checkbox (NO From/To time) *}
							<div class="row calendar-task-datetime-section" style="padding-top: 2%;">
								<div class="col-sm-12">
									<div class="col-sm-6 calendar-task-date-wrapper">
										<label class="control-label" style="font-size: 12px; color: #666; margin-bottom: 5px;">Date</label>
										{assign var="FIELD_MODEL" value=$RECORD_STRUCTURE['date_start']}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
									</div>
								</div>
								{* All Day Toggle for Task *}
								<div class="col-sm-12" style="margin-top: 10px; padding-left: 14px;">
									<label class="checkbox-inline">
										<input type="checkbox" name="allday" id="calendar_allday" value="1" />
										<strong>All Day</strong>
									</label>
								</div>
							</div>
						{else}
							{* Event Layout: Keep existing layout *}
							<div class="row calendar-event-datetime-section" style="padding-top: 2%;">
								<div class="col-sm-12">
									<div class="col-sm-5 calendar-date-time-wrapper">
										{assign var="FIELD_MODEL" value=$RECORD_STRUCTURE['date_start']}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
									</div>
									<div class="muted col-sm-1" style="line-height: 67px;left: 20px; padding-right: 7%;">
										{vtranslate('LBL_TO',$MODULE)}
									</div>
									<div class="col-sm-5 calendar-date-time-wrapper">
										{assign var="FIELD_MODEL" value=$RECORD_STRUCTURE['due_date']}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
									</div>
								</div>
								{* All Day Toggle and Duration Display for Event *}
								<div class="col-sm-12" style="margin-top: 10px; padding-left: 14px;">
									<label class="checkbox-inline">
										<input type="checkbox" name="allday" id="calendar_allday" value="1" />
										<strong>All Day</strong>
									</label>
									<span id="calendar-duration-display" style="margin-left: 15px; color: #666; font-size: 12px;"></span>
								</div>
							</div>
						{/if}
						<div class="container-fluid paddingTop15">
							<table class="massEditTable table no-border">
								<tr>
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
									{if $FIELD_NAME eq 'subject' || $FIELD_NAME eq 'date_start'}
								</tr>{continue}
								{/if}
								{* For Task: Hide time_start, time_end, due_date (we show deadline separately), Location, Meeting link *}
								{* For Event: Hide time_start (it's shown with date_start), keep due_date in loop *}
								{if $IS_TASK}
									{if $FIELD_NAME eq 'time_start' || $FIELD_NAME eq 'time_end' || $FIELD_NAME eq 'due_date' || $FIELD_NAME eq 'location' || $FIELD_NAME eq 'meeting_link'}
								</tr>{continue}
									{/if}
								{else}
									{* For Event: Hide time_start from RECORD_STRUCTURE loop (it's shown with date_start) *}
									{if $FIELD_NAME eq 'time_start'}
								</tr>{continue}
									{/if}
									{* For Event: Show time_end and due_date fields normally *}
									{if $FIELD_NAME eq 'time_end' || $FIELD_NAME eq 'due_date'}
								</tr>
								<tr>
								{else}
								</tr><tr>
								{/if}
								{/if}
								{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
								{assign var="referenceList" value=$FIELD_MODEL->getReferenceList()}
								{assign var="referenceListCount" value=php7_count($referenceList)}
								{if $FIELD_MODEL->get('uitype') eq "19"}
								{if $COUNTER eq '1'}
								<td></td><td></td></tr><tr>
									{assign var=COUNTER value=0}
									{/if}
									{/if}
								</tr><tr>
									<td class='fieldLabel col-lg-3'>
										{if $isReferenceField neq "reference"}<label class="muted">{/if}
											{if $isReferenceField eq "reference"}
												{if $referenceListCount > 1}
													{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
													{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
													{if !empty($REFERENCED_MODULE_STRUCT)}
														{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
													{/if}
													<span class="">
														<select style="width: 150px;" class="select2 referenceModulesList">
															{foreach key=index item=value from=$referenceList}
																<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if} >{vtranslate($value, $value)}</option>
															{/foreach}
														</select>
													</span>
												{else}
													<label class="muted">{vtranslate($FIELD_MODEL->get('label'), $MODULE)} &nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}</label>
												{/if}
											{else}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
											{/if}
											{if $isReferenceField neq "reference"}</label>{/if}
									</td>
									<td class="fieldValue col-lg-9" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
									</td>
									{/foreach}
								</tr>
							</table>
						</div>
						
						{* Repeat Section (UI Only - Phase 1) *}
						<div class="calendar-repeat-section" style="margin-top: 15px; padding: 10px 15px; background: #f9f9f9; border-radius: 4px;">
							<div class="form-group" style="margin-bottom: 0;">
								<label class="control-label" style="font-size: 12px; font-weight: bold; margin-bottom: 5px;">Repeat</label>
								<select name="calendar_repeat_type" id="calendar_repeat_type" class="form-control" style="font-size: 13px;">
									<option value="">Does not repeat</option>
									<option value="Daily">Daily</option>
									<option value="Weekly">Weekly</option>
									<option value="Monthly">Monthly</option>
									<option value="Yearly">Yearly</option>
								</select>
								<input type="hidden" name="recurringtype" id="calendar_recurringtype_hidden" value="" />
							</div>
						</div>
						
						{* Optional Fields Section - Different for Task vs Event *}
						{if $IS_TASK}
							{* Task: Deadline field + Description only *}
							<div class="calendar-task-optional-fields" style="margin-top: 15px; padding: 10px 15px; background: #f9f9f9; border-radius: 4px;">
								<h5 style="margin-top: 0; margin-bottom: 12px; font-size: 13px; color: #666; font-weight: bold;">Optional Details</h5>
								<div class="form-group" style="margin-bottom: 10px;">
									<label class="control-label" style="font-size: 12px;">Add deadline</label>
									{* Use existing due_date field if available, else create UI-only field *}
									{assign var="DEADLINE_FIELD" value=$RECORD_STRUCTURE['due_date']}
									{if $DEADLINE_FIELD}
										{* due_date already exists, will be rendered in RECORD_STRUCTURE loop *}
										<input type="text" name="task_deadline" id="task_deadline" class="form-control dateField" placeholder="Select deadline" style="font-size: 13px;" data-date-format="{$USER_MODEL->get('date_format')}" />
									{else}
										<input type="text" name="task_deadline" id="task_deadline" class="form-control dateField" placeholder="Select deadline" style="font-size: 13px;" data-date-format="{$USER_MODEL->get('date_format')}" />
									{/if}
								</div>
								<div class="form-group" style="margin-bottom: 0;">
									<label class="control-label" style="font-size: 12px;">Description</label>
									<textarea name="description" class="form-control" rows="3" placeholder="Add description" style="font-size: 13px; resize: vertical;"></textarea>
								</div>
							</div>
						{else}
							{* Event: Location, Meeting Link, Description *}
							<div class="calendar-optional-fields" style="margin-top: 15px; padding: 10px 15px; background: #f9f9f9; border-radius: 4px;">
								<h5 style="margin-top: 0; margin-bottom: 12px; font-size: 13px; color: #666; font-weight: bold;">Optional Details</h5>
								<div class="form-group" style="margin-bottom: 10px;">
									<label class="control-label" style="font-size: 12px;">Location</label>
									<input type="text" name="location" class="form-control" placeholder="Add location" style="font-size: 13px;" />
								</div>
								<div class="form-group" style="margin-bottom: 10px;">
									<label class="control-label" style="font-size: 12px;">Meeting Link</label>
									<input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/..." style="font-size: 13px;" />
								</div>
								<div class="form-group" style="margin-bottom: 0;">
									<label class="control-label" style="font-size: 12px;">Description</label>
									<textarea name="description" class="form-control" rows="3" placeholder="Add description" style="font-size: 13px; resize: vertical;"></textarea>
								</div>
							</div>
						{/if}
					</div>
				</div>
				<div class="modal-footer">
					<center>
						{if $BUTTON_NAME neq null}
							{assign var=BUTTON_LABEL value=$BUTTON_NAME}
						{else}
							{assign var=BUTTON_LABEL value={vtranslate('LBL_SAVE', $MODULE)}}
						{/if}
						{assign var="CALENDAR_MODULE_MODEL" value=$QUICK_CREATE_CONTENTS['Calendar']['moduleModel']}
						{assign var="EDIT_VIEW_URL" value=$CALENDAR_MODULE_MODEL->getCreateTaskRecordUrl()}
						{if $MODULE eq 'Events'}
							{assign var="EDIT_VIEW_URL" value=$CALENDAR_MODULE_MODEL->getCreateEventRecordUrl()}
						{/if}
						<button class="btn btn-default" id="goToFullForm" data-edit-view-url="{$EDIT_VIEW_URL}" type="button"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button>
						<button {if $BUTTON_ID neq null} id="{$BUTTON_ID}" {/if} class="btn btn-success" type="submit" name="saveButton"><strong>{$BUTTON_LABEL}</strong></button>
						<a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
					</center>
				</div>
			</form>
		</div>
		{if $FIELDS_INFO neq null}
			<script type="text/javascript">
				var quickcreate_uimeta = (function () {
					var fieldInfo = {$FIELDS_INFO};
					return {
						field: {
							get: function (name, property) {
								if (name && property === undefined) {
									return fieldInfo[name];
								}
								if (name && property) {
									return fieldInfo[name][property]
								}
							},
							isMandatory: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].mandatory;
								}
								return false;
							},
							getType: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].type;
								}
								return false;
							}
						},
					};
				})();
			</script>
		{/if}
	</div>
{/strip}
