<?php

require_once 'hrim.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrim_civicrm_config(&$config) {
  _hrim_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrim_civicrm_xmlMenu(&$files) {
  _hrim_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrim_civicrm_install() {
  return _hrim_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrim_civicrm_uninstall() {
  return _hrim_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrim_civicrm_enable() {
  return _hrim_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrim_civicrm_disable() {
  return _hrim_civix_civicrm_disable();
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
function hrim_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrim_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrim_civicrm_managed(&$entities) {
  return _hrim_civix_civicrm_managed($entities);
}
function hrim_civicrm_pageRun($page) {
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrim', 'js/dist/hrim.min.js', 1010);

  }
}
