<?php

//----------------------------------------------------------------------------//
//                             File Organization                              //
//                                                                            //
// To keep this file organized, it is split into 2 sections: CiviCRM Hooks    //
// and Helper Functions. The former has all the civicrm hooks implementations //
// used by this extension, whereas the latter, has all the helper functions   //
// used by those hooks.                                                       //
//                                                                            //
// If you're adding new things here, please keep this organization in mind.   //
//                                                                            //
//----------------------------------------------------------------------------//

use CRM_HRLeaveAndAbsences_Factory_PublicHolidayLeaveRequestService as PublicHolidayLeaveRequestServiceFactory;
use CRM_HRLeaveAndAbsences_Service_AbsenceType as AbsenceTypeService;

require_once 'hrleaveandabsences.civix.php';


//----------------------------------------------------------------------------//
//                           CiviCRM Hooks                                    //
//----------------------------------------------------------------------------//

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
  _hrleaveandabsences_create_has_leave_approved_by_relationship_type();

  _hrleaveandabsences_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function hrleaveandabsences_civicrm_uninstall() {
  _hrleaveandabsences_delete_extension_menus();
  _hrleaveandabsences_delete_has_leave_approved_by_relationship_type();

  _hrleaveandabsences_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function hrleaveandabsences_civicrm_enable() {
  _hrleaveandabsences_update_extension_is_active_flag(true);
  _hrleaveandabsences_update_has_leave_approved_by_relationship_type_is_active_flag(true);

  _hrleaveandabsences_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function hrleaveandabsences_civicrm_disable() {
  _hrleaveandabsences_update_extension_is_active_flag(false);
  _hrleaveandabsences_update_has_leave_approved_by_relationship_type_is_active_flag(false);

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
 * Implements hook_civicrm_alterAPIPermissions().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterAPIPermissions
 */
function hrleaveandabsences_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $actionEntities = [
    'get' => ['absence_type', 'absence_period', 'option_group', 'option_value',
              'leave_period_entitlement', 'public_holiday', 'leave_request', 'comment'],
    'getbalancechangebyabsencetype' => ['leave_request'],
    'calculatebalancechange' => ['leave_request'],
    'create' => ['leave_request', 'comment'],
    'update' => ['leave_request'],
    'getcalendar' => ['work_pattern'],
    'ismanagedby' => ['leave_request'],
    'isvalid' => ['leave_request'],
    'getfull' => ['leave_request'],
    'calculatetoilexpirydate' => ['absence_type'],
    'getleavemanagees' => ['contact'],
    'getcomment' => ['leave_request'],
    'addcomment' => ['leave_request'],
    'deletecomment' => ['leave_request'],
    'getattachments' => ['leave_request'],
    'deleteattachment' => ['leave_request'],
  ];

  foreach ($actionEntities as $action => $entities) {
    foreach ($entities as $entity) {
      $permissions[$entity][$action] = ['access AJAX API'];
    }
  }
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
  $entityTypes[] = [
      'name'  => 'AbsenceType',
      'class' => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
      'table' => 'civicrm_hrleaveandabsences_absence_type',
  ];

  $entityTypes[] = [
    'name'  => 'NotificationReceiver',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_NotificationReceiver',
    'table' => 'civicrm_hrleaveandabsences_notification_receiver',
  ];

  $entityTypes[] = [
    'name'  => 'WorkPattern',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_WorkPattern',
    'table' => 'civicrm_hrleaveandabsences_work_pattern',
  ];

  $entityTypes[] = [
    'name'  => 'WorkWeek',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_WorkWeek',
    'table' => 'civicrm_hrleaveandabsences_work_week',
  ];

  $entityTypes[] = [
    'name'  => 'WorkDay',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_WorkDay',
    'table' => 'civicrm_hrleaveandabsences_work_day',
  ];

  $entityTypes[] = [
    'name'  => 'AbsencePeriod',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_AbsencePeriod',
    'table' => 'civicrm_hrleaveandabsences_absence_period',
  ];

  $entityTypes[] = [
    'name'  => 'PublicHoliday',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_PublicHoliday',
    'table' => 'civicrm_hrleaveandabsences_public_holiday',
  ];

  $entityTypes[] = [
    'name'  => 'LeavePeriodEntitlement',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_LeavePeriodEntitlement',
    'table' => 'civicrm_hrleaveandabsences_leave_period_entitlement',
  ];

  $entityTypes[] = [
    'name'  => 'LeaveBalanceChange',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_LeaveBalanceChange',
    'table' => 'civicrm_hrleaveandabsences_leave_balance_change',
  ];

  $entityTypes[] = [
    'name'  => 'LeaveRequest',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_LeaveRequest',
    'table' => 'civicrm_hrleaveandabsences_leave_request',
  ];

  $entityTypes[] = [
    'name'  => 'LeaveRequestDate',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_LeaveRequestDate',
    'table' => 'civicrm_hrleaveandabsences_leave_request_date',
  ];

  $entityTypes[] = [
    'name'  => 'ContactWorkPattern',
    'class' => 'CRM_HRLeaveAndAbsences_DAO_ContactWorkPattern',
    'table' => 'civicrm_hrleaveandabsences_contact_work_pattern',
  ];
}

/**
 * Implementation of hook_civicrm_searchTasks
 */
function hrleaveandabsences_civicrm_searchTasks($objectType, &$tasks) {
  if($objectType == 'contact' && CRM_Core_Permission::check('administer leave and absences')) {
    $tasks[] = [
      'title' => ts('Manage leave entitlements'),
      'class' => 'CRM_HRLeaveAndAbsences_Form_Task_ManageEntitlements'
    ];
  }
}

/**
 * Implementation of the hook_civicrm_post.
 *
 * Basically, this is a decoupled way for this extension to execute tasks after
 * actions are executed on entities of other extensions
 *
 * @param string $op
 * @param string $objectName
 * @param int $objectId
 * @param object $objectRef
 */
function hrleaveandabsences_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  $postFunction = "_hrleaveandabsences_civicrm_post_" . strtolower($objectName);
  if(!function_exists($postFunction)) {
    return;
  }

  call_user_func_array($postFunction, [$op, $objectId, $objectRef]);
}

/**
 * Uses the hook_civicrm_container hook in order to insert L&A services in the
 * global Civi container.
 *
 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
 */
function hrleaveandabsences_civicrm_container(\Symfony\Component\DependencyInjection\ContainerBuilder $container) {
  $settingsManagerDefinition = new Symfony\Component\DependencyInjection\Definition(
    CRM_HRLeaveAndAbsences_Service_SettingsManager::class
  );
  $settingsManagerDefinition->setFactoryClass(CRM_HRLeaveAndAbsences_Factory_SettingsManager::class);
  $settingsManagerDefinition->setFactoryMethod('create');
  // If we running unit tests, this will make the factory return an InMemorySettingsManager
  $settingsManagerDefinition->setArguments([CIVICRM_UF == 'UnitTests']);

  $container->setDefinition('hrleaveandabsences.settings_manager', $settingsManagerDefinition);
}

/**
 * Implementation of the hook_civicrm_postInstall.
 *
 * Basically, it finishes the extension installation by setting things that are
 * not available during the installation phase.
 */
function hrleaveandabsences_civicrm_postInstall() {
  _hrleaveandabsences_set_has_leave_approved_by_as_default_relationship_type();
  _hrleaveandabsences_set_default_permissions();
}

/**
 * Implementation of hook_civicrm_tabset.
 *
 * This is a way for this extension to add its own tabs to
 * the core tabs interface used for contacts, contributions and events.
 *
 * @param string $tabsetName
 * @param array $tabs
 * @param array $context
 */
function hrleaveandabsences_civicrm_tabset($tabsetName, &$tabs, $context) {
  //check if the tabset is Contact Summary Page
  if ($tabsetName == 'civicrm/contact/view') {
    $contactId = $context['contact_id'];
    $tabs[] = [
      'id'        => 'absence',
      'url'       => CRM_Utils_System::url('civicrm/contact/view/absence', ['cid' => $contactId]),
      'title'     => ts('Absence'),
      'weight'    => 10
    ];
  }
}

/**
 * Implementation of hook_civicrm_selectWhereClause
 *
 * @param $entity
 * @param $clauses
 */
function hrleaveandabsences_civicrm_selectWhereClause($entity, &$clauses) {

  // We remove all the ACL clauses here, because we are adding more specific
  // ones with the hrcomments_selectWhereClause hook.
  // If we keep the default clauses, users will not be able to see all the
  // comments they should.
  // This is not 100% guaranteed to work, because other extensions can add
  // their own implementation of this and there's no way to know in which order
  // they will be called. For now it works, because this is the only place where
  // we deal with Comments ACL
  if($entity == 'Comment') {
    $clauses = [];
  }
}

/**
 * Implementation of hook_hrcomments_selectWhereClause
 *
 * We use this special custom hook here because it gives us access to the params
 * passed to the get operation, and then we can add custom ACLs based on the
 * entity_name.
 *
 * @param array $conditions
 * @param array $params
 */
function hrleaveandabsences_hrcomments_selectWhereClause(&$conditions, $params) {
  if($params['entity_name'] != 'LeaveRequest') {
    return;
  }

  $leaveManagerService = new CRM_HRLeaveAndAbsences_Service_LeaveManager();
  $commentsWhereClause = new CRM_HRLeaveAndAbsences_ACL_LeaveRequestCommentsWhereClause($leaveManagerService);
  $conditions = array_merge($conditions, $commentsWhereClause->get());
}

//----------------------------------------------------------------------------//
//                               Helper Functions                             //
//----------------------------------------------------------------------------//

/**
 * Creates the "Leave and Absences" menu item under Civi's "Administer" menu
 */
function _hrleaveandabsences_create_administer_menu() {
  $administerMenuId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Administer', 'id', 'name');
  $maxWeightOfAdminMenuItems = _hrleaveandabsences_get_max_child_weight_for_menu($administerMenuId);

  $params = [
    'label'      => ts('Leave and Absences'),
    'name'       => 'leave_and_absences',
    'url'        => null,
    'operator'   => null,
    'is_active'  => 1,
    'parent_id'  => $administerMenuId,
    'weight'     => $maxWeightOfAdminMenuItems + 1,
    'permission' => 'administer leave and absences'
  ];

  $leaveAndAbsencesAdminNavigation = _hrleaveandabsences_add_navigation_menu($params);

  _hrleaveandabsences_create_administer_menu_tree($leaveAndAbsencesAdminNavigation);
}

/**
 * @param $leaveAndAbsencesAdminNavigation
 */
function _hrleaveandabsences_create_administer_menu_tree($leaveAndAbsencesAdminNavigation) {
  $leaveAndAbsencesAdministerMenuTree = [
    [
      'label' => ts('Leave/Absence Types'),
      'name' => 'leave_and_absence_types',
      'url' => 'civicrm/admin/leaveandabsences/types?action=browse&reset=1',
      'permission' => 'administer leave and absences',
    ],
    [
      'label' => ts('Leave/Absence Periods'),
      'name' => 'leave_and_absence_periods',
      'url' => 'civicrm/admin/leaveandabsences/periods?action=browse&reset=1',
      'permission' => 'administer leave and absences',
    ],
    [
      'label' => ts('Public Holidays'),
      'name' => 'leave_and_absence_public_holidays',
      'url' => 'civicrm/admin/leaveandabsences/public_holidays?action=browse&reset=1',
      'permission' => 'administer leave and absences',
    ],
    [
      'label' => ts('Manage Work Patterns'),
      'name' => 'leave_and_absence_manage_work_patterns',
      'url' => 'civicrm/admin/leaveandabsences/work_patterns?action=browse&reset=1',
      'permission' => 'administer leave and absences',
    ],
    [
      'label' => ts('General Settings'),
      'name' => 'leave_and_absence_general_settings',
      'url' => 'civicrm/admin/leaveandabsences/general_settings',
      'permission' => 'administer leave and absences',
    ]
  ];

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
  $params = [
    1 => [$menu_id, 'Integer']
  ];
  $dao = CRM_Core_DAO::executeQuery($query, $params);
  $dao->fetch();
  if($dao->max) {
    return $dao->max;
  }

  return 0;
}

/**
 * Creates the extension's menu item on the main navigation
 */
function _hrleavesandabsences_create_main_menu() {
  $vacanciesWeight = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Vacancies', 'weight', 'name');

  $params = [
    'label'      => ts('Leave and Absences'),
    'name'       => 'leave_and_absences',
    'url'        => 'civicrm/leaveandabsences/dashboard',
    'operator'   => null,
    'weight'     => $vacanciesWeight + 1,
    'is_active'  => 1,
    'permission' => 'access leave and absences'
  ];

  _hrleaveandabsences_add_navigation_menu($params);
}

/**
 * Creates a new navigation menu with the given parameters
 *
 * @param array $params
 *
 * @return array
 */
function _hrleaveandabsences_add_navigation_menu($params) {
  $navigationMenu = new CRM_Core_DAO_Navigation();
  if(!isset($params['domain_id'])) {
    $params['domain_id'] = CRM_Core_Config::domainID();
  }
  $navigationMenu->copyValues($params);
  $navigationMenu->save();

  return $navigationMenu;
}

/**
 * Deletes from the database all the menus created by this extension
 */
function _hrleaveandabsences_delete_extension_menus() {
  $query = "DELETE FROM civicrm_navigation WHERE name LIKE 'leave_and_absence%'";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_BAO_Navigation::resetNavigation();
}

/**
 * Updates the is_active flag for this extension menus, according to the given
 * param.
 *
 * @param bool $active
 */
function _hrleaveandabsences_update_extension_is_active_flag($active = true) {
  $value = $active ? '1' : '0';

  $query = "UPDATE civicrm_navigation SET is_active = {$value} WHERE name LIKE 'leave_and_absence%'";
  CRM_Core_DAO::executeQuery($query);
  CRM_Core_BAO_Navigation::resetNavigation();
}

/**
 * Function which will be called when hook_civicrm_post is executed for the
 * HRJobDetails entity
 *
 * @param string $op
 * @param int $objectId
 * @param object $objectRef
 */
function _hrleaveandabsences_civicrm_post_hrjobdetails($op, $objectId, &$objectRef) {
  if(in_array($op, ['create', 'edit'])) {

    try {
      $revision = civicrm_api3('HRJobContractRevision', 'getsingle', [
        'id' => $objectRef->jobcontract_revision_id
      ]);

      $service = PublicHolidayLeaveRequestServiceFactory::create();
      $service->updateAllInTheFutureForContract($revision['jobcontract_id']);
    } catch(Exception $e) {}
  }
}

/**
 * Function which will be called when hook_civicrm_post is executed for the
 * AbsenceType entity
 *
 * @param string $op
 * @param int $objectId
 * @param object $objectRef
 */
function _hrleaveandabsences_civicrm_post_absencetype($op, $objectId, &$objectRef) {
  if(in_array($op, ['edit'])) {

    try {
      $absenceTypeService = new AbsenceTypeService();
      $absenceTypeService->postUpdateActions($objectRef);
    } catch (Exception $e) {}
  }
}

/**
 * Creates the "Has Leave Approved By" relationship type, if it doesn't exist yet.
 */
function _hrleaveandabsences_create_has_leave_approved_by_relationship_type() {
  $relationshipType = _hrleaveandabsences_get_has_leave_approved_by_relationship_type();

  if(NULL === $relationshipType) {
    civicrm_api3('RelationshipType', 'create', [
      'sequential'     => 1,
      'description'    => 'Has Leave Approved By',
      'name_a_b'       => 'has Leave Approved by',
      'name_b_a'       => 'is Leave Approver of',
      'contact_type_a' => 'Individual',
      'contact_type_b' => 'Individual',
    ]);
  }
}

/**
 * Deletes the "Has Leave Approved By" relationship type, if it exists
 */
function _hrleaveandabsences_delete_has_leave_approved_by_relationship_type() {
  $relationshipType = _hrleaveandabsences_get_has_leave_approved_by_relationship_type();

  if (NULL !== $relationshipType) {
    civicrm_api3('RelationshipType', 'delete', [
      'sequential' => 1,
      'id' => $relationshipType['id'],
    ]);
  }
}

/**
 * Enable or disable the "Has Leave Approved By" relationship type, according to
 * the value of the $active param.
 *
 * @param bool $active
 */
function _hrleaveandabsences_update_has_leave_approved_by_relationship_type_is_active_flag($active = true) {
  $relationshipType = _hrleaveandabsences_get_has_leave_approved_by_relationship_type();

  if ($relationshipType) {
    civicrm_api3('RelationshipType', 'create', [
      'id' => $relationshipType['id'],
      'is_active' => $active,
      // we need to pass both name_a_b and name_b_a
      // to avoid some notices thrown by the poor code in
      // the civicrm Relationship Type API, which tries to
      // access them without checking first if they exist
      'name_a_b' => $relationshipType['name_a_b'],
      'name_b_a' => $relationshipType['name_b_a']
    ]);
  }
}

/**
 * Returns the data for the "Has Leave Approved By" relationship type. If it doesn't
 * exist, returns null.
 *
 * @return mixed|null
 */
function _hrleaveandabsences_get_has_leave_approved_by_relationship_type() {
  $result = civicrm_api3('RelationshipType', 'get', [
    'sequential' => 1,
    'name_a_b' => 'has Leave Approved by',
  ]);

  if (!empty($result['values'])) {
    return $result['values'][0];
  }

  return NULL;
}

/**
 * Sets the "Has Leave Approved By" relationship type as the default Leave Approver
 * relationship type one on the General Settings.
 */
function _hrleaveandabsences_set_has_leave_approved_by_as_default_relationship_type() {
  $settingsManager  = CRM_HRLeaveAndAbsences_Factory_SettingsManager::create();
  $relationshipType = _hrleaveandabsences_get_has_leave_approved_by_relationship_type();

  if ($relationshipType) {
    $settingsManager->set(
      'relationship_types_allowed_to_approve_leave',
      [$relationshipType['id']]
    );
  }
}

/**
 * Sets the default CiviCRM permissions for the 3 main roles in CiviHR
 */
function _hrleaveandabsences_set_default_permissions() {
  _hrleaveandabsences_set_roles_permissions(
    ['civihr_staff', 'civihr_manager', 'civihr_admin'],
    ['access leave and absences']
  );

  _hrleaveandabsences_set_roles_permissions(
    ['civihr_admin'],
    ['administer leave and absences']
  );
}

/**
 * Grant the given $permissions to the given $roles
 *
 * @param array $roles
 * @param array $permissions
 */
function _hrleaveandabsences_set_roles_permissions($roles, $permissions) {
  if(!function_exists('user_role_load_by_name') || !function_exists('user_role_grant_permissions')) {
    return;
  }

  foreach($roles as $roleName) {
    $role = user_role_load_by_name($roleName);
    user_role_grant_permissions($role->rid, $permissions);
  }
}
