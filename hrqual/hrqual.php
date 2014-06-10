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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'hrqual.civix.php';

/**
 * Implementation of hook_civicrm_buildProfile
 */
function hrqual_civicrm_buildProfile($name) {
  if ($name == 'hrqual_tab') {
    // To fix validation alert issue
    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign('urlIsPublic', FALSE);

    $action = CRM_Utils_Request::retrieve('multiRecord', 'String', $this);
    // display the select box only in add and update mode
    if (in_array($action, array("add", "update"))) {
      $regionParams = array(
        'markup' => "<select id='category_name' name='category_name' style='display:none' class='form-select required'></select>",
      );
      CRM_Core_Region::instance('profile-form-hrqual_tab')->add($regionParams);
    }

    $config = CRM_Core_Config::singleton();
    if ($config->logging && 'multiProfileDialog' !== CRM_Utils_Request::retrieve('context', 'String', CRM_Core_DAO::$_nullObject)) {
      $contactID = CRM_Utils_Request::retrieve('id', 'Positive', $this);
      CRM_Core_Region::instance('profile-form-hrqual_tab')->add(array(
        'template' => 'CRM/common/logButton.tpl',
        'instance_id' => CRM_Report_Utils_Report::getInstanceIDForValue('logging/contact/summary'),
        'css_class' => 'hrqual-revision-link',
        'table_name' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Qualifications', 'table_name', 'name'),
        'contact_id' => $contactID,
        'weight' => -2,
      ));
    }
  }
}

/**
 * Implementation of hook_civicrm_config
 */
function hrqual_civicrm_config(&$config) {
  _hrqual_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrqual_civicrm_xmlMenu(&$files) {
  _hrqual_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrqual_civicrm_install() {
  return _hrqual_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrqual_civicrm_uninstall() {
  //Uninstall OptionGroup and OptionValue
  foreach (array('Language', 'Computing', 'Finance', 'Management', 'Legal') as $qualGroupType) {
    if($qualGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', $qualGroupType, 'id', 'name')){
      CRM_Core_BAO_OptionGroup::del($qualGroupID);
    }
  }
  //Uninstall CustomGroup and CustomField
  if($customGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Qualifications', 'id', 'name')){
    civicrm_api3('CustomGroup', 'delete', array('id' => $customGroupID));
  }
  //Uninstall UFGroup and UFField
  if ($ufID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrqual_tab', 'id', 'name')) {
    CRM_Core_BAO_UFGroup::del($ufID);
  }
  return _hrqual_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrqual_civicrm_enable() {
  //Enable CustomGroup, CustomFields and UFFields
  if ($customGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Qualifications', 'id', 'name')) {
    CRM_Core_BAO_CustomGroup::setIsActive($customGroupID, 1);
    $customFields = civicrm_api3('CustomField', 'get', array('custom_group_id' => $customGroupID));
    foreach ($customFields['values'] as $key => $val) {
      CRM_Core_DAO::setFieldValue('CRM_Core_DAO_CustomField', $key, 'is_active', 1);
      CRM_Core_BAO_UFField::setUFField($key, 1);
    }
  }
  //Enable UFGroup
  if ($ufID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrqual_tab', 'id', 'name')) {
    CRM_Core_BAO_UFGroup::setIsActive($ufID, 1);
  }
  //Enable OptionGroup and OptionValues
  foreach (array('category_of_skill_20130510015438', 'level_of_skill_20130510015934', 'Language', 'Computing', 'Finance', 'Management', 'Legal'  ) as $qualGroupType) {
    if ($qualGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', $qualGroupType, 'id', 'name')) {
      CRM_Core_BAO_OptionGroup::setIsActive($qualGroupID, 1);
      $qualValueIDs = civicrm_api3('OptionValue', 'get', array('option_group_id' => $qualGroupID,));
      foreach ($qualValueIDs['values'] as $qualValueID => $val) {
        CRM_Core_BAO_OptionValue::setIsActive($qualValueID, 1);
      }
    }
  }
  return _hrqual_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrqual_civicrm_disable() {
  //Disable CustomGroup, CustomFields and UFFields
  if ($customGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Qualifications', 'id', 'name')) {
    $customFields = civicrm_api3('CustomField', 'get', array('custom_group_id' => $customGroupID));
    foreach ($customFields['values'] as $key => $val) {
      CRM_Core_BAO_CustomField::setIsActive($key, 0);
    }
    CRM_Core_BAO_CustomGroup::setIsActive($customGroupID, 0);
  }
  //Disable UFGroup
  if ($ufID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrqual_tab', 'id', 'name')) {
    CRM_Core_BAO_UFGroup::setIsActive($ufID, 0);
  }
  //Disable OptionGroup and OptionValue
  foreach (array('category_of_skill_20130510015438', 'level_of_skill_20130510015934', 'Language', 'Computing', 'Finance', 'Management', 'Legal'  ) as $qualGroupType) {
    if ($qualGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', $qualGroupType, 'id', 'name')) {
      $qualValueIDs = civicrm_api3('OptionValue', 'get', array('option_group_id' => $qualGroupID,));
      foreach ($qualValueIDs['values'] as $qualValueID => $val) {
        CRM_Core_BAO_OptionValue::setIsActive($qualValueID, 0);
      }
      CRM_Core_BAO_OptionGroup::setIsActive($qualGroupID, 0);
    }
  }
  return _hrqual_civix_civicrm_disable();
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
function hrqual_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrqual_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrqual_civicrm_managed(&$entities) {
  return _hrqual_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_tabs
 */
function hrqual_civicrm_tabs(&$tabs, $contactID) {
  $cgid = hrqual_getCustomGroupId();
  foreach ($tabs as $k => $v) {
    if ($v['id'] == "custom_{$cgid}") {
      $tabs[$k]['url'] = CRM_Utils_System::url('civicrm/profile/edit', array(
        'reset' => 1,
        'gid' => hrqual_getUFGroupID(),
        'id' => $contactID,
        'snippet' => 1,
        'onPopupClose' => 'redirectToTab',
      ));
    }
  }
  CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.hrqual', 'css/hrqual.css');
  CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrqual', 'js/hrqual.js');

  $optionGroups = CRM_Core_OptionGroup::values('category_of_skill_20130510015438');
  foreach ($optionGroups as $name => $optionGroup) {
    $options = array_values(CRM_Core_OptionGroup::values($name));
    $val[$optionGroup] = $options;
    unset($options);
  }

  $cfId1 = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', 'Name_of_Skill', 'id', 'name');
  $cfId2 = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', 'Category_of_Skill', 'id', 'name');
  CRM_Core_Resources::singleton()->addSetting(array(
    'hrqual' => array(
      'name' => $cfId1,
      'category' => $cfId2,
      'optionGroups' => $val,
    ),
  ));
}

function hrqual_getCustomGroupId() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
  return array_search('Qualifications', $groups);
}

function hrqual_getUFGroupID() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_UFField', 'uf_group_id', array('labelColumn' => 'name'));
  return array_search('hrqual_tab', $groups);
}

/**
 * Implementation of hook_civicrm_pageRun
 */
function hrqual_civicrm_pageRun($page) {
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    CRM_Core_Resources::singleton()
      ->addScriptFile('civicrm', 'js/jquery/jquery.crmRevisionLink.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
  }
}
