<?php
/*+***********************************************************************************
 * Add a comment to an announcement (by creatorid).
 *************************************************************************************/

class Home_AddAnnouncementCommentAjax_Action extends Vtiger_Action_Controller {

	public function process(Vtiger_Request $request) {
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		try {
			$announcementId = (int)$request->get('id');
			$commentText = $request->get('comment_text');
			if (!$announcementId || $commentText === null || trim($commentText) === '') {
				$response->setError('Missing id or comment_text');
				$response->emit();
				return;
			}
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$ann = Settings_Vtiger_Announcement_Model::getById($announcementId, $currentUser->getId());
			if (!$ann) {
				$response->setError('Not found or access denied');
				$response->emit();
				return;
			}
			$cid = Settings_Vtiger_Announcement_Model::addComment($announcementId, $currentUser->getId(), trim($commentText));
			$comments = Settings_Vtiger_Announcement_Model::getComments($announcementId);
			$response->setResult(array('success' => true, 'id' => $cid, 'comments' => $comments));
		} catch (Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}
}
