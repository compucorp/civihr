<?php

require_once 'hrcase.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrcase_civicrm_config(&$config) {
  _hrcase_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrcase_civicrm_xmlMenu(&$files) {
  _hrcase_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrcase_civicrm_install() {
  // PCHR-1263 : hrcase should not be installed without Task & Assignments extension
  if (empty(hrcase_checkTasksassignments()))  {
    hrcase_extensionsPageRedirect();
  }

  //update query to replace Case with Assignment
  $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'activity_type', 'id', 'name');
  $sql = "UPDATE civicrm_option_value SET label= replace(label,'Case','Assignment') WHERE label like '%Case%' and option_group_id=$optionGroupID and label <> 'Open Case'";
  CRM_Core_DAO::executeQuery($sql);

  $sql = "UPDATE civicrm_option_value SET label= replace(label,'Open Case','Created New Assignment') WHERE label like '%Case%' and option_group_id=$optionGroupID";
  CRM_Core_DAO::executeQuery($sql);

  $sql = "INSERT INTO `civicrm_relationship_type`
(`name_a_b`, `label_a_b`, `name_b_a`, `label_b_a`, `description`, `contact_type_a`, `contact_type_b`, `contact_sub_type_a`, `contact_sub_type_b`, `is_reserved`, `is_active`)
VALUES
('HR Manager is','HR Manager is','HR Manager','HR Manager','HR Manager','Individual','Individual',NULL,NULL,0,1),
('Line Manager is','Line Manager is','Line Manager','Line Manager','Line Manager','Individual','Individual',NULL,NULL,0,1)";
  CRM_Core_DAO::executeQuery($sql);

  $sql = "SELECT count(id) as count FROM `civicrm_relationship_type` WHERE `name_b_a`='Recruiting Manager'";
  $dao = CRM_Core_DAO::executeQuery($sql);
  while($dao->fetch()) {
    if ($dao->count == 0) {
      $sql = "INSERT INTO `civicrm_relationship_type`
    (`name_a_b`, `label_a_b`, `name_b_a`, `label_b_a`, `description`, `contact_type_a`, `contact_type_b`, `contact_sub_type_a`, `contact_sub_type_b`, `is_reserved`, `is_active`)
    VALUES
    ('Recruiting Manager is','Recruiting Manager is','Recruiting Manager','Recruiting Manager','Recruiting Manager','Individual','Individual',NULL,NULL,0,1)";
      CRM_Core_DAO::executeQuery($sql);
    }
  }
  return _hrcase_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_postInstall
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 */

function hrcase_civicrm_postInstall() {
  //disable example case types
  hrcase_example_caseType(FALSE);

  // Import custom group
  require_once 'CRM/Utils/Migrate/Import.php';
  $import = new CRM_Utils_Migrate_Import();

  $files = glob(__DIR__ . '/xml/*_customGroupCaseType.xml');
  if (is_array($files)) {
    foreach ($files as $file) {
      $import->run($file);
    }
  }

  updateEntityColumnValues();

  $scheduleActions = hrcase_getActionsSchedule();
  foreach($scheduleActions as $actionName=>$scheduleAction) {
  	$result = civicrm_api3('action_schedule', 'get', array('name' => $actionName));
  	if (empty($result['id'])) {
  	  $result = civicrm_api3('action_schedule', 'create', $scheduleAction);
  	}
  }
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrcase_civicrm_uninstall() {
  //update query to replace Assignment with Case
  $optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'activity_type', 'id', 'name');
  $sql = "UPDATE civicrm_option_value SET label= replace(label, 'Assignment', 'Case') WHERE label like '%Assignment%' and option_group_id=$optionGroupID and label <> 'New Assignment Created'";
  CRM_Core_DAO::executeQuery($sql);

  $sql = "UPDATE civicrm_option_value SET label= replace(label, 'Created New Assignment', 'Open Case') WHERE option_group_id=$optionGroupID";
  CRM_Core_DAO::executeQuery($sql);

  $scheduleActions = hrcase_getActionsSchedule(TRUE);
  $scheduleAction = implode("','",$scheduleActions );
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_action_schedule WHERE name IN ('{$scheduleAction}')");

  $sql = "DELETE FROM civicrm_option_value WHERE name IN ('Issue appointment letter','Fill Employee Details Form','Submission of ID/Residence proofs and photos','Program and work induction by program supervisor','Enter employee data in CiviHR','Group Orientation to organization values policies','Probation appraisal','Conduct appraisal','Collection of appraisal paperwork','Issue confirmation/warning letter','Get \"No Dues\" certification','Conduct Exit interview','Revoke access to databases','Block work email ID','Follow up on progress','Collection of Appraisal forms','Issue extension letter','Schedule joining date','Group Orientation to organization, values, policies','Probation appraisal (start probation workflow)','Schedule Exit Interview','Prepare formats','Print formats','Collate and print goals','References Check','Prepare and email schedule')";
  CRM_Core_DAO::executeQuery($sql);

  hrcase_example_caseType(TRUE);
  //delete custom group and custom field
  foreach (array('Joining_Data', 'Exiting_Data') as $cgName) {
    $customGroup = civicrm_api3('CustomGroup', 'getsingle', array('return' => "id",'name' => $cgName));
    civicrm_api3('CustomGroup', 'delete', array('id' => $customGroup['id']));
  }

  $isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrrecruitment', 'is_active', 'full_name');
  if (!$isEnabled) {
    CRM_Core_DAO::executeQuery("DELETE FROM `civicrm_relationship_type` WHERE name_b_a IN ('Recruiting Manager')");
  }

  //Delete cases and related contact on uninstall
  $caseTypes = CRM_Case_PseudoConstant::caseType('name', FALSE);
  $cases = array('Joining', 'Exiting', 'Probation', 'Appraisal');
  foreach ($caseTypes as $caseTypeKey => $caseType) {
    if (in_array($caseType, $cases)) {
      $caseDAO = new CRM_Case_DAO_Case();
      $caseDAO->case_type_id = $caseTypeKey;
      $caseDAO->find();
      while ($caseDAO->fetch()) {
        CRM_Case_BAO_Case::deleteCase($caseDAO->id);
      }
    }
  }
  return _hrcase_civix_civicrm_uninstall();
}

/**
 * Enable/Disable example case type
 */
function hrcase_example_caseType($is_active) {
  $isActive = $is_active ? 1 : 0;
  $sql = "Update civicrm_case_type SET is_active = {$isActive} where name IN ('AdultDayCareReferral', 'HousingSupport', 'adult_day_care_referral', 'housing_support')";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_BAO_Navigation::resetNavigation();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrcase_civicrm_enable() {
  // PCHR-1263 : hrcase should not be installed/enabled without Task & Assignments extension
  if (empty(hrcase_checkTasksassignments()))  {
    hrcase_extensionsPageRedirect();
  }
  _hrcase_setActiveFields(1);
  return _hrcase_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrcase_civicrm_disable() {
  _hrcase_setActiveFields(0);
  return _hrcase_civix_civicrm_disable();
}

function _hrcase_setActiveFields($setActive) {
  //disable/enable all custom group and fields
  $sql = "UPDATE civicrm_custom_field JOIN civicrm_custom_group
ON civicrm_custom_group.id = civicrm_custom_field.custom_group_id
SET civicrm_custom_field.is_active = {$setActive}
WHERE civicrm_custom_group.name IN ('Joining_Data', 'Exiting_Data')";

  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name IN ('Joining_Data', 'Exiting_Data')");

  //disable/enable activity type
  $query = "UPDATE civicrm_option_value
SET is_active = {$setActive}
WHERE name IN ('Open Case', 'Change Case Type', 'Change Case Status', 'Change Case Start Date', 'Assign Case Role', 'Remove Case Role', 'Merge Case', 'Reassigned Case', 'Link Cases', 'Change Case Tags', 'Add Client To Case','Issue appointment letter','Fill Employee Details Form','Submission of ID/Residence proofs and photos','Program and work induction by program supervisor','Enter employee data in CiviHR','Group Orientation to organization values policies','Probation appraisal','Conduct appraisal','Collection of appraisal paperwork','Issue confirmation/warning letter','Get \"No Dues\" certification','Conduct Exit interview','Revoke access to databases','Block work email ID','Follow up on progress','Collection of Appraisal forms','Issue extension letter','Schedule joining date','Group Orientation to organization, values, policies','Probation appraisal (start probation workflow)','Schedule Exit Interview','Prepare formats','Print formats','Collate and print goals','References Check','Prepare and email schedule')";

  CRM_Core_DAO::executeQuery($query);

  //disable/enable action schedule
  $scheduleActions = hrcase_getActionsSchedule(TRUE);
  $scheduleAction = implode("','",$scheduleActions );
  $query = "UPDATE civicrm_action_schedule SET is_active = {$setActive} WHERE name IN ('{$scheduleAction}')";
  CRM_Core_DAO::executeQuery($query);

  $sqlrel = "UPDATE `civicrm_relationship_type` SET is_active={$setActive} WHERE name_b_a IN ('HR Manager','Line Manager')";
  CRM_Core_DAO::executeQuery($sqlrel);

  $isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrrecruitment', 'is_active', 'full_name');
  if (!$isEnabled) {
    CRM_Core_DAO::executeQuery("UPDATE `civicrm_relationship_type` SET is_active={$setActive} WHERE name_b_a IN ('Recruiting Manager')");
  }
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function hrcase_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrcase_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrcase_civicrm_managed(&$entities) {
  return _hrcase_civix_civicrm_managed($entities);
}

function hrcase_civicrm_buildForm($formName, &$form) {
  //change pageTitle for adding Case/Assignment Activity
  if ($formName == 'CRM_Case_Form_Activity'){
    $contactDisplayName = CRM_Contact_BAO_Contact::displayName($form->getVar('_targetContactId'));
    $viewedContactDisplayName = CRM_Contact_BAO_Contact::displayName($form->_currentlyViewedContactId);
    if ($form->_activityTypeName == 'Created New Assignment') {
      CRM_Utils_System::setTitle($viewedContactDisplayName . ' - ' . ts('Created New Assignment'));
    }
    if ($form->_activityTypeName == 'Change Assignment Type') {
      CRM_Utils_System::setTitle($contactDisplayName . ' - ' . ts('Change Assignment Type'));
    }
    elseif ($form->_activityTypeName == 'Change Assignment Status') {
      CRM_Utils_System::setTitle($contactDisplayName . ' - ' . ts('Change Assignment Status'));
    }
    elseif ($form->_activityTypeName == 'Change Assignment Start Date') {
      CRM_Utils_System::setTitle($contactDisplayName . ' - ' . ts('Change Assignment Start Date'));
    }
    elseif ($form->_activityTypeName == 'Link Assignments') {
      CRM_Utils_System::setTitle($contactDisplayName . ' - ' . ts('Link Assignments'));
    }
  }

  //change label and page title
  if ($formName == 'CRM_Case_Form_Case') {
    CRM_Utils_System::setTitle(ts('Create New Assignment'));
  }
  //remove Run QA Audit/Redact dropdown,
  if ($formName == 'CRM_Case_Form_CaseView') {
    if ($form->elementExists('report_id') || $form->elementExists('activity_type_filter_id')){
      $check = $form->getElement('report_id');
      $check->_attributes = array();
      array_push($check->_attributes, 'display:none');
    }
  }

  if ($form instanceof CRM_Case_Form_Activity OR $form instanceof CRM_Case_Form_Case OR $form instanceof CRM_Case_Form_CaseView) {
    $optionID = CRM_Core_BAO_OptionValue::getOptionValuesAssocArrayFromName('activity_status');
    $completed = array_search( 'Completed', $optionID );
    CRM_Core_Resources::singleton()->addSetting(array(
      'hrcase' => array(
        'statusID' => $completed,
      ),
    ));
    if( $form instanceof CRM_Case_Form_CaseView ) {
    CRM_Core_Resources::singleton()->addSetting(array(
      'hrcase' => array(
        'manageScreen' => 1,
      ),
    ));
    }
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrcase', 'js/hrcase.js');
  }
}

function hrcase_civicrm_navigationMenu(&$params) {
  // process only if civiCase is enabled
  if (!array_key_exists('CiviCase', CRM_Core_Component::getEnabledComponents())) {
    return;
  }
  $values = array();
  $caseMenuItems = array();

  // the parent menu
  $referenceMenuItem['name'] = 'New Case';
  CRM_Core_BAO_Navigation::retrieve($referenceMenuItem, $values);

  if (!empty($values)) {
    // fetch all the case types
    $caseTypes = CRM_Case_PseudoConstant::caseType();
    $appValue = array_search('Application', $caseTypes);
    unset($caseTypes[$appValue]);

    $parentId = $values['id'];
    $maxKey = (max(array_keys($params)));

    // now create nav menu items
    if (!empty($caseTypes)) {
      foreach ($caseTypes as $cTypeId => $caseTypeName) {
        $maxKey = $maxKey + 1;
        $caseMenuItems[$maxKey] = array(
          'attributes' => array(
            'label'      => "New {$caseTypeName}",
            'name'       => "New {$caseTypeName}",
            'url'        => $values['url'] . "&ctype={$cTypeId}",
            'permission' => $values['permission'],
            'operator'   => $values['permission_operator'],
            'separator'  => NULL,
            'parentID'   => $parentId,
            'navID'      => $maxKey,
            'active'     => 1
          )
        );
      }
    }
    if (!empty($caseMenuItems)) {
      $params[$values['parent_id']]['child'][$values['id']]['child'] = $caseMenuItems;
    }
  }
}

/**
 * Implementation of hook_civicrm_alterContent
 *
 * @return void
 */
function hrcase_civicrm_alterContent( &$content, $context, $tplName, &$object ) {
  if ($context == "form" && $tplName == "CRM/Case/Form/Case.tpl" ) {
    $content .="<script type=\"text/javascript\">
      CRM.$(function($) {
        if ($('#activity_subject').val().length < 1)
          $('#activity_subject').val($( '#case_type_id option:selected').html());

        $('#case_type_id').on('change', function() {
          $('#activity_subject').val($('#case_type_id option:selected').html());
        });
      });
    </script>";
  }
}

/**
 * Implementation of hook_civicrm_post, executed after task creation/update
 *
 * @param string $op
 *   The type of operation being performed
 * @param string $objectName
 *   Type of object being processed
 * @param string $objectId string 
 *   Id of object
 * @param CRM_Activity_DAO_Activity $objectRef
 *   Object being used to process operation
 */
function hrcase_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  if ($objectName == 'Activity' && isset($objectRef->case_id) && !activityCreatedByTaskandAssignments($objectId)) {
    $component_id = CRM_Core_Component::getComponentID('CiviCase');
    $contact_id =  CRM_Case_BAO_Case::retrieveContactIdsByCaseId($objectRef->case_id);
    $hrjob = civicrm_api3('HRJobContract', 'get', array(
      'sequential' => 1,
      'contact_id' => $contact_id[1],
      'is_primary' => 1,
      'return' => "notice_amount,notice_unit",
    ));
    foreach($hrjob['values'] as $key=>$val) {
      $notice_amt = $val['notice_amount'];
      $notice_unit = $val['notice_unit'];
    }
    if (isset($notice_amt)) {
      $revoke = civicrm_api3('OptionValue', 'getsingle', array('return' => "value", 'name' => "Revoke access to databases"));
      $block = civicrm_api3('OptionValue', 'getsingle', array('return' => "value", 'name' => "Block work email ID", 'component_id' => $component_id));
      $date = strtotime($objectRef->activity_date_time);
      if ($objectRef->activity_type_id == $revoke['value']) {
        $date = date('Y-m-d h:i:s',strtotime("+{$notice_amt} {$notice_unit}", $date));
        civicrm_api3('Activity', 'create', array('id' => $objectRef->id ,'activity_date_time' => $date));
      }
      if ($objectRef->activity_type_id == $block['value']) {
        $date = date('Y-m-d h:i:s',strtotime("+{$notice_amt} {$notice_unit} +1 day", $date));
        civicrm_api3('Activity', 'create', array('id' => $objectRef->id ,'activity_date_time' => $date));
      }
    }
  }
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 */
function hrcase_civicrm_caseTypes(&$caseTypes) {
  _hrcase_civix_civicrm_caseTypes($caseTypes);
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
        'start_action_offset' => 2,
        'start_action_unit' => 'week',
        'start_action_condition' => 'before',
        'start_action_date' => 'activity_date_time',
        'is_repeat' => 1,
        'repetition_frequency_unit' => 'week',
        'repetition_frequency_interval' => 1,
        'end_frequency_unit' => 'month',
        'end_frequency_interval' => 2,
        'end_action' => 'after',
        'end_date' => 'activity_date_time',
        'body_html' => "<p>{$activityType} on {activity.activity_date_time}</p>",
        'subject' => "Reminder : {$activityType}",
      );
    }
  }
  return $schedules;
}

/**
 * function to check if the activity is created by task and assignments extension
 *
 * @param int $activity_id
 * @return boolean
 */
function activityCreatedByTaskandAssignments($activity_id) {
  $params = ['id' => $activity_id];
  $activity = CRM_Activity_BAO_Activity::retrieve($params);

  // check if task and assignments is enabled
  $isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'uk.co.compucorp.civicrm.tasksassignments', 'is_active', 'full_name');
  if(!$isEnabled) {
    return FALSE;
  }

  $tasksAssignmentsComponentIds[] = CRM_Core_Component::getComponentID('CiviTask');
  $tasksAssignmentsComponentIds[] = CRM_Core_Component::getComponentID('CiviDocument');

  // get the component_id of current object passed into hook_civicrm_post():
  $optionGroup = civicrm_api3('OptionGroup', 'getsingle', array(
    'sequential' => 1,
    'name' => "activity_type",
  ));

  $result = civicrm_api3('OptionValue', 'getsingle', array(
    'sequential' => 1,
    'option_group_id' => $optionGroup['id'],
    'value' => $activity->activity_type_id,
  ));

  if (!empty($result['component_id']) && in_array($result['component_id'], $tasksAssignmentsComponentIds)) {
    return TRUE;
  }

  return FALSE;
}

