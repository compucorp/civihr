<?php

/**
 * PublicHoliday.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_public_holiday_create_spec(&$spec) {
}

/**
 * PublicHoliday.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_public_holiday_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * PublicHoliday.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_public_holiday_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * PublicHoliday.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_public_holiday_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * PublicHoliday.getcountforcurrentperiod API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_public_holiday_getcountforcurrentperiod_spec(&$spec) {
  $spec['exclude_weekends']['api.default'] = 0;
}

/**
 * PublicHoliday.getcountforcurrentperiod API
 *
 * @param array $params
 * @return array API result descriptor
 */
function civicrm_api3_public_holiday_getcountforcurrentperiod($params) {
  $excludeWeekends = empty($params['exclude_weekends']) ? false : true;
  return CRM_HRLeaveAndAbsences_BAO_PublicHoliday::getCountForCurrentPeriod($excludeWeekends);
}

/**
 * PublicHoliday.process_public_holiday_leave_request_updates_queue API specification
 *
 * @param array $spec
 *  Description of fields supported by this API call
 *
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_public_holiday_process_public_holiday_leave_request_updates_queue_spec(&$spec) {
}

/**
 * PublicHoliday.process_public_holiday_leave_request_updates_queue API
 *
 * Execute all the jobs added to the ProcessPublicHolidayLeaveRequestUpdates Queue
 *
 * @param array $params
 *
 * @return array
 */
function civicrm_api3_public_holiday_process_public_holiday_leave_request_updates_queue($params) {
  return civicrm_api3_create_success(
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::processPublicHolidayLeaveRequestUpdatesQueue(),
    $params
  );
}
