<?php

require_once 'hrabsence.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function hrabsence_civicrm_config(&$config) {
  _hrabsence_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function hrabsence_civicrm_xmlMenu(&$files) {
  _hrabsence_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function hrabsence_civicrm_install() {
  $result = civicrm_api3('activity_type', 'get', array());
  if (CRM_Utils_Array::value('is_error', $result, FALSE)) {
    $weight = count($result["values"]);
    if(!array_search("Public Holiday", $result["values"])) {
      $weight = $weight+1;
      $params = array(
        'weight' => $weight,
        'label' => 'Public Holiday',
        'filter' => 0,
        'is_active' => 1,
        'is_optgroup' => 0,
        'is_default' => 0,
	  );
      $resultCreateActivityType = civicrm_api3('activity_type', 'create', $params);
	}
	 
    if(!array_search("Absence", $result["values"]) && array_key_exists("CiviTimesheet",$components)) {
      $weight = $weight+1;
      $params = array(
        'weight' => $weight,
        'label' => 'Absence',
        'filter' => 0,
        'is_active' => 1,
        'is_optgroup' => 0,
        'is_default' => 0,
        'component_id' => $components["CiviTimesheet"]->componentID,
      );
	  $resultCreateActivityType = civicrm_api3('activity_type', 'create', $params);
    }
  } 
  return _hrabsence_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function hrabsence_civicrm_uninstall() {
  return _hrabsence_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function hrabsence_civicrm_enable() {
  return _hrabsence_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function hrabsence_civicrm_disable() {
  return _hrabsence_civix_civicrm_disable();
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
function hrabsence_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hrabsence_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function hrabsence_civicrm_managed(&$entities) {
  return _hrabsence_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 */
function hrabsence_civicrm_caseTypes(&$caseTypes) {
  _hrabsence_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_entityTypes
 */
function hrabsence_civicrm_entityTypes(&$entityTypes) {
  $entityTypes[] = array(
    'name' => 'HRAbsence',
    'class' => 'CRM_HRAbsence_DAO_HRAbsence',
    'table' => 'civicrm_absence_type',
  );
}
