<?php

/**
 * Contact.getStaff API
 *
 * Returns the list of all staff (i.e. Contacts of the type "Individual") with some
 * basic details.
 *
 * This is an alternative to Contact.get for cases where roles without
 * access to all Contacts (due to ACL restrictions or permissions) still
 * need to load some basic info like Contact's IDs and names.
 *
 * @param array $params
 *
 * @return array API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_contact_getstaff($params) {
  $params['check_permissions'] = false;

  _civicrm_api3_contact_getstaff_filter_params($params);
  $params['contact_type'] = 'Individual';

  $result = civicrm_api3_contact_get($params);

  return _civicrm_api3_contact_getstaff_strip_non_basic_details($result);
}

/**
 * Internally, the Contact.getStaff API simply delegates things to
 * Contact.get, which means it will return all the fields that are
 * included in its response. Some of these fields might contain
 * confidential information (like the person address), so we need
 * to make sure that Contact.getStaff will not include any of such
 * fields in its response.
 *
 * @param $result
 *
 * @return mixed
 */
function _civicrm_api3_contact_getstaff_strip_non_basic_details($result) {
  if (empty($result['values'])) {
    return $result;
  }

  foreach ($result['values'] as $i => $value) {
    $result['values'][$i] = [];
    foreach (_civicrm_api3_contact_getstaff_fields() as $field) {
      $result['values'][$i][$field] = CRM_Utils_Array::value($field, $value);
    }
  }

  return $result;
}

/**
 * The spec for the Contact.getStaff API
 *
 * @param $spec
 */
function _civicrm_api3_contact_getstaff_spec(&$spec) {
  $spec['id'] = [
    'name' => 'id',
    'title' => 'Contact ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => FALSE
  ];

  $spec['display_name'] = [
    'name' => 'display_name',
    'title' => 'Display Name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => FALSE
  ];

  $spec['first_name'] = [
    'name' => 'first_name',
    'title' => 'First Name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => FALSE
  ];

  $spec['middle_name'] = [
    'name' => 'middle_name',
    'title' => 'Middle Name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => FALSE
  ];

  $spec['last_name'] = [
    'name' => 'last_name',
    'title' => 'Last Name',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => FALSE
  ];
}

/**
 * Returns a list of fields that are allowed for the Contact.getStaff API
 *
 * @return array
 */
function _civicrm_api3_contact_getstaff_fields() {
  $spec = [];
  _civicrm_api3_contact_getstaff_spec($spec);

  return array_keys($spec);
}

/**
 * Makes sure that only the fields allowed in the Contact.getStaff API are
 * used for filtering.
 *
 * Without this, for example, API clients would be able to find whether someone
 * lives in a given address simply by doing something like:
 *
 * civicrm_api3('Contact', 'getStaff', ['street_name' => 'Some street']);
 *
 * The address itself would not be included in the response, but the fact that
 * a contact was returned in such a call would indicate that this is their address.
 *
 * @param $params
 */
function _civicrm_api3_contact_getstaff_filter_params(&$params) {
  $allowedFields = _civicrm_api3_contact_getstaff_fields();
  $allowedFields[] = 'options';

  foreach (array_keys($params) as $key) {
    if (!in_array($key, $allowedFields, TRUE)) {
      unset($params[$key]);
    }
  }
}
