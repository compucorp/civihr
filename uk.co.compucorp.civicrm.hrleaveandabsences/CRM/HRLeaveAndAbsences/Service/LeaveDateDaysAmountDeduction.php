<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

/**
 * Class leaveDateDaysAmountDeduction
 */
class CRM_HRLeaveAndAbsences_Service_LeaveDateDaysAmountDeduction
  implements CRM_HRLeaveAndAbsences_Service_LeaveDateAmountDeduction {

  /**
   * @var array
   *   Stores day types that are half day types.
   */
  private static $halfDayTypes;

  /**
   * Calculate the amount to be deducted in days for a Leave Request
   * date using the work day information the leave date falls on.
   * For Leave Start and End Dates that have date types that are
   * half day types, 0.5 is deducted for such dates.
   *
   * @param \DateTime $leaveDateTime
   * @param array $workDay
   * @param CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return float
   */
  public function calculate(DateTime $leaveDateTime, $workDay, LeaveRequest $leaveRequest) {
    $isHalfDay = false;

    $workDayAmount = empty($workDay['leave_days']) ? 0 : (float)$workDay['leave_days'];

    if($leaveDateTime == new DateTime($leaveRequest->from_date)) {
      $isHalfDay = $this->isHalfDay($leaveRequest->from_date_type);
    }

    if($leaveDateTime == new DateTime($leaveRequest->to_date)){
      $isHalfDay = $this->isHalfDay($leaveRequest->to_date_type);
    }


    return $isHalfDay && $workDayAmount != 0 ? 0.5 : $workDayAmount;
	}

  /**
   * Returns whether a leave day type option value is an half day
   * type or not.
   *
   * @param string $optionValue
   *
   * @return bool
   */
  private function isHalfDay($optionValue) {
    if(empty(self::$halfDayTypes)) {
      $leaveRequestDayTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));

      $halfDayTypesValues = [
        $leaveRequestDayTypes['half_day_am'],
        $leaveRequestDayTypes['half_day_pm'],
      ];

      self::$halfDayTypes = $halfDayTypesValues;
    }

    return in_array($optionValue, self::$halfDayTypes);
  }
}
