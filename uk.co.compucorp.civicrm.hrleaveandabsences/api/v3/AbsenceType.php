<?php

/**
 * AbsenceType.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_absence_type_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * AbsenceType.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_absence_type_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * AbsenceType.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_absence_type_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * AbsenceType.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_absence_type_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}


/**
 * AbsenceType.calculateToilExpiryDate Specification
 *
 * @param array $spec
 */
function _civicrm_api3_absence_type_calculatetoilexpirydate_spec(&$spec) {
  $spec['absence_type_id'] = [
    'name' => 'absence_type_id',
    'title' => 'Absence Type ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1
  ];

  $spec['date'] = [
    'name' => 'date',
    'title' => 'Date to calculate TOIL expiry for',
    'type' => CRM_Utils_Type::T_DATE,
    'api.required' => 1
  ];
}

/**
 * AbsenceType.calculateToilExpiryDate API
 *
 * @param array $params
 *   An array of parameters passed to the API
 *
 * @return array API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_absence_type_calculatetoilexpirydate($params) {
  $absenceType = CRM_HRLeaveAndAbsences_BAO_AbsenceType::findById($params['absence_type_id']);

  $expiry = $absenceType->calculateToilExpiryDate(new DateTime($params['date']));
  $expiryDate = false;

  if($expiry instanceof DateTime){
    $expiryDate = $expiry->format('Y-m-d');
  }

  $result = ['expiry_date' => $expiryDate];
  return civicrm_api3_create_success($result);
}

