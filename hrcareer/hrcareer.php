<?php

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
  return _hrcareer_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrcareer_civicrm_enable() {
  return _hrcareer_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrcareer_civicrm_disable() {
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
  CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.hrcareer', 'css/hrcareer.css');
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
    $contactID  = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    CRM_Core_Region::instance('profile-form-hrcareer_tab')->add(array(
        'template'    => 'CRM/common/logButton.tpl',
        'instance_id' => CRM_Report_Utils_Report::getInstanceIDForValue('logging/contact/summary'),
        'css_class'   => 'hrcareer-revision-link',
        'table_name'  => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Career', 'table_name', 'name'),
        'contact_id'  => $contactID,
        'weight'      => -2,
      ));
  }
}
