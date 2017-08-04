<?php

/**
 * ContactJobRole.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_contact_hr_job_roles_get($params) {
  $query = new CRM_Hrjobroles_API_Query_ContactHrJobRolesSelect($params);

  return civicrm_api3_create_success($query->run(), $params, 'ContactHrJobRoles', 'get');
}

/**
 * This function is used internally, to respond to a call to
 * ContactHrJobRoles.getFields. The CiviCRM will try to call a function with
 * this name to get the DAO for this entity.
 *
 * This is necessary because there is no DAO for ContactHrJobRoles.
 *
 * @return string
 */
function _civicrm_api3_contact_hr_job_roles_DAO() {
  return CRM_Hrjobroles_BAO_ContactHrJobRoles::class;
}
