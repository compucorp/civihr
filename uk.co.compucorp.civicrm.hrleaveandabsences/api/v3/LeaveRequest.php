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
 * Since this method uses the LeaveRequest service instead of the default
 * _civicrm_api3_basic_create function, we need to duplicate some of the code of
 * that function in order to make sure the $params array will be handled/validate
 * the same way and also to make sure the response will have the same format.
 *
 *
 * @param array $params
 *
 * @return array API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_leave_request_create($params) {
  $bao = _civicrm_api3_get_BAO(__FUNCTION__);
  _civicrm_api3_check_edit_permissions($bao, $params);
  _civicrm_api3_format_params_for_create($params, null);

  $leaveBalanceChangeService = new CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange();
  $service = new CRM_HRLeaveAndAbsences_Service_LeaveRequest($leaveBalanceChangeService);
  $leaveRequest = $service->create($params);
  $values = [];
  _civicrm_api3_object_to_array($leaveRequest, $values[$leaveRequest->id]);

  return civicrm_api3_create_success($values, $params, null, 'create', $leaveRequest);
}

/**
 * LeaveRequest.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_leave_request_delete($params) {
  $bao = _civicrm_api3_get_BAO(__FUNCTION__);
  civicrm_api3_verify_mandatory($params, NULL, array('id'));
  _civicrm_api3_check_edit_permissions($bao, array('id' => $params['id']));
  civicrm_api3_create_success(true);

  $leaveBalanceChangeService = new CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange();
  $service = new CRM_HRLeaveAndAbsences_Service_LeaveRequest($leaveBalanceChangeService);
  $service->delete($params['id']);

  return civicrm_api3_create_success(true);
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

  $spec['managed_by'] = [
    'name' => 'managed_by',
    'title' => 'Include only Leave Requests for contacts managed by the contact with the given ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 0,
    'FKClassName'  => 'CRM_Contact_DAO_Contact',
    'FKApiName'    => 'Contact',
  ];

}

/**
 * LeaveRequest.get API
 *
 * This API accepts some special params:
 *
 * - public_holiday: It does not map directly to one of the LeaveRequests
 * fields, but it can be used to make the response include only Public Holiday
 * Leave Requests. When it's not present, or if it's false, the API will return
 * all Leave Requests, except the Public Holiday ones.
 *
 * - managed_by: It's another filter which doesn't map directly to one of
 * the LeaveRequests fields. It accepts a contact ID and, when present, will
 * only return LeaveRequests of contacts managed by given contact ID.
 *
 * @param array $params
 *
 * @return array API result descriptor
 *
 * @throws CiviCRM_API3_Exception
 */
function civicrm_api3_leave_request_get($params) {
  $query = new CRM_HRLeaveAndAbsences_API_Query_LeaveRequestSelect($params);
  return civicrm_api3_create_success($query->run(), $params, '', 'get');
}

/**
 * LeaveRequest.getFull API specification
 *
 * @param array $spec
 */
function _civicrm_api3_leave_request_getfull_spec(&$spec) {
  $spec['public_holiday'] = [
    'name' => 'public_holiday',
    'title' => 'Include only Public Holiday Leave Requests?',
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'api.required' => 0,
  ];
}

/**
 * LeaveRequest.getFull API
 *
 * This API works exactly as LeaveRequest.get, but it will, for each returned
 * Leave Request, include the balance change and the Leave Request dates.
 *
 * @param array $params
 *
 * @return array API result descriptor
 *
 * @throws CiviCRM_API3_Exception
 */
function civicrm_api3_leave_request_getfull($params) {
  $query = new CRM_HRLeaveAndAbsences_API_Query_LeaveRequestSelect($params);
  $query->setReturnFullDetails(true);

  return civicrm_api3_create_success($query->run(), $params, '', 'getfull');
}

/**
 * LeaveRequest.calculateBalanceChange specification
 *
 * @param array $spec
 *
 * @return void
 */
