<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_TOILRequest as TOILRequest;

class CRM_HRLeaveAndAbsences_Test_Fabricator_TOILRequest {

  private static $leaveRequestStatuses;
  private static $toilAmounts;

  public static function fabricate($params) {
    $params = self::mergeDefaultParams($params);

    return TOILRequest::create($params);
  }

  /**
   * Creates a new Sickness Request without running any validation
   *
   * @param array $params
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_SicknessRequest
   */
  public static function fabricateWithoutValidation($params = []) {
    $params = self::mergeDefaultParams($params);

    return TOILRequest::create($params, false);
  }

  private static function mergeDefaultParams($params) {
    $defaultParams = [
      'status_id' => self::getStatusValue('Approved'),
      'toil_to_accrue' => self::getToilToAccrueValue('1 Day')
    ];

    return array_merge($defaultParams, $params);
  }

  private static function getStatusValue($statusLabel) {
    if(is_null(self::$leaveRequestStatuses)) {
      self::$leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    }

    return self::$leaveRequestStatuses[$statusLabel];
  }

  private static function getToilToAccrueValue($reasonLabel) {
    if(is_null(self::$toilAmounts)) {
      $result = civicrm_api3('OptionValue', 'get', ['option_group_id' => 'hrleaveandabsences_toil_amounts']);
      foreach($result['values'] as $value) {
        self::$toilAmounts[$value['label']] = $value['value'];
      }
    }

    return self::$toilAmounts[$reasonLabel];
  }
}
