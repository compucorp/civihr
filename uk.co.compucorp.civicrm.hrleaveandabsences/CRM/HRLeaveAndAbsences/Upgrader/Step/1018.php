<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_ContactWorkPattern as ContactWorkPatternService;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1018 {

  /**
   * As per PCHR-3164 end time can be chosen for single day leave requests
   * in hours calculation unit. However, existing such leave requests are
   * storing end time currently equal to start time. This upgrader fetches such
   * leave requests, retrieves information about work pattern day based on the
   * date of the leave request and, if it is still a working day, sets the end
   * time from the working day as an end time of the leave request.
   *
   * @return boolean
   */

  public function upgrade_1018 () {
    $calculationUnits = array_flip(AbsenceType::buildOptions('calculation_unit', 'validate'));

    $leaveRequestTable = LeaveRequest::getTableName();
    $absenceTypeTable = AbsenceType::getTableName();

    $query = "SELECT leaveRequest.id
      FROM {$leaveRequestTable} leaveRequest
      INNER JOIN {$absenceTypeTable} absenceType ON leaveRequest.type_id = absenceType.id
      WHERE absenceType.calculation_unit = {$calculationUnits['hours']}
      AND leaveRequest.from_date IS NOT NULL
      AND leaveRequest.from_date = leaveRequest.to_date
      AND leaveRequest.is_deleted = 0";

    $leaveRequestRecord = CRM_Core_DAO::executeQuery($query);
    $contactWorkPatternService = new ContactWorkPatternService();

    while ($leaveRequestRecord->fetch()) {
      $leaveRequest = LeaveRequest::findById($leaveRequestRecord->id);
      $workDay = $contactWorkPatternService->getContactWorkDayForDate(
        $leaveRequest->contact_id, new DateTime($leaveRequest->from_date));

      if (!empty($workDay['time_to'])) {
        $leaveRequest->to_date =
          substr($leaveRequest->from_date, 0, 10) . ' ' . $workDay['time_to'] . ':00';

        $leaveRequest->save();
      }
    };

    return true;
  }
}
