<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveDaysBalanceChangeCalculation as LeaveDaysBalanceChangeCalculation;
use CRM_HRLeaveAndAbsences_Service_LeaveHoursBalanceChangeCalculation as LeaveHoursBalanceChangeCalculation;

class CRM_HRLeaveAndAbsences_Factory_LeaveBalanceChangeCalculation {

  /**
   * Returns a new instance of a Leave Balance Change Calculation class
   * extending from the parent LeaveBalanceChangeCalculation class based
   * on the calculation unit of the Leave Request's Absence Type.
   *
   * @param CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return CRM_HRLeaveAndAbsences_Service_LeaveBalanceChangeCalculation
   */
  public static function create(LeaveRequest $leaveRequest) {
    $isCalculationUnitInHours = AbsenceType::isCalculationUnitInHours($leaveRequest->type_id);

    if($isCalculationUnitInHours) {
      return new LeaveHoursBalanceChangeCalculation();
    }

    return new LeaveDaysBalanceChangeCalculation();
  }
}
