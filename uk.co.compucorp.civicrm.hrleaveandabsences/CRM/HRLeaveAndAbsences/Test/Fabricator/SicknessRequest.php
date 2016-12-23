<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_SicknessRequest as SicknessRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange as LeaveBalanceChangeFabricator;

class CRM_HRLeaveAndAbsences_Test_Fabricator_SicknessRequest {

  private static $leaveRequestStatuses;
  private static $dayTypes;
  private static $sicknessReasons;

  public static function fabricate($params, $withBalanceChanges = false) {
    $params = self::mergeDefaultParams($params);

    $sicknessRequest = SicknessRequest::create($params);
    if($withBalanceChanges) {
      $leaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);
      foreach($leaveRequest->getDates() as $date) {
        LeaveBalanceChangeFabricator::fabricateForLeaveRequestDate($date);
      }
    }

    return $sicknessRequest;
  }

  /**
   * Creates a new Sickness Request without running any validation
   *
   * @param array $params
   * @param boolean $withBalanceChanges
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_SicknessRequest
   */
  public static function fabricateWithoutValidation($params = [], $withBalanceChanges = false) {
    $params = self::mergeDefaultParams($params);
    $sicknessRequest =  SicknessRequest::create($params, false);

    if ($withBalanceChanges) {
      $leaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);
      foreach ($leaveRequest->getDates() as $date) {
        LeaveBalanceChangeFabricator::fabricateForLeaveRequestDate($date);
      }
    }

    return $sicknessRequest;
  }

  private static function mergeDefaultParams($params) {
    $defaultParams = [
      'status_id' => self::getStatusId('Approved'),
      'from_date_type' => self::getDayTypeId('All Day'),
      'reason' => self::getSicknessReasonId('Accident')
    ];

    if(!empty($params['to_date']) && empty($params['to_date_type'])) {
      $defaultParams['to_date_type'] = self::getDayTypeId('All Day');
    }

    return array_merge($defaultParams, $params);
  }

  private static function getStatusId($statusLabel) {
    if(is_null(self::$leaveRequestStatuses)) {
      self::$leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    }

    return self::$leaveRequestStatuses[$statusLabel];
  }

  private static function getDayTypeId($typeLabel) {
    if(is_null(self::$dayTypes)) {
      self::$dayTypes = array_flip(LeaveRequest::buildOptions('from_date_type'));
    }

    return self::$dayTypes[$typeLabel];
  }

  private static function getSicknessReasonId($reasonLabel) {
    if(is_null(self::$sicknessReasons)) {
      self::$sicknessReasons = array_flip(SicknessRequest::buildOptions('reason'));
    }

    return self::$sicknessReasons[$reasonLabel];
  }
}
