<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Factory_LeaveDateAmountDeduction as LeaveDateAmountDeductionFactory;

class CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange {

  /**
   * Creates LeaveBalanceChange instances for each of the dates of the given
   * LeaveRequest and sets the type property for the dates, according to the
   * values returned by the Balance Change calculation.
   *
   * @param CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   */
  public function createForLeaveRequest(LeaveRequest $leaveRequest) {
    LeaveBalanceChange::deleteAllForLeaveRequest($leaveRequest);

    if($leaveRequest->request_type == LeaveRequest::REQUEST_TYPE_TOIL) {
      $this->createForTOILRequest($leaveRequest);
      return;
    }

    $balanceChanges = $this->calculateBalanceChanges($leaveRequest);

    $dates = $leaveRequest->getDates();

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id', 'validate'));
    foreach($dates as $date) {
      foreach($balanceChanges['breakdown'] as $balanceChange) {
        if($balanceChange['date'] == $date->date) {
          LeaveBalanceChange::create([
            'source_id' => $date->id,
            'source_type' => LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY,
            'amount' => $balanceChange['amount'] * -1,
            'type_id' => $balanceChangeTypes['debit']
          ]);

          $date->type = $balanceChange['type']['value'];
          $date->save();
        }
      }
    }
  }

  /**
   * Calculates the balance changes for each of the LeaveRequest dates
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return array
   */
  private function calculateBalanceChanges(LeaveRequest $leaveRequest) {
    return LeaveRequest::calculateBalanceChange(
      $leaveRequest->contact_id,
      new DateTime($leaveRequest->from_date),
      $leaveRequest->from_date_type,
      !empty($leaveRequest->to_date) ? new DateTime($leaveRequest->to_date) : null,
      $leaveRequest->to_date_type,
      $leaveRequest->type_id
    );
  }

  /**
   * Creates LeaveBalanceChange for the given LeaveRequest of type toil.
   *
   * The toil_to_accrue will be store in the balance change linked to the first
   * day of the request. The same will happen to the toil_expiry_date. For all
   * the other dates, the amount will be 0 and the expiry_date will be null.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   */
  private function createForTOILRequest(LeaveRequest $leaveRequest) {
    $dates = $leaveRequest->getDates();
    $firstDate = array_shift($dates);

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id', 'validate'));
    LeaveBalanceChange::create([
      'type_id' => $balanceChangeTypes['credit'],
      'amount' => $leaveRequest->toil_to_accrue,
      'expiry_date' => $leaveRequest->toil_expiry_date,
      'source_id' => $firstDate->id,
      'source_type' => LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY
    ]);

    foreach($dates as $date) {
      LeaveBalanceChange::create([
        'type_id' => $balanceChangeTypes['credit'],
        'amount' => 0,
        'expiry_date' => null,
        'source_id' => $date->id,
        'source_type' => LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY
      ]);
    }
  }

  /**
   * Recalculates expired TOIL/Brought Forward balance changes for
   * a leave request with past dates having expired LeaveBalanceChanges that expired on or after the
   * LeaveRequest past date using the function provided by LeaveBalanceChange BAO
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   */
  public function recalculateExpiredBalanceChangesForLeaveRequestPastDates(LeaveRequest $leaveRequest) {
    LeaveBalanceChange::recalculateExpiredBalanceChangesForLeaveRequestPastDates($leaveRequest);
  }

  /**
   * This method uses calculateAmountForDate method of LeaveBalanceChange BAO to
   * calculates the amount to be deducted for a leave taken by the given contact
   * on the given date.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *  The LeaveRequest which the $date belongs to
   * @param \DateTime $date
   *
   * @return float
   */
  public function calculateAmountToBeDeductedForDate(LeaveRequest $leaveRequest, DateTime $date) {
    $dateDeductionFactory = LeaveDateAmountDeductionFactory::createForAbsenceType($leaveRequest->type_id);
    return LeaveBalanceChange::calculateAmountForDate($leaveRequest, $date, $dateDeductionFactory);
  }
}
