<?php

/**
 * TOILRequest.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_t_o_i_l_request_create_spec(&$spec) {
  $spec['type_id'] = [
    'name'         => 'type_id',
    'type'         => CRM_Utils_Type::T_INT,
    'description'  => 'FK to AbsenceType',
    'api.required' => TRUE,
    'FKClassName'  => 'CRM_HRLeaveAndAbsences_DAO_AbsenceType',
    'FKApiName'    => 'AbsenceType',
  ];

  $spec['contact_id'] = [
    'name'         => 'contact_id',
    'type'         => CRM_Utils_Type::T_INT,
    'description'  => 'FK to Contact',
    'api.required' => TRUE,
    'FKClassName'  => 'CRM_Contact_DAO_Contact',
    'FKApiName'    => 'Contact',
  ];

  $spec['status_id'] = array(
    'name'           => 'status_id',
    'type'           => CRM_Utils_Type::T_INT,
    'description'    => 'One of the values of the Leave Request Status option group',
    'api.required'   => TRUE,
    'pseudoconstant' => [
      'optionGroupName' => 'hrleaveandabsences_leave_request_status',
      'optionEditPath'  => 'civicrm/admin/options/hrleaveandabsences_leave_request_status'
    ]
  );

  $spec['from_date'] = [
    'name'         => 'from_date',
    'type'         => CRM_Utils_Type::T_DATE,
    'title'        => ts('From Date'),
    'description'  => 'The date the leave request starts.',
    'api.required' => TRUE,
  ];

  $spec['from_date_type'] = [
    'name'           => 'from_date_type',
    'type'           => CRM_Utils_Type::T_INT,
    'title'          => ts('From Date Type'),
    'description'    => 'One of the values of the Leave Request Day Type option group',
    'api.required'   => TRUE,
    'pseudoconstant' => [
      'optionGroupName' => 'hrleaveandabsences_leave_request_day_type',
      'optionEditPath'  => 'civicrm/admin/options/hrleaveandabsences_leave_request_day_type',
    ]
  ];

  $spec['to_date'] = [
    'name'        => 'to_date',
    'type'        => CRM_Utils_Type::T_DATE,
    'title'       => ts('To Date'),
    'description' => 'The date the leave request ends. If null, it means is starts and ends at the same date',
  ];

  $spec['to_date_type'] = [
    'name'           => 'to_date_type',
    'type'           => CRM_Utils_Type::T_INT,
    'title'          => ts('To Date Type'),
    'description'    => 'One of the values of the Leave Request Day Type option group',
    'pseudoconstant' => [
      'optionGroupName' => 'hrleaveandabsences_leave_request_day_type',
      'optionEditPath'  => 'civicrm/admin/options/hrleaveandabsences_leave_request_day_type',
    ]
  ];

  $spec['leave_request_id'] = [
    'api.required' => 0
  ];

  $spec['expiry_date'] = [
    'name' => 'expiry_date',
    'type' => CRM_Utils_Type::T_DATE,
    'title' => ts('Expiry Date'),
    'description' => 'The date when the accrued TOIL will expire, if not used',
    'api.required' => false
  ];
}

/**
 * TOILRequest.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_t_o_i_l_request_create($params) {
  $result = _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  if ($result['count'] > 0) {
    array_walk($result['values'], '_civicrm_api3_t_o_i_l_request_merge_with_leave_request');
  }

  return $result;
}

/**
 * TOILRequest.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_t_o_i_l_request_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

function _civicrm_api3_t_o_i_l_request_get_spec(&$spec) {
  $spec['expired'] = [
    'name'         => 'expired',
    'type'         => CRM_Utils_Type::T_BOOLEAN,
    'title'        => ts('Expired?'),
    'description'  => ts('When true, only expired TOIL Requests will be returned. Otherwise, only the non-expired ones will be returned'),
    'api.required' => FALSE
  ];
}

/**
 * TOILRequest.get API
 * This API also returns the associated LeaveRequest data along with the TOILRequest.
 *
 * @param array $params
 *
 * @return array API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_t_o_i_l_request_get($params) {
  $query = new CRM_HRLeaveAndAbsences_API_Query_TOILRequestSelect($params);
  $result = civicrm_api3_create_success($query->run(), $params, '', 'get');

  if ($result['count'] > 0) {
    array_walk($result['values'], '_civicrm_api3_t_o_i_l_request_merge_with_leave_request');
  }

  return $result;
}

/**
 * TOILRequest.isValid API
 * This API runs the validation on the TOILRequest BAO create method
 * without a call to the TOILRequest create itself.
 *
 * @param array $params
 *  An array of params passed to the API
 *
 * @return array
 */
function civicrm_api3_t_o_i_l_request_isvalid($params) {
  $result = [];

  try {
    CRM_HRLeaveAndAbsences_BAO_TOILRequest::validateParams($params);
  }
  catch (CRM_HRLeaveAndAbsences_Exception_EntityValidationException $e) {
    $result[$e->getField()] = [$e->getExceptionCode()];
  }

  $results =  civicrm_api3_create_success($result);
  if (isset($results['id'])) {
    unset($results['id']);
  }

  return $results;
}

/**
 * Helper method to fetch the LeaveRequest associated with a TOILRequest and
 * merge it into a TOILRequest array returned by the API
 *
 * @param array $item
 */
function _civicrm_api3_t_o_i_l_request_merge_with_leave_request(&$item) {
  $leaveRequest = CRM_HRLeaveAndAbsences_BAO_LeaveRequest::findById($item['leave_request_id']);
  $leaveRequestFieldValues = $leaveRequest->toArray();
  $item = array_merge($leaveRequestFieldValues, $item);
}
