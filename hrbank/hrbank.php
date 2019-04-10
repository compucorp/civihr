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

require_once 'hrbank.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrbank_civicrm_config(&$config) {
  _hrbank_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrbank_civicrm_xmlMenu(&$files) {
  _hrbank_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrbank_civicrm_install() {
  return _hrbank_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrbank_civicrm_uninstall() {
  $customGroup = civicrm_api3('CustomGroup', 'getsingle', array('return' => "id",'name' => "Bank_Details",));
  civicrm_api3('CustomGroup', 'delete', array('id' => $customGroup['id']));
  return _hrbank_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrbank_civicrm_enable() {
  _hrbank_setActiveFields(1);
  return _hrbank_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrbank_civicrm_disable() {
  _hrbank_setActiveFields(0);
  return _hrbank_civix_civicrm_disable();
}

function _hrbank_setActiveFields($setActive) {
  $sql = "UPDATE civicrm_custom_field JOIN civicrm_custom_group on civicrm_custom_group.id = civicrm_custom_field.custom_group_id SET civicrm_custom_field.is_active = {$setActive} WHERE civicrm_custom_group.name = 'Bank_Details'";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name = 'Bank_Details'");
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
function hrbank_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrbank_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrbank_civicrm_managed(&$entities) {
  return _hrbank_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_tabset
 *
 * @param string $tabsetName
 * @param array $tabs
 * @param mixed $context
 */
function hrbank_civicrm_tabset($tabsetName, &$tabs, $context) {
  if ($tabsetName != 'civicrm/contact/view') {
    return;
  }

  foreach ($tabs as $i => $tab) {
    if ($tab['title'] == 'Bank Details') {
        $tabs[$i]['icon'] = 'crm-i fa-bank';
    }
  }
}
