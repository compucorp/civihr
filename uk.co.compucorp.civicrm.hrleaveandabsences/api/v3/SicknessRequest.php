<?php

/**
 * SicknessRequest.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_sickness_request_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * SicknessRequest.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_sickness_request_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * SicknessRequest.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_sickness_request_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * SicknessRequest.get API
 * This API also returns the associated LeaveRequest data along with the SicknessRequest.
 *
 * @param array $params
 *
 * @return array API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_sickness_request_get($params) {
  $getLeaveRequest = function (&$item) {
    $leaveRequest = CRM_HRLeaveAndAbsences_BAO_LeaveRequest::findById($item['leave_request_id']);
    $leaveRequestFieldValues = $leaveRequest->toArray();
    $item = array_merge($leaveRequestFieldValues, $item);
  };

  $result = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  if ($result['count'] > 0) {
    array_walk($result['values'], $getLeaveRequest);
  }

  return $result;
}

/**
 * SicknessRequest.isValid API
 * This API runs the validation on the SicknessRequest BAO create method
 * without a call to the SicknessRequest create itself.
 *
 * @param array $params
 *  An array of params passed to the API
 *
 * @return array
 */
function civicrm_api3_sickness_request_isvalid($params) {
  $result = [];

  try {
    CRM_HRLeaveAndAbsences_BAO_SicknessRequest::validateParams($params);
  }
  catch (CRM_HRLeaveAndAbsences_Exception_InvalidSicknessRequestException $e) {
    $result[$e->getField()] = [$e->getExceptionCode()];
  }

  return civicrm_api3_create_success($result);
}
