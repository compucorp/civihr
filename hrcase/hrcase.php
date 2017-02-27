<?php

require_once 'hrcase.civix.php';
require_once 'CRM/HRCase/Upgrader.php';

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
  if (!CRM_HRCase_Upgrader::isExtensionEnabled('uk.co.compucorp.civicrm.tasksassignments'))  {
    _hrcase_TaskAndAssignmentsPageRedirect();
  }

  return _hrcase_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_postInstall
 */
function hrcase_civicrm_postInstall() {
  return _hrcase_civix_civicrm_postInstall();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrcase_civicrm_uninstall() {
  return _hrcase_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrcase_civicrm_enable() {
  // PCHR-1263 : hrcase should not be installed/enabled without Task & Assignments extension
  if (!CRM_HRCase_Upgrader::isExtensionEnabled('uk.co.compucorp.civicrm.tasksassignments'))  {
    _hrcase_TaskAndAssignmentsPageRedirect();
  }

  return _hrcase_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrcase_civicrm_disable() {
  return _hrcase_civix_civicrm_disable();
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
  if ($objectName == 'Activity' && isset($objectRef->case_id) && !_hrcase_activityCreatedByTaskandAssignments($objectId)) {
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
 * Checks if the activity is created by task and assignments extension
 *
 * @param int $activity_id
 *
 * @return boolean
 */
function _hrcase_activityCreatedByTaskandAssignments($activity_id) {
  $params = ['id' => $activity_id];
  $activity = CRM_Activity_BAO_Activity::retrieve($params);

  if (!CRM_HRCase_Upgrader::isExtensionEnabled('uk.co.compucorp.civicrm.tasksassignments'))  {
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
 * redirect to extension list page and show error notification if T&A isn't installed/enabled
 */
function _hrcase_TaskAndAssignmentsPageRedirect()  {
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
