<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveBalanceChangeCalculation
 */
abstract class CRM_HRLeaveAndAbsences_Service_LeaveBalanceChangeCalculation {

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
  abstract public function getAmount(LeaveRequest $leaveRequest, DateTime $leaveDate, $balanceChanges);
}
