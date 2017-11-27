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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'hrvisa.civix.php';

/**
 * Implementation of hook_civicrm_buildProfile
 */
function hrvisa_civicrm_buildProfile($name) {
  if ($name == 'hrvisa_tab') {
    // To fix validation alert issue
    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign('urlIsPublic', FALSE);

    $contactID = CRM_Utils_Request::retrieve('id', 'Positive', $this);

    $config = CRM_Core_Config::singleton();
    if ($config->logging && 'multiProfileDialog' !== CRM_Utils_Request::retrieve('context', 'String', CRM_Core_DAO::$_nullObject)) {
      CRM_Core_Region::instance('profile-form-hrvisa_tab')->add(array(
        'template' => 'CRM/common/logButton.tpl',
        'instance_id' => CRM_Report_Utils_Report::getInstanceIDForValue('logging/contact/summary'),
        'css_class' => 'hrvisa-revision-link',
        'table_name' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Immigration', 'table_name', 'name'),
        'contact_id' => $contactID,
        'weight' => -2,
      ));
    }
  }
}

/**
 * Implementation of hook_civicrm_config
 */
function hrvisa_civicrm_config(&$config) {
  _hrvisa_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrvisa_civicrm_xmlMenu(&$files) {
  _hrvisa_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrvisa_civicrm_install() {
  if (!CRM_HRVisa_Activity::getActivityTypeID('Visa Expiration')) {
    // create activity_type 'Visa Expiration'
    $params = array(
      'weight' => 1,
      'label' => 'Visa Expiration',
      'filter' => 0,
      'is_active' => 1,
      'is_default' => 0,
    );
    $result = civicrm_api3('activity_type', 'create', $params);
    if (CRM_Utils_Array::value('is_error', $result, FALSE)) {
      CRM_Core_Error::debug_var("Failed to create activity type 'Visa  Expiration'", $result);
      throw new CRM_Core_Exception('Failed to create activity type \'Visa  Expiration\'');
    }
    $activityTypeId =  $result['values'][$result['id']]['value'];
  }
  else {
    $activityTypeId = CRM_HRVisa_Activity::getActivityTypeID('Visa Expiration');
  }

  // set weekly reminder for Visa Expiration activities (not active)
  // will be active when extension is enabled
  if (!empty($activityTypeId)) {
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
    $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);

    // schedule reminder for Visa Expiration Creation
    $result = civicrm_api3('action_schedule', 'get', array('name' => 'Visa Expiration Reminder'));
    if (empty($result['id'])) {
      $params = array(
        'name' => 'Visa Expiration Reminder',
        'title' => 'Visa Expiration Reminder',
        'recipient' => $targetID,
        'limit_to' => 1,
        'entity_value' => $activityTypeId,
        'entity_status' => CRM_HRVisa_Activity::getActivityStatusID('Scheduled'),
        'start_action_offset' => 1,
        'start_action_unit' => 'week',
        'start_action_condition' => 'before',
        'start_action_date' => 'activity_date_time',
        'is_repeat' => 0,
        'is_active' => 0,
        'body_html' => '<p>Your latest visa expiries on {activity.activity_date_time}</p>',
        'subject' => 'Reminder for Visa Expiration',
        'record_activity' => 1,
        'mapping_id' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_ActionMapping', 'activity_type', 'id', 'entity_value')
      );
      $result = civicrm_api3('action_schedule', 'create', $params);
    }
  }
  return _hrvisa_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrvisa_civicrm_uninstall() {
  // delete weekly reminder for Visa Expiration activities
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_action_schedule WHERE name = 'Visa Expiration Reminder'");

  //delete ufgroup
  $ufID = civicrm_api3('UFGroup', 'getsingle', array('return' => "id",  'name' => "hrvisa_tab"));
  civicrm_api3('UFGroup', 'delete', array('id' => $ufID['id']));

  //delete customgroup and customfield
  foreach (array('Immigration', 'Immigration_Summary') as $cgName) {
    $customGroup = civicrm_api3('CustomGroup', 'getsingle', array('return' => "id",'name' => $cgName));
    civicrm_api3('CustomGroup', 'delete', array('id' => $customGroup['id']));
  }
  return _hrvisa_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrvisa_civicrm_enable() {
  _hrvisa_setActiveFields(1);
  return _hrvisa_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrvisa_civicrm_disable() {
  _hrvisa_setActiveFields(0);
  return _hrvisa_civix_civicrm_disable();
}

function _hrvisa_setActiveFields($setActive) {
  // disable/enable weekly reminder for Visa Expiration activities
  CRM_Core_DAO::executeQuery("UPDATE civicrm_action_schedule SET is_active = {$setActive} WHERE name = 'Visa Expiration Reminder'");

  //disable/enable customgroup and customvalue
  $sql = "UPDATE civicrm_custom_field
JOIN civicrm_custom_group ON civicrm_custom_group.id = civicrm_custom_field.custom_group_id
SET civicrm_custom_field.is_active = {$setActive}
WHERE civicrm_custom_group.name IN ('Immigration', 'Immigration_Summary')";

  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_custom_group SET is_active = {$setActive} WHERE name IN ('Immigration', 'Immigration_Summary')");

  //disable/enable ufgroup and uffield
  $sql = "UPDATE civicrm_uf_field
JOIN civicrm_uf_group ON civicrm_uf_group.id = civicrm_uf_field.uf_group_id
SET civicrm_uf_field.is_active = {$setActive}
WHERE civicrm_uf_group.name = 'hrvisa_tab'";

  CRM_Core_DAO::executeQuery($sql);
  CRM_Core_DAO::executeQuery("UPDATE civicrm_uf_group SET is_active = {$setActive} WHERE name = 'hrvisa_tab'");
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
function hrvisa_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrvisa_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrvisa_civicrm_managed(&$entities) {
  return _hrvisa_civix_civicrm_managed($entities);
}


/**
 * Implementation of hook_civicrm_tabset.
 *
 * @param string $tabsetName
 * @param array &$tabs
 * @param array $context
 */
function hrvisa_civicrm_tabset($tabsetName, &$tabs, $context) {
  if ($tabsetName === 'civicrm/contact/view') {
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrvisa', 'js/hrvisa.js');
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.hrvisa', 'css/hrvisa.css');
    CRM_Core_Resources::singleton()->addSetting(array(
      'hrvisa' => array(
        'contactID' => $context['contact_id'],
      ),
    ));
  }
}

function hrvisa_getCustomGroupId() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
  return array_search('Immigration', $groups);
}

function hrvisa_getUFGroupID() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_UFField', 'uf_group_id', array('labelColumn' => 'name'));
  return array_search('hrvisa_tab', $groups);
}

/**
 * Implementation of hook_civicrm_pageRun
 */
function hrvisa_civicrm_pageRun($page) {
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    CRM_Core_Resources::singleton()
      ->addScriptFile('civicrm', 'js/jquery/jquery.crmRevisionLink.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header');
  }
}

/**
 * Implementation of hook_civicrm_custom
 */
function hrvisa_civicrm_custom($op, $groupID, $entityID, &$params) {
  if ($op != 'create' && $op != 'edit') {
    return;
  }

  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
  $groupName = CRM_Utils_Array::value($groupID, $groups);
  if ($groupName == 'Immigration' || $groupName == 'Extended_Demographics') {
    CRM_HRVisa_Activity::sync($entityID);
  }
}
