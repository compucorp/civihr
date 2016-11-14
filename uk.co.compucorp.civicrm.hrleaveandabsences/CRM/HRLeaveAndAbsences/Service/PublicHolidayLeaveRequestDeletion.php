<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion {

  /**
   * Deletes the Public Holiday Leave Request for the contact and Public Holiday.
   *
   * If there are LeaveRequestDates overlapping the public holiday, their
   * balance change amount will be updated to no be 0 anymore.
   *
   * @param int $contactID
   * @param \CRM_HRLeaveAndAbsences_BAO_PublicHoliday $publicHoliday
   */
  public function deleteForContact($contactID, PublicHoliday $publicHoliday) {
    $leaveRequest = LeaveRequest::findPublicHolidayLeaveRequest($contactID, $publicHoliday);

    if(!$leaveRequest) {
      return;
    }

    foreach($leaveRequest->getDates() as $date) {
      LeaveBalanceChange::deleteForLeaveRequestDate($date);
      $this->recalculateDeductionForOverlappingLeaveRequestDate($leaveRequest, new DateTime($date->date));
      $date->delete();
    }

    $leaveRequest->delete();
  }

  /**
   * First, searches for an existing balance change for the same contact and absence
   * type of the given $leaveRequest and linked to a LeaveRequestDate with the
   * same date as $date. Next, if such balance change exists, update
   * it's amount to using the Work Pattern assigned to the contact or the default
   * one, if the contact has no work patterns.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   * @param \DateTime $date
   */
  private function recalculateDeductionForOverlappingLeaveRequestDate(LeaveRequest $leaveRequest, DateTime $date) {
    $leaveBalanceChange = LeaveBalanceChange::getExistingBalanceChangeForALeaveRequestDate($leaveRequest, $date);

    if($leaveBalanceChange) {
      $deduction = LeaveBalanceChange::calculateAmountForDate(
        $leaveRequest,
        $date
      );

      LeaveBalanceChange::create([
        'id' => $leaveBalanceChange->id,
        'amount' => $deduction
      ]);
    }
  }

}
