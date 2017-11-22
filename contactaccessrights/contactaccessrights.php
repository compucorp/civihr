<?php

require_once 'contactaccessrights.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function contactaccessrights_civicrm_config(&$config) {
  _contactaccessrights_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function contactaccessrights_civicrm_xmlMenu(&$files) {
  _contactaccessrights_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function contactaccessrights_civicrm_install() {
  _contactaccessrights_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function contactaccessrights_civicrm_uninstall() {
  _contactaccessrights_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function contactaccessrights_civicrm_enable() {
  _contactaccessrights_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function contactaccessrights_civicrm_disable() {
  _contactaccessrights_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_permission().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 */
function contactaccessrights_civicrm_permission(&$permissions) {
  $prefix = ts('CiviHRContactAccessRights') . ': ';
  $permissions['administer roles and teams'] = $prefix . ts('Administer roles and teams');
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op    string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function contactaccessrights_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _contactaccessrights_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function contactaccessrights_civicrm_managed(&$entities) {
  _contactaccessrights_civix_civicrm_managed($entities);
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
function contactaccessrights_civicrm_caseTypes(&$caseTypes) {
  _contactaccessrights_civix_civicrm_caseTypes($caseTypes);
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
function contactaccessrights_civicrm_angularModules(&$angularModules) {
  _contactaccessrights_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function contactaccessrights_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _contactaccessrights_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *
 *
 * /**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
 * function contactaccessrights_civicrm_preProcess($formName, &$form) {
 *
 * }
 */

/**
 * Implementation of hook_civicrm_entityTypes
 */
function contactaccessrights_civicrm_entityTypes(&$entityTypes) {
  $entityTypes['CRM_Contactaccessrights_DAO_Rights'] = array(
    'name'  => 'Rights',
    'class' => 'CRM_Contactaccessrights_DAO_Rights',
    'table' => 'civicrm_contactaccessrights_rights',
  );
}

/**
 * Implementation of hook_civicrm_aclWhereClause.
 *
 * @param $type - Type of permission needed
 * @param array $tables - (reference) Add the tables that are needed for the select clause
 * @param array $whereTables - (reference) Add the tables that are needed for the where clause
 * @param int $contactID - The contactID for whom the check is made
 * @param string $where - The current where clause
 */
function contactaccessrights_civicrm_aclWhereClause($type, &$tables, &$whereTables, &$contactID, &$where) {
  if (!$contactID) {
    return;
  }

  $aclUtil = new CRM_Contactaccessrights_Utils_ACL($contactID);

  $whereTables = array_merge($whereTables, $aclUtil->getWhereTables());

  $whereStr = implode(' AND ', $aclUtil->getWhereConditions());
  $whereStr = '(' . ($whereStr ?: '1') . ')';
  $where = trim($where) ? $where . " OR " . $whereStr : $whereStr;
}

function contactaccessrights_civicrm_pageRun($page) {
  if ($page instanceof CRM_Contact_Page_View_Summary && CRM_Core_Permission::check('administer roles and teams')) {
    $extName = 'uk.co.compucorp.contactaccessrights';
    CRM_Core_Resources::singleton()->addVars('contactAccessRights', array(
      'baseURL' => CRM_Extension_System::singleton()->getMapper()->keyToUrl($extName)
    ));
    CRM_Core_Resources::singleton()->addStyleFile($extName, 'css/access-rights.css');
    CRM_Core_Resources::singleton()->addScriptFile($extName, 'js/dist/access-rights.min.js', 1010);
  }
}
