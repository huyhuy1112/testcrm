<?php
/*+***********************************************************************************
 * Main Page (Management) - custom landing view.
 * Allow all logged-in users; load real Project/ProjectTask data and link to modules.
 *************************************************************************************/

class Home_MainPage_View extends Vtiger_Index_View {

	public function requiresPermission(\Vtiger_Request $request) {
		return array();
	}

	public function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function preProcess(Vtiger_Request $request, $display = true) {
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);
		// Main Page: luôn dùng MANAGEMENT và hiển thị app menu (sidebar)
		$viewer->assign('SELECTED_MENU_CATEGORY', 'MANAGEMENT');
		$viewer->assign('SELECTED_MENU_CATEGORY_LABEL', vtranslate('LBL_MANAGEMENT', 'Vtiger'));
		$menuGroupedByParent = Settings_MenuEditor_Module_Model::getAllVisibleModules();
		if (isset($menuGroupedByParent['MANAGEMENT'])) {
			$viewer->assign('SELECTED_CATEGORY_MENU_LIST', $menuGroupedByParent['MANAGEMENT']);
		}
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	protected function preProcessTplName(Vtiger_Request $request) {
		return 'MainPageViewPreProcess.tpl';
	}

	public function postProcess(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->view('MainPagePostProcess.tpl', $request->getModule());
		parent::postProcess($request);
	}

	/**
	 * Fallback: lấy Project/ProjectTask trực tiếp từ DB theo smownerid (khi list view trả về 0).
	 */
	protected function getMainPageListFromDb($moduleName, $userId, $limit = 8) {
		$rows = array();
		$db = PearDatabase::getInstance();
		$userId = (int) $userId;
		try {
			if ($moduleName === 'Project') {
				$sql = "SELECT p.projectid AS id, p.projectname, p.startdate, p.enddate, p.projectstatus
					FROM vtiger_project p
					INNER JOIN vtiger_crmentity c ON c.crmid = p.projectid AND c.deleted = 0 AND c.smownerid = ?
					ORDER BY c.modifiedtime DESC LIMIT ?";
				$res = $db->pquery($sql, array($userId, $limit));
				while ($row = $db->fetchByAssoc($res)) {
					$id = $row['id'];
					$rec = Vtiger_Record_Model::getInstanceById($id, $moduleName);
					$rows[] = array(
						'id' => $id,
						'url' => $rec ? $rec->getDetailViewUrl() : ('index.php?module=Project&view=Detail&record=' . $id),
						'title' => $row['projectname'] ?: ('#' . $id),
						'startdate' => $rec ? $rec->getDisplayValue('startdate') : $row['startdate'],
						'enddate' => $rec ? $rec->getDisplayValue('enddate') : $row['enddate'],
						'status' => $rec ? $rec->getDisplayValue('projectstatus') : $row['projectstatus'],
						'status_raw' => $row['projectstatus'],
					);
				}
			} elseif ($moduleName === 'ProjectTask') {
				$sql = "SELECT t.projecttaskid AS id, t.projecttaskname, t.startdate, t.enddate, t.projecttaskprogress
					FROM vtiger_projecttask t
					INNER JOIN vtiger_crmentity c ON c.crmid = t.projecttaskid AND c.deleted = 0 AND c.smownerid = ?
					ORDER BY c.modifiedtime DESC LIMIT ?";
				$res = $db->pquery($sql, array($userId, $limit));
				while ($row = $db->fetchByAssoc($res)) {
					$id = $row['id'];
					$rec = Vtiger_Record_Model::getInstanceById($id, $moduleName);
					$rows[] = array(
						'id' => $id,
						'url' => $rec ? $rec->getDetailViewUrl() : ('index.php?module=ProjectTask&view=Detail&record=' . $id),
						'title' => $row['projecttaskname'] ?: ('Task #' . $id),
						'duedate' => $rec ? ($rec->getDisplayValue('enddate') ?: $rec->getDisplayValue('startdate')) : $row['enddate'],
						'status' => $rec ? $rec->getDisplayValue('projecttaskprogress') : $row['projecttaskprogress'],
						'status_raw' => $row['projecttaskprogress'],
					);
				}
			}
		} catch (Exception $e) {
			// ignore
		}
		return $rows;
	}

