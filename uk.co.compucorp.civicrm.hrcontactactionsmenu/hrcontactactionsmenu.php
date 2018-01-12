<?php

require_once 'hrcontactactionsmenu.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function hrcontactactionsmenu_civicrm_config(&$config) {
  _hrcontactactionsmenu_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function hrcontactactionsmenu_civicrm_xmlMenu(&$files) {
  _hrcontactactionsmenu_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function hrcontactactionsmenu_civicrm_install() {
  _hrcontactactionsmenu_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function hrcontactactionsmenu_civicrm_postInstall() {
  _hrcontactactionsmenu_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function hrcontactactionsmenu_civicrm_uninstall() {
  _hrcontactactionsmenu_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function hrcontactactionsmenu_civicrm_enable() {
  _hrcontactactionsmenu_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function hrcontactactionsmenu_civicrm_disable() {
  _hrcontactactionsmenu_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function hrcontactactionsmenu_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrcontactactionsmenu_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function hrcontactactionsmenu_civicrm_managed(&$entities) {
  _hrcontactactionsmenu_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function hrcontactactionsmenu_civicrm_caseTypes(&$caseTypes) {
  _hrcontactactionsmenu_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function hrcontactactionsmenu_civicrm_angularModules(&$angularModules) {
  _hrcontactactionsmenu_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function hrcontactactionsmenu_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _hrcontactactionsmenu_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_pageRun
 */
function hrcontactactionsmenu_civicrm_pageRun(&$page) {
  CRM_Core_Region::instance('contact-page-inline-actions')->update('default', array(
    'disabled' => TRUE,
  ));
  $template_path = CRM_Core_Resources::singleton()->getPath('uk.co.compucorp.civicrm.hrcontactactionsmenu','templates/CRM/ContactActionsMenu/Page/Inline/ActionsPart2.tpl');
  
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    CRM_Core_Region::instance('contact-page-inline-actions')->add(array(
      'template' => $template_path,
    ));
  }
  
  $extName = 'uk.co.compucorp.civicrm.hrcontactactionsmenu';
  CRM_Core_Resources::singleton()->addStyleFile($extName, 'css/contactactions.css');
}