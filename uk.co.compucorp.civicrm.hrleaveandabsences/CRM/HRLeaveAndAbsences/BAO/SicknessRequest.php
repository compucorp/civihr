<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Exception_InvalidSicknessRequestException as InvalidSicknessRequestException;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

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

    $instance = new self();
    $sicknessRequestParams = self::parseSicknessRequestParams($params);
    $leaveRequestParams = self::parseLeaveRequestParams($params);

    if ($hook == 'edit') {
      $instance->id = $sicknessRequestParams['id'];
      $instance->find(true);
      if ($instance->leave_request_id) {
        $sicknessRequestParams['leave_request_id'] = $instance->leave_request_id;
        $leaveRequestParams['id'] = $instance->leave_request_id;
      }
    }
    if ($validate) {
      $params['leave_request_id'] = $instance->leave_request_id;
      self::validateParams($params);
    }

    $instance->copyValues($sicknessRequestParams);

    $leaveRequestParams['request_type'] = LeaveRequest::REQUEST_TYPE_SICKNESS;
    $leaveRequestParams['sickness_reason'] = $params['reason'];
    $leaveRequestInstance = LeaveRequest::create($leaveRequestParams, false);

    $instance->leave_request_id = $leaveRequestInstance->id;

    $instance->save();
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
    self::validateAbsenceTypeAllowsSicknessRequest($params);

    //run LeaveRequest Validation after all validations on Sickness Request
    if (isset($params['leave_request_id'])) {
      $params['id'] = $params['leave_request_id'];
    }
    LeaveRequest::validateParams($params);
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

  /**
   * A method for validating that the absence type allows sickness request
   * before allowing a sickness request to be created/updated
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRLeaveAndAbsences_Exception_InvalidSicknessRequestException
   */
  private static function validateAbsenceTypeAllowsSicknessRequest($params) {
    if (empty($params['type_id'])) {
      return;
    }

    $absenceType = AbsenceType::findById($params['type_id']);
    if (!$absenceType->is_sick) {
      throw new InvalidSicknessRequestException(
        'This absence does not allow sickness request',
        'sickness_request_absence_type_does_not_allow_sickness_request',
        'type_id'
      );
    }
  }

  /**
   * Parses SicknessRequest related parameters from the params array received
   * by the create method.
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @return array
   */
  private static function parseSicknessRequestParams($params) {
    return array_intersect_key($params, self::fields());
  }

  /**
   * Parses LeaveRequest related parameters from the params array received
   * by the create method.
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @return array
   */
  private static function parseLeaveRequestParams($params) {
    return array_intersect_key($params, LeaveRequest::fields());
  }
}
