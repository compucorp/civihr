<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

class CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange {

  /**
   * Creates LeaveBalanceChange instances for each of the dates of the given
   * LeaveRequest
   */
  public function createForLeaveRequest(LeaveRequest $leaveRequest) {
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
      $leaveRequest->to_date_type
    );
  }
}
