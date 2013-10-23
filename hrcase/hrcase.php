<?php

require_once 'hrcase.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrcase_civicrm_config(&$config) {
  _hrcase_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrcase_civicrm_xmlMenu(&$files) {
  _hrcase_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrcase_civicrm_install() {
  return _hrcase_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrcase_civicrm_uninstall() {
  return _hrcase_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrcase_civicrm_enable() {
  return _hrcase_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrcase_civicrm_disable() {
  return _hrcase_civix_civicrm_disable();
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
function hrcase_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrcase_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrcase_civicrm_managed(&$entities) {
  return _hrcase_civix_civicrm_managed($entities);
}

function hrcase_civicrm_buildForm($formName, &$form) {
  if ($form instanceof CRM_Case_Form_Activity OR $form instanceof CRM_Case_Form_Case) {
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.hrcase', 'js/hrcase.js');
  }
}