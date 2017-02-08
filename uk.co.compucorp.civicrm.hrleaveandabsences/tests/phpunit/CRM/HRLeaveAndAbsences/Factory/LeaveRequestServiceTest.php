<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequest as LeaveRequestService;
use CRM_HRLeaveAndAbsences_Factory_LeaveRequestService as LeaveRequestServiceFactory;

/**
 * Class CRM_HRLeaveAndAbsences_Factory_LeaveRequestService
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Factory_LeaveRequestServiceTest extends BaseHeadlessTest  {

  public function testCreate() {
    $service = LeaveRequestServiceFactory::create();

    $this->assertInstanceOf(LeaveRequestService::class, $service);
  }

}
