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

require_once 'hrcareer.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrcareer_civicrm_config(&$config) {
  _hrcareer_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrcareer_civicrm_xmlMenu(&$files) {
  _hrcareer_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrcareer_civicrm_install() {
  return _hrcareer_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrcareer_civicrm_uninstall() {
  //delete custom groups and field
  $customGroup = civicrm_api3('CustomGroup', 'getsingle', array('return' => "id",'name' => "Career",));
  civicrm_api3('CustomGroup', 'delete', array('id' => $customGroup['id']));

  $ufID = civicrm_api3('UFGroup', 'getsingle', array('return' => "id", 'name' => "hrcareer_tab",));
  civicrm_api3('UFGroup', 'delete', array('id' => $ufID['id']));

  //delete all option group and values
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_option_group WHERE name IN ('occupation_type_20130617111138', 'full_time_part_time_20130617111405', 'paid_unpaid_20130617111520')");

  return _hrcareer_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrcareer_civicrm_enable() {
  _hrcareer_setActiveFields(1);
  return _hrcareer_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrcareer_civicrm_disable() {
  _hrcareer_setActiveFields(0);
  return _hrcareer_civix_civicrm_disable();
}

function _hrcareer_setActiveFields($setActive) {
  //disable all custom group and fields
  $sql = "UPDATE civicrm_custom_field
JOIN civicrm_custom_group ON civicrm_custom_group.id = civicrm_custom_field.custom_group_id
SET civicrm_custom_field.is_active = {$setActive}
WHERE civicrm_custom_group.name IN ('Career')";

  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name IN ('Career')");

  //disable optionGroup and optionValue
  $query = "UPDATE civicrm_option_value
JOIN civicrm_option_group ON civicrm_option_group.id = civicrm_option_value.option_group_id
SET civicrm_option_value.is_active = {$setActive}
WHERE civicrm_option_group.name IN ('occupation_type_20130617111138', 'full_time_part_time_20130617111405', 'paid_unpaid_20130617111520')";

  CRM_Core_DAO::executeQuery($query);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$setActive} WHERE name IN ('occupation_type_20130617111138', 'full_time_part_time_20130617111405', 'paid_unpaid_20130617111520')");

  $uffield = "UPDATE civicrm_uf_field JOIN civicrm_uf_group
ON civicrm_uf_group.id = civicrm_uf_field.uf_group_id
SET civicrm_uf_field.is_active = {$setActive}
WHERE civicrm_uf_group.name = 'hrcareer_tab'";

  CRM_Core_DAO::executeQuery($uffield);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_uf_group SET is_active = {$setActive} WHERE name = 'hrcareer_tab'");
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
function hrcareer_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrcareer_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrcareer_civicrm_managed(&$entities) {
  return _hrcareer_civix_civicrm_managed($entities);
}

function hrcareer_getCustomGroupId() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
  return array_search('Career', $groups);
}

function hrcareer_getUFGroupID() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_UFField', 'uf_group_id', array('labelColumn' => 'name'));
  return array_search('hrcareer_tab', $groups);
}

/**
 * Implementation of hook_civicrm_buildProfile
 */
function hrcareer_civicrm_buildProfile($name) {
  if ($name == 'hrcareer_tab') {
    $isDialog = ('multiProfileDialog' == CRM_Utils_Request::retrieve('context', 'String', CRM_Core_DAO::$_nullObject));

    // To fix validation alert issue
    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign('urlIsPublic', FALSE);

    if (! $isDialog) {
      CRM_Core_Region::instance('profile-form-hrcareer_tab')->add(array(
        'weight' => -10,
        'markup' =>
          '<div class="help">'
          . ts('Like a CV or resume, Career History records the work and study that a person has undertaken before joining the organization.')
          . '</div>'
      ));
    }

    $config = CRM_Core_Config::singleton();
    if ($config->logging && ! $isDialog) {
      $contactID = CRM_Utils_Request::retrieve('id', 'Positive', $this);
      CRM_Core_Region::instance('profile-form-hrcareer_tab')->add(array(
        'template' => 'CRM/common/logButton.tpl',
        'instance_id' => CRM_Report_Utils_Report::getInstanceIDForValue('logging/contact/summary'),
        'css_class' => 'hrcareer-revision-link',
        'table_name' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Career', 'table_name', 'name'),
        'contact_id' => $contactID,
        'weight' => -2,
      ));
    }
  }
}

/**
 * Implementation of hook_civicrm_pageRun
 */
function hrcareer_civicrm_pageRun($page) {
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    CRM_Core_Resources::singleton()
      ->addScriptFile('civicrm', 'js/jquery/jquery.crmRevisionLink.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');

    CRM_Core_Resources::singleton()
      ->addScriptFile('org.civicrm.hrcareer', 'js/hrcareer.js');
  }
}
