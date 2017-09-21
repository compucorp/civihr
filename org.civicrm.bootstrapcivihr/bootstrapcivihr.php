<?php
require_once 'bootstrapcivihr.civix.php';
/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function bootstrapcivihr_civicrm_config(&$config) {
  _bootstrapcivihr_civix_civicrm_config($config);
}
/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function bootstrapcivihr_civicrm_xmlMenu(&$files) {
  _bootstrapcivihr_civix_civicrm_xmlMenu($files);
}
/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function bootstrapcivihr_civicrm_install() {
  _bootstrapcivihr_civix_civicrm_install();
}
/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function bootstrapcivihr_civicrm_uninstall() {
  _bootstrapcivihr_civix_civicrm_uninstall();
}
/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function bootstrapcivihr_civicrm_enable() {
  _bootstrapcivihr_civix_civicrm_enable();
}
/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function bootstrapcivihr_civicrm_disable() {
  _bootstrapcivihr_civix_civicrm_disable();
}
/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function bootstrapcivihr_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _bootstrapcivihr_civix_civicrm_upgrade($op, $queue);
}
/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function bootstrapcivihr_civicrm_managed(&$entities) {
  _bootstrapcivihr_civix_civicrm_managed($entities);
}
/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function bootstrapcivihr_civicrm_caseTypes(&$caseTypes) {
  _bootstrapcivihr_civix_civicrm_caseTypes($caseTypes);
}
/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function bootstrapcivihr_civicrm_angularModules(&$angularModules) {
_bootstrapcivihr_civix_civicrm_angularModules($angularModules);
}
/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function bootstrapcivihr_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _bootstrapcivihr_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
/**
 * Implementation of hook_civicrm_pageRun
 */
function bootstrapcivihr_civicrm_pageRun($page) {
  if (!(isset($_GET['snippet']) && $_GET['snippet'] == 'json')) {
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.bootstrapcivihr', 'css/civihr.css');
  }
}
/**
 * Implements hook_civicrm_buildForm().
 */
function bootstrapcivihr_civicrm_buildForm($formName, &$form) {
  CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.bootstrapcivihr', 'css/civihr.css');
}
