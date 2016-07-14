<?php

require_once 'hrleaveandabsences.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function hrleaveandabsences_civicrm_config(&$config) {
  _hrleaveandabsences_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function hrleaveandabsences_civicrm_xmlMenu(&$files) {
  _hrleaveandabsences_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function hrleaveandabsences_civicrm_install() {
  _hrleavesandabsences_create_main_menu();
  _hrleaveandabsences_create_administer_menu();

  _hrleaveandabsences_civix_civicrm_install();
}

function _hrleaveandabsences_create_administer_menu() {
  $administerMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Administer', 'id', 'name');
  $maxWeightOfAdminMenuItems = _hrleaveandabsences_get_max_child_weight_for_menu($administerMenuId);

  $params = array(
      'label'      => ts('Leave and Absences'),
      'name'       => 'leave_and_absences',
      'url'        => null,
      'operator'   => null,
      'is_active'  => 1,
      'parent_id'  => $administerMenuId,
      'weight'     => $maxWeightOfAdminMenuItems + 1,
      'permission' => 'administer leave and absences'
  );

  $leaveAndAbsencesAdminNavigation = _hrleaveandabsences_add_navigation_menu($params);

  _hrleaveandabsences_create_administer_menu_tree($leaveAndAbsencesAdminNavigation);
}

/**
 * @param $leaveAndAbsencesAdminNavigation
 */
function _hrleaveandabsences_create_administer_menu_tree($leaveAndAbsencesAdminNavigation) {
  $leaveAndAbsencesAdministerMenuTree = array(
      array(
          'label'      => ts('Leave/Absence Types'),
          'name'       => 'leave_and_absence_types',
          'url'        => 'civicrm/admin/leaveandabsences/types?action=browse&reset=1',
          'permission' => 'administer leave and absences',
      ),
      array(
          'label'      => ts('Leave/Absence Periods'),
          'name'       => 'leave_and_absence_periods',
          'url'        => 'civicrm/admin/leaveandabsences/periods?action=browse&reset=1',
          'permission' => 'administer leave and absences',
      ),
      array(
          'label'      => ts('Public Holidays'),
          'name'       => 'leave_and_absence_public_holidays',
          'url'        => 'civicrm/admin/leaveandabsences/public_holidays?action=browse&reset=1',
          'permission' => 'administer leave and absences',
      ),
      array(
          'label'      => ts('Manage Work Patterns'),
          'name'       => 'leave_and_absence_manage_work_patterns',
          'url'        => 'civicrm/admin/leaveandabsences/work_patterns?action=browse&reset=1',
          'permission' => 'administer leave and absences',
      )
  );

  foreach ($leaveAndAbsencesAdministerMenuTree as $i => $item) {
    $item['weight']    = $i;
    $item['parent_id'] = $leaveAndAbsencesAdminNavigation->id;
    $item['is_active'] = 1;
    CRM_Core_BAO_Navigation::add($item);
  }
}

/**
 * Returns the maximum weight for a child item of the given parent menu.
 * If theres no child for this menu, 0 is returned
 *
 * @param $menu_id
 *
 * @return int
 */
function _hrleaveandabsences_get_max_child_weight_for_menu($menu_id) {
  $query = "SELECT MAX(weight) AS max FROM civicrm_navigation WHERE parent_id = %1";
  $params = array(
      1 => array($menu_id, 'Integer')
  );
  $dao = CRM_Core_DAO::executeQuery($query, $params);
  $dao->fetch();
  if($dao->max) {
    return $dao->max;
  }

  return 0;
}

function _hrleavesandabsences_create_main_menu() {
  $reportWeight = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'job_contracts', 'weight', 'name');

  $params = array(
      'label'      => ts('Leave and Absences'),
      'name'       => 'leave_and_absences',
      'url'        => 'civicrm/leaveandabsences/dashboard',
      'operator'   => null,
      'weight'     => $reportWeight + 1,
      'is_active'  => 1,
      'permission' => 'access leave and absences'
  );

  _hrleaveandabsences_add_navigation_menu($params);
}

