<?php

/**
 * Collection of upgrade steps
 */
class CRM_HRCase_Upgrader extends CRM_HRCase_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   */
  public function install() {
    //$this->executeSqlFile('sql/myinstall.sql');
    $this->setComponentStatuses(array(
      'CiviCase' => true,
    ));
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a couple simple queries
   *
   * @return TRUE on success
   * @throws Exception
   *
  public function upgrade_4200() {
    $this->ctx->log->info('Applying update 4200');
    CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
    CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
    return TRUE;
  } // */


  /**
   * Example: Run an external SQL script
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */
  public function upgrade_4400() {
    $this->ctx->log->info('Applying update 4400');
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
  	  $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);
  	
  	  $result = civicrm_api3('action_schedule', 'get', array('name' => 'Send_Termination_Letter'));
  	  if (empty($result['id'])) {
  	    $params = array(
  		  'name' => 'Send_Termination_Letter',
  		  'title' => 'Send Termination Letter',
  		  'recipient' => $targetID,
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
  	  $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);
  	
  	  $result = civicrm_api3('action_schedule', 'get', array('name' => 'Exit_Interview'));
  	  if (empty($result['id'])) {
  	    $params = array(
  		  'name' => 'Exit_Interview',
  		  'title' => 'Exit Interview',
  		  'recipient' => $targetID,
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
  	  $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);
  	
  	  $result = civicrm_api3('action_schedule', 'get', array('name' => 'Attach_Offer_Letter'));
  	  if (empty($result['id'])) {
  	    $params = array(
  		  'name' => 'Attach_Offer_Letter',
  		  'title' => 'Attach Offer Letter',
  		  'recipient' => $targetID,
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
  	  $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);
  	
  	  $result = civicrm_api3('action_schedule', 'get', array('name' => 'Attach_Reference'));
  	  if (empty($result['id'])) {
  	    $params = array(
  		  'name' => 'Attach_Reference',
  		  'title' => 'Attach Reference',
  		  'recipient' => $targetID,
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
  	  $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);
  		 
  	  $result = civicrm_api3('action_schedule', 'get', array('name' => 'Attach_Draft_Job_Contract'));
  	  if (empty($result['id'])) {
  	    $params = array(
  		  'name' => 'Attach_Draft_Job_Contract',
  		  'title' => 'Attach Draft Job Contract',
  		  'recipient' => $targetID,
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
  	  $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);
  	
  	  $result = civicrm_api3('action_schedule', 'get', array('name' => 'Attach_Objects_Document'));
  	  if (empty($result['id'])) {
  	    $params = array(
  		  'name' => 'Attach_Objects_Document',
  		  'title' => 'Attach Objects Document',
  		  'recipient' => $targetID,
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
  	  $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);
  	
  	  $result = civicrm_api3('action_schedule', 'get', array('name' => 'Attach_Appraisal_Document'));
  	  if (empty($result['id'])) {
  	    $params = array(
  		  'name' => 'Attach_Appraisal_Document',
  		  'title' => 'Attach Appraisal Document',
  		  'recipient' => $targetID,
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
  	  $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);
  	
  	  $result = civicrm_api3('action_schedule', 'get', array('name' => 'Attach_Probation_Notification'));
  	  if (empty($result['id'])) {
  	    $params = array(
  		  'name' => 'Attach_Probation_Notification',
  		  'title' => 'Attach Probation Notification',
  		  'recipient' => $targetID,
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
  /**
   * Set components as enabled or disabled. Leave any other
   * components unmodified.
   *
   * Note: This API has only been tested with CiviCRM 4.4.
   *
   * @param array $components keys are component names (e.g. "CiviMail"); values are booleans
   */
  public function setComponentStatuses($components) {
    $getResult = civicrm_api3('setting', 'getsingle', array(
      'domain_id' => CRM_Core_Config::domainID(),
      'return' => array('enable_components'),
    ));
    if (!is_array($getResult['enable_components'])) {
      throw new CRM_Core_Exception("Failed to determine component statuses");
    }

    // Merge $components with existing list
    $enableComponents = $getResult['enable_components'];
    foreach ($components as $component => $status) {
      if ($status) {
        $enableComponents = array_merge($enableComponents, array($component));
      } else {
        $enableComponents = array_diff($enableComponents, array($component));
      }
    }

    civicrm_api3('setting', 'create', array(
      'domain_id' => CRM_Core_Config::domainID(),
      'enable_components' => $enableComponents,
    ));
  }
}
