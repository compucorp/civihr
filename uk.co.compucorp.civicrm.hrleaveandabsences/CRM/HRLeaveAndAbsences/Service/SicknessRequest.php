<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_SicknessRequest as SicknessRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveRequest as LeaveRequestService;

class CRM_HRLeaveAndAbsences_Service_SicknessRequest extends LeaveRequestService {

  /**
   * {@inheritDoc}
   */
  protected function canChangeDatesFor($params) {
    return true;
  }

  /**
   * This method creates/updates the SicknessRequest along with the
   * associated LeaveRequest and it's LeaveBalanceChanges
   *
   * @param array $params
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_SicknessRequest|NULL
   */
  protected function createRequestWithBalanceChanges($params) {
    $sicknessRequest = SicknessRequest::create($params, false);
    $leaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);
    $this->leaveBalanceChangeService->createForLeaveRequest($leaveRequest);

    return $sicknessRequest;
  }

  /**
   * Returns the LeaveRequest object associated with the SicknessRequest
   * in its current state (i.e before it gets updated)
   *
   * @param int $sicknessRequestID
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_LeaveRequest
   */
  protected function getOldLeaveRequest($sicknessRequestID) {
    $sicknessRequest = SicknessRequest::findById($sicknessRequestID);
    if (!$this->oldLeaveRequest) {
      $this->oldLeaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);
    }
    return $this->oldLeaveRequest;
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
    parent::delete($sicknessRequest->leave_request_id);
    $sicknessRequest->delete();
  }

  /**
   * {@inheritDoc}
   */
  protected function runValidation($params) {
    if (isset($params['id'])) {
      $params['leave_request_id'] = $this->getOldLeaveRequest($params['id'])->id;
    }

    SicknessRequest::validateParams($params);
  }
}
