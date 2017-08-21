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


function hrjobroles_civicrm_navigationMenu( &$params ) {
  // Add sub-menu
  $navId = CRM_Core_DAO::singleValueQuery("SELECT max(id) FROM civicrm_navigation");
  $navId++;

  $topMenuID =  CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Contacts', 'id', 'name');
  $parentID =  CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'import_export_job_contracts', 'id', 'name');
  $params[$topMenuID]['child'][$parentID]['child'][$navId] = array(
    'attributes' => array(
      'label' => "Import Job Roles",
      'name' => "import_job_roles",
      'url' => "civicrm/jobroles/import",
      'permission' => NULL,
      'operator' => NULL,
      'separator' => TRUE,
      'parentID' => $parentID,
      'navID' => $navId,
      'active' => 1
      )
    );
}

/**
 * Implementation of hook_civicrm_tabset.
 *
 * Create a custom tab for civicrm contact which will implement custom drupal
 * callback function this tab should appear after job contracts tab directly
 * and since contact summary tab weight is -190 we chose this to be -180
 * to give some room for other extensions to place their tabs between these two.
 *
 * @param string $tabsetName
 * @param array &$tabs
 * @param array $context
 */
function hrjobroles_civicrm_tabset($tabsetName, &$tabs, $context) {
  if ($tabsetName === 'civicrm/contact/view') {
    $url = CRM_Utils_System::url('civicrm/job-roles/' . $context['contact_id']);
    $tabs[] = array( 'id' => 'hrjobroles',
      'url' => $url,
      'title' => 'Job Roles',
      'weight' => -180,
    );
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

/**
 * Implementation of hook_civicrm_queryObjects
 */
function hrjobroles_civicrm_queryObjects(&$queryObjects, $type) {
    if ($type == 'Contact') {
        $queryObjects[] = new CRM_Hrjobroles_BAO_Query();
    }
}

/**
 * This extension deals with multi values fields and
 * options lists in a peculiar way. Because of that,
 * the CiviCRM export function isn't capable to
 * properly retrieve all the related information about
 * the Job Role. With this hook, we have a chance to
 * fix this, by manipulating the results and fixing
 * the fields data
 *
 *
 * @param $exportTempTable
 * @param $headerRows
 * @param $sqlColumns
 * @param $exportMode
 */
function hrjobroles_civicrm_export( $exportTempTable, $headerRows, $sqlColumns, $exportMode ) {

    $splitItemsQuery = function($items) {
        return "
SELECT SUBSTRING_INDEX(SUBSTRING_INDEX($items, '|', n.n), '|', -1) value
FROM (
    SELECT a.N + b.N * 10 + 1 n
    FROM
      (SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) a
      ,(SELECT 0 AS N UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) b
    ORDER BY n
  ) n
having value <> ''";
    };

    $optionsFields = array('hrjc_role_department', 'hrjc_level_type', 'location', 'hrjc_region');
    $optionsUpdates = array();
    foreach($optionsFields as $field) {
        if(isset($sqlColumns[$field])) {
            $optionsUpdates[] = "et.$field = (SELECT ov.label FROM civicrm_option_value ov WHERE ov.id = et.$field)";
        }
    }

    if(isset($sqlColumns['funder'])) {
        // Since the contacts IDs are all stored on a single column, separated by |,
        // we need this ugly query to split them into rows, so we can pass the IDs
        // to a WHERE IN on the contacts table and concat their display names
        // Note: This query can handle roles with a maximum number of 98 funders.
        $optionsUpdates[] = "et.funder = (SELECT GROUP_CONCAT(c.display_name SEPARATOR ', ') FROM civicrm_contact c WHERE id IN({$splitItemsQuery('et.funder')}))";
    }

    if(isset($sqlColumns['hrjc_cost_center'])) {
        $optionsUpdates[] = "et.hrjc_cost_center = (SELECT GROUP_CONCAT(ov.label SEPARATOR ', ') FROM civicrm_option_value ov WHERE id IN({$splitItemsQuery('et.hrjc_cost_center')}))";
    }

    // The values for those fields are hardcoded, so here we replace them
    // with their "labels" (1 = %, 0 = Fixed)
    $valTypeFields = array('hrjc_funder_val_type', 'hrjc_cost_center_val_type');
    foreach($valTypeFields as $field) {
        if(isset($sqlColumns[$field])) {
            $optionsUpdates[] = "et.$field = REPLACE(REPLACE(REPLACE(et.$field, '|1', '%,'), '|0', 'Fixed,'), ',|', '')";
        }
    }

    // Those fields also store multiple values separeted by |, but they are not
    // related to any other table or option list. Here this ugly combination of
    // substrings, reverse and replace is used to replace the | with commas and
    // not letting any leading or trailing commas
    $fieldsToSplitValues = array(
        'hrjc_role_amount_pay_cost_center',
        'hrjc_role_amount_pay_funder',
        'hrjc_role_percent_pay_cost_center',
        'hrjc_role_percent_pay_funder'
    );
    foreach($fieldsToSplitValues as $i => $field) {
        if(isset($sqlColumns[$field])) {
            $optionsUpdates[] = "et.$field = (SELECT REPLACE(REVERSE(SUBSTRING(REVERSE(SUBSTRING(et.$field, 2)), 2)), '|', ', '))";
        }
    }

    if(!empty($optionsUpdates)) {
        $query = "UPDATE $exportTempTable et SET " . implode(', ', $optionsUpdates);
        CRM_Core_DAO::executeQuery($query);
    }

}

/**
 * Implements hook_civicrm_alterAPIPermissions to define the required
 * permissions to this extension's APIs
 *
 * @param string $entity
 * @param string $action
 * @param array $params
 * @param array $permissions
 */
function hrjobroles_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $permissions['contact_hr_job_roles']['get'] = ['access AJAX API'];
}
