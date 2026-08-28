<?php
/*+***********************************************************************************
 * Activities_Detail_View – show one activity.
 * URL: index.php?module=Activities&view=Detail&record=ID
 ************************************************************************************/

class Activities_Detail_View extends Vtiger_Detail_View {
	public function requiresPermission(Vtiger_Request $request) {
		return [];
	}

	public function checkPermission(Vtiger_Request $request) {
		return true;
	}

	public function process(Vtiger_Request $request) {
		$recordId = (int)$request->get('record');
		$adb = PearDatabase::getInstance();
		$row = null;

		if ($recordId > 0) {
			$res = $adb->pquery(
				"SELECT a.*, ce.smownerid, ce.createdtime,
				        u.first_name, u.last_name,
				        org.accountname AS org_name,
				        pr.projectname AS project_name
				   FROM vtiger_activities a
				   JOIN vtiger_crmentity ce ON ce.crmid = a.activityid AND ce.deleted = 0
				   LEFT JOIN vtiger_users u ON u.id = ce.smownerid
				   LEFT JOIN vtiger_account org ON org.accountid = a.organizationid
				   LEFT JOIN vtiger_project pr ON pr.projectid = a.projectid
				  WHERE a.activityid = ?",
				[$recordId]
			);
			if ($res && $adb->num_rows($res) > 0) {
				$row = $adb->fetchByAssoc($res);
			}
		}

		if ($row === null) {
			header('Location: index.php?module=Activities&view=List&app=SUPPORT');
			exit;
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', 'Activities');
		$viewer->assign('RECORD', $row);
		$viewer->view('Detail.tpl', 'Activities');
	}

	public function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = $this->getActivitiesCoreCssFiles();
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		return array_merge($headerCssInstances, $cssInstances);
	}

	/**
	 * Load requested core files when available, fallback to existing v7 files.
	 *
	 * @return array
	 */
	protected function getActivitiesCoreCssFiles() {
		$layout = Vtiger_Viewer::getDefaultLayoutName();
		$root = rtrim(vglobal('root_directory'), '/');

		$candidates = array(
			"layouts/{$layout}/lib/bootstrap/css/bootstrap.min.css",
			"layouts/{$layout}/lib/todc/css/bootstrap.min.css",
			"layouts/{$layout}/lib/font-awesome/css/font-awesome.min.css",
			"layouts/{$layout}/resources/ListView.css",
			"layouts/{$layout}/resources/DetailView.css",
			"layouts/{$layout}/modules/Calendar/resources/style.css",
			"layouts/{$layout}/modules/Calendar/resources/calendar-google.css",
			"layouts/{$layout}/modules/Calendar/resources/Calendar.css",
			"layouts/{$layout}/skins/support/style.css",
		);

		$cssFileNames = array();
		foreach ($candidates as $relativePath) {
			if (file_exists($root . '/' . $relativePath)) {
				$cssFileNames[] = '~' . $relativePath;
			}
		}

		return array_values(array_unique($cssFileNames));
	}
}
