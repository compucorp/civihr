<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'hrqual.civix.php';

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
  return _hrqual_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrqual_civicrm_enable() {
  return _hrqual_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrqual_civicrm_disable() {
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
      ));
    }
  }
}

function hrqual_getCustomGroupId() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_group_id', array('labelColumn' => 'name'));
  return array_search('Qualifications', $groups);
}

function hrqual_getUFGroupID() {
  $groups = CRM_Core_PseudoConstant::get('CRM_Core_BAO_UFField', 'uf_group_id', array('labelColumn' => 'name'));
  return array_search('hrqual_tab', $groups);
}
