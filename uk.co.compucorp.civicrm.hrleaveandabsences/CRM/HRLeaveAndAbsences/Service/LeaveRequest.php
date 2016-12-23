<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;

class CRM_HRLeaveAndAbsences_Service_LeaveRequest {

  /**
   * @var \LeaveBalanceChangeService
   */
  private $leaveBalanceChangeService;

  public function __construct(LeaveBalanceChangeService $leaveBalanceChangeService) {
    $this->leaveBalanceChangeService = $leaveBalanceChangeService;
  }

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
    $this->leaveBalanceChangeService->createForLeaveRequest($leaveRequest);

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
}
