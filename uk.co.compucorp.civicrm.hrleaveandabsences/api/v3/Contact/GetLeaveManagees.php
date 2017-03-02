<?php

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
  $query = new CRM_HRLeaveAndAbsences_API_Query_ContactSelect($params);
  return civicrm_api3_create_success($query->run(), $params);
}

