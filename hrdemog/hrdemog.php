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

require_once 'hrdemog.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrdemog_civicrm_config(&$config) {
  _hrdemog_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrdemog_civicrm_xmlMenu(&$files) {
  _hrdemog_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrdemog_civicrm_install() {
  return _hrdemog_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrdemog_civicrm_uninstall() {
  $customGroup = civicrm_api3('CustomGroup', 'getsingle', array('return' => "id",'name' => "Extended_Demographics",));
  civicrm_api3('CustomGroup', 'delete', array('id' => $customGroup['id']));
  //delete optionGroup
  if ($visaGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'is_visa_required_20130702051150', 'id', 'name')) {
    CRM_Core_BAO_OptionGroup::del($visaGroupID);
  }
  return _hrdemog_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrdemog_civicrm_enable() {
  _hrdemog_setActiveFields(1);
  return _hrdemog_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrdemog_civicrm_disable() {
  _hrdemog_setActiveFields(0);
  return _hrdemog_civix_civicrm_disable();
}

function _hrdemog_setActiveFields($setActive) {
  //disable/enable customgroup and customvalue
  $sql = "UPDATE civicrm_custom_field JOIN civicrm_custom_group on civicrm_custom_group.id = civicrm_custom_field.custom_group_id SET civicrm_custom_field.is_active = {$setActive} WHERE civicrm_custom_group.name = 'Extended_Demographics'";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name = 'Extended_Demographics'");

  //disable/enable optionGroup and optionValue
  $query = "UPDATE civicrm_option_value JOIN civicrm_option_group on civicrm_option_group.id = civicrm_option_value.option_group_id SET civicrm_option_value.is_active = {$setActive} WHERE civicrm_option_group.name IN ('ethnicity_20130725123943','religion_20130725124132','sexual_orientation_20130725124348','marital_status_20130913084916','is_visa_required_20130702051150')";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$setActive} WHERE name IN ('ethnicity_20130725123943','religion_20130725124132','sexual_orientation_20130725124348','marital_status_20130913084916','is_visa_required_20130702051150')");
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
function hrdemog_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrdemog_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrdemog_civicrm_managed(&$entities) {
  return _hrdemog_civix_civicrm_managed($entities);
}
