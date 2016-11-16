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
   * Deletes all the Public Holiday Leave Requests for Public Holidays in the
   * future
   */
  public function deleteAllInTheFuture() {
    $futurePublicHolidays = PublicHoliday::getAllInFuture();
    $lastPublicHoliday = end($futurePublicHolidays);

    $contracts = $this->getContractsForPeriod(
      new DateTime(),
      new DateTime($lastPublicHoliday->date)
    );

    foreach($contracts as $contract) {
      foreach($futurePublicHolidays as $publicHoliday) {
        $this->deleteForContact($contract['contact_id'], $publicHoliday);
      }
    }
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

  /**
   * Gets all the contracts overlapping the given $startDate and $endDate
   *
   * @param \DateTime $startDate
   * @param \DateTime $endDate
   *
   * @return mixed
   */
  private function getContractsForPeriod(DateTime $startDate, DateTime $endDate) {
    $result = civicrm_api3('HRJobContract', 'getcontractswithdetailsinperiod', [
      'start_date' => $startDate->format('Y-m-d'),
      'end_date'   => $endDate->format('Y-m-d'),
      'sequential' => 1
    ]);

    return $result['values'];
  }

}
