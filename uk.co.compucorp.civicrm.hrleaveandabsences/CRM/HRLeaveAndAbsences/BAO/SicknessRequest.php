<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Exception_InvalidSicknessRequestException as InvalidSicknessRequestException;

class CRM_HRLeaveAndAbsences_BAO_SicknessRequest extends CRM_HRLeaveAndAbsences_DAO_SicknessRequest {

  /**
   * Create a new SicknessRequest based on array-data
   *
   * @param array $params key-value pairs
   * @param boolean $validate
   *   Whether to allow validation or not
   *
   * @return CRM_HRLeaveAndAbsences_BAO_SicknessRequest|NULL
   **/
  public static function create($params, $validate = true) {
    $entityName = 'SicknessRequest';
    $hook = empty($params['id']) ? 'create' : 'edit';
    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);

    if ($validate) {
      self::validateParams($params);
    }
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

  /**
   * A method for validating the params passed to the SicknessRequest create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidSicknessRequestException
   */
  public static function validateParams($params) {
    self::validateMandatory($params);
  }

  /**
   * A method for validating the mandatory fields in the params
   * passed to the SicknessRequest create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidSicknessRequestException
   */
  private static function validateMandatory($params) {
    if (empty($params['reason'])) {
      throw new InvalidSicknessRequestException(
        'Sickness Requests should have a reason',
        'sickness_request_empty_reason',
        'reason'
      );
    }
  }
}
