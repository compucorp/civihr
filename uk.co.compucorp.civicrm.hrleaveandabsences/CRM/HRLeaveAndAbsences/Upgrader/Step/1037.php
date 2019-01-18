<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1037 {
  /**
   * Updates request type and sickness reason for sickness leave requests
   *
   * @return bool
   */
  public function upgrade_1037() {
    $sickAbsenceTypeId = $this->up1037_getSicknessAbsenceTypeId();
    if (count($sickAbsenceTypeId) === 0) {
      return TRUE;
    }

    $sicknessReasons = array_flip(LeaveRequest::buildOptions('sickness_reason', 'validate'));
    $otherSicknessReason = $sicknessReasons['other'];
    $sicknessLeaveRequests = $this->up1037_getSicknessLeaveRequests($sickAbsenceTypeId);

    foreach ($sicknessLeaveRequests as $sicknessLeaveRequest) {
      $sicknessLeaveRequest->request_type = LeaveRequest::REQUEST_TYPE_SICKNESS;
      $sicknessLeaveRequest->sickness_reason = $otherSicknessReason;
      $sicknessLeaveRequest->save(FALSE);
    }

    return TRUE;
  }

  /**
   * Retrieves ids of sickness absence type
   *
   * @return array
   */
  private function up1037_getSicknessAbsenceTypeId() {
    $sicknessIds = [];
    $absenceType = new AbsenceType();
    $absenceType->is_sick = 1;
    $absenceType->find();
    while ($absenceType->fetch()) {
      $sicknessIds[] = $absenceType->id;
    }

    return $sicknessIds;
  }

  /**
   * Retrieves sickness leave requests
   *
   * @param $sicknessIds
   *
   * @return array
   */
  private function up1037_getSicknessLeaveRequests($sicknessIds) {
    $leaveRequests = [];
    $leaveRequest = new LeaveRequest();
    $leaveRequest->whereAdd('request_type != "' . LeaveRequest::REQUEST_TYPE_SICKNESS . '"');
    $leaveRequest->whereAdd('type_id in (' . implode(',', $sicknessIds) . ')');
    $leaveRequest->find();
    while ($leaveRequest->fetch()) {
      $leaveRequests[] = clone $leaveRequest;
    }

    return $leaveRequests;
  }
}
