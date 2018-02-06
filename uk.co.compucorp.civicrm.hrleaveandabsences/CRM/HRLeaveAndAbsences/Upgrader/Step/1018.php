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

    $absenceTypeTable = AbsenceType::getTableName();

    $query = "SELECT id FROM {$absenceTypeTable} WHERE calculation_unit = {$calculationUnits['hours']}";

    $result = CRM_Core_DAO::executeQuery($query)->fetchAll();
    $absenceTypesInHour = array_column($result, 'id');

    $leaveRequest = new LeaveRequest();
    $leaveRequest->whereAdd('from_date IS NOT NULL');
    $leaveRequest->whereAdd('from_date = to_date');
    $leaveRequest->whereAdd('is_deleted = 0');
    $leaveRequest->whereAdd('type_id IN (' . implode(',', $absenceTypesInHour) . ')');
    $leaveRequest->find();

    $contactWorkPatternService = new ContactWorkPatternService();

    while ($leaveRequest->fetch()) {
      $workDay = $contactWorkPatternService->getContactWorkDayForDate(
        $leaveRequest->contact_id, new DateTime($leaveRequest->from_date));

      if (!empty($workDay['time_to'])) {
        $leaveRequest->to_date =
          date("Y-m-d {$workDay['time_to']}:00", strtotime($leaveRequest->from_date));

        $leaveRequest->update();
      }
    }

    return true;
  }
}
