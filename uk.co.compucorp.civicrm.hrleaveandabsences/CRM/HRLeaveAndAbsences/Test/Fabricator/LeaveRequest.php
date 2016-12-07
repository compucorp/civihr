<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange as LeaveBalanceChangeFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

class CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest {

  private static $leaveRequestStatuses;
  private static $dayTypes;

  public static function fabricate($params, $withBalanceChanges = false) {
    $params = self::mergeDefaultParams($params);

    $leaveRequest = LeaveRequest::create($params);
    if($withBalanceChanges) {
      foreach($leaveRequest->getDates() as $date) {
        LeaveBalanceChangeFabricator::fabricateForLeaveRequestDate($date);
      }
    }

    return $leaveRequest;
  }

  private static function mergeDefaultParams($params) {
    $defaultParams = [
      'status_id' => self::getStatusId('Approved'),
      'from_date_type' => self::getDayTypeId('All Day'),
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

  /**
   * Creates a new Leave Request without running any validation. That is,
   * the leave request is created without calling the create() method.
   *
   * @param array $params
   * @param boolean $withBalanceChanges
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest
   */
  public static function fabricateWithoutValidation($params = [], $withBalanceChanges = false) {
    $leaveRequestFields = LeaveRequest::fields();
    $leaveRequest = new LeaveRequest();
    foreach($params as $field => $value) {
      if(!array_key_exists($field, $leaveRequestFields)) {
        continue;
      }
      $leaveRequest->$field = $value;
    }
    $leaveRequest->save();

    LeaveRequestDate::deleteDatesForLeaveRequest($leaveRequest->id);
    $startDate = new DateTime($leaveRequest->from_date);

    if (!$leaveRequest->to_date) {
      $endDate = new DateTime($leaveRequest->from_date);
    }
    else {
      $endDate = new DateTime($leaveRequest->to_date);
    }
    // We need to add 1 day to the end date to include it
    // when we loop through the DatePeriod
    $endDate->modify('+1 day');

    $interval   = new DateInterval('P1D');
    $datePeriod = new DatePeriod($startDate, $interval, $endDate);

    foreach ($datePeriod as $date) {
      LeaveRequestDate::create([
        'date' => $date->format('YmdHis'),
        'leave_request_id' => $leaveRequest->id
      ]);
    }

    if ($withBalanceChanges) {
      foreach ($leaveRequest->getDates() as $date) {
        LeaveBalanceChangeFabricator::fabricateForLeaveRequestDate($date);
      }
    }

    return $leaveRequest;
  }
}
