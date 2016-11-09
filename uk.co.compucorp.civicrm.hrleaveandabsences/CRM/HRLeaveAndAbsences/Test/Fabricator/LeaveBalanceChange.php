<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

class CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange {

  private static $balanceChangeTypes;

  public static function fabricate($params) {
    $params = array_merge(self::getDefaultParams(), $params);

    return LeaveBalanceChange::create($params);
  }

  public static function fabricateForLeaveRequestDate(LeaveRequestDate $date) {
    return self::fabricate([
      'source_id' => $date->id,
      'source_type' => LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY
    ]);
  }

  private static function getTypeId($typeLabel) {
    if(is_null(self::$balanceChangeTypes)) {
      self::$balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id'));
    }

    return self::$balanceChangeTypes[$typeLabel];
  }

  private static function getDefaultParams() {
    return [
      'amount' => -1,
      'type_id' => self::getTypeId('Leave')
    ];
  }
}
