<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_SicknessRequest as SicknessRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequest as LeaveRequestService;

class CRM_HRLeaveAndAbsences_Service_SicknessRequest {

  /**
   * @var \LeaveBalanceChangeService
   */
  private $leaveBalanceChangeService;

  /**
   * @var \LeaveRequestService
   */
  private $leaveRequestService;

  public function __construct(
    LeaveBalanceChangeService $leaveBalanceChangeService,
    LeaveRequestService $leaveRequestService
  ) {
    $this->leaveBalanceChangeService = $leaveBalanceChangeService;
    $this->leaveRequestService = $leaveRequestService;
  }

  /**
   * Wraps the SicknessRequest BAO create method in order to perform some more
   * actions before/after calling it.
   *
   * @param array $params
   * @param bool $validate
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_SicknessRequest
   */
  public function create($params, $validate = true) {
    $sicknessRequest = SicknessRequest::create($params, $validate);
    $leaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);
    $this->leaveBalanceChangeService->createForLeaveRequest($leaveRequest);

    return $sicknessRequest;
  }

  /**
   * Deletes the SicknessRequest with the given $sicknessRequestID, including
   * its associated LeaveRequest and all of its LeaveRequestDates and
   * LeaveBalanceChanges
   *
   * @param int $sicknessRequestID
   */
  public function delete($sicknessRequestID) {
    $sicknessRequest = SicknessRequest::findById($sicknessRequestID);
    try {
      $transaction = new CRM_Core_Transaction();

      $this->leaveRequestService->delete($sicknessRequest->leave_request_id);
      $sicknessRequest->delete();

      $transaction->commit();
    } catch(Exception $e) {
      $transaction->rollback();
    }
  }

}
