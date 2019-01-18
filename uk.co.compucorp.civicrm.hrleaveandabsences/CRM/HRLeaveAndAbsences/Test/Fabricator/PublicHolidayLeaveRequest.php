<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_Service_JobContract as JobContractService;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation as PublicHolidayLeaveRequestCreation;
use CRM_HRLeaveAndAbsences_Service_LeavePeriodEntitlement as LeavePeriodEntitlementService;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;

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
  public static function fabricate($contactID, PublicHoliday $publicHoliday, $mockBalanceChangeService = null) {
    $leaveBalanceChangeService = new LeaveBalanceChangeService();

    if ($mockBalanceChangeService) {
      $leaveBalanceChangeService = $mockBalanceChangeService;
    }
    $leavePeriodEntitlementService = new LeavePeriodEntitlementService();
    $creationLogic = new PublicHolidayLeaveRequestCreation(
      new JobContractService(),
      $leaveBalanceChangeService,
      $leavePeriodEntitlementService
    );

    $absenceTypes = AbsenceType::getAllWithMustTakePublicHolidayAsLeaveRequest();

    if (empty($absenceTypes)) {
      $absenceTypes[] = AbsenceTypeFabricator::fabricate(['must_take_public_holiday_as_leave' => TRUE]);
    }

    $publicHolidayRequests = [];
    foreach ($absenceTypes as $absenceType) {
      $publicHolidayRequests[] = $creationLogic->createForContact($contactID, $publicHoliday, $absenceType);
    }

    if (count($publicHolidayRequests) == 1) {
      return $publicHolidayRequests[0];
    }

    return $publicHolidayRequests;
  }
}
