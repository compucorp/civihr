<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

class CRM_HRLeaveAndAbsences_Service_LeaveRequest {

  /**
   * Wraps the LeaveRequest BAO create method in order to perform some more
   * actions before/after calling it.
   *
   * @param array $params
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest|NULL
   */
  public function create($params, $validate = true) {
    $leaveRequest = LeaveRequest::create($params, $validate);
    $this->saveBalanceChanges($leaveRequest);

    return $leaveRequest;
  }

  /**
   * Deletes the LeaveRequest with the given $leaveRequestID, including all of
   * its LeaveRequestDates and LeaveBalanceChanges
   *
   * @param int $leaveRequestID
   */
  public function delete($leaveRequestID) {
    $leaveRequest = LeaveRequest::findById($leaveRequestID);

    $transaction = new CRM_Core_Transaction();
    try {
      LeaveBalanceChange::deleteAllForLeaveRequest($leaveRequest);
      LeaveRequestDate::deleteDatesForLeaveRequest($leaveRequest->id);
      $leaveRequest->delete();

      $transaction->commit();
    } catch(Exception $e) {
      $transaction->rollback();
    }
  }

  /**
   * Saves LeaveBalanceChange instances for each of the dates of the given
   * LeaveRequest
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   */
  private function saveBalanceChanges(LeaveRequest $leaveRequest) {
    $balanceChanges = $this->calculateBalanceChanges($leaveRequest);

    $dates = $leaveRequest->getDates();

    $balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id', 'validate'));
    foreach($dates as $date) {
      foreach($balanceChanges['breakdown'] as $balanceChange) {
        if($balanceChange['date'] == $date->date) {
          LeaveBalanceChange::create([
            'source_id' => $date->id,
            'source_type' => LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY,
            'amount' => $balanceChange['amount'],
            'type_id' => $balanceChangeTypes['debit']
          ]);
        }
      }
    }
  }

  /**
   * Calculates the balance changes for each of the LeaveRequest dates
   * @param $leaveRequest
   *
   * @return array
   */
  private function calculateBalanceChanges(LeaveRequest $leaveRequest) {
    return LeaveRequest::calculateBalanceChange(
      $leaveRequest->contact_id,
      new DateTime($leaveRequest->from_date),
      $leaveRequest->from_date_type,
      empty($leaveRequest->to_date) ? null : new DateTime($leaveRequest->to_date),
      $leaveRequest->to_date_type
    );
  }
}
