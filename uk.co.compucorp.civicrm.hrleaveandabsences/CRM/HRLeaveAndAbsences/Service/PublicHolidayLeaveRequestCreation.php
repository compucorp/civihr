<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;

class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation {

  public function createForContact($contactId, PublicHoliday $publicHoliday) {
    $absenceTypes = $this->getAbsenceTypesWherePublicHolidaysMustBeTakenAsLeave();
    foreach($absenceTypes as $absenceType) {
      $leaveRequest = $this->createLeaveRequest($contactId, $absenceType, $publicHoliday);
      $this->createLeaveBalanceChangeRecord($leaveRequest);
    }
  }

  private function getAbsenceTypesWherePublicHolidaysMustBeTakenAsLeave() {
    $allAbsenceTypes = AbsenceType::getEnabledAbsenceTypes();

    return array_filter($allAbsenceTypes, function(AbsenceType $absenceType) {
      return boolval($absenceType->must_take_public_holiday_as_leave);
    });
  }

  private function createLeaveRequest(
    $contactId,
    AbsenceType $absenceType,
    PublicHoliday $publicHoliday
  ) {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $leaveRequestDayTypes = array_flip(LeaveRequest::buildOptions('from_date_type'));

    return LeaveRequest::create([
      'contact_id'     => $contactId,
      'type_id'        => $absenceType->id,
      'status_id'      => $leaveRequestStatuses['Admin Approved'],
      'from_date'      => CRM_Utils_Date::processDate($publicHoliday->date),
      'from_date_type' => $leaveRequestDayTypes['All Day']
    ]);
  }

  private function createLeaveBalanceChangeRecord(LeaveRequest $leaveRequest) {
    $leaveBalanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id'));

    $dates = $leaveRequest->getDates();
    foreach($dates as $date) {
      $this->zeroDeductionForOverlappingLeaveRequestDate($leaveRequest, $date);

      LeaveBalanceChange::create([
        'source_id'   => $date->id,
        'source_type' => LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY,
        'type_id'     => $leaveBalanceChangeTypes['Public Holiday'],
        'amount'      => -1
      ]);
    }
  }

  private function zeroDeductionForOverlappingLeaveRequestDate(LeaveRequest $leaveRequest, LeaveRequestDate $leaveRequestDate) {
    $date = new DateTime($leaveRequestDate->date);

    $leaveBalanceChange = LeaveBalanceChange::getExistingBalanceChangeForALeaveRequestDate($leaveRequest, $date);

    if($leaveBalanceChange) {
      LeaveBalanceChange::create([
        'id' => $leaveBalanceChange->id,
        'amount' => 0
      ]);
    }
  }

}
