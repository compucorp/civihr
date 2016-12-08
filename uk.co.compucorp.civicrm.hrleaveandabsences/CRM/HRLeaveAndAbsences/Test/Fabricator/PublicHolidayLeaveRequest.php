<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_Service_JobContract as JobContractService;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation as PublicHolidayLeaveRequestCreation;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange as LeaveBalanceChangeFabricator;

class CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHolidayLeaveRequest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  /**
   * This Fabricator is a bit different than the others, because a Public Holiday
   * Leave Request is more of a concept than an actual entity on the system.
   *
   * For that reason, the fabricate method expected a defined set of parameters,
   * including a Public Holiday instance, differently of the other fabricators,
   * where one would pass a $params array.
   *
   * @param int $contactID
   * @param \CRM_HRLeaveAndAbsences_BAO_PublicHoliday $publicHoliday
   */
  public static function fabricate($contactID, PublicHoliday $publicHoliday) {
    $creationLogic = new PublicHolidayLeaveRequestCreation(new JobContractService());
    $creationLogic->createForContact($contactID, $publicHoliday);
  }

  public static function fabricateWithoutValidation($contactID, PublicHoliday $publicHoliday) {

    $existingLeaveRequest = LeaveRequest::findPublicHolidayLeaveRequest($contactID, $publicHoliday);
    if($existingLeaveRequest) {
      return;
    }

    $publicHolidayDate = new DateTime($publicHoliday->date);
    $absenceType = AbsenceType::getOneWithMustTakePublicHolidayAsLeaveRequest();
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id'));

    $leaveRequest = LeaveRequest::create([
      'contact_id' => $contactID,
      'type_id' => $absenceType->id,
      'from_date' => $publicHolidayDate->format('YmdHis'),
      'from_date_type' => 1,
      'status_id' => $leaveRequestStatuses['Admin Approved'],
    ], false);
    LeaveBalanceChangeFabricator::fabricateForPublicHolidayLeaveRequest($leaveRequest);

    return $leaveRequest;
  }

}