/**
 * Update extends_entity_column_value values in civicrm_custom_group table for both (Exiting & Joining)
 * custom groups to point for related case ID instead of its name since using the name only
 * is no longer work with civicrm 4.7.7+.
 *
 */
function updateEntityColumnValues()  {
  $caseTypes = CRM_Case_PseudoConstant::caseType('name');
  $exitingValue = array_search('Exiting', $caseTypes);
  $exitingValue = CRM_Core_DAO::VALUE_SEPARATOR . $exitingValue . CRM_Core_DAO::VALUE_SEPARATOR;
  $joiningValue = array_search('Joining', $caseTypes);
  $joiningValue = CRM_Core_DAO::VALUE_SEPARATOR . $joiningValue . CRM_Core_DAO::VALUE_SEPARATOR;

  $sql = "UPDATE civicrm_custom_group SET extends_entity_column_value = '{$exitingValue}' WHERE extends_entity_column_value = 'Exiting'";
  CRM_Core_DAO::executeQuery($sql);
  $sql = "UPDATE civicrm_custom_group SET extends_entity_column_value = '{$joiningValue}' WHERE extends_entity_column_value = 'Joining'";
  CRM_Core_DAO::executeQuery($sql);
}

/**
 * check if tasks and assignments extension is installed or enabled
 *
 * @return int
 */
function hrcase_checkTasksassignments()  {
  $isEnabled = CRM_Core_DAO::getFieldValue(
    'CRM_Core_DAO_Extension',
    'uk.co.compucorp.civicrm.tasksassignments',
    'is_active',
    'full_name'
  );
  return $isEnabled;
}

/**
 * redirect to extension list page and show error notification if T&A isn't installed/enabled
 *
 */
function hrcase_extensionsPageRedirect()  {
  $message = ts("You should Install/Enable Task & Assignments extension first");
  CRM_Core_Session::setStatus($message, ts('Cannot install/enable extension'), 'error');
  $url = CRM_Utils_System::url(
    'civicrm/admin/extensions',
    http_build_query([
      'reset' => 1
    ])
  );
  CRM_Utils_System::redirect($url);
}
