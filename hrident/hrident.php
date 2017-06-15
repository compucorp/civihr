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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'hrident.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrident_civicrm_config(&$config) {
  _hrident_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrident_civicrm_xmlMenu(&$files) {
  _hrident_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrident_civicrm_install() {
  return _hrident_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrident_civicrm_uninstall() {
  //delete ufgroup
  $ufID = civicrm_api3('UFGroup', 'getsingle', array('return' => "id",  'name' => "hrident_tab"));
  civicrm_api3('UFGroup', 'delete', array('id' => $ufID['id']));

  //delete customgroup
  $customGroup = civicrm_api3('CustomGroup', 'getsingle', array('return' => "id",'name' => "Identify",));
  civicrm_api3('CustomGroup', 'delete', array('id' => $customGroup['id']));
  return _hrident_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrident_civicrm_enable() {
  _hrident_setActiveFields(1);
  return _hrident_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrident_civicrm_disable() {
  _hrident_setActiveFields(0);
  return _hrident_civix_civicrm_disable();
}

function _hrident_setActiveFields($setActive) {
  //disable/enable customgroup and customvalue
  $sql = "UPDATE civicrm_custom_field JOIN civicrm_custom_group ON civicrm_custom_group.id = civicrm_custom_field.custom_group_id SET civicrm_custom_field.is_active = {$setActive} WHERE civicrm_custom_group.name = 'Identify'";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name = 'Identify'");

  //disable/enable optionGroup and optionValue
  $query = "UPDATE civicrm_option_value JOIN civicrm_option_group ON civicrm_option_group.id = civicrm_option_value.option_group_id SET civicrm_option_value.is_active = {$setActive} WHERE civicrm_option_group.name = 'type_20130502144049'";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$setActive} WHERE name = 'type_20130502144049'");

  //disable/enable ufgroup and uffield
  $sql = "UPDATE civicrm_uf_field JOIN civicrm_uf_group ON civicrm_uf_group.id = civicrm_uf_field.uf_group_id SET civicrm_uf_field.is_active = {$setActive} WHERE civicrm_uf_group.name = 'hrident_tab'";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_uf_group SET is_active = {$setActive} WHERE name = 'hrident_tab'");
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
function hrident_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrident_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrident_civicrm_managed(&$entities) {
  return _hrident_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_tabset.
 *
 * @param string $tabsetName
 * @param array &$tabs
 * @param array $context
 */
function hrident_civicrm_tabset($tabsetName, &$tabs, $context) {
  if ($tabsetName === 'civicrm/contact/view') {
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.hrident', 'css/hrident.css');
  }
}

function hrident_getCustomGroupId() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
  return array_search('Identify', $groups);
}

function hrident_getUFGroupID() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_UFField', 'uf_group_id', array('labelColumn' => 'name'));
  return array_search('hrident_tab', $groups);
}

/**
 * Implementation of hook_civicrm_buildProfile
 */
function hrident_civicrm_buildProfile($name) {
  if ($name == 'hrident_tab') {
    // To fix validation alert issue
    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign('urlIsPublic', FALSE);

    $config = CRM_Core_Config::singleton();
    if ($config->logging && 'multiProfileDialog' !== CRM_Utils_Request::retrieve('context', 'String', CRM_Core_DAO::$_nullObject)) {
      $contactID = CRM_Utils_Request::retrieve('id', 'Positive', $this);
      CRM_Core_Region::instance('profile-form-hrident_tab')->add(array(
        'template' => 'CRM/common/logButton.tpl',
        'instance_id' => CRM_Report_Utils_Report::getInstanceIDForValue('logging/contact/summary'),
        'css_class' => 'hrident-revision-link',
        'table_name' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Identify', 'table_name', 'name'),
        'contact_id' => $contactID,
        'weight' => -2,
      ));
    }
  }
}

/**
 * Implementation of hook_civicrm_pageRun
 */
function hrident_civicrm_pageRun($page) {
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    CRM_Core_Resources::singleton()
      ->addScriptFile('civicrm', 'js/jquery/jquery.crmRevisionLink.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
  }
}
