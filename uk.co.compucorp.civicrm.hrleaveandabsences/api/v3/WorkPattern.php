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

  $spec['period_id'] = [
    'name' => 'period_id',
    'title' => 'Absence Period ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1
  ];
}

/**
 * WorkPattern.getCalendar API
 *
 * This API endpoint returns a list of dates for the given Absence Period. The
 * returned dates are only those within the contracts of the contact with the
 * given contact_id. For each date, we use the work pattern(s) assign to the
 * contact to check if it's a working day, non working day or weekend.
 *
 * @param array $params
 *
 * @return array
 */
function civicrm_api3_work_pattern_getcalendar($params) {
  $jobContractService = new CRM_HRLeaveAndAbsences_Service_JobContract();
  $absencePeriod = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::findById($params['period_id']);
  $calendar = new CRM_HRLeaveAndAbsences_Service_WorkPatternCalendar(
    $params['contact_id'],
    $absencePeriod,
    $jobContractService
  );

  return civicrm_api3_create_success($calendar->get());
}
