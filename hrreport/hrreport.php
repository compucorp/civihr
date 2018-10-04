<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.4                                                 |
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
  return _hrreport_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_postInstall
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 */
function hrreport_civicrm_postInstall() {
  $report_id = _hrreport_getId();
  foreach ($report_id as $key=>$val) {
    $dashlet = civicrm_api3('Dashboard', 'get', array('name' => "report/{$val}",));
    if ($dashlet['count'] == 0) {
      $url = "civicrm/report/instance/{$val}?reset=1&section=2&snippet=5&context=dashlet";
      $fullscreen_url = "civicrm/report/instance/{$val}?reset=1&section=2&snippet=5&context=dashletFullscreen";
      $name = "report/{$val}";
      $label = $key;
      $domain_id = CRM_Core_Config::domainID();
      $query = " INSERT INTO civicrm_dashboard ( domain_id,url, fullscreen_url, is_active, name,label, permission) VALUES ($domain_id,'{$url}', '{$fullscreen_url}', 1, '{$name}', '{$label}','access HRReport' )";
      CRM_Core_DAO::executeQuery($query);
    }
  }
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrreport_civicrm_uninstall() {
  $report_id = _hrreport_getId();
  $report_ids = implode("','report/",$report_id );
  $sql = "DELETE FROM civicrm_dashboard WHERE name IN ('report/{$report_ids}') ";
  CRM_Core_DAO::executeQuery($sql);
  $caseDashlet = civicrm_api3('Dashboard', 'getsingle', array('return' => array("id"), 'name' => 'casedashboard',));
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_dashboard_contact WHERE dashboard_id = {$caseDashlet['id']}");

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
  $report_id = _hrreport_getId();
  $report_ids = implode("','report/",$report_id );
  $sql = "UPDATE civicrm_dashboard_contact JOIN civicrm_dashboard on civicrm_dashboard.id =  civicrm_dashboard_contact.dashboard_id  SET civicrm_dashboard_contact.is_active = {$setActive} WHERE civicrm_dashboard.name IN ('report/{$report_ids}') OR civicrm_dashboard.name = 'casedashboard'";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_dashboard SET is_active = {$setActive} WHERE name IN ('report/{$report_ids}') OR name = 'casedashboard'");
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
 * Implementation of hook_civicrm_buildForm
 *
 * @params string $formName - the name of the form
 *         object $form - reference to the form object
 * @return void
 */
function hrreport_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Report_Form_Case_Detail') {
    CRM_Utils_System::setTitle(ts('Assignment Detail Report - Template'));
  }
  if ($formName == 'CRM_Report_Form_Case_TimeSpent') {
    CRM_Utils_System::setTitle(ts('Assignment Time Spent Report - Template'));
  }
  if ($formName == 'CRM_Report_Form_Case_Summary') {
    CRM_Utils_System::setTitle(ts('Assignment Summary Report - Template'));
  }
}

/**
 * Implementation of hook_civicrm_pageRun
 *
 * @return void
 */
function hrreport_civicrm_pageRun( &$page ) {
  $pageName = $page->getVar( '_name' );
  $componentName = $page->getVar('_compName');
  //change page title from 'Case Report' to  'Assignment Report'
  if ($pageName == 'CRM_Report_Page_InstanceList' && $componentName == 'Case') {
    CRM_Utils_System::setTitle(ts('Assignment Reports'));
  }
  if ($pageName == 'CRM_Report_Page_InstanceList' || $pageName == 'CRM_Report_Page_TemplateList' ){
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrreport', 'js/hrreport.js');
  }

  if ($pageName == 'CRM_Contact_Page_DashBoard') {
    $report_id = _hrreport_getId();
    $session = CRM_Core_Session::singleton();
    $contact_id = $session->get('userID');
    //to do entry of default casedashboard report and civicrm news report
    foreach (array('blog','casedashboard') as $name) {
      $caseDashlet = civicrm_api3('Dashboard', 'getsingle', array('return' => array("id", "url", "permission"), 'name' => $name,));
      $dashboardContactId = civicrm_api3('DashboardContact', 'get', array('return' => array("id", "is_active"),  'dashboard_id' => $caseDashlet['id'],'contact_id' => $contact_id));
      $url =  CRM_Utils_System::getServerResponse($caseDashlet['url'],false);
      if (empty($dashboardContactId['id'])) {
        if ($name == 'blog') {
          civicrm_api3('DashboardContact', 'create', array("dashboard_id" => $caseDashlet['id'],'is_active' => '1','contact_id' => $contact_id,'column_no' => '1','content' => $url));
        }
        elseif (CRM_Case_BAO_Case::accessCiviCase()) {
          civicrm_api3('DashboardContact', 'create', array("dashboard_id" => $caseDashlet['id'],'is_active' => '1','contact_id' => $contact_id,'column_no' => '1','content' => $url));
        }
      }
      if (!empty($dashboardContactId['id']) && $name == 'casedashboard') {
        $id = $dashboardContactId['id'];
        if (!CRM_Case_BAO_Case::accessCiviCase()) {
          _hrreport_createDashlet($id, '0');
        }
        elseif($dashboardContactId['values'][$id]['is_active'] == 0) {
          _hrreport_createDashlet($id,'1');
        }
      }
    }
    $i = 1;
    foreach ($report_id as $key=>$val) {
      $dashletParams['url'] = "civicrm/report/instance/{$val}?reset=1&section=2&snippet=5&context=dashlet";
      $dashlet = civicrm_api3('Dashboard', 'get', array('name' => "report/{$val}",));
      if (!empty($dashlet['count']) && $dashlet['count'] > 0) {
        $contentUrl = CRM_Utils_System::getServerResponse($dashletParams['url']);
        $dashboardContact = civicrm_api3('DashboardContact', 'get', array('return' => array("id", "is_active"), 'dashboard_id' => $dashlet['id'],'contact_id' => $contact_id));
        $dashId = $dashlet['id'];
        if (empty($dashboardContact['id'])) {
          if (CRM_Core_Permission::check($dashlet['values'][$dashId]['permission'])) {
            civicrm_api3('DashboardContact', 'create', array("dashboard_id" => $dashlet['id'],'is_active' => '1','contact_id' => $contact_id ,'column_no' => $i ,'content' =>  CRM_Utils_System::getServerResponse($dashletParams['url'])));
          }
        }
        if (!empty($dashboardContact['id'])) {
          $id = $dashboardContact['id'];
          if (!CRM_Core_Permission::check($dashlet['values'][$dashId]['permission'])) {
            _hrreport_createDashlet($id, '0');
          }
          elseif($dashboardContact['values'][$id]['is_active'] == 0) {
            _hrreport_createDashlet($id, '1');
          }
        }
        $i = 0;
      }
    }
  }
}

function _hrreport_createDashlet($dashboardContact, $setactive) {
  civicrm_api3('DashboardContact', 'create', array("id" => $dashboardContact,'is_active' => $setactive));
}

function _hrreport_getId () {
  $sql = "SELECT * FROM  civicrm_managed WHERE  entity_type = 'ReportInstance' AND name IN ('CiviHR FTE Report', 'CiviHR Annual and Monthly Cost Equivalents Report', 'CiviHR Public Holiday Report') ";
  $dao = CRM_Core_DAO::executeQuery($sql);
  $report_id = array();
  while ($dao->fetch()) {
    $report_id[$dao->name] = $dao->entity_id;
  }
  return $report_id;
}
