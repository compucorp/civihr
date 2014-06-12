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
  $customField = civicrm_api3('CustomField', 'get', array('custom_group_id' => $customGroup['id']));
  foreach ($customField['values'] as $key) {
    civicrm_api3('CustomField', 'delete', array('id' => $key['id']));
  }
  civicrm_api3('CustomGroup', 'delete', array('id' => $customGroup['id']));
  return _hrdemog_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrdemog_civicrm_enable() {
  //enable optiongroup and optionvalue
  foreach (array('ethnicity_20130725123943','religion_20130725124132','sexual_orientation_20130725124348','marital_status_20130913084916', 'is_visa_required_20130702051150') as $optionName) {
    if ($optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', $optionName, 'id', 'name')) {
      $optionGroups[] = $optionGroupID;
    }
  }
  _hrdemog_setActiveOptionFields($optionGroups, 1);

  //enable customgroup and customvalue
 if ($customGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', "Extended_Demographics", 'id', 'name')) {
    _hrdemog_setActiveCustomFields($customGroupID,1);
  }
  return _hrdemog_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrdemog_civicrm_disable() {
  //disable optiongroup and optionvalue
  foreach (array('ethnicity_20130725123943','religion_20130725124132','sexual_orientation_20130725124348','marital_status_20130913084916', 'is_visa_required_20130702051150') as $optionName) {
    if ($optionGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', $optionName, 'id', 'name')) {
      $optionGroups[] = $optionGroupID;
    }
  }
  _hrdemog_setActiveOptionFields($optionGroups, 0);

  //disable customgroup and customvalue
  if ($customGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', "Extended_Demographics", 'id', 'name')) {
    _hrdemog_setActiveCustomFields($customGroupID,0);
  }
  return _hrdemog_civix_civicrm_disable();
}

function _hrdemog_setActiveOptionFields($optionGroup, $setActive) {
  $optionGroupIDs = implode(',',$optionGroup );
  CRM_Core_DAO::executeQuery("UPDATE civicrm_option_value SET is_active = {$setActive} WHERE option_group_id IN ({$optionGroupIDs})");
  CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$setActive} WHERE id IN ({$optionGroupIDs})");
}

function _hrdemog_setActiveCustomFields($customGroupID, $setActive) {
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_field SET is_active = {$setActive} WHERE custom_group_id = {$customGroupID}");
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE id = {$customGroupID}");
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
