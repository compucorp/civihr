<?php

/**
 * LeaveRequest.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_leave_request_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * LeaveRequest.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_leave_request_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * LeaveRequest.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_leave_request_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * LeaveRequest.get API specification
 *
 * @param array $spec
 */
function _civicrm_api3_leave_request_get_spec(&$spec) {
  $spec['public_holiday'] = [
    'name' => 'public_holiday',
    'title' => 'Include only Public Holiday Leave Requests?',
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'api.required' => 0,
  ];
}

/**
 * LeaveRequest.get API
 *
 * This API accepts a special param, named public_holiday. It does not map
 * directly to one of the LeaveRequests fields, but it can be used to make the
 * response include only Public Holiday Leave Requests. When it's not present,
 * or if it's false, the API will return all Leave Requests, except the Public
 * Holiday ones.
 *
 * @param array $params
 *
 * @return array API result descriptor
 *
 * @throws CiviCRM_API3_Exception
 */
function civicrm_api3_leave_request_get($params) {
  $query = new \Civi\API\SelectQuery(CRM_HRLeaveAndAbsences_BAO_LeaveRequest::class, $params, false);

  $balanceChangeJoinConditions = [
    'lbc.source_id = lrd.id',
    "lbc.source_type = '" . CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY . "'",
  ];

  $leaveBalanceChangeTypes = array_flip(CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange::buildOptions('type_id'));
  if(empty($params['public_holiday'])) {
    $balanceChangeJoinConditions[] = "lbc.type_id <> {$leaveBalanceChangeTypes['Public Holiday']}";
  } else {
    $balanceChangeJoinConditions[] = "lbc.type_id = {$leaveBalanceChangeTypes['Public Holiday']}";
  }

  $query->join(
    'INNER',
    CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate::getTableName(),
    'lrd',
    ['lrd.leave_request_id = a.id']
  );
  $query->join(
    'INNER',
    CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange::getTableName(),
    'lbc',
    $balanceChangeJoinConditions
  );

  return civicrm_api3_create_success($query->run(), $params, '', 'get');
}

/**
 * LeaveRequest.getBalanceChangeByAbsenceType API spec
 *
 * @param array $spec
 */
function _civicrm_api3_leave_request_getbalancechangebyabsencetype_spec(&$spec) {
  $spec['contact_id'] = [
    'name' => 'contact_id',
    'title' => 'Contact ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  ];

  $spec['period_id'] = [
    'name' => 'period_id',
    'title' => 'Absence Period ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  ];

  $spec['statuses'] = [
    'name' => 'statuses',
    'title' => 'Leave Request status',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 0,
  ];

  $spec['public_holiday'] = [
    'name' => 'public_holiday',
    'title' => 'Include only Public Holiday Leave Requests?',
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'api.required' => 0,
  ];
}

/**
 * LeaveRequest.getBalanceChangeByAbsenceType API
 *
 * Returns the total balance change for each
 *
 * @param array $params
 *  An array of params passed to the API
 *
 * @return array
 */
function civicrm_api3_leave_request_getbalancechangebyabsencetype($params) {
  $statuses = _civicrm_api3_leave_request_get_statuses_from_params($params);
  $publicHolidayOnly = empty($params['public_holiday']) ? false : true;

  $values = CRM_HRLeaveAndAbsences_BAO_LeaveRequest::getBalanceChangeByAbsenceType(
    $params['contact_id'],
    $params['period_id'],
    $statuses,
    $publicHolidayOnly
  );

  return civicrm_api3_create_success($values);
}

/**
 * Extracts the list of statuses from the $params array
 *
 * Currently, we only support the IN operator for passing an array of statuses.
 * Supporting other operators would be extremely complex and it would not even
 * make sense to support operators like >= and <.
 *
 * @param array $params
 *   The $params array passed to the LeaveRequest.getBalanceChangeByAbsenceType API
 *
 * @return array
 */
function _civicrm_api3_leave_request_get_statuses_from_params($params) {
  if(empty($params['statuses'])) {
    return [];
  }

  if(!is_array($params['statuses'])) {
    return [$params['statuses']];
  }

  if(!array_key_exists('IN', $params['statuses'])) {
    throw new InvalidArgumentException('The statuses parameter only supports the IN operator');
  }

  return $params['statuses']['IN'];
}
