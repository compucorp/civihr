<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Factory_RequestNotificationTemplate as RequestNotificationTemplateFactory;

/**
 * Class CRM_HRLeaveAndAbsences_Factory_RequestMailNotificationServiceTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Factory_RequestMailNotificationServiceTest extends BaseHeadlessTest  {


  /**
   * @dataProvider requestTemplateFactoryDataProvider
   */
  public function testCreate($requestType, $expectedClass) {
    $leaveRequest = new LeaveRequest();
    $leaveRequest->request_type = $requestType;
    $template = RequestNotificationTemplateFactory::create($leaveRequest);

    $this->assertInstanceOf($expectedClass, $template);
  }

  public function requestTemplateFactoryDataProvider() {
    return [
      [LeaveRequest::REQUEST_TYPE_LEAVE, 'CRM_HRLeaveAndAbsences_Mail_LeaveRequestNotificationTemplate'],
      [LeaveRequest::REQUEST_TYPE_SICKNESS, 'CRM_HRLeaveAndAbsences_Mail_SicknessRequestNotificationTemplate'],
      [LeaveRequest::REQUEST_TYPE_TOIL, 'CRM_HRLeaveAndAbsences_Mail_TOILRequestNotificationTemplate'],
    ];
  }
}
