<?php

require_once 'hrrecruitment.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrrecruitment_civicrm_config(&$config) {
  _hrrecruitment_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrrecruitment_civicrm_xmlMenu(&$files) {
  _hrrecruitment_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrrecruitment_civicrm_install() {
  $activityTypesResult = civicrm_api3('activity_type', 'get', array());
  $weight = count($activityTypesResult["values"]);
  foreach (array('Evaluation', 'Comment') as $activityType) {
    if (!in_array($activityType, $activityTypesResult["values"])) {
      civicrm_api3('activity_type', 'create', array(
          'weight'      => $weight++,
          'name'        => $activityType,
          'label'       => ts($activityType),
          'filter'      => 1,
          'is_active'   => 1,
        )
      );
    }
  }

  $result = civicrm_api3('OptionGroup', 'create', array(
    'name' => 'vacancy_status',
    'title' => ts('Vacancy Status'),
    'is_reserved' => 1,
    'is_active' => 1,
    )
  );

  foreach (array('Draft', 'Open', 'Closed', 'Cancelled', 'Rejected') as $key => $status) {
    $statusParam = array(
      'option_group_id' => $result['id'],
      'label' => ts($status),
      'name' => $status,
      'value' => $key+1,
      'is_active' => 1,
    );
    if ($status == 'Draft') {
      $statusParam['is_default'] = 1;
    }
    civicrm_api3('OptionValue', 'create', $statusParam);
  }

  $reportWeight = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Reports', 'weight', 'name');
  $vacancyNavigation = new CRM_Core_DAO_Navigation();
  $params = array (
    'domain_id'  => CRM_Core_Config::domainID(),
    'label'      => ts('Vacancies'),
    'name'       => 'Vacancies',
    'url'        => null,
    'operator'   => null,
    'weight'     => $reportWeight-1,
    'is_active'  => 1
  );
  $vacancyNavigation->copyValues($params);
  $vacancyNavigation->save();

  $vacancyMenuTree = array(
    array(
      'label'      => ts('Dashboard'),
      'name'       => 'dashboard',
      'url'        => 'civicrm/vacancy/dashboard',
      'permission' => null,
    ),
    array(
      'label'      => ts('New Vacancy'),
      'name'       => 'new_vacancy',
      'url'        => 'civicrm/vacancy/add?reset=1',
      'permission' => null,
    ),
    array(
      'label'      => ts('New Template'),
      'name'       => 'new_template',
      'url'        => 'civicrm/vacancy/add?reset=1&template=1',
      'permission' => null,
    ),
    array(
      'label'      => ts('Find Vacancies'),
      'name'       => 'find_vacancies',
      'url'        => 'civicrm/vacancy/search?reset=1',
      'permission' => null,
    ),
    array(
      'label'      => ts('Reports'),
      'name'       => 'reports',
      'url'        => null,
      'permission' => null,
    ),
  );

  foreach ($vacancyMenuTree as $key => $menuItems) {
    $menuItems['is_active'] = 1;
    $menuItems['parent_id'] = $vacancyNavigation->id;
    $menuItems['weight'] = $key;
    CRM_Core_BAO_Navigation::add($menuItems);
  }
  CRM_Core_BAO_Navigation::resetNavigation();

  return _hrrecruitment_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrrecruitment_civicrm_uninstall() {
  $vacanciesId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Vacancies', 'id', 'name');
  CRM_Core_BAO_Navigation::processDelete($vacanciesId);
  CRM_Core_BAO_Navigation::resetNavigation();

  if ($statusId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'vacancy_status', 'id', 'name')) {
    civicrm_api3('OptionGroup', 'delete', array('id' => $statusId));
  }

  foreach (array('Evaluation', 'Comment') as $activityType) {
    if ($id = civicrm_api3('OptionValue', 'getvalue', array('name' => $activityType, 'return' => 'id'))) {
      civicrm_api3('OptionValue', 'delete', array('id' => $id));
    }
  }
  return _hrrecruitment_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrrecruitment_civicrm_enable() {
  CRM_Core_BAO_Navigation::processUpdate(array('name' => 'Vacancies'), array('is_active' => 1));
  CRM_Core_BAO_Navigation::resetNavigation();

  if ($vacancyStatusID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'vacancy_status', 'id', 'name')) {
    civicrm_api3('OptionGroup', 'create', array('id' => $vacancyStatusID, 'is_active' => 1));

    $statusIDs = CRM_Core_OptionGroup::valuesByID($vacancyStatusID, FALSE, FALSE, FALSE, 'id', FALSE);
    foreach ($statusIDs as $statusID) {
      civicrm_api3('OptionValue', 'create', array('id' => $statusID, 'is_active' => 1));
    }
  }

  foreach (array('Evaluation', 'Comment') as $activityType) {
    if ($id = civicrm_api3('OptionValue', 'getvalue', array('name' => $activityType, 'return' => 'id'))) {
      civicrm_api3('OptionValue', 'create', array('id' => $id, 'is_active' => 1));
    }
  }

  return _hrrecruitment_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrrecruitment_civicrm_disable() {
  CRM_Core_BAO_Navigation::processUpdate(array('name' => 'Vacancies'), array('is_active' => 0));
  CRM_Core_BAO_Navigation::resetNavigation();

  if ($vacancyStatusID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'vacancy_status', 'id', 'name')) {
    $statusIDs = CRM_Core_OptionGroup::valuesByID($vacancyStatusID, FALSE, FALSE, FALSE, 'id');
    foreach ($statusIDs as $statusID) {
      civicrm_api3('OptionValue', 'create', array('id' => $statusID, 'is_active' => 0));
    }
    civicrm_api3('OptionGroup', 'create', array('id' => $vacancyStatusID, 'is_active' => 0));
  }

  foreach (array('Evaluation', 'Comment') as $activityType) {
    if ($id = civicrm_api3('OptionValue', 'getvalue', array('name' => $activityType, 'return' => 'id'))) {
      civicrm_api3('OptionValue', 'create', array('id' => $id, 'is_active' => 0));
    }
  }

  return _hrrecruitment_civix_civicrm_disable();
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
function hrrecruitment_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrrecruitment_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrrecruitment_civicrm_managed(&$entities) {
  return _hrrecruitment_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 */
function hrrecruitment_civicrm_caseTypes(&$caseTypes) {
  _hrrecruitment_civix_civicrm_caseTypes($caseTypes);
}

function hrrecruitment_civicrm_entityTypes(&$entityTypes) {
  $entityTypes[] = array(
    'name' => 'HRVacancy',
    'class' => 'CRM_HRRecruitment_DAO_HRVacancy',
    'table' => 'civicrm_hrvacancy',
  );
}

function hrrecruitment_civicrm_navigationMenu( &$params ) {
  $vacanciesMenuItems = array();
  $vacancieStatuses = CRM_Core_OptionGroup::values('vacancy_status');
  $vacanciesId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Vacancies', 'id', 'name');
  $parentVacanciesId =  CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'find_vacancies', 'id', 'name');
  $count = 0;
  foreach ($vacancieStatuses as $value => $vacancyStatus) {
    $vacanciesMenuItems[$count] = array(
      'attributes' => array(
        'label'      => "{$vacancyStatus}",
        'name'       => "{$vacancyStatus}",
        'url'        => "civicrm/vacancy/search?reset=1&status={$value}",
        'permission' => NULL,
        'operator'   => 'OR',
        'separator'  => NULL,
        'parentID'   => $parentVacanciesId,
        'navID'      => 1,
        'active'     => 1
      )
    );
    $count++;
  }
  if (!empty($vacanciesMenuItems)) {
    $params[$vacanciesId]['child'][$parentVacanciesId]['child'] = $vacanciesMenuItems;
  }
}