<?php

require_once 'hrabsence.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrabsence_civicrm_config(&$config) {
  _hrabsence_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrabsence_civicrm_xmlMenu(&$files) {
  _hrabsence_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrabsence_civicrm_install() {
  $reportWeight = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Reports', 'weight', 'name');

  $absenceNavigation = new CRM_Core_DAO_Navigation();
  $params = array (
    'domain_id'  => CRM_Core_Config::domainID(),
    'label'      => 'Absences',
    'name'       => 'Absences',
    'url'        => null,
    'operator'   => null,
    'weight'     => $reportWeight-1,
    'is_active'  => 1
  );
  $absenceNavigation->copyValues($params);
  $absenceNavigation->save();

  $absenceMenuTree = array(
    array(
      'label' => ts('My Absences'),
      'name' => 'my_absences',
      'url'  => 'civicrm/absences',
      'permission' => 'view HRAbsences, edit HRAbsences, administer CiviCRM, manage own HRAbsences',
    ),
    array(
      'label' => ts('Calendar'),
      'name' => 'calendar',
      'url'  => null,
      'permission' => 'access HRReport',
    ),
    array(
      'label' => ts('New Absence'),
      'name' => 'new_absence',
      'url'  => null,
      'permission' => 'edit HRAbsences,administer CiviCRM,manage own HRAbsences',
      'permission_operator' => 'OR',
      'has_separator' => 1,
    ),
    array(
      'label'      => ts('Public Holidays'),
      'name'       => 'publicHolidays',
      'url'        => 'civicrm/absence/holidays?reset=1',
      'permission' => 'administer CiviCRM',
    ),
    array(
      'label'      => ts('Absence Periods'),
      'name'       => 'absencePeriods',
      'url'        => 'civicrm/absence/period?reset=1',
      'permission' => 'administer CiviCRM',
    ),
    array(
      'label'      => ts('Absence Types'),
      'name'       => 'absenceTypes',
      'url'        => 'civicrm/absence/type?reset=1',
      'permission' => 'administer CiviCRM',
      'has_separator' => 1,
    ),
    array(
      'label'      => ts('Absence Report'),
      'name'       => 'absence_report',
      'url'        => 'civicrm/report/list?grp=absence&reset=1',
      'permission' => 'access HRReport',
    ),
  );

  foreach ($absenceMenuTree as $key => $menuItems) {
    $menuItems['is_active'] = 1;
    $menuItems['parent_id'] = $absenceNavigation->id;
    $menuItems['weight'] = $key;
    CRM_Core_BAO_Navigation::add($menuItems);
  }
  CRM_Core_BAO_Navigation::resetNavigation();

  $params = array(
    'sequential' => 1,
    'option_group_id' => 'activity_status',
    'name' => 'Rejected',
    'is_reserved' => 1,
    'is_active' => 1,
  );
  civicrm_api3('OptionValue', 'create', $params);
  return _hrabsence_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrabsence_civicrm_uninstall() {
  $absencesId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Absences', 'id', 'name');
  CRM_Core_BAO_Navigation::processDelete($absencesId);
  CRM_Core_BAO_Navigation::resetNavigation();

  $params = array(
    'sequential' => 1,
    'option_group_id' => 'activity_status',
    'is_reserved' => 1,
    'name' => 'Rejected',
    'return' => 'id',
  );

  if ($id = civicrm_api3('OptionValue', 'getvalue', $params)) {
    $params = array(
      'sequential' => 1,
      'id' => $id,
    );
    civicrm_api3('OptionValue', 'delete', $params);
  }
  return _hrabsence_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrabsence_civicrm_enable() {
  CRM_Core_BAO_Navigation::processUpdate(array('name' => 'Absences'), array('is_active' => 1));
  CRM_Core_BAO_Navigation::resetNavigation();

  $params = array(
    'sequential' => 1,
    'option_group_id' => 'activity_status',
    'is_reserved' => 1,
    'name' => 'Rejected',
    'return' => 'id',
  );

  if ($id = civicrm_api3('OptionValue', 'getvalue', $params)) {
    $params = array(
      'sequential' => 1,
      'id' => $id,
      'is_active' => 1,
    );
    civicrm_api3('OptionValue', 'create', $params);
  }
  return _hrabsence_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrabsence_civicrm_disable() {
  CRM_Core_BAO_Navigation::processUpdate(array('name' => 'Absences'), array('is_active' => 0));
  CRM_Core_BAO_Navigation::resetNavigation();

  $params = array(
    'sequential' => 1,
    'option_group_id' => 'activity_status',
    'is_reserved' => 1,
    'name' => 'Rejected',
    'return' => 'id',
  );

  if ($id = civicrm_api3('OptionValue', 'getvalue', $params)) {
    $params = array(
      'sequential' => 1,
      'id' => $id,
      'is_active' => 0,
    );
    $result = civicrm_api3('OptionValue', 'create', $params);
  }
  return _hrabsence_civix_civicrm_disable();
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
function hrabsence_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrabsence_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrabsence_civicrm_managed(&$entities) {
  return _hrabsence_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 */
function hrabsence_civicrm_caseTypes(&$caseTypes) {
  _hrabsence_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function myext_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _myext_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_entityTypes
 */
function hrabsence_civicrm_entityTypes(&$entityTypes) {
  $entityTypes[] = array(
    'name' => 'HRAbsenceType',
    'class' => 'CRM_HRAbsence_DAO_HRAbsenceType',
    'table' => 'civicrm_hrabsence_type',
  );
   $entityTypes[] = array(
    'name' => 'HRAbsencePeriod',
    'class' => 'CRM_HRAbsence_DAO_HRAbsencePeriod',
    'table' => 'civicrm_hrabsence_period',
  );
  $entityTypes[] = array(
    'name' => 'HRAbsenceEntitlement',
    'class' => 'CRM_HRAbsence_DAO_HRAbsenceEntitlement',
    'table' => 'civicrm_hrabsence_entitlement',
  );
}

/**
 * Implementation of hook_civicrm_alterFilters
 *
 * @param array $wrappers list of API_Wrapper instances
 * @param array $apiRequest
 */
function hrabsence_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  $action = strtolower($apiRequest['action']);
  if (strtolower($apiRequest['entity']) == 'activity' && ($action == 'get' || $action == 'getabsences')) {
    $wrappers[] = new CRM_HRAbsence_AbsenceRangeOption();
  }
}

/**
 * Implementation of hook_civicrm_tabs
 */
function hrabsence_civicrm_tabs(&$tabs, $contactID) {
  $contactType = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $contactID, 'contact_type', 'id');
  if ($contactType != 'Individual' || !(CRM_HRAbsence_Page_EmployeeAbsencePage::checkPermissions($contactID, 'viewWidget'))) {
    return;
  }
  $absence = civicrm_api3('Activity', 'getabsences', array('target_contact_id' => $contactID));
  $absenceDuration = 0;
  foreach ($absence['values'] as $k => $v) {
    $absenceDuration += CRM_HRAbsence_BAO_HRAbsenceType::getAbsenceDuration($v['id']);
  }
  CRM_HRAbsence_Page_EmployeeAbsencePage::registerResources($contactID);
  $tabs[] = array(
    'id'    => 'absenceTab',
    'url'   =>  CRM_Utils_System::url( 'civicrm/absences', array(
      'cid' => $contactID,
      'snippet' => 1,
    )),
    'count' => $absenceDuration/(8*60),
    'title' => ts('Absences'),
    'weight' => 300
  );
}

/**
 * Implementation of hook_civicrm_permission
 *
 * @param array $permissions
 * @return void
 */
function hrabsence_civicrm_permission(&$permissions) {
  $prefix = ts('CiviHRAbsence') . ': '; // name of extension or module
  $permissions += array(
    'view HRAbsences' => $prefix . ts('view HRAbsences'),
    'edit HRAbsences' => $prefix . ts('edit HRAbsences'),
    'manage own HRAbsences' => $prefix . ts('manage own HRAbsences'),
  );
}

/**
 * Implementaiton of hook_civicrm_alterAPIPermissions
 *
 * @param $entity
 * @param $action
 * @param $params
 * @param $permissions
 * @return void
 */
function hrabsence_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $session = CRM_Core_Session::singleton();
  $cid = $session->get('userID');
  if ($entity == 'h_r_absence_entitlement') {
    $permissions['h_r_absence_entitlement']['get'] = array('access CiviCRM', 'view HRAbsences');
    $permissions['h_r_absence_entitlement']['create'] = array('access CiviCRM', 'edit HRAbsences');
    $permissions['h_r_absence_entitlement']['update'] = array('access CiviCRM', 'edit HRAbsences');
    $permissions['h_r_absence_entitlement']['delete'] = array('administer CiviCRM');
  }

  if ($entity == 'h_r_absence_entitlement' && $cid == $params['contact_id'] && $action == 'get') {
    $permissions['h_r_absence_entitlement']['get'] = array('access CiviCRM');
  }

  $permissions['h_r_absence']['get'] = array('access CiviCRM', 'view HRAbsences');
  $permissions['h_r_absence']['create'] = array('access CiviCRM', 'edit HRAbsences');
  $permissions['h_r_absence']['update'] = array('access CiviCRM', 'edit HRAbsences');
  $permissions['h_r_absence']['delete'] = array('administer CiviCRM');
  $permissions['CiviHRAbsence'] = $permissions['h_r_absence'];
}

function hrabsence_civicrm_navigationMenu( &$params ) {
  $absenceMenuItems = array();
  $absenceType = CRM_HRAbsence_BAO_HRAbsenceType::getActivityTypes();
  $absenceId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Absences', 'id', 'name');
  $newAbsenceId =  CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'new_absence', 'id', 'name');
  $count = 0;
  foreach ($absenceType as $aTypeId => $absenceTypeName) {
    $absenceMenuItems[$count] = array(
      'attributes' => array(
        'label'      => "{$absenceTypeName}",
        'name'       => "{$absenceTypeName}",
        'url'        => "civicrm/absence/set?atype={$aTypeId}&action=add&cid=0",
        'permission' => 'edit HRAbsences,administer CiviCRM,manage own HRAbsences',
        'operator'   => 'OR',
        'separator'  => NULL,
        'parentID'   => $newAbsenceId,
        'navID'      => 1,
        'active'     => 1
      )
    );
    $count++;
  }
  if (!empty($absenceMenuItems)) {
    $params[$absenceId]['child'][$newAbsenceId]['child'] = $absenceMenuItems;
  }
  $calendarReportId = CRM_Core_DAO::getFieldValue('CRM_Report_DAO_ReportInstance', 'civihr/absence/calendar', 'id', 'report_id');
  $calendarId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'calendar', 'id', 'name');
  if ($calendarReportId) {
    $params[$absenceId]['child'][$calendarId]['attributes']['url'] = "civicrm/report/instance/{$calendarReportId}?reset=1";
  }
  else {
    $params[$absenceId]['child'][$calendarId]['attributes']['active'] = 0;
  }
}

function hrabsence_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Activity_Form_Activity') {
    $activityTypeId = $form->_activityTypeId;
    $activityId = $form->_activityId;
    $currentlyViewedContactId = $form->_currentlyViewedContactId;
    $paramsAbsenceType = array(
      'version' => 3,
      'sequential' => 1,
    );
    $resultAbsenceType = civicrm_api('HRAbsenceType', 'get', $paramsAbsenceType);
    $absenceType =  array();
    foreach ($resultAbsenceType['values'] as $key => $val) {
      $absenceType[$val['id']] = $val['debit_activity_type_id'];
    }

    if ( in_array($activityTypeId, $absenceType)) {
      if ($form->_action == CRM_Core_Action::VIEW) {
        $urlPathView = CRM_Utils_System::url('civicrm/absence/set', "atype={$activityTypeId}&aid={$activityId}&cid={$currentlyViewedContactId}&action=view&context=search&reset=1");
        CRM_Utils_System::redirect($urlPathView);
      }

      if ($form->_action == CRM_Core_Action::UPDATE) {
        $urlPathEdit = CRM_Utils_System::url('civicrm/absence/set', "atype={$activityTypeId}&aid={$activityId}&cid={$currentlyViewedContactId}&action=update&context=search&reset=1");
        CRM_Utils_System::redirect($urlPathEdit);
      }
    }
  }
}
