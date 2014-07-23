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
    CRM_Core_Component::flushEnabledComponents();
  }

  public function upgrade_1200() {
    $this->ctx->log->info('Applying update 1200');
    $groupSql = CRM_Core_DAO::executeQuery("SELECT id FROM civicrm_option_group WHERE name = 'case_type'");
    if ($groupSql->fetch()) {
      $sql = "UPDATE civicrm_option_value SET is_active = 0 WHERE option_group_id = {$groupSql->id} AND name IN ('adult_day_care_referral', 'housing_support')";
      CRM_Core_DAO::executeQuery($sql);
      CRM_Core_BAO_Navigation::resetNavigation();
    }
    return TRUE;
  }

  public function upgrade_1300() {
    $this->ctx->log->info('Applying update 1300');
    $sql = "Update civicrm_case_type SET is_active = 0 where name IN ('AdultDayCareReferral', 'HousingSupport', 'adult_day_care_referral', 'housing_support')";
    CRM_Core_DAO::executeQuery($sql);

    $caseTypes = CRM_Case_PseudoConstant::caseType('name');
    foreach (array('Exiting', 'Joining', 'Probation') as $caseName) {
      $caseID = array_search($caseName, $caseTypes);
      $values .= " WHEN '{$caseName}' THEN '{$caseID}'";
    }
    $query = "UPDATE civicrm_managed
      SET entity_id = CASE name
      {$values}
      END, entity_type = 'caseType' WHERE name IN ('Exiting', 'Joining', 'Probation');";
    CRM_Core_DAO::executeQuery($query);
    CRM_Core_BAO_Navigation::resetNavigation();
    return TRUE;
  }

  public function upgrade_1400() {
    $this->ctx->log->info('Applying update 1400');
    $i = 3;
    foreach (array('Joining','Probation') as $caseName) {
      CRM_Core_DAO::executeQuery("UPDATE civicrm_case_type SET weight = {$i} WHERE name = '{$caseName}'");
      $i++;
    }
    CRM_Core_DAO::executeQuery("UPDATE civicrm_case_type SET weight = 6 WHERE name = 'Exiting'");
    $this->executeSqlFile('sql/activities_install.sql');
    $scheduleActions = hrcase_getActionsSchedule();
    foreach($scheduleActions as $actionName=>$scheduleAction) {
      $result = civicrm_api3('action_schedule', 'get', array('name' => $actionName));
      if (empty($result['id'])) {
        $result = civicrm_api3('action_schedule', 'create', $scheduleAction);
      }
    }
    CRM_Core_Invoke::rebuildMenuAndCaches(TRUE);

    //update query to replace Case with Assignment
    $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'activity_type', 'id', 'name');
    $sql = "UPDATE civicrm_option_value SET label= replace(label,'Case','Assignment') WHERE label like '%Case%' and option_group_id=$optionGroupID and label <> 'Open Case'";
    CRM_Core_DAO::executeQuery($sql);

    $sql = "UPDATE civicrm_option_value SET label= replace(label,'Open Case','Created New Assignment') WHERE label like '%Case%' and option_group_id=$optionGroupID";
    CRM_Core_DAO::executeQuery($sql);
    return TRUE;
  }

  function hrcase_getActionsSchedule($getNamesOnly = FALSE) {
    $actionsForActivities = array(
      'Issue_appointment_letter' => 'Issue appointment letter',
      'Fill_Employee_Details_Form' => 'Fill Employee Details Form',
      'Submission_of_ID/Residence_proofs_and_photos' => 'Submission of ID/Residence proofs and photos',
      'Program_and_work_induction_by_program_supervisor' => 'Program and work induction by program supervisor',
      'Enter_employee_data_in_CiviHR' => 'Enter employee data in CiviHR',
      'Group_Orientation_to_organization_values_policies' => 'Group Orientation to organization, values, policies',
      'Probation_appraisal' => 'Probation appraisal (start probation workflow)',
      'Conduct_appraisal' => 'Conduct appraisal',
      'Collection_of_appraisal_paperwork' => 'Collection of appraisal paperwork',
      'Issue_confirmation/warning_letter' => 'Issue confirmation/warning letter',
      'Get_"No Dues"_certification' => 'Get "No Dues" certification',
      'Conduct_Exit_interview' => 'Conduct Exit interview',
      'Revoke_access_to_databases' => 'Revoke access to databases',
      'Block_work_email_ID' => 'Block work email ID',
      'Follow_up_on_progress' => 'Follow up on progress',
      'Collection_of_Appraisal_forms' => 'Collection of Appraisal forms',
      'Issue_extension_letter' => 'Issue extension letter',
    );
    if ($getNamesOnly) {
      return array_keys($actionsForActivities);
    }
    $schedules = array();
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
    $assigneeID = CRM_Utils_Array::key('Activity Assignees', $activityContacts);
    $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);
    $scheduledStatus = CRM_Core_OptionGroup::getValue('activity_status', 'Scheduled', 'name');
    $mappingId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_ActionMapping', 'activity_type', 'id', 'entity_value');
    // looping to build schedule params
    foreach ($actionsForActivities as $reminderName => $activityType) {
      $activityTypeId = CRM_Core_OptionGroup::getValue('activity_type', $activityType, 'name');
      if (!empty($activityTypeId)) {
        $reminderTitle = str_replace('_', ' ', $reminderName);
        $schedules[$reminderName] = array(
          'name' => $reminderName,
          'title' => $reminderTitle,
          'recipient' => $assigneeID,
          'limit_to' => 1,
          'entity_value' => $activityTypeId,
          'entity_status' => $scheduledStatus,
          'is_active' => 1,
          'record_activity' => 1,
          'mapping_id' => $mappingId,
        );
        if ($reminderName == 'Issue_appointment_letter') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Issue appointment letter on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Issue appointment letter';
        }
        elseif ($reminderName == 'Fill_Employee_Details_Form') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Fill Employee Details Form on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Fill Employee Details Form';
        }
        elseif ($reminderName == 'Submission_of_ID/Residence_proofs_and_photos') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Submission of ID/Residence proofs and photos on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Submit ID/Residence proofs and photos';
        }
        elseif ($reminderName == 'Program_and_work_induction_by_program_supervisor') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Program and work induction by program supervisor on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder for Program and work induction by program supervisor';
        }
        elseif ($reminderName == 'Enter_employee_data_in_CiviHR') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Enter employee data in CiviHR on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Enter employee data in CiviHR';
        }
        elseif ($reminderName == 'Group_Orientation_to_organization_values_policies') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Group Orientation to organization values policies on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder for Group Orientation to organization values policies';
        }
        elseif ($reminderName == 'Probation_appraisal') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Probation appraisal on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder for Probation appraisal';
        }
        elseif ($reminderName == 'Conduct_appraisal') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Conduct appraisal on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Conduct appraisal';
        }
        elseif ($reminderName == 'Collection_of_appraisal_paperwork') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Collection of appraisal paperwork on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder for Collection of appraisal paperwork';
        }
        elseif ($reminderName == 'Issue_confirmation/warning_letter') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Issue confirmation/warning letter on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Issue confirmation/warning letter';
        }
        elseif ($reminderName == 'Get_"No Dues"_certification') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Get "No Dues" certification on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Get "No Dues" certification';
        }
        elseif ($reminderName == 'Conduct_Exit_interview') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Conduct Exit interview on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Conduct Exit interview';
        }
        elseif ($reminderName == 'Revoke_access_to_databases') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Revoke access to databases on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Revoke access to databases';
        }
        elseif ($reminderName == 'Block_work_email_ID') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Block work email ID on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Block work email ID';
        }
        elseif ($reminderName == 'Follow_up_on_progress') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Follow up on progress on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Follow up on progress';
        }
        elseif ($reminderName == 'Collection_of_Appraisal_forms') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Collection of Appraisal forms on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Collect the Appraisal forms';
        }
        elseif ($reminderName == 'Issue_extension_letter') {
          $schedules[$reminderName]['recipient'] = $targetID;
          $schedules[$reminderName]['start_action_offset'] = 2;
          $schedules[$reminderName]['start_action_unit'] = 'week';
          $schedules[$reminderName]['start_action_condition'] = 'before';
          $schedules[$reminderName]['start_action_date'] = 'activity_date_time';
          $schedules[$reminderName]['is_repeat'] = 1;
          $schedules[$reminderName]['repetition_frequency_unit'] = 'week';
          $schedules[$reminderName]['repetition_frequency_interval'] = 1;
          $schedules[$reminderName]['end_frequency_unit'] = 'month';
          $schedules[$reminderName]['end_frequency_interval'] = 2;
          $schedules[$reminderName]['end_action'] = 'after';
          $schedules[$reminderName]['end_date'] = 'activity_date_time';
          $schedules[$reminderName]['body_html'] = '<p>Issue extension letter on {activity.activity_date_time}</p>';
          $schedules[$reminderName]['subject'] = 'Reminder to Issue extension letter';
        }
      }
    }
    return $schedules;
  }
}
