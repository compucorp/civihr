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

  $isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrreport', 'is_active', 'full_name');
  $reportParentId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Reports', 'id', 'name');
  $params = array(
    'domain_id' => CRM_Core_Config::domainID(),
    'label'     => 'Absence Report',
    'name'      => 'absenceReport',
    'url'       => 'civicrm/report/list?grp=absence&reset=1',
    'permission'=> 'access HRReport',
    'parent_id' => $reportParentId,
    'is_active' => $isEnabled,
  );
  CRM_Core_BAO_Navigation::add($params);

  CRM_Core_BAO_Navigation::resetNavigation();

  $params = array(
    'sequential' => 1,
    'option_group_id' => 'activity_status',
    'name' => 'Rejected',
    'is_reserved' => 1,
    'is_active' => 1,
  );
  civicrm_api3('OptionValue', 'create', $params);

  /* Create message template for absence leave application */
  $msg_text = 'Dear {$displayName},

{ts}Employee:{/ts} {$empName}
{ts}Position:{/ts} {$empPosition}
{ts}Absence Type:{/ts} {$absenceType}
{ts}Dates:{/ts} {$startDate} - {$endDate}

{if $cancel}
{ts}Leave has been cancelled.{/ts}
{elseif $reject}
{ts}Leave has been rejected.{/ts}
{else}

{ts}Date{/ts} | {ts}Absence{/ts} | {if $approval} {ts}Approve{/ts} {/if}

{foreach from=$absentDateDurations item=value key=label}
{$label|date_format} | {if $value.duration == 480} {ts}Full Day{/ts} {elseif $value.duration == 240} {ts}Half Day{/ts} {/if} | {if $approval} {if $value.approval == 2}{ts}Approved{/ts} {elseif $value.approval == 9} {ts}Unapproved{/ts} {/if} {/if}
{/foreach}

{ts}Total{/ts} | {$totDays} | {if $approval} {$appDays} {/if}

{/if}

{ts}Type of Sickness:{/ts} {$sickType}
{ts}Absence Comment:{/ts} {$absenceComment}

{ts}Thanks{/ts}
CiviHR';

  $msg_html = '<p>{ts}Dear{/ts} {$displayName},</p>
<table>
	<tbody>
		<tr>
			<td>{ts}Employee:{/ts}</td>
			<td>{$empName}</td>
		</tr>
		<tr>
			<td>{ts}Position:{/ts}</td>
			<td>{$empPosition}</td>
		</tr>
		<tr>
			<td>{ts}Absence Type:{/ts}</td>
			<td>{$absenceType}</td>
		</tr>
		<tr>
			<td>{ts}Dates:{/ts}</td>
			<td>{$startDate} - {$endDate}</td>
		</tr>
	</tbody>
</table>

{if $cancel}
  <p> {ts}Leave has been cancelled.{/ts} </p>
{elseif $reject}
  <p> {ts}Leave has been rejected.{/ts} </p>
{else}

<table border="1" border-spacing="0">
	<tbody>
		<tr>
			<th> {ts}Date{/ts} </th>
			<th> {ts}Absence{/ts} </th>
{if $approval}
			<th> {ts}Approve{/ts} </th>
{/if}
		</tr>
{foreach from=$absentDateDurations item=value key=label}
		<tr>
			<td>{$label|date_format}</td>
			<td>{if $value.duration == 480} {ts}Full Day{/ts} {elseif $value.duration == 240} {ts}Half Day{/ts} {else}
{/if}</td>
{if $approval}
			<td>{if $value.approval == 2} {ts}Approved{/ts} {elseif $value.approval == 9} {ts}Unapproved{/ts} {else}
{/if}</td>
{/if}
		</tr>
{/foreach}
		<tr>
			<td>{ts}Total{/ts}</td>
			<td>{$totDays}</td>
{if $approval}
			<td>{$appDays}</td>
{/if}
		</tr>
	</tbody>
</table>
{/if}
<br/>
<table>
	<tbody>
		<tr>
			<td>{ts}Type of Sickness:{/ts}</td>
			<td>{$sickType}</td>
		</tr>
		<tr>
			<td>{ts}Absence Comment:{/ts}</td>
			<td>{$absenceComment}</td>
		</tr>
	</tbody>
