<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_TOILRequest as TOILRequest;

class CRM_HRLeaveAndAbsences_Service_AbsenceType {

  /**
   * Actions that occurs when an AbsenceType is updated
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsenceType $absenceType
   */
  public function postUpdateActions(AbsenceType $absenceType) {
    $oldAbsenceType = $absenceType->oldAbsenceType;
    if ($oldAbsenceType->allow_accruals_request == true && $absenceType->allow_accruals_request == false) {
      $this->onToilDisable($absenceType);
    }
  }

  /**
   * The set of actions/updates that occurs when TOIL is disabled for an AbsenceType.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_AbsenceType $absenceType
   */
  private function onToilDisable(AbsenceType $absenceType) {
    $currentPeriod = AbsencePeriod::getCurrentPeriod();
    $startDate = new DateTime($currentPeriod->start_date);
    TOILRequest::deleteAllForAbsenceType($absenceType->id, $startDate, null, true);
  }
}
