<?php

/**
 * Contact.GetLeaveManagees API specification
 *
 * @param array $spec
 */
function _civicrm_api3_contact_getleavemanagees_spec(&$spec) {
  $spec['managed_by'] = [
    'name' => 'managed_by',
    'title' => 'Managed By',
    'description' => 'Only information for contacts managed by the contact with the given ID are returned',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 0,
    'FKClassName'  => 'CRM_Contact_DAO_Contact',
    'FKApiName'    => 'Contact',
  ];

  $spec['unassigned'] = [
    'name' => 'unassigned',
    'title' => 'Unassigned only?',
    'description' => 'Include only contacts without active leave managers?',
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'api.required' => 0,
  ];
}

/**
 * Contact.GetLeaveManagees API
 *
 * Returns the list of contacts that are managed by the currently logged in user
 *
 * @param array $params
 *
 * @return array API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_contact_getleavemanagees($params) {
  _civicrm_api3_contact_getleavemanagees_validate_params($params);

  // We need to set check_permissions to false so as to disable default Civi ACL checks for
  // this endpoint. ACL checks needed are already in the ContactSelect Query class.
  $params['check_permissions'] = false;
  $leaveManagerService = new CRM_HRLeaveAndAbsences_Service_LeaveManager();
  $query = new CRM_HRLeaveAndAbsences_API_Query_ContactSelect($params, $leaveManagerService);
  return civicrm_api3_create_success($query->run(), $params);
}

/**
 * Validates the parameters passed to the Contact.GetLeaveManagees API
 *
 * @param array $params
 *
 * @throws API_Exception
 */
function _civicrm_api3_contact_getleavemanagees_validate_params($params) {
  $hasUnassignedAsTrue = !empty($params['unassigned']);
  $hasManagedBy = isset($params['managed_by']);

  if (!$hasUnassignedAsTrue && !$hasManagedBy) {
    throw new API_Exception('Either unassigned must be true or managed_by parameter present');
  }

  if ($hasUnassignedAsTrue && $hasManagedBy) {
    throw new API_Exception('Unassigned cannot be true and managed_by parameter also present');
  }
}

