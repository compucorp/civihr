<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1023 {

  /**
   * Deletes Expired balance changes records that were wrongly expired by the
   * scheduled job responsible for creating balance expiry records.
   *
   * @return bool
   */
  public function upgrade_1023() {
    $today = date('Y-m-d');
    $leaveBalanceChangeTable  =  LeaveBalanceChange::getTableName();

    CRM_Core_DAO::executeQuery("
      DELETE FROM {$leaveBalanceChangeTable} 
      WHERE expired_balance_change_id IS NOT NULL
      AND expiry_date > '$today'");

    return TRUE;
  }
}
