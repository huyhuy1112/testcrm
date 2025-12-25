<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

require_once 'data/VTEntityDelta.php';

class CalendarHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $log, $adb;

		try {
			// STRICT: Handle ONLY vtiger.entity.aftersave.final (after commit)
			if ($eventName !== 'vtiger.entity.aftersave.final') {
				return;
			}

			$moduleName = $entityData->getModuleName();
			if ($moduleName !== 'Calendar') {
				return;
			}

			$recordId = $entityData->getId();
			if (empty($recordId)) {
				return;
			}

			// Check if this is a Task (not Event)
			$activityType = $entityData->get('activitytype');
			if (empty($activityType)) {
				// Fallback: check from database
				$typeResult = $adb->pquery("SELECT activitytype FROM vtiger_activity WHERE activityid = ?", array($recordId));
				if ($adb->num_rows($typeResult) > 0) {
					$activityType = $adb->query_result($typeResult, 0, 'activitytype');
				}
			}
			
			// Only handle Tasks, not Events
			if ($activityType !== 'Task') {
				return;
			}

			// Get owner from vtiger_crmentity (after commit, data is committed)
			$ownerResult = $adb->pquery("SELECT smownerid FROM vtiger_crmentity WHERE crmid = ?", array($recordId));
			if ($adb->num_rows($ownerResult) == 0) {
				return;
			}
			
			$newOwnerId = $adb->query_result($ownerResult, 0, 'smownerid');
			if (empty($newOwnerId)) {
				return;
			}

			// Check if owner changed using VTEntityDelta
			$delta = new VTEntityDelta();
			$changes = $delta->getEntityDelta('Calendar', $recordId);

			// If no change OR 'assigned_user_id' not in $changes, check if it's a new record
			$isNew = $entityData->isNew();
			$shouldNotify = false;

			if ($isNew) {
				// New record - always notify
				$shouldNotify = true;
			} else if (!empty($changes) && isset($changes['assigned_user_id'])) {
				// Existing record - check if assigned user changed
				$oldOwnerId = isset($changes['assigned_user_id']['oldValue']) ? $changes['assigned_user_id']['oldValue'] : null;
				$newOwnerIdFromDelta = isset($changes['assigned_user_id']['currentValue']) ? $changes['assigned_user_id']['currentValue'] : null;

				// Parse webservice ID format if needed (e.g., "19x123")
				if (!empty($newOwnerIdFromDelta) && strpos($newOwnerIdFromDelta, 'x') !== false) {
					$newOwnerIdParts = explode('x', $newOwnerIdFromDelta);
					$newOwnerIdFromDelta = isset($newOwnerIdParts[1]) ? $newOwnerIdParts[1] : $newOwnerIdParts[0];
				}

				if ($oldOwnerId != $newOwnerIdFromDelta && !empty($newOwnerIdFromDelta)) {
					$shouldNotify = true;
					$newOwnerId = $newOwnerIdFromDelta;
				}
			}

			if (!$shouldNotify) {
				return;
			}

			// Verify new owner is USER (not GROUP)
			$userCheck = $adb->pquery("SELECT id FROM vtiger_users WHERE id = ?", array($newOwnerId));
			if ($adb->num_rows($userCheck) == 0) {
				// Owner is GROUP, not USER - exit
				return;
			}

			// Get Task subject
			$taskSubject = $entityData->get('subject');
			if (empty($taskSubject)) {
				// Fallback: get from database
				$nameResult = $adb->pquery("SELECT subject FROM vtiger_activity WHERE activityid = ?", array($recordId));
				if ($adb->num_rows($nameResult) > 0) {
					$taskSubject = $adb->query_result($nameResult, 0, 'subject');
				}
			}
			if (empty($taskSubject)) {
				$taskSubject = 'Task #' . $recordId;
			}

			// Insert notification (after commit, so it won't be rolled back)
			$message = "Bạn được assign vào Task: " . $taskSubject;
			$insertSql = "INSERT INTO vtiger_notifications (userid, module, recordid, message, created_at) VALUES (?, 'Calendar', ?, ?, NOW())";
			$adb->pquery($insertSql, array($newOwnerId, $recordId, $message));

		} catch (Exception $e) {
			if ($log) {
				$log->error("[CalendarHandler] Error creating notification: " . $e->getMessage());
			}
		}
	}
}

