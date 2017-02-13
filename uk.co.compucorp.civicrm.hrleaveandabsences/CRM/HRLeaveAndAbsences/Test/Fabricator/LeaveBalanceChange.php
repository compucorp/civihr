<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_TOILRequest as TOILRequest;

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

  public static function fabricateForTOIL(TOILRequest $toilRequest, $toilToAccrue, DateTime $expiryDate = null) {
    //delete any existing TOIL LeaveBalanceChanges
    self::deleteForTOIL($toilRequest);

    return self::fabricate([
      'type_id' => self::getTypeId('Credit'),
      'amount' => $toilToAccrue,
      'source_id' => $toilRequest->id,
      'expiry_date' => $expiryDate ? $expiryDate->format('Ymd') : null,
      'source_type' => LeaveBalanceChange::SOURCE_TOIL_REQUEST
    ]);
  }

  private static function deleteForTOIL(TOILRequest $toilRequest) {
    $dao = new LeaveBalanceChange();
    $dao->source_id = $toilRequest->id;
    $dao->source_type = LeaveBalanceChange::SOURCE_TOIL_REQUEST;
    $dao->delete();
  }
}
