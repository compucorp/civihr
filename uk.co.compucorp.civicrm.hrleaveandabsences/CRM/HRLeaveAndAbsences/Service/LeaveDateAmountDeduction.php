<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveDateAmountDeduction
 */
abstract class CRM_HRLeaveAndAbsences_Service_LeaveDateAmountDeduction {

  /**
   * Calculates the amount to be deducted for a leave date
   *
   * @param \DateTime $leaveDateTime
   * @param array $workDay
   * @param CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return float
   */
  abstract public function calculate(DateTime $leaveDateTime, $workDay, LeaveRequest $leaveRequest);
}
