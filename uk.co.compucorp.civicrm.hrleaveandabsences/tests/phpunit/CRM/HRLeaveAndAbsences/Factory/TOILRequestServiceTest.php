<?php

use CRM_HRLeaveAndAbsences_Service_TOILRequest as TOILRequestService;
use CRM_HRLeaveAndAbsences_Factory_TOILRequestService as TOILRequestServiceFactory;

/**
 * Class CRM_HRLeaveAndAbsences_Factory_TOILRequestServiceTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Factory_TOILRequestServiceTest extends BaseHeadlessTest  {

  public function testCreate() {
    $service = TOILRequestServiceFactory::create();

    $this->assertInstanceOf(TOILRequestService::class, $service);
  }
}
