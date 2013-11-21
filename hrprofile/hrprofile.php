<?php

require_once 'hrprofile.civix.php';

/**
 * Implementation of hook_civicrm_pageRun
 */
function hrprofile_civicrm_pageRun($page) {
  if ($page instanceof CRM_HRProfile_Page_HRProfile) {
  CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.hrprofile', 'css/hrprofile.css');
  }
}
/**
 * Implementation of hook_civicrm_config
 */
function hrprofile_civicrm_config(&$config) {
  _hrprofile_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrprofile_civicrm_xmlMenu(&$files) {
  _hrprofile_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrprofile_civicrm_install() {
  return _hrprofile_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrprofile_civicrm_uninstall() {
  return _hrprofile_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrprofile_civicrm_enable() {
  return _hrprofile_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrprofile_civicrm_disable() {
  return _hrprofile_civix_civicrm_disable();
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
function hrprofile_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrprofile_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrprofile_civicrm_managed(&$entities) {
  return _hrprofile_civix_civicrm_managed($entities);
}
