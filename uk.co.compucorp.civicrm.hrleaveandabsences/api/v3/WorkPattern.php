<?php

/**
 * WorkPattern.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_work_pattern_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * WorkPattern.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_work_pattern_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * WorkPattern.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_work_pattern_delete($params) {
  civicrm_api3_verify_mandatory($params, NULL, ['id']);
  $workPatternService = new CRM_HRLeaveAndAbsences_Service_WorkPattern();
  $workPatternService->delete($params['id']);

  return civicrm_api3_create_success(true);
}

/**
 * WorkPattern.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_work_pattern_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * WorkPattern.getCalendar API specification
 *
 * @param array $spec
 */
function _civicrm_api3_work_pattern_getcalendar_spec(&$spec) {
  $spec['contact_id'] = [
    'name' => 'contact_id',
    'title' => 'Contact ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1
  ];

  $spec['start_date'] = [
    'name' => 'start_date',
    'title' => 'Start date of the period',
    'type' => CRM_Utils_Type::T_DATE,
    'api.required' => 1
  ];

  $spec['end_date'] = [
    'name' => 'end_date',
    'title' => 'End date of the period',
    'type' => CRM_Utils_Type::T_DATE,
    'api.required' => 1
  ];
}

/**
 * WorkPattern.getCalendar API
 *
 * This API endpoint returns a list of dates for the given start and end date
 * Period. The returned dates are only those within the contracts of the contact
 * with the given contact_id. For each date, we use the work pattern(s) assign to
 * the contact to check if it's a working day, non working day or weekend.
 *
 * @param array $params
 *
 * @return array
 */
function civicrm_api3_work_pattern_getcalendar($params) {
  $contactIDs = _civicrm_api3_work_pattern_get_contact_id_from_params($params);
  $jobContractService = new CRM_HRLeaveAndAbsences_Service_JobContract();
  $datePeriod = new CRM_HRCore_Date_BasicDatePeriod($params['start_date'], $params['end_date']);

  $calendars = [];
  foreach($contactIDs as $contactID) {
    $calendar = new CRM_HRLeaveAndAbsences_Service_WorkPatternCalendar(
      $contactID,
      $datePeriod,
      $jobContractService
    );

    $calendars[] = [
      'contact_id' => $contactID,
      'calendar' => $calendar->get()
    ];
  }

  return civicrm_api3_create_success($calendars);
}

/**
 * Extracts the contact id(s) from the params array.
 *
 * It can be either a single value, or one of the operators supported by the API
 * followed by a list of IDs. In the second case, only the IN operator is
 * supported, since that supporting other operators would be extremely complex
 * and it would not even make sense to support operators like >= and <.
 *
 * @param array $params
 *   The $params array passed to the WorkPattern.getCalendar API
 *
 * @return array
 */
function _civicrm_api3_work_pattern_get_contact_id_from_params($params) {
  if(empty($params['contact_id'])) {
    return [];
  }

  if(!is_array($params['contact_id'])) {
    return [$params['contact_id']];
  }

  if(!array_key_exists('IN', $params['contact_id'])) {
    throw new InvalidArgumentException('The contact_id parameter only supports the IN operator');
  }

  return $params['contact_id']['IN'];
}