/**
 * Creates a new navigation menu with the given parameters
 *
 * @param $reportWeight
 *
 * @return array
 */
function _hrleaveandabsences_add_navigation_menu($params)
{
  $navigationMenu = new CRM_Core_DAO_Navigation();
  if(!isset($params['domain_id'])) {
    $params['domain_id'] = CRM_Core_Config::domainID();
  }
  $navigationMenu->copyValues($params);
  $navigationMenu->save();

  return $navigationMenu;
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function hrleaveandabsences_civicrm_uninstall() {
  $query = "DELETE FROM civicrm_navigation WHERE name LIKE 'leave_and_absence%'";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_BAO_Navigation::resetNavigation();

  _hrleaveandabsences_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function hrleaveandabsences_civicrm_enable() {
  $query = "UPDATE civicrm_navigation SET is_active = 1 WHERE name LIKE 'leave_and_absence%'";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_BAO_Navigation::resetNavigation();

  _hrleaveandabsences_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function hrleaveandabsences_civicrm_disable() {
  $query = "UPDATE civicrm_navigation SET is_active = 0 WHERE name LIKE 'leave_and_absence%'";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_BAO_Navigation::resetNavigation();

  _hrleaveandabsences_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_permission().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 */
function hrleaveandabsences_civicrm_permission(&$permissions) {
  $prefix = ts('CiviHRLeaveAndAbsences') . ': '; // name of extension or module
  $permissions['access leave and absences'] = $prefix . ts('Access Leave and Absences');
  $permissions['administer leave and absences'] = $prefix . ts('Administer Leave and Absences');
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
function hrleaveandabsences_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrleaveandabsences_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function hrleaveandabsences_civicrm_managed(&$entities) {
  _hrleaveandabsences_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function hrleaveandabsences_civicrm_caseTypes(&$caseTypes) {
  _hrleaveandabsences_civix_civicrm_caseTypes($caseTypes);
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
function hrleaveandabsences_civicrm_angularModules(&$angularModules) {
_hrleaveandabsences_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function hrleaveandabsences_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _hrleaveandabsences_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_entityTypes
 */
function hrleaveandabsences_civicrm_entityTypes(&$entityTypes) {
  $entityTypes[] = array(
      'name'  => 'AbsenceType',
      'class' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
      'table' => 'civicrm_hrleaveandabsences_absence_type',
  );

  $entityTypes[] = array(
    'name'  => 'NotificationReceiver',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_NotificationReceiver',
    'table' => 'civicrm_hrleaveandabsences_notification_receiver',
  );

  $entityTypes[] = array(
    'name'  => 'WorkPattern',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_WorkPattern',
    'table' => 'civicrm_hrleaveandabsences_work_pattern',
  );

  $entityTypes[] = array(
    'name'  => 'WorkWeek',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_WorkWeek',
    'table' => 'civicrm_hrleaveandabsences_work_week',
  );

  $entityTypes[] = array(
    'name'  => 'WorkDay',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_WorkDay',
    'table' => 'civicrm_hrleaveandabsences_work_day',
  );

  $entityTypes[] = array(
    'name'  => 'AbsencePeriod',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_AbsencePeriod',
    'table' => 'civicrm_hrleaveandabsences_absence_period',
  );

  $entityTypes[] = array(
    'name'  => 'PublicHoliday',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_PublicHoliday',
    'table' => 'civicrm_hrleaveandabsences_public_holiday',
  );

  $entityTypes[] = array(
    'name'  => 'Entitlement',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_Entitlement',
    'table' => 'civicrm_hrleaveandabsences_entitlement',
  );

  $entityTypes[] = array(
    'name'  => 'BroughtForward',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_BroughtForward',
    'table' => 'civicrm_hrleaveandabsences_brought_forward',
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
function hrleaveandabsences_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function hrleaveandabsences_civicrm_navigationMenu(&$menu) {
  _hrleaveandabsences_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'uk.co.compucorp.civicrm.hrleaveandabsences')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _hrleaveandabsences_civix_navigationMenu($menu);
} // */
