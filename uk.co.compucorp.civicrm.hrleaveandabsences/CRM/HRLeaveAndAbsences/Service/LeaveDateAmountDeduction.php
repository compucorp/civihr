<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

/**
 * Interface CRM_HRLeaveAndAbsences_Service_LeaveDateAmountDeduction
 */
interface CRM_HRLeaveAndAbsences_Service_LeaveDateAmountDeduction {

  /**
   * Calculates the amount to be deducted for a leave date
   *
   * @param \DateTime $leaveDateTime
   * @param array $workDay
   * @param CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return float
   */
  public function calculate(DateTime $leaveDateTime, $workDay, LeaveRequest $leaveRequest);
}