	/**
	 * Fetch recent records from a module assigned to current user (max 8).
	 * For Project and ProjectTask only records assigned to me are shown.
	 * @param string $moduleName
	 * @return array list of assoc: id, url, display fields
	 */
	protected function getMainPageList($moduleName, $limit = 8) {
		$rows = array();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!Users_Privileges_Model::isPermitted($moduleName, 'DetailView')) {
			return $rows;
		}
		try {
			$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, '0', array());
			// Project/ProjectTask: thêm assigned_user_id vào query để lọc "assign cho tôi" trong PHP
			if ($moduleName === 'Project' || $moduleName === 'ProjectTask') {
				$qg = $listViewModel->get('query_generator');
				$fields = $qg->getFields();
				if (!in_array('assigned_user_id', $fields)) {
					$fields[] = 'assigned_user_id';
					$qg->setFields($fields);
				}
			}
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', 1);
			$fetchLimit = ($moduleName === 'Project' || $moduleName === 'ProjectTask') ? 80 : $limit;
			$pagingModel->set('limit', $fetchLimit);
			$entries = $listViewModel->getListViewEntries($pagingModel);
			if (!is_array($entries)) {
				return $rows;
			}
			// Fallback: list view trả về 0 (do custom view/permission) thì lấy trực tiếp từ DB theo smownerid
			if (($moduleName === 'Project' || $moduleName === 'ProjectTask') && count($entries) === 0) {
				return $this->getMainPageListFromDb($moduleName, $currentUser->getId(), $limit);
			}
			$currentUserId = $currentUser->getId();
			foreach ($entries as $recordId => $recordModel) {
				if (!$recordModel || !($recordModel instanceof Vtiger_Record_Model)) {
					continue;
				}
				// Chỉ hiển thị bản ghi assign cho user hiện tại (owner id từ raw data; list view gán assigned_user_id = tên hiển thị)
				if ($moduleName === 'Project' || $moduleName === 'ProjectTask') {
					$rawData = $recordModel->getRawData();
					$ownerId = null;
					if (is_array($rawData)) {
						if (isset($rawData['smownerid'])) {
							$ownerId = $rawData['smownerid'];
						} elseif (isset($rawData['assigned_user_id'])) {
							$ownerId = $rawData['assigned_user_id'];
						} else {
							foreach (array('smownerid', 'assigned_user_id', 'vtiger_crmentity_smownerid') as $k) {
								if (isset($rawData[$k])) { $ownerId = $rawData[$k]; break; }
							}
							if ($ownerId === null) {
								foreach ($rawData as $k => $v) {
									if (stripos($k, 'owner') !== false && is_numeric($v)) { $ownerId = $v; break; }
								}
							}
						}
					}
					if ($ownerId !== null && $ownerId !== $currentUserId && $ownerId !== (string) $currentUserId && (string) $ownerId !== (string) $currentUserId) {
						continue;
					}
				}
				if (count($rows) >= $limit) {
					break;
				}
				$url = $recordModel->getDetailViewUrl();
				if ($moduleName === 'Project') {
					$rows[] = array(
						'id' => $recordId,
						'url' => $url,
						'title' => $recordModel->get('projectname') ?: ('#' . $recordId),
						'startdate' => $recordModel->getDisplayValue('startdate'),
						'enddate' => $recordModel->getDisplayValue('enddate'),
						'status' => $recordModel->getDisplayValue('projectstatus'),
						'status_raw' => $recordModel->get('projectstatus'),
					);
				} elseif ($moduleName === 'ProjectTask') {
					$rows[] = array(
						'id' => $recordId,
						'url' => $url,
						'title' => $recordModel->get('projecttaskname') ?: ('Task #' . $recordId),
						'duedate' => $recordModel->getDisplayValue('enddate') ?: $recordModel->getDisplayValue('startdate'),
						'status' => $recordModel->getDisplayValue('projecttaskprogress') ?: '-',
						'status_raw' => $recordModel->get('projecttaskprogress'),
					);
				}
			}
		} catch (Exception $e) {
			// ignore
		}
		return $rows;
	}

	/**
	 * Count ProjectTask records assigned to current user (for badge).
	 */
	protected function getProjectTaskCount() {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!Users_Privileges_Model::isPermitted('ProjectTask', 'DetailView')) {
			return 0;
		}
		try {
			$listViewModel = Vtiger_ListView_Model::getInstance('ProjectTask', '0', array());
			$qg = $listViewModel->get('query_generator');
			$fields = $qg->getFields();
			if (!in_array('assigned_user_id', $fields)) {
				$fields[] = 'assigned_user_id';
				$qg->setFields($fields);
			}
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', 1);
			$pagingModel->set('limit', 500);
			$entries = $listViewModel->getListViewEntries($pagingModel);
			if (!is_array($entries)) {
				return 0;
			}
			if (count($entries) === 0) {
				$db = PearDatabase::getInstance();
				$res = $db->pquery("SELECT COUNT(1) AS c FROM vtiger_projecttask t INNER JOIN vtiger_crmentity c ON c.crmid = t.projecttaskid AND c.deleted = 0 AND c.smownerid = ?", array($currentUser->getId()));
				return (int) $db->query_result($res, 0, 'c');
			}
			$currentUserId = $currentUser->getId();
			$count = 0;
			foreach ($entries as $recordModel) {
				if (!$recordModel || !($recordModel instanceof Vtiger_Record_Model)) {
					continue;
				}
				$rawData = $recordModel->getRawData();
				$ownerId = null;
				if (is_array($rawData) && isset($rawData['smownerid'])) {
					$ownerId = $rawData['smownerid'];
				} elseif (is_array($rawData) && isset($rawData['assigned_user_id'])) {
					$ownerId = $rawData['assigned_user_id'];
				} else {
					$ownerId = $recordModel->get('assigned_user_id');
				}
				if ($ownerId === $currentUserId || $ownerId === (string) $currentUserId || (string) $ownerId === (string) $currentUserId) {
					$count++;
				}
			}
			return $count;
		} catch (Exception $e) {
			return 0;
		}
	}

	/**
	 * Build one agenda item from activity (date + time display for UI giống Today).
	 */
	/** Màu mặc định theo loại (Schedule): Task, Call, Meeting, ... */
	protected static $agendaTypeColors = array(
		'Task' => '#6c757d',
		'Call' => '#3498db',
		'Meeting' => '#27ae60',
		'Planned' => '#9b59b6',
		'Events' => '#3498db',
	);

	protected function buildAgendaItem(Vtiger_Record_Model $activity) {
		$id = $activity->getId();
		$subject = $activity->get('subject');
		$type = $activity->get('activitytype') ?: 'Events';
		if ($activity->get('activitytype') === 'Task') {
			$dateDisplay = $activity->getDisplayValue('due_date');
			$timeDisplay = '';
			$dateTime = $dateDisplay;
		} else {
			$dateDisplay = $activity->getDisplayValue('date_start');
			$timeStart = $activity->getDisplayValue('time_start');
			$timeEnd = $activity->getDisplayValue('time_end');
			$timeDisplay = trim($timeStart . ($timeEnd ? ' ' . $timeEnd : ''));
			$dateTime = $dateDisplay . ($timeDisplay ? ' ' . $timeDisplay : '');
		}
		$color = isset(self::$agendaTypeColors[$type]) ? self::$agendaTypeColors[$type] : '#95a5a6';
		return array(
			'id' => $id,
			'url' => $activity->getDetailViewUrl(),
			'title' => $subject ?: ('#' . $id),
			'dateTime' => $dateTime,
			'dateDisplay' => $dateDisplay,
			'timeDisplay' => $timeDisplay,
			'type' => $type,
			'color' => $color,
		);
	}

	/**
	 * Agenda: chỉ lịch hôm nay (Today).
	 */
	protected function getAgendaToday($limit = 10) {
		$out = array();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!Users_Privileges_Model::isPermitted('Calendar', 'DetailView')) {
			return $out;
		}
		try {
			$homeModel = Vtiger_Module_Model::getInstance('Home');
			if (!method_exists($homeModel, 'getCalendarActivities')) {
				return $out;
			}
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', 1);
			$pagingModel->set('limit', $limit);
			$activities = $homeModel->getCalendarActivities('today', $pagingModel, $currentUser->getId(), null);
			if (is_array($activities)) {
				foreach ($activities as $activity) {
					if ($activity instanceof Vtiger_Record_Model) {
						$out[] = $this->buildAgendaItem($activity);
					}
				}
			}
		} catch (Exception $e) {
			// ignore
		}
		return $out;
	}

	/**
	 * Agenda: lịch sắp tới (ngày mai, ngày kia...) – không gồm hôm nay.
	 */
	protected function getAgendaUpcoming($limit = 10) {
		$out = array();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!Users_Privileges_Model::isPermitted('Calendar', 'DetailView')) {
			return $out;
		}
		try {
			$homeModel = Vtiger_Module_Model::getInstance('Home');
			if (!method_exists($homeModel, 'getCalendarActivities')) {
				return $out;
			}
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', 1);
			$pagingModel->set('limit', $limit);
			$activities = $homeModel->getCalendarActivities('upcoming', $pagingModel, $currentUser->getId(), null);
			if (is_array($activities)) {
				$todayDate = date('Y-m-d');
				foreach ($activities as $activity) {
					if (!($activity instanceof Vtiger_Record_Model)) {
						continue;
					}
					$start = $activity->get('date_start');
					if ($start === $todayDate) {
						continue;
					}
					$out[] = $this->buildAgendaItem($activity);
					if (count($out) >= $limit) {
						break;
					}
				}
			}
		} catch (Exception $e) {
			// ignore
		}
		return $out;
	}

	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$currentUser = Users_Record_Model::getCurrentUserModel();

		// Real data: projects and tasks (assigned to me only), agenda (my schedule)
		$mainPageProjects = $this->getMainPageList('Project', 8);
		$mainPageTasks = $this->getMainPageList('ProjectTask', 8);
		$projectTaskCount = $this->getProjectTaskCount();
		$mainPageAgenda = $this->getAgendaToday(10);
		$mainPageAgendaUpcoming = $this->getAgendaUpcoming(10);

		// Links for shortcuts (with app=MANAGEMENT)
		$app = 'MANAGEMENT';
		$mainPageLinks = array(
			'projecttask_list' => 'index.php?module=ProjectTask&view=List&app=' . $app,
			'project_list' => 'index.php?module=Project&view=List&app=' . $app,
			'calendar' => 'index.php?module=Calendar&view=Calendar&app=' . $app,
			'home' => 'index.php?module=Home&view=DashBoard&app=' . $app,
		);

		$viewer->assign('CURRENT_USER', $currentUser);
		$viewer->assign('MAINPAGE_PROJECTS', $mainPageProjects);
		$viewer->assign('MAINPAGE_TASKS', $mainPageTasks);
		$viewer->assign('MAINPAGE_TASK_COUNT', $projectTaskCount);
		$viewer->assign('MAINPAGE_AGENDA', $mainPageAgenda);
		$viewer->assign('MAINPAGE_AGENDA_UPCOMING', $mainPageAgendaUpcoming);
		$viewer->assign('MAINPAGE_LINKS', $mainPageLinks);
		// Announcements from vtiger_announcement (filter by current user: assigned to me or to all)
		$mainPageAnnouncements = array();
		$mainPageAssignableUsers = array();
		$mainPageAccessibleGroups = array();
		if (class_exists('Settings_Vtiger_Announcement_Model')) {
			$mainPageAnnouncements = Settings_Vtiger_Announcement_Model::getAllForDisplay(20, $currentUser->getId());
			$mainPageAssignableUsers = $currentUser->getAccessibleUsers();
			if (!is_array($mainPageAssignableUsers)) {
				$mainPageAssignableUsers = array();
			}
			$mainPageAccessibleGroups = $currentUser->getAccessibleGroups();
			if (!is_array($mainPageAccessibleGroups)) {
				$mainPageAccessibleGroups = array();
			}
		}
		$viewer->assign('MAINPAGE_ANNOUNCEMENTS', $mainPageAnnouncements);
		$viewer->assign('MAINPAGE_ASSIGNABLE_USERS', $mainPageAssignableUsers);
		$viewer->assign('MAINPAGE_ACCESSIBLE_GROUPS', $mainPageAccessibleGroups);
		$viewer->assign('MAINPAGE_CURRENT_USER_ID', $currentUser->getId());

		// My logged time: tính từ lúc đăng nhập (session)
		$loginTime = isset($_SESSION['user_login_time']) ? (int)$_SESSION['user_login_time'] : 0;
		$loggedTimeDisplay = '';
		$loggedTimeSeconds = 0;
		if ($loginTime > 0) {
			$loggedTimeSeconds = time() - $loginTime;
			$loggedTimeDisplay = self::formatLoggedTime($loggedTimeSeconds);
		}
		$viewer->assign('MAINPAGE_LOGIN_TIMESTAMP', $loginTime);
		$viewer->assign('MAINPAGE_LOGGED_TIME_DISPLAY', $loggedTimeDisplay);
		$viewer->assign('MAINPAGE_LOGGED_TIME_SECONDS', $loggedTimeSeconds);

		// Lịch sử đăng nhập (vtiger_loginhistory) cho user hiện tại
		$mainPageLoginHistory = self::getLoginHistoryForUser($currentUser->get('user_name'), 15);
		$viewer->assign('MAINPAGE_LOGIN_HISTORY', $mainPageLoginHistory);

		$viewer->view('MainPage.tpl', 'Home');
	}

	/**
	 * Format seconds to human string e.g. "2h 15m", "45m", "5 giây"
	 */
	protected static function formatLoggedTime($seconds) {
		$seconds = (int)$seconds;
		if ($seconds < 60) {
			return $seconds . ' giây';
		}
		if ($seconds < 3600) {
			return floor($seconds / 60) . ' phút';
		}
		$h = floor($seconds / 3600);
		$m = floor(($seconds % 3600) / 60);
		if ($m > 0) {
			return $h . 'h ' . $m . 'm';
		}
		return $h . 'h';
	}

	/**
	 * Lấy lịch sử đăng nhập/đăng xuất của user (vtiger_loginhistory).
	 * @param string $userName
	 * @param int $limit
	 * @return array list of { login_time, logout_time, status, login_display, logout_display, duration_display }
	 */
	protected static function getLoginHistoryForUser($userName, $limit = 15) {
		$list = array();
		if (empty($userName)) {
			return $list;
		}
		try {
			$db = PearDatabase::getInstance();
			$sql = "SELECT login_id, login_time, logout_time, status FROM vtiger_loginhistory 
				WHERE user_name = ? ORDER BY login_id DESC LIMIT " . (int)$limit;
			$res = $db->pquery($sql, array($userName));
			while ($row = $db->fetchByAssoc($res)) {
				$loginTs = strtotime($row['login_time']);
				$logoutTs = !empty($row['logout_time']) && $row['logout_time'] !== '0000-00-00 00:00:00' ? strtotime($row['logout_time']) : 0;
				$durationSec = $logoutTs > 0 ? ($logoutTs - $loginTs) : 0;
				$list[] = array(
					'login_time' => $row['login_time'],
					'logout_time' => $row['logout_time'],
					'status' => $row['status'],
					'login_display' => $loginTs ? date('d/m H:i', $loginTs) : '-',
					'logout_display' => $logoutTs ? date('d/m H:i', $logoutTs) : '-',
					'duration_display' => $durationSec > 0 ? self::formatLoggedTime($durationSec) : '-',
				);
			}
		} catch (Exception $e) {
			// ignore
		}
		return $list;
	}
}
