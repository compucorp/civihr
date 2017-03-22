<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Exception_OperationNotAllowedException as OperationNotAllowedException;

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
    LeaveRequest::deleteAllNonExpiredTOILRequestsForAbsenceType($absenceType->id, $startDate);
  }

  /**
   * Checks if an absenceType has ever been used for a leave request
   *
   * @param int $absenceTypeID
   *
   * @return boolean
   */
  public function absenceTypeHasEverBeenUsed($absenceTypeID) {
    if ($this->absenceTypeIsLinkedToLeaveRequest($absenceTypeID)) {
      return true;
    }

    return false;
  }

  /**
   * Deletes the AbsenceType with the given id.
   * Checks first to see if the absence type can be deleted or not.
   *
   * @param int $absenceTypeID
   *
   * @throws CRM_HRLeaveAndAbsences_Exception_OperationNotAllowedException
   */
  public function delete($absenceTypeID) {
    $absenceType = AbsenceType::findById($absenceTypeID);

    if ($this->absenceTypeHasEverBeenUsed($absenceType->id)) {
      throw new OperationNotAllowedException('Absence type cannot be deleted because it is linked to one or more leave requests');
    }

    if($absenceType->is_reserved) {
      throw new OperationNotAllowedException('Reserved types cannot be deleted!');
    }

    AbsenceType::del($absenceType->id);
  }

  /**
   * Checks if an absenceType is linked to at least one leave request
   *
   * @param int $absenceTypeID
   *
   * @return boolean
   */
  private function absenceTypeIsLinkedToLeaveRequest($absenceTypeID) {
    $leaveRequest = new LeaveRequest();
    $leaveRequest->type_id = $absenceTypeID;
    $leaveRequest->find();

    if ($leaveRequest->N > 0) {
      return true;
    }

    return false;
  }
}