function _civicrm_api3_leave_request_calculateBalanceChange_spec(&$spec) {
  $spec['contact_id'] = [
    'name' => 'contact_id',
    'title' => 'Contact ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1
  ];

  $spec['from_date'] = [
    'name' => 'from_date',
    'title' => 'Starting Day of the Leave Period',
    'type' => CRM_Utils_Type::T_DATE,
    'api.required' => 1
  ];

  $spec['from_type'] = [
    'name' => 'from_type',
    'title' => 'Starting Day Type',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 1
  ];

  $spec['to_date'] = [
    'name' => 'to_date',
    'title' => 'Ending Day of the Leave Period',
    'type' => CRM_Utils_Type::T_DATE,
    'api.required' => 0
  ];

  $spec['to_type'] = [
    'name' => 'to_type',
    'title' => 'Ending Day Type',
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => 0
  ];
}

/**
 * LeaveRequest.calculateBalanceChange API
 *
 * @param array $params
 *
 * @return array
 */
function civicrm_api3_leave_request_calculateBalanceChange($params) {
  $hasToDate = !empty($params['to_date']);
  $hasToType = !empty($params['to_type']);

  if (($hasToDate && !$hasToType) || ($hasToType && !$hasToDate)) {
    throw new InvalidArgumentException("to_date and to_type must be included together");
  }
  $toDate = !empty($params['to_date']) ? new DateTime($params['to_date']) : null;
  $toType = !empty($params['to_type']) ? $params['to_type'] : null;

  $result = CRM_HRLeaveAndAbsences_BAO_LeaveRequest::calculateBalanceChange(
    $params['contact_id'],
    new DateTime($params['from_date']),
    $params['from_type'],
    $toDate,
    $toType
  );
  return civicrm_api3_create_success($result);
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

/**
 * LeaveRequest.isValid API
 * This API runs the validation on the LeaveRequest BAO create method
 * without a call to the LeaveRequest create itself.
 *
 * @param array $params
 *  An array of params passed to the API
 *
 * @return array
 */
function civicrm_api3_leave_request_isvalid($params) {
  $result = [];

  try {
    CRM_HRLeaveAndAbsences_BAO_LeaveRequest::validateParams($params);
  }
  catch (CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException $e) {
    $result[$e->getField()] = [$e->getExceptionCode()];
  }

  return civicrm_api3_create_success($result);
}

/**
 * LeaveRequest.isManagedBy API spec
 *
 * @param array $spec
 */
function _civicrm_api3_leave_request_ismanagedby_spec(&$spec) {
  $spec['leave_request_id'] = [
    'name' => 'leave_request_id',
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Leave Request',
    'description' => 'The Leave Request to check if the contact is the manager of',
    'api.required' => 1
  ];

  $spec['contact_id'] = [
    'name' => 'contact_id',
    'type' => CRM_Utils_Type::T_INT,
    'title' => 'Contact',
    'description' => 'The contact to check if the Leave Request is managed by',
    'api.required' => 1,
    'FKClassName'  => 'CRM_Contact_DAO_Contact',
    'FKApiName'    => 'Contact',
  ];
}

/**
 * LeaveRequest.isManagedBy API
 *
 * Uses the LeaveManager service in order to check if the contact of the given
 * Leave Request is managed by the contact with the given contact_id.
 *
 * @see CRM_HRLeaveAndAbsences_Service_LeaveManager::isContactManagedBy()
 *
 * @param array $params
 *
 * @return array
 */
function civicrm_api3_leave_request_ismanagedby($params) {
  $leaveRequest = CRM_HRLeaveAndAbsences_BAO_LeaveRequest::findById($params['leave_request_id']);
  $leaveManagerService = new CRM_HRLeaveAndAbsences_Service_LeaveManager();

  $result = civicrm_api3_create_success($leaveManagerService->isContactManagedBy(
    $leaveRequest->contact_id,
    $params['contact_id'])
  );

  // When isContactManagedBy returns false, civicrm_api3_create_success will
  // consider no value was returned and will set count to 0. So we manually
  // set it to 1 here.
  $result['count'] = 1;

  return $result;
}
