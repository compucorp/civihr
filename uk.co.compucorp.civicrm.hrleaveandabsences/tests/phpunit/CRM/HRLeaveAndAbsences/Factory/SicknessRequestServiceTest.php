<?php

use CRM_HRLeaveAndAbsences_Service_SicknessRequest as SicknessRequestService;
use CRM_HRLeaveAndAbsences_Factory_SicknessRequestService as SicknessRequestServiceFactory;

/**
 * Class CRM_HRLeaveAndAbsences_Factory_SicknessRequestServiceTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Factory_SicknessRequestServiceTest extends BaseHeadlessTest  {

  public function testCreate() {
    $service = SicknessRequestServiceFactory::create();

    $this->assertInstanceOf(SicknessRequestService::class, $service);
  }
}
