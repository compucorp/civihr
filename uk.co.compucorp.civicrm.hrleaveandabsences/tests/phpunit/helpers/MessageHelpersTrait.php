<?php

trait CRM_HRLeaveAndAbsences_MessageHelpersTrait {

  public function createRequestNotificationTemplateFactoryMock() {
    $leaveTemplateMock = $this->createLeaveTemplateMock();
    $templateFactory = $this->getMockBuilder(CRM_HRLeaveAndAbsences_Factory_RequestNotificationTemplate::class)
      ->setMethods(['create'])
      ->getMock();

    $templateFactory->expects($this->once())
      ->method('create')
      ->will($this->returnValue($leaveTemplateMock));

    return $templateFactory;
  }

  private function createLeaveTemplateMock() {
    $leaveTemplate = $this->getMockBuilder(CRM_HRLeaveAndAbsences_Mail_LeaveRequestNotificationTemplate::class)
      ->setMethods(['getTemplateParameters', 'getTemplate'])
      ->setConstructorArgs([new CRM_HRLeaveAndAbsences_Service_LeaveRequestComment()])
      ->getMock();

    $leaveTemplate->expects($this->any())
      ->method('getTemplateParameters');

    $leaveTemplate->expects($this->any())
      ->method('getTemplate');

    return $leaveTemplate;
  }
}
