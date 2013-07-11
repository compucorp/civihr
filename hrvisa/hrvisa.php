<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'hrvisa.civix.php';

/**
 * Implementation of hook_civicrm_buildProfile
 */
function hrvisa_civicrm_buildProfile($name) {
  if ($name == 'hrvisa_tab') {
    $contactID = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    $cfId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', 'Is_Visa_Required', 'id', 'name');
    $params = array(
      'entityID' => $contactID,
      "custom_{$cfId}" => 1
    );
    $value = CRM_Core_BAO_CustomValueTable::getValues($params);
    $regionParams = array(
      'markup' => "<p id='custom_{$cfId}_is_visa_required' class='hrvisa-is_visa_required'>
        Is Visa Required &nbsp;<input type='checkbox' id='is_visa_required' value='1' name='is_visa_required'></p>",
      'weight' => -1
    );

    //check if the value is set. If it is, then add attribute checked='checked'
    if ($value["custom_{$cfId}"]) {
      $regionParams['markup'] = "<p id='custom_{$cfId}_is_visa_required' class='hrvisa-is_visa_required'>Is Visa Required &nbsp;
        <input type='checkbox' id='is_visa_required' value='1' name='is_visa_required' checked='checked'></p>";
    }
    CRM_Core_Region::instance('profile-form-hrvisa_tab')->add($regionParams);
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
  return _hrvisa_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrvisa_civicrm_uninstall() {
  return _hrvisa_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrvisa_civicrm_enable() {
  return _hrvisa_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrvisa_civicrm_disable() {
  return _hrvisa_civix_civicrm_disable();
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
 * Implementation of hook_civicrm_tabs
 */
function hrvisa_civicrm_tabs(&$tabs, $contactID) {
  $cgid = hrvisa_getCustomGroupId();
  foreach ($tabs as $k => $v) {
    if ($v['id'] == "custom_{$cgid}") {
      $tabs[$k]['url'] = CRM_Utils_System::url('civicrm/profile/edit', array(
        'reset' => 1,
        'gid' => hrvisa_getUFGroupID(),
        'id' => $contactID,
        'snippet' => 1,
        'onPopupClose' => 'redirectToTab',
      ));
    }
  }
  CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrvisa', 'js/hrvisa.js');
  CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.hrvisa', 'css/hrvisa.css');
  CRM_Core_Resources::singleton()->addSetting(array('contactID' => $contactID));
}

function hrvisa_getCustomGroupId() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
  return array_search('Immigration', $groups);
}

function hrvisa_getUFGroupID() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_UFField', 'uf_group_id', array('labelColumn' => 'name'));
  return array_search('hrvisa_tab', $groups);
}
