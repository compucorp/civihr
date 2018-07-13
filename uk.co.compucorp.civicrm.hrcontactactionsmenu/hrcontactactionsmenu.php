<?php

require_once 'hrcontactactionsmenu.civix.php';

use CRM_HRContactActionsMenu_ExtensionUtil as E;
use CRM_HRContactActionsMenu_Component_Menu as ActionsMenu;
use CRM_HRContactActionsMenu_Helper_UserInformationActionGroup as UserInformationActionGroupHelper;
use CRM_HRContactActionsMenu_Helper_CommunicationActionGroup as CommunicationActionGroupHelper;
use CRM_HRContactActionsMenu_Helper_Contact as ContactHelper;
use CRM_HRCore_CMSData_UserRoleFactory as CMSUserRoleFactory;
use CRM_HRCore_CMSData_PathsFactory as CMSUserPathFactory;
use CRM_HRContactActionsMenu_Hook_AddContactMenuActions as AddContactMenuActionsHook;
use CRM_HRCore_CMSData_UserAccountFactory as UserAccountFactory;

/**
 * Implementation of hook_addContactMenuActions to add the
 * User Information menu group and the Communicate menu group
 * to the contact actions menu.
 *
 * @param \CRM_HRContactActionsMenu_Component_Menu $menu
 *
 * @throws \Exception
 */
function hrcontactactionsmenu_addContactMenuActions(ActionsMenu $menu) {
  $contactID = empty($_GET['cid']) ? '' : $_GET['cid'];
  if (!$contactID) {
    return;
  }

  $contactUserInfo = ContactHelper::getUserInformation($contactID);
  if(!empty($contactUserInfo['cmsId'])) {
    $cmsFramework = CRM_Core_Config::singleton()->userFramework;
    $cmsUserPath = CMSUserPathFactory::create($cmsFramework, $contactUserInfo);
    $cmsUserRole = CMSUserRoleFactory::create($cmsFramework, $contactUserInfo);
    $cmsUserAccount = UserAccountFactory::create($contactUserInfo);
  }

  $userInformationActionGroup = new UserInformationActionGroupHelper(
    $contactUserInfo,
    !empty($cmsUserPath) ? $cmsUserPath : null,
    !empty($cmsUserRole) ? $cmsUserRole : null,
    !empty($cmsUserAccount) ? $cmsUserAccount : null
  );
  $menu->addToHighlightedPanel($userInformationActionGroup->get());

  $communicationActionGroup = new CommunicationActionGroupHelper($contactID);
  $communicationActionGroup = $communicationActionGroup->get();
  $communicationActionGroup->setWeight(3);
  $menu->addToMainPanel($communicationActionGroup);
}

/**
 * Implementation of hook_civicrm_pageRun
 */
function hrcontactactionsmenu_civicrm_pageRun(&$page) {
  if ($page instanceof CRM_Contact_Page_View_Summary) {
    $contactID = $_GET['cid'];
    $extName = E::LONG_NAME;
    $menu = AddContactMenuActionsHook::invoke();
    $contactInfo = ContactHelper::getUserInformation($contactID);
    $userAccountDisabled = _hrcontactactionsmenu_get_is_user_disabled($contactInfo);

    $page->assign('menu', $menu);
    $page->assign('userAccountDisabled', $userAccountDisabled);
    $page->assign('contactInfo', $contactInfo);

    CRM_Core_Resources::singleton()->addStyleFile($extName, 'css/contactactions.css');
    CRM_Core_Resources::singleton()->addScriptFile($extName, 'js/contactactions.js');

    CRM_Core_Region::instance('contact-page-inline-actions')->update('default', [
      'disabled' => TRUE,
    ]);
    CRM_Core_Region::instance('contact-page-inline-actions')->add([
      'template' => 'CRM/HRContactActionsMenu/Page/Inline/Actions.tpl'
    ]);
  }
}

/**
 * Implements hook_civicrm_permission().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 */
function hrcontactactionsmenu_civicrm_permission(&$permissions) {
  $prefix = ts('CiviHRContactActions') . ': '; // name of extension or module
  $permissions['administer staff accounts'] = $prefix . ts('Administer Staff Accounts');
}

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
 * Checks whether the User account of the contact is
 * disabled.
 *
 * @param array $contactInfo
 *
 * @return bool
 */
function _hrcontactactionsmenu_get_is_user_disabled($contactInfo) {
  if(empty($contactInfo['cmsId'])) {
    return false;
  }
  $userAccount = UserAccountFactory::create();

  return $userAccount->isUserDisabled($contactInfo);
}
