<?php

use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrix as LeaveRequestStatusMatrixService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequest as LeaveRequestService;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

/**
 * A factory for the LeaveRequest service, which can be used
 * to get instances of this service without having to manually create all of
 * its dependencies
 */
class CRM_HRLeaveAndAbsences_Factory_LeaveRequestService {

  /**
   * Returns a new instance of a LeaveRequest Service
   *
   * @return \CRM_HRLeaveAndAbsences_Service_LeaveRequest
   */
  public static function create() {
    $leaveBalanceChangeService = new LeaveBalanceChangeService();
    $leaveManagerService = new LeaveManagerService();
    $leaveRequestStatusMatrixService = new LeaveRequestStatusMatrixService($leaveManagerService);
    $leaveRequestRightsService = new LeaveRequestRightsService($leaveManagerService);

    return new LeaveRequestService(
      $leaveBalanceChangeService,
      $leaveRequestStatusMatrixService,
      $leaveRequestRightsService
    );
  }

}
