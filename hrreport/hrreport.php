<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.3                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2014                                |
+--------------------------------------------------------------------+
| This file is a part of CiviCRM.                                    |
|                                                                    |
| CiviCRM is free software; you can copy, modify, and distribute it  |
| under the terms of the GNU Affero General Public License           |
| Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
|                                                                    |
| CiviCRM is distributed in the hope that it will be useful, but     |
| WITHOUT ANY WARRANTY; without even the implied warranty of         |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
| See the GNU Affero General Public License for more details.        |
|                                                                    |
| You should have received a copy of the GNU Affero General Public   |
| License and the CiviCRM Licensing Exception along                  |
| with this program; if not, contact CiviCRM LLC                     |
| at info[AT]civicrm[DOT]org. If you have questions about the        |
| GNU Affero General Public License or the licensing of CiviCRM,     |
| see the CiviCRM license FAQ at http://civicrm.org/licensing        |
+--------------------------------------------------------------------+
*/

require_once __DIR__ . DIRECTORY_SEPARATOR . 'hrreport.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrreport_civicrm_config(&$config) {
  _hrreport_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrreport_civicrm_xmlMenu(&$files) {
  _hrreport_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrreport_civicrm_install() {
  $isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrabsence', 'is_active', 'full_name');
  $absenceReport = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'absenceReport', 'id', 'name');
  if ($isEnabled && !$absenceReport) {
    $reportParentId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Reports', 'id', 'name');
    $params = array(
      'domain_id' => CRM_Core_Config::domainID(),
      'label'     => 'Absence Report',
      'name'      => 'absenceReport',
      'url'       => 'civicrm/report/list?grp=absence&reset=1',
      'permission'=> 'access HRReport',
      'parent_id' => $reportParentId,
      'is_active' => 1,
    );
    CRM_Core_BAO_Navigation::add($params);
    $absenceParentId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Absences', 'id', 'name');
    $calendarId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'calendar', 'id', 'name');
    if (empty($calendarId)) {
      $params = array(
        'domain_id' => CRM_Core_Config::domainID(),
        'label'     => 'Calendar',
        'name'      => 'calendar',
        'url'       => null,
        'permission'=> 'access HRReport',
        'parent_id' => $absenceParentId,
        'is_active' => 1,
        'weight'    => 2,
      );
      CRM_Core_BAO_Navigation::add($params);
    }
  }
  return _hrreport_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_postInstall
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 */
function hrreport_civicrm_postInstall() {
  foreach (array("CiviHR Full Time Equivalents Report", "CiviHR Annual and Monthly Cost Equivalents Report", "CiviHR Public Holiday Report" ,"CiviHR Absence Report") as $title ) {
    $result = civicrm_api3('ReportInstance', 'getsingle', array('return' => "id",  'title' => $title));
    $url = "civicrm/report/instance/{$result['id']}?reset=1&section=2&snippet=5&context=dashlet";
    $fullscreen_url = "civicrm/report/instance/{$result['id']}?reset=1&section=2&snippet=5&context=dashletFullscreen";
    $name = "report/{$result['id']}";
    $label = $title;
    $domain_id = CRM_Core_Config::domainID();
    $query = " INSERT INTO civicrm_dashboard ( domain_id,url, fullscreen_url, is_active, name,label ) VALUES ($domain_id,'{$url}', '{$fullscreen_url}', 1, '{$name}', '{$label}' )";
    $dao = CRM_Core_DAO::executeQuery($query);
  }
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrreport_civicrm_uninstall() {
  $sql = "DELETE FROM civicrm_dashboard WHERE label IN ('CiviHR Full Time Equivalents Report', 'CiviHR Annual and Monthly Cost Equivalents Report', 'CiviHR Public Holiday Report','CiviHR Absence Report') ";
  CRM_Core_DAO::executeQuery($sql);
  $caseDashlet = civicrm_api3('Dashboard', 'getsingle', array('return' => array("id"), 'name' => 'casedashboard',));
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_dashboard_contact WHERE dashboard_id = {$caseDashlet['id']}");

  $isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrabsence', 'is_active', 'full_name');
  if ($isEnabled) {
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_navigation WHERE name IN ('absenceReport','calendar')");
    CRM_Core_BAO_Navigation::resetNavigation();
  }
  return _hrreport_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrreport_civicrm_enable() {
  _hrreport_setActiveFields(1);
  return _hrreport_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrreport_civicrm_disable() {
  _hrreport_setActiveFields(0);
  return _hrreport_civix_civicrm_disable();
}

function _hrreport_setActiveFields($setActive) {
  $sql = "UPDATE civicrm_dashboard_contact JOIN civicrm_dashboard on civicrm_dashboard.id =  civicrm_dashboard_contact.dashboard_id  SET civicrm_dashboard_contact.is_active = {$setActive} WHERE civicrm_dashboard.label IN ('CiviHR Full Time Equivalents Report', 'CiviHR Annual and Monthly Cost Equivalents Report', 'CiviHR Public Holiday Report' ,'CiviHR Absence Report') OR civicrm_dashboard.name = 'casedashboard'";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET is_active = {$setActive} WHERE label IN ('CiviHR Full Time Equivalents Report', 'CiviHR Annual and Monthly Cost Equivalents Report', 'CiviHR Public Holiday Report','CiviHR Absence Report') OR name = 'casedashboard'");

  $isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrabsence', 'is_active', 'full_name');
  if ($isEnabled) {
    CRM_Core_DAO::executeQuery("UPDATE civicrm_navigation SET is_active= {$setActive} WHERE name IN ('absenceReport','calendar')");
    CRM_Core_BAO_Navigation::resetNavigation();
  }
}

/**
 * Implementation of hook_civicrm_permission
 *
 * @param array $permissions
 * @return void
 */
function hrreport_civicrm_permission(&$permissions) {
  $prefix = ts('CiviHRReport') . ': '; // name of extension or module
  $permissions += array(
    'access HRReport' => $prefix . ts('access HRReport'),
  );
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
function hrreport_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrreport_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrreport_civicrm_managed(&$entities) {
  return _hrreport_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_pageRun
 *
 * @return void
 */
function hrreport_civicrm_pageRun( &$page ) {
  $pageName = $page->getVar( '_name' );
  if ($pageName == 'CRM_Contact_Page_DashBoard') {
    $session = CRM_Core_Session::singleton();
    $contact_id = $session->get('userID');
    //to do entry of default casedashboard report
    $caseDashlet = civicrm_api3('Dashboard', 'getsingle', array('return' => array("id", "url"), 'name' => 'casedashboard',));
    $dashboardContactId = civicrm_api3('DashboardContact', 'get', array('return' => "id",  'dashboard_id' => $caseDashlet['id'],'contact_id' => $contact_id));
    if (empty($dashboardContactId['id'])) {
      $url =  CRM_Utils_System::getServerResponse($caseDashlet['url'],false);
      civicrm_api3('DashboardContact', 'create', array("dashboard_id" => $caseDashlet['id'],'is_active' => '1','contact_id' => $contact_id,'content' => $url));
    }
    foreach (array("CiviHR Full Time Equivalents Report", "CiviHR Annual and Monthly Cost Equivalents Report", "CiviHR Public Holiday Report" ,"CiviHR Absence Report") as $title ) {
      //to get the report id
      $result = civicrm_api3('ReportInstance', 'getsingle', array('return' => "id",  'title' => $title));
      $dashletParams['url'] = "civicrm/report/instance/{$result['id']}?reset=1&section=2&snippet=5&context=dashlet";
      $dashlet = civicrm_api3('Dashboard', 'get', array('label' => $title,));
      $dashboardContact = civicrm_api3('DashboardContact', 'get', array('return' => "id",  'dashboard_id' => $dashlet['id'],'contact_id' => $contact_id));
      if (empty($dashboardContact['id'])) {
        civicrm_api3('DashboardContact', 'create', array("dashboard_id" => $dashlet['id'],'is_active' => '1','contact_id' => $contact_id,'content' =>  CRM_Utils_System::getServerResponse($dashletParams['url'])));
      }
    }
  }
}
