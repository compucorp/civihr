<?php

require_once 'hrjobroles.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function hrjobroles_civicrm_config(&$config) {
  _hrjobroles_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function hrjobroles_civicrm_xmlMenu(&$files) {
  _hrjobroles_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function hrjobroles_civicrm_install() {
  _hrjobroles_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function hrjobroles_civicrm_uninstall() {
  _hrjobroles_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function hrjobroles_civicrm_enable() {
  _hrjobroles_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function hrjobroles_civicrm_disable() {
  _hrjobroles_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function hrjobroles_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrjobroles_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function hrjobroles_civicrm_managed(&$entities) {
  _hrjobroles_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function hrjobroles_civicrm_caseTypes(&$caseTypes) {
  _hrjobroles_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function hrjobroles_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _hrjobroles_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * @param $tabs
 * @param $contactID
 * Create a custom tab for civicrm contact which will implement custom drupal callback function
 */
function hrjobroles_civicrm_tabs(&$tabs, $contactID) {

    $url = CRM_Utils_System::url('civicrm/job-roles/' . $contactID);
    $tabs[] = array( 'id' => 'hrjobroles',
        'url' => $url,
        'title' => 'Job Roles',
        'weight' => 300 );
}

/**
 * Implementation of hook_civicrm_pageRun
 */
function hrjobroles_civicrm_pageRun($page) {
    if ($page instanceof CRM_Contact_Page_View_Summary) {

        // Returns the fully qualified URL for our extension
        CRM_Core_Resources::singleton()->addVars('hrjobroles', array(
            'baseURL' => CRM_Extension_System::singleton()->getMapper()->keyToUrl('com.civicrm.hrjobroles')
        ));

        CRM_Core_Resources::singleton()->addScriptFile('com.civicrm.hrjobroles', CRM_Core_Config::singleton()->debug ? 'js/hrjobroles-main.js' : 'dist/hrjobroles-main.js',1010);

        CRM_Core_Resources::singleton()
            ->addStyleFile('com.civicrm.hrjobroles', 'css/hrjobroles.css');

        // Add angular xeditable css library
        CRM_Core_Resources::singleton()
            ->addStyleFile('com.civicrm.hrjobroles', 'angular-xeditable/css/xeditable.css');
    }
}

/**
 * Implementation of hook_civicrm_entityTypes
 */
function hrjobroles_civicrm_entityTypes(&$entityTypes) {

    $entityTypes[] = array (
        'name' => 'HrJobRoles',
        'class' => 'CRM_Hrjobroles_DAO_HrJobRoles',
        'table' => 'civicrm_hrjobroles',
    );

}