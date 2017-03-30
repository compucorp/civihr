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
    'api.required' => 1,
    'FKClassName'  => 'CRM_Contact_DAO_Contact',
    'FKApiName'    => 'Contact',
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
  // We need to set check_permissions to false so as to disable default Civi ACL checks for
  // this endpoint. ACL checks needed are already in the ContactSelect Query class.
  $params['check_permissions'] = false;
  $leaveManagerService = new CRM_HRLeaveAndAbsences_Service_LeaveManager();
  $query = new CRM_HRLeaveAndAbsences_API_Query_ContactSelect($params, $leaveManagerService);
  return civicrm_api3_create_success($query->run(), $params);
}

