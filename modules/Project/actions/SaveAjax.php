<?php
/* ***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class Project_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('saveColor');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}

		$fieldToBeSaved = (string)$request->get('field');
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);

		try {
			vglobal('MK_PROJECT_SAVEAJAX_INLINE', 1);
			vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', $request->get('_timeStampNoChangeMode', false));
			$recordModel = $this->saveRecord($request);
			vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', false);
			vglobal('MK_PROJECT_SAVEAJAX_INLINE', 0);

			$result = $this->buildInlineSaveResult($recordModel, $fieldToBeSaved);
			$response->setResult($result);
		} catch (DuplicateException $e) {
			$response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
		} catch (\Throwable $e) {
			$response->setError($e->getMessage());
		}

		$response->emit();
	}

	/**
	 * Return only the inline-edited field (+ record meta) to avoid 500s from getDisplayValue on unrelated fields.
	 */
	protected function buildInlineSaveResult($recordModel, $fieldToBeSaved) {
		$result = array(
			'_recordLabel' => decode_html($recordModel->getName()),
			'_recordId' => $recordModel->getId(),
		);

		if ($fieldToBeSaved === '') {
			return $result;
		}

		$fieldModel = $recordModel->getModule()->getField($fieldToBeSaved);
		if (!$fieldModel || !$fieldModel->isViewable()) {
			return $result;
		}

		$picklistColorMap = array();
		$recordFieldValue = $recordModel->get($fieldToBeSaved);
		$fieldDataType = $fieldModel->getFieldDataType();

		if (is_array($recordFieldValue) && $fieldDataType == 'multipicklist') {
			foreach ($recordFieldValue as $picklistValue) {
				$picklistColorMap[$picklistValue] = Settings_Picklist_Module_Model::getPicklistColorByValue($fieldToBeSaved, $picklistValue);
			}
			$recordFieldValue = implode(' |##| ', $recordFieldValue);
		}
		if ($fieldDataType == 'picklist' && $recordFieldValue !== '' && $recordFieldValue !== null) {
			$picklistColorMap[$recordFieldValue] = Settings_Picklist_Module_Model::getPicklistColorByValue($fieldToBeSaved, $recordFieldValue);
		}

		$fieldValue = $displayValue = Vtiger_Util_Helper::toSafeHTML($recordFieldValue);
		if ($fieldDataType !== 'currency' && $fieldDataType !== 'datetime' && $fieldDataType !== 'date' && $fieldDataType !== 'double') {
			$displayValue = $fieldModel->getDisplayValue($fieldValue, $recordModel->getId());
		}
		if ($fieldDataType == 'currency') {
			$displayValue = Vtiger_Currency_UIType::transformDisplayValue(Vtiger_Currency_UIType::convertToDBFormat($fieldValue));
		}

		if (!empty($picklistColorMap) && ($fieldDataType == 'picklist' || $fieldDataType == 'multipicklist')) {
			$result[$fieldToBeSaved] = array(
				'value' => $fieldValue,
				'display_value' => $displayValue,
				'colormap' => $picklistColorMap,
			);
		} else {
			$result[$fieldToBeSaved] = array(
				'value' => $fieldValue,
				'display_value' => $displayValue,
			);
		}

		return $result;
	}

	public function saveRecord(Vtiger_Request $request) {
		$recordModel = parent::saveRecord($request);
		$projectId = (int)$recordModel->getId();
		if ($projectId <= 0 || !$this->shouldSyncTeamGroupFromRequest($request)) {
			return $recordModel;
		}

		$this->ensureProjectAssignTables();
		if (!$this->projectTeamGroupTableExists()) {
			return $recordModel;
		}

		$db = PearDatabase::getInstance();
		$teamGroupId = $this->resolveTeamGroupIdFromRequest($request);
		$db->pquery("DELETE FROM vtiger_project_team_groups WHERE projectid = ?", array($projectId));
		if ($teamGroupId > 0) {
			$db->pquery(
				"INSERT INTO vtiger_project_team_groups (projectid, team_groupid) VALUES (?, ?)",
				array($projectId, $teamGroupId)
			);
		}

		if ($request->has('_additional_assignees') && $this->projectAssigneesTableExists()) {
			$assignees = $request->get('_additional_assignees');
			if (!is_array($assignees)) {
				$assignees = array();
			}
			$db->pquery("DELETE FROM vtiger_project_assignees WHERE projectid = ?", array($projectId));
			foreach ($assignees as $uid) {
				$uid = (int)$uid;
				if ($uid > 0) {
					$db->pquery(
						"INSERT IGNORE INTO vtiger_project_assignees (projectid, userid) VALUES (?, ?)",
						array($projectId, $uid)
					);
				}
			}
		}

		return $recordModel;
	}

	protected function shouldSyncTeamGroupFromRequest(Vtiger_Request $request) {
		$field = (string)$request->get('field');
		if ($field === 'assigned_user_id' || $request->has('assigned_user_id')) {
			return true;
		}
		if ($request->has('_team_group_id') && (int)$request->get('_team_group_id') > 0) {
			return true;
		}
		if ($request->has('_additional_assignees')) {
			return true;
		}
		return false;
	}

	protected function ensureProjectAssignTables() {
		if (class_exists('Teams_Module_Model')) {
			Teams_Module_Model::ensureProjectAssignSchema();
		}
	}

	protected function projectTeamGroupTableExists() {
		$db = PearDatabase::getInstance();
		$res = $db->pquery("SHOW TABLES LIKE 'vtiger_project_team_groups'", array());
		return $res && $db->num_rows($res) > 0;
	}

	protected function projectAssigneesTableExists() {
		$db = PearDatabase::getInstance();
		$res = $db->pquery("SHOW TABLES LIKE 'vtiger_project_assignees'", array());
		return $res && $db->num_rows($res) > 0;
	}

	protected function resolveTeamGroupIdFromRequest(Vtiger_Request $request) {
		$rawOwner = $request->get('assigned_user_id');
		$field = (string)$request->get('field');
		if (($rawOwner === null || $rawOwner === '') && $field === 'assigned_user_id') {
			$rawOwner = $request->get('value');
		}

		if ($rawOwner !== null && $rawOwner !== '') {
			$ownerId = (int)$rawOwner;
			if ($ownerId < 0) {
				return abs($ownerId);
			}
			return 0;
		}

		return (int)$request->get('_team_group_id');
	}

	function saveColor(Vtiger_Request $request) {
		$db = PearDatabase::getInstance();
		$color = $request->get('color');
		$status = $request->get('status');

		$db->pquery('INSERT INTO vtiger_projecttask_status_color(status,color) VALUES(?,?) ON DUPLICATE KEY UPDATE color = ?', array($status, $color, $color));
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult(true);
		$response->emit();
	}

}
