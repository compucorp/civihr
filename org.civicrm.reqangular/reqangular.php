<?php

require_once 'reqangular.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function reqangular_civicrm_config(&$config) {
  _reqangular_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function reqangular_civicrm_xmlMenu(&$files) {
  _reqangular_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function reqangular_civicrm_install() {
  _reqangular_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function reqangular_civicrm_uninstall() {
  _reqangular_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function reqangular_civicrm_enable() {
  _reqangular_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function reqangular_civicrm_disable() {
  _reqangular_civix_civicrm_disable();
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
function reqangular_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _reqangular_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function reqangular_civicrm_managed(&$entities) {
  _reqangular_civix_civicrm_managed($entities);
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
function reqangular_civicrm_caseTypes(&$caseTypes) {
  _reqangular_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function reqangular_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _reqangular_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_pageRun
 */
function reqangular_civicrm_pageRun($page) {

    $url = CRM_Extension_System::singleton()->getMapper()->keyToUrl('org.civicrm.reqangular');

    CRM_Core_Resources::singleton()->addVars('reqAngular', array(
        'baseUrl' => $url,
        'angular' => $url . '/src/angular/angular.min',
        'angularAnimate' => $url . '/src/angular/angular-animate.min',
        'angularBootstrap' => $url . '/src/angular/ui-bootstrap-tpls',
        'angularFileUpload' => $url . '/src/angular/angular-file-upload',
        'angularResource' => $url . '/src/angular/angular-resource.min',
        'angularRoute' => $url . '/src/angular/angular-route.min',
        'requireLib' => $url . '/src/require',
        'reqangular' => $url . '/reqangular',
    ));

    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.reqangular','reqangular.js', 1000);
}