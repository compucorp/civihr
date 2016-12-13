<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

class CRM_HRLeaveAndAbsences_BAO_SicknessRequest extends CRM_HRLeaveAndAbsences_DAO_SicknessRequest {

  /**
   * Create a new SicknessRequest based on array-data
   *
   * @param array $params key-value pairs
   *
   * @return CRM_HRLeaveAndAbsences_BAO_SicknessRequest|NULL
   **/
  public static function create($params, $validate = true) {
    $entityName = 'SicknessRequest';
    $hook = empty($params['id']) ? 'create' : 'edit';
    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();

    if ($hook == 'edit') {
      $instance->id = $params['id'];
      $instance->find(true);

      if ($instance->leave_request_id) {
        $instance->copyValues($params);
        $params['id'] = $instance->leave_request_id;
        LeaveRequest::create($params, $validate);
        $instance->save();
      }
    }

    if ($hook == 'create') {
      $leaveRequest = LeaveRequest::create($params, $validate);
      $instance->copyValues($params);
      $instance->leave_request_id = $leaveRequest->id;
      $instance->save();
    }

    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }
}
