<?php

use CRM_HRLeaveAndAbsences_Service_JobContract as JobContractService;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestService;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation as PublicHolidayLeaveRequestCreation;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion as PublicHolidayLeaveRequestDeletion;

/**
 * A factory for the PublicHolidayLeaveRequest service, which can be used
 * to get instances of this service without having to manually create all of
 * its dependencies
 */
class CRM_HRLeaveAndAbsences_Factory_PublicHolidayLeaveRequestService {

  /**
   * Returns a new instance of a PublicHolidayLeaveRequest Service
   *
   * @return \CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequest
   */
  public static function create() {
    $jobContractService = new JobContractService();
    $leaveBalanceChangeService = new LeaveBalanceChangeService();
    $creationLogic = new PublicHolidayLeaveRequestCreation($jobContractService, $leaveBalanceChangeService);
    $deletionLogic = new PublicHolidayLeaveRequestDeletion($jobContractService);

    return new PublicHolidayLeaveRequestService($creationLogic, $deletionLogic);
  }

}
