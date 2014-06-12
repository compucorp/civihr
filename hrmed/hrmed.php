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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'hrmed.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrmed_civicrm_config(&$config) {
  _hrmed_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrmed_civicrm_xmlMenu(&$files) {
  _hrmed_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrmed_civicrm_install() {
  return _hrmed_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrmed_civicrm_uninstall() {
  //delete ufgroup
  if ($ufID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrmed_tab', 'id', 'name')) {
    CRM_Core_BAO_UFGroup::del($ufID);
  }
  //delete customgroup
  if ($cgID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Medical_Disability', 'id', 'name')) {
    civicrm_api3('CustomGroup', 'delete', array('id' => $cgID));
  }
  return _hrmed_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrmed_civicrm_enable() {
  //enable optionGroup and optionValue
  $optionGroups = array();
  foreach (array('type_20130502151940', 'special_requirements_20130502152523') as $medGroupType) {
    if ($medGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', $medGroupType, 'id', 'name')) {
      $optionGroups[] = $medGroupID;
    }
  }
  _hrmed_setActiveOptionFields($optionGroups, 1);
  //enable UFGroup
  if ($ufID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrmed_tab', 'id', 'name')) {
    _hrmed_setUFFields($ufID, 1);
  }
  //enable CustomGroup,CustomFields,UFField
  if ($cusGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Medical_Disability', 'id', 'name')) {
    _hrmed_setActiveCustomFields($cusGroupID, 1);
  }
  return _hrmed_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrmed_civicrm_disable() {
  //disable optionGroup and optionValue
  $optionGroups = array();
  foreach (array('type_20130502151940', 'special_requirements_20130502152523') as $medGroupType){
    if ($medGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', $medGroupType, 'id', 'name')) {
      $optionGroups[] = $medGroupID;
    }
  }
  _hrmed_setActiveOptionFields($optionGroups, 0);
  //disable UFProfile
  if ($ufID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrmed_tab', 'id', 'name')) {
    _hrmed_setUFFields($ufID, 0);
  }
  //disable customGroups,UFFields,customFields
  if ($cusGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Medical_Disability', 'id', 'name')) {
    _hrmed_setActiveCustomFields($cusGroupID, 0);
  }
  return _hrmed_civix_civicrm_disable();
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
function hrmed_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrmed_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrmed_civicrm_managed(&$entities) {
  return _hrmed_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_tabs
 */
function hrmed_civicrm_tabs(&$tabs, $contactID) {
  $cgid = hrmed_getCustomGroupId();
  foreach ($tabs as $k => $v) {
    if ($v['id'] == "custom_{$cgid}") {
      $tabs[$k]['url'] = CRM_Utils_System::url('civicrm/profile/edit', array(
        'reset' => 1,
        'gid' => hrmed_getUFGroupID(),
        'id' => $contactID,
        'snippet' => 1,
        'onPopupClose' => 'redirectToTab',
      ));
    }
  }
}

function hrmed_getCustomGroupId() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
  return array_search('Medical_Disability', $groups);
}

function hrmed_getUFGroupID() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_UFField', 'uf_group_id', array('labelColumn' => 'name'));
  return array_search('hrmed_tab', $groups);
}

/**
 * Implementation of hook_civicrm_buildProfile
 */
function hrmed_civicrm_buildProfile($name) {
  if ($name == 'hrmed_tab') {
    // To fix validation alert issue
    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign('urlIsPublic', FALSE);

    $config = CRM_Core_Config::singleton();
    if ($config->logging && 'multiProfileDialog' !== CRM_Utils_Request::retrieve('context', 'String', CRM_Core_DAO::$_nullObject)) {
      $contactID = CRM_Utils_Request::retrieve('id', 'Positive', $this);
      CRM_Core_Region::instance('profile-form-hrmed_tab')->add(array(
        'template' => 'CRM/common/logButton.tpl',
        'instance_id' => CRM_Report_Utils_Report::getInstanceIDForValue('logging/contact/summary'),
        'css_class' => 'hrmed-revision-link',
        'table_name' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Medical_Disability', 'table_name', 'name'),
        'contact_id' => $contactID,
        'weight' => -2,
      ));
    }
  }
}

/**
 * Implementation of hook_civicrm_pageRun
 */
function hrmed_civicrm_pageRun($page) {
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    CRM_Core_Resources::singleton()
      ->addScriptFile('civicrm', 'js/jquery/jquery.crmRevisionLink.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
    CRM_Core_Resources::singleton()
      ->addScriptFile('org.civicrm.hrmed', 'js/hrmed.js');
    CRM_Core_Resources::singleton()
      ->addStyleFile('org.civicrm.hrmed', 'css/hrmed.css');
  }
}

function _hrmed_setActiveOptionFields($optionGroup, $setActive) {
  if (!empty($optionGroup)) {
    $optionGroups = implode(',', $optionGroup);
    CRM_Core_DAO::executeQuery("UPDATE civicrm_option_value SET is_active = {$setActive} WHERE option_group_id IN ({$optionGroups})");
    CRM_Core_DAO::executeQuery("UPDATE civicrm_option_group SET is_active = {$setActive} WHERE id IN ({$optionGroups})");
  }
}

function _hrmed_setUFFields($ufGroups, $setActive) {
  CRM_Core_DAO::executeQuery("UPDATE civicrm_uf_field SET is_active = {$setActive} WHERE uf_group_id IN ({$ufGroups})");
  CRM_Core_DAO::executeQuery("UPDATE civicrm_uf_group SET is_active = {$setActive} WHERE id IN ($ufGroups)");
}

function _hrmed_setActiveCustomFields($customGroupID, $setActive) {
  $sql = "UPDATE civicrm_custom_field SET is_active = {$setActive} WHERE custom_group_id = {$customGroupID}";
  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_BAO_CustomGroup::setIsActive($customGroupID, $setActive);
}