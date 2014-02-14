<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.2                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2013                                |
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
  if ($isEnabled) {
    $reportParentId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Reports', 'id', 'name');
    $params = array(
      'domain_id' => CRM_Core_Config::domainID(),
      'label'     => 'Absence Report',
      'name'      => 'absenceReport',
      'url'       => 'civicrm/report/list?grp=absence&reset=1',
      'permission'=> 'access HRAbsences',
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
        'permission'=> 'access HRAbsences',
        'parent_id' => $absenceParentId,
        'is_active' => 1,
      );
      CRM_Core_BAO_Navigation::add($params);
    }
  }
  return _hrreport_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrreport_civicrm_uninstall() {
  $isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrabsence', 'is_active', 'full_name');
  if ($isEnabled) {
    $absenceMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'absenceReport', 'id', 'name');
    $calendarMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'calendar', 'id', 'name');
    CRM_Core_BAO_Navigation::processDelete($absenceMenuId);
    CRM_Core_BAO_Navigation::processDelete($calendarMenuId);
    CRM_Core_BAO_Navigation::resetNavigation();
  }
  return _hrreport_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrreport_civicrm_enable() {
  $isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrabsence', 'is_active', 'full_name');
  if ($isEnabled) {
    CRM_Core_BAO_Navigation::processUpdate(array('name' => 'absenceReport'), array('is_active' => 1));
    CRM_Core_BAO_Navigation::processUpdate(array('name' => 'calendar'), array('is_active' => 1));
    CRM_Core_BAO_Navigation::resetNavigation();
  }
  return _hrreport_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrreport_civicrm_disable() {
  $isEnabled = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrabsence', 'is_active', 'full_name');
  if ($isEnabled) {
    CRM_Core_BAO_Navigation::processUpdate(array('name' => 'absenceReport'), array('is_active' => 0));
    CRM_Core_BAO_Navigation::processUpdate(array('name' => 'calendar'), array('is_active' => 0));
    CRM_Core_BAO_Navigation::resetNavigation();
  }
  return _hrreport_civix_civicrm_disable();
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
