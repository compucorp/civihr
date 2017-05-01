<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Factory_RequestNotificationTemplate as RequestNotificationTemplateFactory;

/**
 * Class CRM_HRLeaveAndAbsences_Factory_RequestMailNotificationServiceTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Factory_RequestMailNotificationServiceTest extends BaseHeadlessTest  {

  private $leaveRequestTemplateFactory;

  public function setUp() {
    $this->leaveRequestTemplateFactory = new RequestNotificationTemplateFactory();
  }

  /**
   * @dataProvider requestTemplateFactoryDataProvider
   */
  public function testCreate($requestType, $expectedClass) {
    $leaveRequest = new LeaveRequest();
    $leaveRequest->request_type = $requestType;
    $template = $this->leaveRequestTemplateFactory->create($leaveRequest);

    $this->assertInstanceOf($expectedClass, $template);
  }

  public function requestTemplateFactoryDataProvider() {
    return [
      [LeaveRequest::REQUEST_TYPE_LEAVE, 'CRM_HRLeaveAndAbsences_Mail_Template_LeaveRequestNotification'],
      [LeaveRequest::REQUEST_TYPE_SICKNESS, 'CRM_HRLeaveAndAbsences_Mail_Template_SicknessRequestNotification'],
      [LeaveRequest::REQUEST_TYPE_TOIL, 'CRM_HRLeaveAndAbsences_Mail_Template_TOILRequestNotification'],
    ];
  }
}
