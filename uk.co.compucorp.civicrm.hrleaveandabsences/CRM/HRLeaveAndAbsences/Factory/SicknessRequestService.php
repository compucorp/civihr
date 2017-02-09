<?php

use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrix as LeaveRequestStatusMatrixService;
use CRM_HRLeaveAndAbsences_Service_SicknessRequest as SicknessRequestService;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

/**
 * A factory for the SicknessRequest service, which can be used
 * to get instances of this service without having to manually create all of
 * its dependencies
 */
class CRM_HRLeaveAndAbsences_Factory_SicknessRequestService {

  /**
   * Returns a new instance of a SicknessRequest Service
   *
   * @return \CRM_HRLeaveAndAbsences_Service_SicknessRequest
   */
  public static function create() {
    $leaveBalanceChangeService = new LeaveBalanceChangeService();
    $leaveManagerService = new LeaveManagerService();
    $leaveRequestStatusMatrixService = new LeaveRequestStatusMatrixService($leaveManagerService);
    $leaveRequestRightsService = new LeaveRequestRightsService($leaveManagerService);

    return new SicknessRequestService(
      $leaveBalanceChangeService,
      $leaveRequestStatusMatrixService,
      $leaveRequestRightsService
    );
  }

}
