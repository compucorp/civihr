<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotification as LeaveRequestMailNotificationService;
use CRM_HRLeaveAndAbsences_Factory_LeaveRequestMailNotificationService as LeaveRequestMailNotificationServiceFactory;

/**
 * Class CRM_HRLeaveAndAbsences_Factory_LeaveRequestMailNotificationServiceTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Factory_LeaveRequestMailNotificationServiceTest extends BaseHeadlessTest  {

  public function testCreate() {
    $service = LeaveRequestMailNotificationServiceFactory::create();

    $this->assertInstanceOf(LeaveRequestMailNotificationService::class, $service);
  }

}