</table>
<br/>
<p> {ts}Thanks{/ts} <br/>
CiviHR</p>';

  $msg_params = array(
    'msg_title' => 'Absence Email',
    'msg_subject' => 'Absences Application',
    'msg_text' => $msg_text,
    'msg_html' => $msg_html,
    'workflow_id' => NULL,
    'is_default' => '1',
    'is_reserved' => '0',
  );
  civicrm_api3('message_template', 'create', $msg_params);

  return _hrabsence_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrabsence_civicrm_uninstall() {
  $query = "DELETE FROM civicrm_navigation WHERE name in ('Absences','absenceReport')";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_BAO_Navigation::resetNavigation();

  $sql = "DELETE civicrm_option_value FROM civicrm_option_value JOIN civicrm_option_group on civicrm_option_group.id = civicrm_option_value.option_group_id WHERE civicrm_option_group.name = 'activity_status' and civicrm_option_value.name = 'Rejected'";
  CRM_Core_DAO::executeQuery($sql);

  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_option_group WHERE title = 'Sick Type'");

  foreach (array('Absence_Comment', 'Type_of_Sickness') as $abType) {
    $customGroup = civicrm_api3('CustomGroup', 'getsingle', array('return' => "id",'name' => $abType));
    civicrm_api3('CustomGroup', 'delete', array('id' => $customGroup['id']));
  }

  $absenceTypes = CRM_Core_OptionGroup::values('activity_type', FALSE, FALSE, FALSE, " AND grouping = 'Timesheet'", 'id', FALSE);
  $absenceType = implode(',', $absenceTypes);
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_option_value WHERE civicrm_option_value.id IN ({$absenceType})");
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_msg_template WHERE msg_title = 'Absence Email'");

  return _hrabsence_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrabsence_civicrm_enable() {
  //Enable the Navigation menu and submenus
  $sql = "UPDATE civicrm_navigation SET is_active=1 WHERE name IN ('Absences','my_absences', 'new_absence', 'publicHolidays', 'absencePeriods', 'absenceTypes', 'absence_report')";
  CRM_Core_DAO::executeQuery($sql);
  $isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrreport', 'is_active', 'full_name');
  if ($isEnabled) {
    $sql = "UPDATE civicrm_navigation SET is_active=1 WHERE name IN ('absenceReport','calendar')";
    CRM_Core_DAO::executeQuery($sql);
  }
  CRM_Core_BAO_Navigation::resetNavigation();
  CRM_Core_DAO::executeQuery("UPDATE civicrm_msg_template SET is_active=1 WHERE msg_title = 'Absence Email'");

  _hrabsence_setActiveFields(1);
  return _hrabsence_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrabsence_civicrm_disable() {
  //Disable the Navigation menu and submenus
  $sql = "UPDATE civicrm_navigation SET is_active=0 WHERE name IN ('Absences','my_absences', 'calendar', 'new_absence', 'publicHolidays', 'absencePeriods', 'absenceTypes', 'absence_report','absenceReport')";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_BAO_Navigation::resetNavigation();
  CRM_Core_DAO::executeQuery("UPDATE civicrm_msg_template SET is_active=0 WHERE msg_title = 'Absence Email'");

  _hrabsence_setActiveFields(0);
  return _hrabsence_civix_civicrm_disable();
}

function _hrabsence_setActiveFields($setActive) {
  //disable/enable customGroup and customValue
  $sql = "UPDATE civicrm_custom_field JOIN civicrm_custom_group on civicrm_custom_group.id = civicrm_custom_field.custom_group_id SET civicrm_custom_field.is_active = {$setActive} WHERE civicrm_custom_group.name IN ('Absence_Comment', 'Type_of_Sickness')";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name IN ('Absence_Comment', 'Type_of_Sickness')");

  //disable/enable optionGroup and optionValue
  $query = "UPDATE civicrm_option_value JOIN civicrm_option_group on civicrm_option_group.id = civicrm_option_value.option_group_id SET civicrm_option_value.is_active = {$setActive} WHERE civicrm_option_group.title = ('Sick Type')";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$setActive} WHERE title = 'Sick Type'");

  $absenceTypes = CRM_Core_OptionGroup::values('activity_type', FALSE, FALSE, FALSE, " AND grouping = 'Timesheet'", 'id', FALSE);
  $customfieldIDs = implode(',', $absenceTypes);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_option_value SET is_active = {$setActive} WHERE id IN ({$customfieldIDs})");

  $sql = "UPDATE civicrm_option_value JOIN civicrm_option_group on civicrm_option_group.id = civicrm_option_value.option_group_id SET civicrm_option_value.is_active = {$setActive} WHERE civicrm_option_group.name = 'activity_status' and civicrm_option_value.name = 'Rejected'";
  CRM_Core_DAO::executeQuery($sql);

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
  if (!($contactType == 'Individual' && CRM_HRAbsence_Page_EmployeeAbsencePage::checkPermissions($contactID, 'viewWidget'))) {
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
  if (substr($entity, 0, 11) == 'h_r_absence') {
    $permissions['h_r_absence']['get'] = array('access CiviCRM', 'view HRAbsences');
    $permissions['h_r_absence']['create'] = array('access CiviCRM', 'edit HRAbsences');
    $permissions['h_r_absence']['update'] = array('access CiviCRM', 'edit HRAbsences');
    $permissions['h_r_absence']['delete'] = array('administer CiviCRM');
  }
  if ($entity == 'h_r_absence_entitlement' && $action == 'get') {
    $permissions['h_r_absence_entitlement']['get'] = array('access CiviCRM', array('administer CiviCRM', 'view HRAbsences', 'edit HRAbsences', 'manage own HRAbsences'));
  }
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
  else
    if($calendarId) {
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
