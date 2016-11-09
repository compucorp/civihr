<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

class CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest {

  private static $leaveRequestStatuses;
  private static $dayTypes;

  public static function fabricate($params) {
    $params = self::mergeDefaultParams($params);

    return LeaveRequest::create($params);
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

}
