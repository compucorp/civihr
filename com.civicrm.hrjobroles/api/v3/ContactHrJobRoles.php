<?php

/**
 * ContactJobRole.get API
 *
 * @param array $params
 *
 * @return array API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_contact_hr_job_roles_get($params) {
  if (!empty($params['contact_id'])) {
    $params['contact_id'] = _civicrm_api3_contact_hr_job_roles_get_contacts_from_params($params);
  }

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

/**
 * Extracts the list of contactID's from the $params array
 *
 * @param array $params
 *
 * @return array
 */
function _civicrm_api3_contact_hr_job_roles_get_contacts_from_params($params) {
  if (!is_array($params['contact_id'])) {
    return [$params['contact_id']];
  }

  if (!array_key_exists('IN', $params['contact_id'])) {
    throw new InvalidArgumentException('The contact_id parameter only supports the IN operator');
  }

  return $params['contact_id']['IN'];
}
