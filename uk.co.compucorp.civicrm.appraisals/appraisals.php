<?php

require_once 'appraisals.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function appraisals_civicrm_config(&$config) {
  _appraisals_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function appraisals_civicrm_xmlMenu(&$files) {
  _appraisals_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function appraisals_civicrm_install() {
  _appraisals_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function appraisals_civicrm_uninstall() {
  _appraisals_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function appraisals_civicrm_enable() {
  _appraisals_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function appraisals_civicrm_disable() {
  _appraisals_civix_civicrm_disable();
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
function appraisals_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _appraisals_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function appraisals_civicrm_managed(&$entities) {
  _appraisals_civix_civicrm_managed($entities);
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
function appraisals_civicrm_caseTypes(&$caseTypes) {
  _appraisals_civix_civicrm_caseTypes($caseTypes);
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
function appraisals_civicrm_angularModules(&$angularModules) {
_appraisals_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function appraisals_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _appraisals_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_entityTypes
 */
function appraisals_civicrm_entityTypes(&$entityTypes) {
    $entityTypes[] = array(
        'name' => 'AppraisalCycle',
        'class' => 'CRM_Appraisals_DAO_AppraisalCycle',
        'table' => 'civicrm_appraisal_cycle',
    );
    $entityTypes[] = array(
        'name' => 'Appraisal',
        'class' => 'CRM_Appraisals_DAO_Appraisal',
        'table' => 'civicrm_appraisal',
    );
    $entityTypes[] = array(
        'name' => 'AppraisalCriteria',
        'class' => 'CRM_Appraisals_DAO_AppraisalCriteria',
        'table' => 'civicrm_appraisal_criteria',
    );
}

/**
 * Implementation of hook_civicrm_tabs
 */

function appraisals_civicrm_tabs(&$tabs) {
    CRM_Appraisals_Page_Appraisals::registerScripts();
    
    $tabs[] = Array(
        'id'        => 'appraisals',
        'url'       => CRM_Utils_System::url('civicrm/contact/view/appraisals'),
        'title'     => ts('Appraisals'),
        'weight'    => 1,
    );
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function appraisals_civicrm_preProcess($formName, &$form) {

}

*/
