<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

/**
 * Interface CRM_HRLeaveAndAbsences_Service_LeaveBalanceChangeCalculation
 */
interface CRM_HRLeaveAndAbsences_Service_LeaveBalanceChangeCalculation {

  /**
   * Returns the balance change amount for a leave request
   * date
   *
   * @param CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   * @param DateTime $leaveDate
   * @param array $balanceChanges
   *   An array containing balance changes for the
   *   Leave Request dates.
   *
   * @return float
   */
  public function getAmount(LeaveRequest $leaveRequest, DateTime $leaveDate, $balanceChanges);
}
