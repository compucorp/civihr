<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_Service_LeaveDateHoursAmountDeduction as LeaveDateHoursAmountDeduction;
use CRM_HRLeaveAndAbsences_Service_LeaveDateDaysAmountDeduction as LeaveDateDaysAmountDeduction;

class CRM_HRLeaveAndAbsences_Factory_LeaveDateAmountDeduction {

  /**
   * Returns a new instance of a Leave Date Deduction class extending from the parent
   * LeaveDateAmountDeduction class based on the calculation unit of the Absence Type.
   *
   * @param int $absenceTypeID
   *
   * @return CRM_HRLeaveAndAbsences_Service_LeaveDateAmountDeduction
   */
  public static function createForAbsenceType($absenceTypeID) {
    $isCalculationUnitInHours = AbsenceType::isCalculationUnitInHours($absenceTypeID);

    if($isCalculationUnitInHours) {
      return new LeaveDateHoursAmountDeduction();
    }

    return new LeaveDateDaysAmountDeduction();
  }
}
