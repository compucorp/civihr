<?php

/**
 * HRJobDetails.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_h_r_job_details_create_spec(&$spec) {
}

/**
 * HRJobDetails.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_details_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * HRJobDetails.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_details_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * HRJobDetails.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_details_get($params) {
  _civicrm_hrjobcontract_api3_set_current_revision($params, _civicrm_get_table_name(_civicrm_api3_get_BAO(__FUNCTION__)));
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * HRJobDetails.validatedates API
 * Check if given Contract start and end dates are available for given Contact.
 * See CRM_Hrjobcontract_BAO_HRJobDetails::validateDates() method for details.
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_h_r_job_details_validatedates($params) {
  $validateDates = CRM_Hrjobcontract_BAO_HRJobDetails::validateDates($params);
  $result = array(
    'success' => $validateDates['result'],
    'message' => !empty($validateDates['message']) ? $validateDates['message'] : null,
  );
  return civicrm_api3_create_success($result, $params);
}