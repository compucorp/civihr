<?php

// AUTO-GENERATED FILE -- Civix may overwrite any changes made to this file

/**
 * (Delegated) Implementation of hook_civicrm_config
 */
function _hrcase_civix_civicrm_config(&$config = NULL) {
  static $configured = FALSE;
  if ($configured) return;
  $configured = TRUE;

  $template =& CRM_Core_Smarty::singleton();

  $extRoot = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
  $extDir = $extRoot . 'templates';

  if ( is_array( $template->template_dir ) ) {
      array_unshift( $template->template_dir, $extDir );
  } else {
      $template->template_dir = array( $extDir, $template->template_dir );
  }

  $include_path = $extRoot . PATH_SEPARATOR . get_include_path( );
  set_include_path( $include_path );
}

/**
 * (Delegated) Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function _hrcase_civix_civicrm_xmlMenu(&$files) {
  foreach (_hrcase_civix_glob(__DIR__ . '/xml/Menu/*.xml') as $file) {
    $files[] = $file;
  }
}

/**
 * Implementation of hook_civicrm_install
 */
function _hrcase_civix_civicrm_install() {
  _hrcase_civix_civicrm_config();
  if ($upgrader = _hrcase_civix_upgrader()) {
    return $upgrader->onInstall();
  }
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function _hrcase_civix_civicrm_uninstall() {
  _hrcase_civix_civicrm_config();
  if ($upgrader = _hrcase_civix_upgrader()) {
    return $upgrader->onUninstall();
  }
}

/**
 * (Delegated) Implementation of hook_civicrm_enable
 */
function _hrcase_civix_civicrm_enable() {
  _hrcase_civix_civicrm_config();
  if ($upgrader = _hrcase_civix_upgrader()) {
    if (is_callable(array($upgrader, 'onEnable'))) {
      return $upgrader->onEnable();
    }
  }
}

/**
 * (Delegated) Implementation of hook_civicrm_disable
 */
function _hrcase_civix_civicrm_disable() {
  _hrcase_civix_civicrm_config();
  if ($upgrader = _hrcase_civix_upgrader()) {
    if (is_callable(array($upgrader, 'onDisable'))) {
      return $upgrader->onDisable();
    }
  }
}

/**
 * (Delegated) Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function _hrcase_civix_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  if ($upgrader = _hrcase_civix_upgrader()) {
    return $upgrader->onUpgrade($op, $queue);
  }
}

function _hrcase_civix_upgrader() {
  if (!file_exists(__DIR__.'/CRM/HRCase/Upgrader.php')) {
    return NULL;
  } else {
    return CRM_HRCase_Upgrader_Base::instance();
  }
}

/**
 * Search directory tree for files which match a glob pattern
 *
 * Note: Dot-directories (like "..", ".git", or ".svn") will be ignored.
 * Note: In Civi 4.3+, delegate to CRM_Utils_File::findFiles()
 *
 * @param $dir string, base dir
 * @param $pattern string, glob pattern, eg "*.txt"
 * @return array(string)
 */
function _hrcase_civix_find_files($dir, $pattern) {
  if (is_callable(array('CRM_Utils_File', 'findFiles'))) {
    return CRM_Utils_File::findFiles($dir, $pattern);
  }

  $todos = array($dir);
  $result = array();
  while (!empty($todos)) {
    $subdir = array_shift($todos);
    foreach (_hrcase_civix_glob("$subdir/$pattern") as $match) {
      if (!is_dir($match)) {
        $result[] = $match;
      }
    }
    if ($dh = opendir($subdir)) {
      while (FALSE !== ($entry = readdir($dh))) {
        $path = $subdir . DIRECTORY_SEPARATOR . $entry;
        if ($entry{0} == '.') {
        } elseif (is_dir($path)) {
          $todos[] = $path;
        }
      }
      closedir($dh);
    }
  }
  return $result;
}
/**
 * (Delegated) Implementation of hook_civicrm_managed
 *
 * Find any *.mgd.php files, merge their content, and return.
 */
function _hrcase_civix_civicrm_managed(&$entities) {
  $mgdFiles = _hrcase_civix_find_files(__DIR__, '*.mgd.php');
  foreach ($mgdFiles as $file) {
    $es = include $file;
    foreach ($es as $e) {
      if (empty($e['module'])) {
        $e['module'] = 'org.civicrm.hrcase';
      }
      $entities[] = $e;
    }
  }
}

/**
 * (Delegated) Implementation of hook_civicrm_caseTypes
 *
 * Find any and return any files matching "xml/case/*.xml"
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 */
function _hrcase_civix_civicrm_caseTypes(&$caseTypes) {
  if (!is_dir(__DIR__ . '/xml/case')) {
    return;
  }

  foreach (_hrcase_civix_glob(__DIR__ . '/xml/case/*.xml') as $file) {
    $name = preg_replace('/\.xml$/', '', basename($file));
    if ($name != CRM_Case_XMLProcessor::mungeCaseType($name)) {
      $errorMessage = sprintf("Case-type file name is malformed (%s vs %s)", $name, CRM_Case_XMLProcessor::mungeCaseType($name));
      CRM_Core_Error::fatal($errorMessage);
      // throw new CRM_Core_Exception($errorMessage);
    }
    $caseTypes[$name] = array(
      'module' => 'org.civicrm.hrcase',
      'name' => $name,
      'file' => $file,
    );
  }
}

/**
 * Glob wrapper which is guaranteed to return an array.
 *
 * The documentation for glob() says, "On some systems it is impossible to
 * distinguish between empty match and an error." Anecdotally, the return
 * result for an empty match is sometimes array() and sometimes FALSE.
 * This wrapper provides consistency.
 *
 * @see http://php.net/glob
 * @param string $pattern
 * @return array, possibly empty
 */
function _hrcase_civix_glob($pattern) {
  $result = glob($pattern);
  return is_array($result) ? $result : array();
}

/**
 * Inserts a navigation menu item at a given place in the hierarchy
 *
 * $menu - menu hierarchy
 * $path - path where insertion should happen (ie. Administer/System Settings)
 * $item - menu you need to insert (parent/child attributes will be filled for you)
 * $parentId - used internally to recurse in the menu structure
 */
function _hrcase_civix_insert_navigation_menu(&$menu, $path, $item, $parentId = NULL) {
  static $navId;

  // If we are done going down the path, insert menu
  if (empty($path)) {
    if (!$navId) $navId = CRM_Core_DAO::singleValueQuery("SELECT max(id) FROM civicrm_navigation");
    $navId ++;
    $menu[$navId] = array (
      'attributes' => array_merge($item, array(
        'label'      => CRM_Utils_Array::value('name', $item),
        'active'     => 1,
        'parentID'   => $parentId,
        'navID'      => $navId,
      ))
    );
    return true;
  } else {
    // Find an recurse into the next level down
    $found = false;
    $path = explode('/', $path);
    $first = array_shift($path);
    foreach ($menu as $key => &$entry) {
      if ($entry['attributes']['name'] == $first) {
        if (!$entry['child']) $entry['child'] = array();
        $found = _hrcase_civix_insert_navigation_menu($entry['child'], implode('/', $path), $item, $key);
      }
    }
    return $found;
  }
}

function _hrcase_postInstall() {
  // Import custom group
  require_once 'CRM/Utils/Migrate/Import.php';
	
  $import = new CRM_Utils_Migrate_Import();
	
  $files = glob(__DIR__ . '/xml/*_customGroupCaseType.xml');
  if (is_array($files)) {
    foreach ($files as $file) {
	  $import->run($file);
	}
  }
	
  // schedule reminder for Termination Letter
  $activityTypeId = CRM_Core_OptionGroup::getValue('activity_type', 'Send Termination Letter', 'name');
  if (!empty($activityTypeId)) {
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
	$assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);

	$result = civicrm_api3('action_schedule', 'get', array('name' => 'Send_Termination_Letter'));
	if (empty($result['id'])) {
	  $params = array(
	    'name' => 'Send_Termination_Letter',
		'title' => 'Send Termination Letter',
		'recipient' => $assigneeID,
		'limit_to' => 1,
		'entity_value' => $activityTypeId,
		'entity_status' => CRM_Core_OptionGroup::getValue('activity_status', 'Scheduled', 'name'),
		'start_action_offset' => 3,
		'start_action_unit' => 'day',
		'start_action_condition' => 'before',
		'start_action_date' => 'activity_date_time',
		'is_repeat' => 1,
		'repetition_frequency_unit' => 'day',
		'repetition_frequency_interval' => 3,
		'end_frequency_unit' => 'hour',
		'end_frequency_interval' => 0,
		'end_action' => 'before',
		'end_date' => 'activity_date_time',
		'is_active' => 1,
		'body_html' => '<p>Your need to send termination letter on {activity.activity_date_time}</p>',
		'subject' => 'Reminder to Send Termination Letter',
		'record_activity' => 1,
		'mapping_id' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_ActionMapping', 'activity_type', 'id', 'entity_value')
		);
	  $result = civicrm_api3('action_schedule', 'create', $params);
    }
  }
	
  // schedule reminder for Exit Interview
  $activityTypeId = CRM_Core_OptionGroup::getValue('activity_type', 'Exit Interview', 'name');
  if (!empty($activityTypeId)) {
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
	$assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);
	
	$result = civicrm_api3('action_schedule', 'get', array('name' => 'Exit_Interview'));
	if (empty($result['id'])) {
	  $params = array(
	    'name' => 'Exit_Interview',
		'title' => 'Exit Interview',
		'recipient' => $assigneeID,
		'limit_to' => 1,
		'entity_value' => $activityTypeId,
		'entity_status' => CRM_Core_OptionGroup::getValue('activity_status', 'Scheduled', 'name'),
		'start_action_offset' => 3,
		'start_action_unit' => 'day',
		'start_action_condition' => 'before',
		'start_action_date' => 'activity_date_time',
		'is_repeat' => 1,
		'repetition_frequency_unit' => 'day',
		'repetition_frequency_interval' => 3,
		'end_frequency_unit' => 'hour',
		'end_frequency_interval' => 0,
		'end_action' => 'before',
		'end_date' => 'activity_date_time',
		'is_active' => 1,
		'body_html' => '<p>Your Exit Interview on {activity.activity_date_time}</p>',
		'subject' => 'Reminder for Exit Interview',
		'record_activity' => 1,
		'mapping_id' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_ActionMapping', 'activity_type', 'id', 'entity_value')
		);
	  $result = civicrm_api3('action_schedule', 'create', $params);
	}
  }
	
  // schedule reminder for Attach Offer Letter
  $activityTypeId = CRM_Core_OptionGroup::getValue('activity_type', 'Attach Offer Letter', 'name');
  if (!empty($activityTypeId)) {
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
	$assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);
	
	$result = civicrm_api3('action_schedule', 'get', array('name' => 'Attach_Offer_Letter'));
	if (empty($result['id'])) {
	  $params = array(
	    'name' => 'Attach_Offer_Letter',
		'title' => 'Attach Offer Letter',
		'recipient' => $assigneeID,
		'limit_to' => 1,
		'entity_value' => $activityTypeId,
		'entity_status' => CRM_Core_OptionGroup::getValue('activity_status', 'Scheduled', 'name'),
        'start_action_offset' => 0,
        'start_action_unit' => 'hour',
        'start_action_condition' => 'before',
        'start_action_date' => 'activity_date_time',
        'is_repeat' => 1,
        'repetition_frequency_unit' => 'week',
        'repetition_frequency_interval' => 1,
        'end_frequency_unit' => 'hour',
        'end_frequency_interval' => 0,
        'end_action' => 'after',
        'end_date' => 'activity_date_time',
	    'is_active' => 1,
		'body_html' => '<p>Attach Offer Letter on {activity.activity_date_time}</p>',
		'subject' => 'Reminder to Attach Offer Letter',
		'record_activity' => 1,
		'mapping_id' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_ActionMapping', 'activity_type', 'id', 'entity_value')
		);
	  $result = civicrm_api3('action_schedule', 'create', $params);
	}
  }
	
  // schedule reminder for Attach Reference 
  $activityTypeId = CRM_Core_OptionGroup::getValue('activity_type', 'Attach Reference', 'name');
  if (!empty($activityTypeId)) {
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
	$assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);
	
	$result = civicrm_api3('action_schedule', 'get', array('name' => 'Attach_Reference'));
	if (empty($result['id'])) {
	  $params = array(
	    'name' => 'Attach_Reference',
		'title' => 'Attach Reference',
		'recipient' => $assigneeID,
		'limit_to' => 1,
		'entity_value' => $activityTypeId,
		'entity_status' => CRM_Core_OptionGroup::getValue('activity_status', 'Scheduled', 'name'),
		'start_action_offset' => 0,
		'start_action_unit' => 'hour',
		'start_action_condition' => 'before',
		'start_action_date' => 'activity_date_time',
		'is_repeat' => 1,
		'repetition_frequency_unit' => 'week',
		'repetition_frequency_interval' => 1,
		'end_frequency_unit' => 'hour',
		'end_frequency_interval' => 0,
		'end_action' => 'after',
		'end_date' => 'activity_date_time',
		'is_active' => 1,
		'body_html' => '<p>Attach Reference on {activity.activity_date_time}</p>',
		'subject' => 'Reminder to Attach Reference',
		'record_activity' => 1,
		'mapping_id' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_ActionMapping', 'activity_type', 'id', 'entity_value')
		);
	  $result = civicrm_api3('action_schedule', 'create', $params);
	}
  }	

  // schedule reminder for Attach Draft Job Contract
  $activityTypeId = CRM_Core_OptionGroup::getValue('activity_type', 'Attach Draft Job Contract', 'name');
  if (!empty($activityTypeId)) {
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
   	$assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);
   
   	$result = civicrm_api3('action_schedule', 'get', array('name' => 'Attach_Draft_Job_Contract'));
   	if (empty($result['id'])) {
   	  $params = array(
   	    'name' => 'Attach_Draft_Job_Contract',
   		'title' => 'Attach Draft Job Contract',
   		'recipient' => $assigneeID,
   		'limit_to' => 1,
   		'entity_value' => $activityTypeId,
   		'entity_status' => CRM_Core_OptionGroup::getValue('activity_status', 'Scheduled', 'name'),
   		'start_action_offset' => 0,
   		'start_action_unit' => 'hour',
   		'start_action_condition' => 'before',
   		'start_action_date' => 'activity_date_time',
   		'is_repeat' => 1,
   		'repetition_frequency_unit' => 'week',
   		'repetition_frequency_interval' => 1,
   		'end_frequency_unit' => 'hour',
   		'end_frequency_interval' => 0,
   		'end_action' => 'after',
   		'end_date' => 'activity_date_time',
   		'is_active' => 1,
   		'body_html' => '<p>Attach Draft Job Contract on {activity.activity_date_time}</p>',
   		'subject' => 'Reminder to Attach Draft Job Contract',
   		'record_activity' => 1,
   		'mapping_id' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_ActionMapping', 'activity_type', 'id', 'entity_value')
   		);
   	  $result = civicrm_api3('action_schedule', 'create', $params);
    }
  }   

  // schedule reminder for Attach Objects Document
  $activityTypeId = CRM_Core_OptionGroup::getValue('activity_type', 'Attach Objectives Document', 'name');
  if (!empty($activityTypeId)) {
  	$activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
  	$assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);
  	 
  	$result = civicrm_api3('action_schedule', 'get', array('name' => 'Attach_Objects_Document'));
  	if (empty($result['id'])) {
  	  $params = array(
  	    'name' => 'Attach_Objects_Document',
  		'title' => 'Attach Objects Document',
  		'recipient' => $assigneeID,
  		'limit_to' => 1,
  		'entity_value' => $activityTypeId,
  		'entity_status' => CRM_Core_OptionGroup::getValue('activity_status', 'Scheduled', 'name'),
  		'start_action_offset' => 2,
  		'start_action_unit' => 'week',
  		'start_action_condition' => 'before',
  		'start_action_date' => 'activity_date_time',
  		'is_repeat' => 1,
  		'repetition_frequency_unit' => 'week',
  		'repetition_frequency_interval' => 1,
  		'end_frequency_unit' => 'hour',
  		'end_frequency_interval' => 0,
  		'end_action' => 'before',
  		'end_date' => 'activity_date_time',
  		'is_active' => 1,
  		'body_html' => '<p>Attach Objects Document on {activity.activity_date_time}</p>',
  		'subject' => 'Reminder to Attach Objects Document',
  		'record_activity' => 1,
  		'mapping_id' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_ActionMapping', 'activity_type', 'id', 'entity_value')
  		);
  	  $result = civicrm_api3('action_schedule', 'create', $params);
    }
  }

  // schedule reminder for Attach Appraisal Document
  $activityTypeId = CRM_Core_OptionGroup::getValue('activity_type', 'Attach Appraisal Document', 'name');
  if (!empty($activityTypeId)) {
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
  	$assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);
  
  	$result = civicrm_api3('action_schedule', 'get', array('name' => 'Attach_Appraisal_Document'));
  	if (empty($result['id'])) {
  	  $params = array(
  	    'name' => 'Attach_Appraisal_Document',
  		'title' => 'Attach Appraisal Document',
  		'recipient' => $assigneeID,
  		'limit_to' => 1,
  		'entity_value' => $activityTypeId,
  		'entity_status' => CRM_Core_OptionGroup::getValue('activity_status', 'Scheduled', 'name'),
  		'start_action_offset' => 2,
  		'start_action_unit' => 'week',
  		'start_action_condition' => 'before',
  		'start_action_date' => 'activity_date_time',
  		'is_repeat' => 1,
  		'repetition_frequency_unit' => 'week',
  		'repetition_frequency_interval' => 1,
  		'end_frequency_unit' => 'hour',
  		'end_frequency_interval' => 0,
  		'end_action' => 'before',
  		'end_date' => 'activity_date_time',
  		'is_active' => 1,
  		'body_html' => '<p>Attach Appraisal Document on {activity.activity_date_time}</p>',
  		'subject' => 'Reminder to Attach Appraisal Document',
  		'record_activity' => 1,
  		'mapping_id' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_ActionMapping', 'activity_type', 'id', 'entity_value')
  		);
  	  $result = civicrm_api3('action_schedule', 'create', $params);
  	}
  }  
  // schedule reminder for Attach Probation Notification
  $activityTypeId = CRM_Core_OptionGroup::getValue('activity_type', 'Attach Probation Notification', 'name');
  if (!empty($activityTypeId)) {
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
  	$assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);
  
  	$result = civicrm_api3('action_schedule', 'get', array('name' => 'Attach_Probation_Notification'));
  	if (empty($result['id'])) {
  	  $params = array(
  	    'name' => 'Attach_Probation_Notification',
  		'title' => 'Attach Probation Notification',
  		'recipient' => $assigneeID,
  		'limit_to' => 1,
  		'entity_value' => $activityTypeId,
  		'entity_status' => CRM_Core_OptionGroup::getValue('activity_status', 'Scheduled', 'name'),
  		'start_action_offset' => 0,
  		'start_action_unit' => 'hour',
  		'start_action_condition' => 'before',
  		'start_action_date' => 'activity_date_time',
  		'is_repeat' => 1,
  		'repetition_frequency_unit' => 'day',
  		'repetition_frequency_interval' => 1,
  		'end_frequency_unit' => 'hour',
  		'end_frequency_interval' => 0,
  		'end_action' => 'before',
  		'end_date' => 'activity_date_time',
  		'is_active' => 1,
  		'body_html' => '<p>Attach Probation Notification on {activity.activity_date_time}</p>',
  		'subject' => 'Reminder to Attach Probation Notification',
  		'record_activity' => 1,
  		'mapping_id' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_ActionMapping', 'activity_type', 'id', 'entity_value')
  		);
  	  $result = civicrm_api3('action_schedule', 'create', $params);
  	}
  }
  
}

