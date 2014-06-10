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
  $customField = civicrm_api3('CustomField', 'get', array('custom_group_id' => $customGroup['id']));
  foreach ($customField['values'] as $key) {
    civicrm_api3('CustomField', 'delete', array('id' => $key['id']));
  }
  civicrm_api3('CustomGroup', 'delete', array('id' => $customGroup['id']));

  if ($ufID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrcareer_tab', 'id', 'name')) {
    CRM_Core_BAO_UFGroup::del($ufID);
  }
  //delete all option group and values
  foreach (array('occupation_type_20130617111138', 'full_time_part_time_20130617111405', 'paid_unpaid_20130617111520') as $careerOptionType) {
    if ($careerGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', $careerOptionType, 'id', 'name')) {
      CRM_Core_BAO_OptionGroup::del($careerGroupID);
    }
  }
  return _hrcareer_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrcareer_civicrm_enable() {
  //enable all option groups
  foreach (array('occupation_type_20130617111138', 'full_time_part_time_20130617111405', 'paid_unpaid_20130617111520') as $careerOptionType) {
    if ($careerGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', $careerOptionType, 'id', 'name')) {
      CRM_Core_BAO_OptionGroup::setIsActive($careerGroupID, 1);
      $careerValueIDs = civicrm_api3('OptionValue', 'get', array('option_group_id' => $careerGroupID,));
      foreach ($careerValueIDs['values'] as $careerValueID => $val) {
        CRM_Core_BAO_OptionValue::setIsActive($careerValueID, 1);
      }
    }
  }
  //enable all custom group and fields
  if ($customGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Career', 'id', 'name')) {
    $customFields = civicrm_api3('CustomField', 'get', array('custom_group_id' => $customGroupID));
    foreach ($customFields['values'] as $key => $val) {
      CRM_Core_DAO::setFieldValue('CRM_Core_DAO_CustomField', $key, 'is_active', 1);
      CRM_Core_BAO_UFField::setUFField($key, 1);
    }
    CRM_Core_BAO_CustomGroup::setIsActive($customGroupID, 1);
  }
  //enable all ufgroup
  if ($ufID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrcareer_tab', 'id', 'name')) {
    CRM_Core_BAO_UFGroup::setIsActive($ufID, 1);
    $ufField = civicrm_api3('UFField', 'get', array('uf_group_id' => $ufID,));
    foreach ($ufField['values'] as $key) {
      CRM_Core_BAO_UFField::setIsActive($key['id'], 1);
    }
  }
  return _hrcareer_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrcareer_civicrm_disable() {
  //disable all custom group and fields
  if ($customGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Career', 'id', 'name')) {
    $customFields = civicrm_api3('CustomField', 'get', array('custom_group_id' => $customGroupID));
    foreach ($customFields['values'] as $key => $val) {
      CRM_Core_BAO_CustomField::setIsActive($key, 0);
    }
    CRM_Core_BAO_CustomGroup::setIsActive($customGroupID, 0);
  }

  //disable all ufgroup
  if ($ufID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'hrcareer_tab', 'id', 'name')) {
    CRM_Core_BAO_UFGroup::setIsActive($ufID, 0);
  }

  //disable all option groups
  foreach (array('occupation_type_20130617111138', 'full_time_part_time_20130617111405', 'paid_unpaid_20130617111520') as $careerOptionType) {
    if ($careerGroupID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', $careerOptionType, 'id', 'name')) {
      $careerValueIDs = civicrm_api3('OptionValue', 'get', array('option_group_id' => $careerGroupID,));
      foreach ($careerValueIDs['values'] as $careerValueID => $val) {
        CRM_Core_BAO_OptionValue::setIsActive($careerValueID, 0);
      }
      CRM_Core_BAO_OptionGroup::setIsActive($careerGroupID, 0);
    }
  }
  return _hrcareer_civix_civicrm_disable();
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


/**
 * Implementation of hook_civicrm_tabs
 */
function hrcareer_civicrm_tabs(&$tabs, $contactID) {
  $cgid = hrcareer_getCustomGroupId();
  foreach ($tabs as $k => $v) {
    if ($v['id'] == "custom_{$cgid}") {
      $tabs[$k]['url'] = CRM_Utils_System::url('civicrm/profile/edit', array(
        'reset' => 1,
        'gid' => hrcareer_getUFGroupID(),
        'id' => $contactID,
        'snippet' => 1,
        'onPopupClose' => 'redirectToTab',
      ));
    }
  }
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

    CRM_Core_Resources::singleton()
      ->addStyleFile('org.civicrm.hrcareer', 'css/hrcareer.css');
  }
}
