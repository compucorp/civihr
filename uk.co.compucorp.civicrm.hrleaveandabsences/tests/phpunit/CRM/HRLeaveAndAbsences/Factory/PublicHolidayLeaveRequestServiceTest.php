<?php

use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequest as PublicHolidayLeaveRequest;
use CRM_HRLeaveAndAbsences_Factory_PublicHolidayLeaveRequestService as PublicHolidayLeaveRequestServiceFactory;

/**
 * Class CRM_HRLeaveAndAbsences_Factory_PublicHolidayLeaveRequestService
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Factory_PublicHolidayLeaveRequestServiceTest extends BaseHeadlessTest  {

  public function testCreate() {
    $service = PublicHolidayLeaveRequestServiceFactory::create();

    $this->assertInstanceOf(PublicHolidayLeaveRequest::class, $service);
  }

}
