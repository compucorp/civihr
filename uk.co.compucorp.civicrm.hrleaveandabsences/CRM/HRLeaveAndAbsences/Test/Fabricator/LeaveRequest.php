<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange as LeaveBalanceChangeFabricator;

class CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest {

  private static $leaveRequestStatuses;
  private static $dayTypes;

  public static function fabricate($params, $withBalanceChanges = false) {
    $params = self::mergeDefaultParams($params);

    $leaveRequest = LeaveRequest::create($params);
    if($withBalanceChanges) {
      LeaveBalanceChangeFabricator::fabricateForLeaveRequest($leaveRequest);
    }

    return $leaveRequest;
  }

  /**
   * Creates a new Leave Request without running any validation
   *
   * @param array $params
   * @param boolean $withBalanceChanges
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest
   */
  public static function fabricateWithoutValidation($params = [], $withBalanceChanges = false) {
    $params = self::mergeDefaultParams($params);
    $leaveRequest =  LeaveRequest::create($params, LeaveRequest::VALIDATIONS_OFF);

    if ($withBalanceChanges) {
      LeaveBalanceChangeFabricator::fabricateForLeaveRequest($leaveRequest);
    }

    return $leaveRequest;
  }

  private static function mergeDefaultParams($params) {
    $absenceType = AbsenceType::findById($params['type_id']);
    $isCalculationUnitInHours = $absenceType->isCalculationUnitInHours();
    $isTOIL = isset($params['request_type']) && $params['request_type'] === LeaveRequest::REQUEST_TYPE_TOIL;

    $defaultParams = [
      'type_id' => $absenceType->id,
      'status_id' => self::getStatusId('approved'),
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ];

    if (!$isCalculationUnitInHours) {
      $defaultParams['from_date_type'] = self::getDayTypeId('all_day');

      if (!empty($params['to_date']) && empty($params['to_date_type'])) {
        $defaultParams['to_date_type'] = self::getDayTypeId('all_day');
      }
    } else {
      $defaultParams['from_date_amount'] = 1;
      $defaultParams['to_date_amount'] = 1;
    }

    if ($isTOIL) {
      $defaultParams['toil_duration'] = 1;
      $defaultParams['toil_to_accrue'] = 1;
    }

    return array_merge($defaultParams, $params);
  }

  private static function getStatusId($statusName) {
    if(is_null(self::$leaveRequestStatuses)) {
      self::$leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));
    }

    return self::$leaveRequestStatuses[$statusName];
  }

  private static function getDayTypeId($typeName) {
    if(is_null(self::$dayTypes)) {
      self::$dayTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));
    }

    return self::$dayTypes[$typeName];
  }
}
