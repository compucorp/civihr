<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_TOILRequest as TOILRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveRequest as LeaveRequestService;

class CRM_HRLeaveAndAbsences_Service_TOILRequest extends LeaveRequestService {

  /**
   * Returns the LeaveRequest object associated with the TOILRequest
   * in its current state (i.e before it gets updated)
   *
   * @param int $toilRequestID
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest
   */
  protected function getOldLeaveRequest($toilRequestID) {
    $toilRequest = TOILRequest::findById($toilRequestID);
    if (!$this->oldLeaveRequest) {
      $this->oldLeaveRequest = LeaveRequest::findById($toilRequest->leave_request_id);
    }
    return $this->oldLeaveRequest;
  }

  /**
   * Deletes the TOILRequest with the given $toilRequestID, including
   * its associated LeaveRequest and all of its LeaveRequestDates and
   * TOIL LeaveBalanceChanges
   *
   * @param int $toilRequestID
   */
  public function delete($toilRequestID) {
    $toilRequest = TOILRequest::findById($toilRequestID);
    parent::delete($toilRequest->leave_request_id);
    $toilRequest->delete();
  }

  /**
   * {@inheritDoc}
   */
  protected function runValidation($params) {
    if (isset($params['id'])) {
      $params['leave_request_id'] = $this->getOldLeaveRequest($params['id'])->id;
    }

    TOILRequest::validateParams($params);
  }
}
