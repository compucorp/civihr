<?php

require_once 'hrcaseutils.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrcaseutils_civicrm_config(&$config) {
  _hrcaseutils_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrcaseutils_civicrm_xmlMenu(&$files) {
  _hrcaseutils_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrcaseutils_civicrm_install() {
  return _hrcaseutils_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrcaseutils_civicrm_uninstall() {
  //delete all activity type
  foreach (array('Interview Prospect', 'Background Check', 'ID badge') as $opValName) {
    if ($id = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionValue', $opValName, 'id', 'name')) {
      CRM_Core_BAO_OptionValue::del($id);
    }
  }
  return _hrcaseutils_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrcaseutils_civicrm_enable() {
  // enable activity type
  $sql = "UPDATE civicrm_option_value SET is_active=1 WHERE name IN ('Interview Prospect', 'Background Check', 'ID badge')";
  CRM_Core_DAO::executeQuery($sql);
  return _hrcaseutils_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrcaseutils_civicrm_disable() {
  // disable activity type
  $sql = "UPDATE civicrm_option_value SET is_active=0 WHERE name IN ('Interview Prospect', 'Background Check', 'ID badge')";
  CRM_Core_DAO::executeQuery($sql);
  return _hrcaseutils_civix_civicrm_disable();
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
function hrcaseutils_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrcaseutils_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrcaseutils_civicrm_managed(&$entities) {
  return _hrcaseutils_civix_civicrm_managed($entities);
}

function hrcaseutils_civicrm_caseTypes(&$caseTypes) {
  _hrcaseutils_civix_civicrm_caseTypes($caseTypes);
}

function hrcaseutils_civicrm_post($op, $objectName, $objectId, $objectRef) {
  if ($objectName == 'Activity' && isset($objectRef->case_id)) {
    $analyzer = new CRM_HRCaseUtils_Analyzer($objectRef->case_id, $objectRef->id);
    $listenerClasses = 'CRM_HRCaseUtils_Listener_Pipeline';
    $listener = new $listenerClasses();
    $listener->onChange($analyzer, $objectRef);
  }
}